<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Session;
use App\Models\Seat;
use YooKassa\Client;
use YooKassa\Model\CurrencyCode;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Получить клиент ЮKassa
     */
    private function getClient()
    {
        $client = new Client();
        $client->setAuth(
            config('yookassa.shop_id'),
            config('yookassa.secret_key')
        );
        return $client;
    }

    /**
     * Конвертировать статус из английского (от ЮKassa) в русский
     */
    private function convertStatusToRussian($status)
    {
        $statusMap = [
            'pending' => 'ожидание',
            'succeeded' => 'оплачено',
            'canceled' => 'отменено',
            'waiting_for_capture' => 'ожидает_подтверждения',
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Страница подтверждения - автоматически отправляет POST-запрос
     */
    public function confirm()
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        $pendingBooking = session('pending_booking');

        if (!$pendingBooking || !isset($pendingBooking['session_id']) || !isset($pendingBooking['seat_ids'])) {
            return redirect()->route('home')->with('error', 'Данные бронирования не найдены');
        }

        // Создаем представление с автоматической отправкой формы
        return view('payment.confirm', [
            'session_id' => $pendingBooking['session_id'],
            'seat_ids' => $pendingBooking['seat_ids'],
        ]);
    }

    /**
     * Создать платеж в ЮKassa
     */
    public function create(Request $request)
    {
        // Автоматически освобождаем истекшие бронирования
        Booking::expireOldBookings();
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        // Получаем данные из запроса или из сессии
        $sessionId = $request->input('session_id');
        $seatIds = $request->input('seat_ids', []);

        // Если данных нет в запросе, берем из сессии и добавляем в request
        if (!$sessionId || empty($seatIds)) {
            $pendingBooking = session('pending_booking');
            if ($pendingBooking) {
                $request->merge([
                    'session_id' => $pendingBooking['session_id'] ?? null,
                    'seat_ids' => $pendingBooking['seat_ids'] ?? [],
                ]);
            }
        }

        $validated = $request->validate([
            'session_id' => 'required|exists:cinema_sessions,id_session',
            'seat_ids' => 'required|array|min:1|max:7',
            'seat_ids.*' => 'required|exists:seats,id_seat',
        ], [], [
            'session_id' => 'сеанс',
            'seat_ids' => 'места',
        ]);

        // Очищаем данные из сессии после использования
        session()->forget('pending_booking');

        // Получаем данные сеанса
        $session = Session::with(['movie', 'hall'])->findOrFail($validated['session_id']);
        
        // Проверяем, что у сеанса есть привязанный зал
        if (!$session->hall_id || !$session->hall) {
            return back()->with('error', 'Для данного сеанса не указан зал');
        }
        
        $hallId = $session->hall_id;
        $seatIds = $validated['seat_ids'];
        $seatCount = count($seatIds);

        // Проверяем, что все места принадлежат залу сеанса
        $seats = Seat::whereIn('id_seat', $seatIds)->get();
        foreach ($seats as $seat) {
            if ($seat->hall_id != $hallId) {
                return back()->with('error', 'Одно из выбранных мест не принадлежит залу сеанса');
            }
        }

        // Проверяем, не забронированы ли места уже (исключаем истекшие)
        $paymentTimeoutMinutes = 15; // Время на оплату в минутах
        $expirationTime = Carbon::now()->subMinutes($paymentTimeoutMinutes);
        
        $existingBookings = Booking::where('session_id', $validated['session_id'])
            ->whereIn('seat_id', $seatIds)
            ->whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->where(function($query) use ($expirationTime) {
                // Исключаем истекшие бронирования со статусом "ожидание"
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', '!=', 'ожидание');
                })
                ->orWhere('created_ad', '>', $expirationTime);
            })
            ->pluck('seat_id')
            ->toArray();

        if (!empty($existingBookings)) {
            return back()->with('error', 'Одно или несколько мест уже забронированы');
        }

        // Проверяем статус мест
        $bookedSeats = $seats->filter(function($seat) {
            return $seat->status === 'Забронировано';
        });

        if ($bookedSeats->isNotEmpty()) {
            return back()->with('error', 'Одно или несколько мест уже забронированы');
        }

        // Рассчитываем стоимость
        $amountPerSeat = config('yookassa.ticket_price', 500.00);
        $totalAmount = $seatCount * $amountPerSeat;

        // Создаем временные бронирования (pending) для резервирования мест
        $pendingBookings = [];
        foreach ($seatIds as $seatId) {
            $booking = Booking::create([
                'show_date' => $session->date_time_session->format('Y-m-d'),
                'show_time' => $session->date_time_session->format('H:i:s'),
                'created_ad' => now(),
                'user_id' => Auth::id(),
                'movie_id' => $session->movie_id,
                'session_id' => $validated['session_id'],
                'hall_id' => $hallId,
                'seat_id' => $seatId,
            ]);
            
            // Создаем payment для этого бронирования
            Payment::create([
                'booking_id' => $booking->id_booking,
                'payment_status' => 'ожидание',
                'amount' => $amountPerSeat,
            ]);
            
            $pendingBookings[] = $booking;
        }

        try {
            $client = $this->getClient();
            
            // Формируем описание платежа
            $description = "Билеты на фильм: {$session->movie->movie_title}. Количество: {$seatCount} шт.";
            
            // Генерируем уникальный ключ идемпотентности
            $idempotenceKey = uniqid('', true);
            
            // Создаем платеж
            $payment = $client->createPayment([
                'amount' => [
                    'value' => number_format($totalAmount, 2, '.', ''),
                    'currency' => CurrencyCode::RUB,
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => url(config('yookassa.return_url')),
                ],
                'capture' => true,
                'description' => $description,
                'metadata' => [
                    'booking_ids' => implode(',', array_map(fn($b) => $b->id_booking, $pendingBookings)),
                    'user_id' => Auth::id(),
                    'order_id' => 'order_' . uniqid(),
                ],
            ], $idempotenceKey);

            // Сохраняем payment_id в payments
            $paymentId = $payment->getId();
            foreach ($pendingBookings as $booking) {
                $bookingPayment = $booking->payment;
                if ($bookingPayment) {
                    $bookingPayment->payment_id = $paymentId;
                    $bookingPayment->save();
                }
            }

            // Сохраняем payment_id в сессии
            session(['pending_payment_id' => $paymentId]);

            // Логируем создание платежа
            Log::info('YooKassa payment created', [
                'payment_id' => $paymentId,
                'amount' => $totalAmount,
                'booking_ids' => array_map(fn($b) => $b->id_booking, $pendingBookings),
            ]);

            // Перенаправляем на страницу оплаты
            $confirmationUrl = $payment->getConfirmation()->getConfirmationUrl();
            return redirect($confirmationUrl);

        } catch (\Exception $e) {
            // Удаляем временные бронирования при ошибке
            foreach ($pendingBookings as $booking) {
                $booking->delete();
            }

            Log::error('YooKassa payment creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Ошибка при создании платежа: ' . $e->getMessage());
        }
    }

    /**
     * Callback после оплаты (возврат с ЮKassa)
     */
    public function success(Request $request)
    {
        $paymentId = session('pending_payment_id') ?? $request->input('payment_id');

        if (!$paymentId) {
            return redirect()->route('home')->with('error', 'Платеж не найден');
        }

        try {
            $client = $this->getClient();
            
            // Получаем информацию о платеже
            $payment = $client->getPaymentInfo($paymentId);
            
            // Находим бронирования по payment_id через payments
            $payments = Payment::where('payment_id', $paymentId)->with('booking')->get();
            $bookings = $payments->pluck('booking')->filter();

            if ($bookings->isEmpty()) {
                return redirect()->route('home')->with('error', 'Бронирования не найдены');
            }

            $status = $payment->getStatus();
            $russianStatus = $this->convertStatusToRussian($status);
            
            // Сохраняем movie_id для возможного редиректа
            $movieId = $bookings->first()->movie_id ?? null;
            
            // Обновляем статус оплаты
            $deletedBookingIds = [];
            foreach ($payments as $paymentRecord) {
                $paymentRecord->payment_status = $russianStatus;
                $paymentRecord->save();
                
                $booking = $paymentRecord->booking;
                if (!$booking) {
                    continue;
                }
                
                if ($status === 'succeeded') {
                    // Обновляем статус места
                    $seat = Seat::find($booking->seat_id);
                    if ($seat) {
                        $seat->status = 'Забронировано';
                        $seat->save();
                    }
                } elseif ($status === 'canceled') {
                    // Удаляем бронирование при отмене
                    $deletedBookingIds[] = $booking->id_booking;
                    $booking->delete();
                }
            }

            session()->forget('pending_payment_id');

            // Фильтруем не удаленные бронирования
            $activeBookings = $bookings->filter(function($booking) use ($deletedBookingIds) {
                return !in_array($booking->id_booking, $deletedBookingIds);
            });

            if ($status === 'succeeded') {
                if ($activeBookings->isEmpty()) {
                    return redirect()->route('home')->with('error', 'Бронирования не найдены');
                }
                
                // Перенаправляем на страницу успеха
                $bookingIds = $activeBookings->pluck('id_booking')->toArray();
                session(['last_booking_ids' => $bookingIds]);
                
                return redirect()->route('booking.success', $activeBookings->first()->id_booking)
                    ->with('success', 'Оплата успешно завершена!');
            } elseif ($status === 'canceled') {
                // При отмене возвращаем на страницу бронирования
                if ($movieId) {
                    return redirect()->route('booking.show', $movieId)
                        ->with('error', 'Платеж был отменен');
                }
                return redirect()->route('home')->with('error', 'Платеж был отменен');
            } else {
                // Для других статусов
                if ($movieId) {
                    return redirect()->route('booking.show', $movieId)
                        ->with('error', 'Платеж обрабатывается. Статус: ' . $status);
                }
                return redirect()->route('home')->with('error', 'Платеж обрабатывается');
            }
        } catch (\Exception $e) {
            Log::error('YooKassa callback error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('home')->with('error', 'Ошибка при проверке платежа');
        }
    }

    /**
     * Webhook для обработки уведомлений от ЮKassa
     */
    public function webhook(Request $request)
    {
        // Логируем получение webhook
        Log::info('YooKassa webhook received', [
            'data' => $request->all(),
            'raw' => $request->getContent(),
        ]);

        try {
            $data = $request->all();
            
            if (empty($data)) {
                $input = $request->getContent();
                $data = json_decode($input, true);
            }

            if (empty($data) || !isset($data['event'])) {
                Log::warning('YooKassa webhook: invalid data', ['data' => $data]);
                return response()->json(['error' => 'Invalid data'], 400);
            }

            $event = $data['event'];
            $payment = $data['object'] ?? null;

            if (!$payment || !isset($payment['id'])) {
                Log::warning('YooKassa webhook: payment not found', ['data' => $data]);
                return response()->json(['error' => 'Payment not found'], 400);
            }

            $paymentId = $payment['id'];
            $status = $payment['status'] ?? null;
            $metadata = $payment['metadata'] ?? [];

            Log::info('YooKassa webhook processing', [
                'event' => $event,
                'payment_id' => $paymentId,
                'status' => $status,
            ]);

            // Находим payments по payment_id
            $payments = Payment::where('payment_id', $paymentId)->with('booking')->get();
            $bookings = $payments->pluck('booking')->filter();

            if ($bookings->isEmpty()) {
                Log::warning('YooKassa webhook: bookings not found', ['payment_id' => $paymentId]);
                return response()->json(['error' => 'Bookings not found'], 404);
            }

            // Обрабатываем в зависимости от события
            switch ($event) {
                case 'payment.succeeded':
                    $this->handleSuccessfulPayment($payments);
                    break;

                case 'payment.canceled':
                    $this->handleCanceledPayment($payments);
                    break;

                case 'payment.waiting_for_capture':
                    $this->handleWaitingPayment($payments);
                    break;

                default:
                    Log::info('YooKassa webhook: unhandled event', ['event' => $event]);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('YooKassa webhook error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка успешного платежа
     */
    private function handleSuccessfulPayment($payments)
    {
        foreach ($payments as $payment) {
            $payment->payment_status = 'оплачено';
            $payment->save();

            $booking = $payment->booking;
            if ($booking) {
                // Обновляем статус места
                $seat = Seat::find($booking->seat_id);
                if ($seat) {
                    $seat->status = 'Забронировано';
                    $seat->save();
                }
            }
        }

        Log::info('YooKassa payment succeeded', [
            'payment_ids' => $payments->pluck('id_payment')->toArray(),
        ]);
    }

    /**
     * Обработка отмененного платежа
     */
    private function handleCanceledPayment($payments)
    {
        foreach ($payments as $payment) {
            $payment->payment_status = 'отменено';
            $payment->save();

            $booking = $payment->booking;
            if ($booking) {
                $booking->delete();
            }
        }

        Log::info('YooKassa payment canceled', [
            'payment_ids' => $payments->pluck('id_payment')->toArray(),
        ]);
    }

    /**
     * Обработка платежа в ожидании
     */
    private function handleWaitingPayment($payments)
    {
        foreach ($payments as $payment) {
            $payment->payment_status = 'ожидает_подтверждения';
            $payment->save();
        }

        Log::info('YooKassa payment waiting', [
            'payment_ids' => $payments->pluck('id_payment')->toArray(),
        ]);
    }

    /**
     * Вернуться к оплате бронирования
     */
    public function retryPayment($bookingId)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Необходимо войти в систему');
        }

        try {
            // Находим бронирование
            $booking = Booking::with('payment')->findOrFail($bookingId);

            // Проверяем, что бронирование принадлежит текущему пользователю
            if ($booking->user_id != Auth::id()) {
                return redirect()->route('user.dashboard')
                    ->with('error', 'У вас нет прав для доступа к этому бронированию');
            }

            // Проверяем наличие платежа
            if (!$booking->payment) {
                return redirect()->route('user.dashboard')
                    ->with('error', 'Платеж для бронирования не найден');
            }

            $payment = $booking->payment;

            // Проверяем статус платежа
            if ($payment->payment_status !== 'ожидание') {
                return redirect()->route('user.dashboard')
                    ->with('error', 'Это бронирование уже оплачено или отменено');
            }

            // Проверяем наличие payment_id
            if (!$payment->payment_id) {
                return redirect()->route('user.dashboard')
                    ->with('error', 'ID платежа не найден. Пожалуйста, создайте новое бронирование');
            }

            try {
                $client = $this->getClient();
                
                // Получаем информацию о платеже от ЮKassa
                $yookassaPayment = $client->getPaymentInfo($payment->payment_id);
                
                // Получаем URL для оплаты
                $confirmation = $yookassaPayment->getConfirmation();
                
                if (!$confirmation || !$confirmation->getConfirmationUrl()) {
                    return redirect()->route('user.dashboard')
                        ->with('error', 'Не удалось получить ссылку на оплату. Пожалуйста, обратитесь в поддержку');
                }

                $paymentUrl = $confirmation->getConfirmationUrl();

                // Сохраняем payment_id в сессии для обработки после возврата
                session(['pending_payment_id' => $payment->payment_id]);

                // Перенаправляем на страницу оплаты
                return redirect()->away($paymentUrl);

            } catch (\Exception $e) {
                Log::error('YooKassa retry payment error', [
                    'booking_id' => $bookingId,
                    'payment_id' => $payment->payment_id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return redirect()->route('user.dashboard')
                    ->with('error', 'Ошибка при получении ссылки на оплату: ' . $e->getMessage());
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Бронирование не найдено');
        } catch (\Exception $e) {
            Log::error('Retry payment error', [
                'booking_id' => $bookingId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('user.dashboard')
                ->with('error', 'Произошла ошибка при обработке запроса');
        }
    }
}

