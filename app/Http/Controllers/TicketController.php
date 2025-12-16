<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class TicketController extends Controller
{
    /**
     * Генерация PDF билета
     */
    public function generatePdf($bookingId)
    {
        $user = Auth::user();

        // Поддержка списка id через запятую
        $ids = collect(explode(',', (string) $bookingId))
            ->filter(fn($id) => ctype_digit(trim($id)))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            abort(404);
        }
        
        // Получаем бронирования пользователя
        $bookings = Booking::with(['session.movie', 'session.hall', 'seat', 'user', 'payment'])
            ->whereIn('id_booking', $ids)
            ->where('user_id', $user->id_user)
            ->get();

        // Проверяем, что все бронирования найдены
        if ($bookings->count() !== $ids->count()) {
            abort(404);
        }

        // Проверяем оплату для каждого билета
        $unpaid = $bookings->filter(function($booking) {
            return !$booking->payment || $booking->payment->payment_status !== 'оплачено';
        });

        if ($unpaid->isNotEmpty()) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Все билеты должны быть оплачены для печати.');
        }

        // Генерируем PDF с несколькими билетами
        $pdf = PDF::loadView('tickets.pdf', [
            'bookings' => $bookings,
        ]);
        
        // Имя файла
        $filename = 'tickets_' . $ids->implode('_') . '_' . date('YmdHis') . '.pdf';
        
        // Возвращаем PDF для скачивания
        return $pdf->download($filename);
    }
}

