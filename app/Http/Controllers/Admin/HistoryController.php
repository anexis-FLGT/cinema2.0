<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Отображение истории всех операций
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'session.movie', 'session.hall', 'seat', 'payment'])
            ->orderBy('created_ad', 'desc');

        // Фильтрация по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Фильтрация по статусу платежа
        if ($request->filled('payment_status')) {
            $query->whereHas('payment', function($q) use ($request) {
                $q->where('payment_status', $request->input('payment_status'));
            });
        }

        // Фильтрация по дате создания
        if ($request->filled('date_from')) {
            $query->whereDate('created_ad', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_ad', '<=', $request->input('date_to'));
        }

        // Поиск по ФИО пользователя
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        // Пагинация
        $bookings = $query->paginate(20)->withQueryString();

        // Получаем всех пользователей для фильтра
        $users = User::orderBy('last_name')->get();

        // Статистика
        $totalBookings = Booking::count();
        $totalPayments = Payment::count();
        $totalRevenue = Payment::where('payment_status', 'оплачено')->sum('amount');
        $paidBookings = Payment::where('payment_status', 'оплачено')->count();
        $cancelledBookings = Payment::where('payment_status', 'отменено')->count();

        return view('admin.history.index', compact('bookings', 'users', 'totalBookings', 'totalPayments', 'totalRevenue', 'paidBookings', 'cancelledBookings'));
    }

    /**
     * Детальная информация о бронировании
     */
    public function show($id)
    {
        $booking = Booking::with(['user', 'session.movie', 'session.hall', 'seat', 'payment'])
            ->findOrFail($id);

        return view('admin.history.show', compact('booking'));
    }
}
