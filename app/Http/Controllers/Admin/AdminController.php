<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $moviesCount = DB::table('movies')->count();
        $sessionsCount = DB::table('cinema_sessions')->count();
        $usersCount = DB::table('cinema_users')->count();
        
        // Активные бронирования (не отмененные)
        $activeBookingsCount = Booking::whereHas('payment', function($query) {
                $query->where('payment_status', '!=', 'отменено');
            })
            ->orWhereDoesntHave('payment')
            ->count();
        
        // Отмененные бронирования
        $cancelledBookingsCount = Booking::whereHas('payment', function($query) {
                $query->where('payment_status', '=', 'отменено');
            })
            ->count();
        
        // Выручка за периоды (только оплаченные платежи)
        $revenueToday = Payment::where('payment_status', 'оплачено')
            ->whereDate('created_at', today())
            ->sum('amount');
        
        $revenueWeek = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');
        
        $revenueMonth = Payment::where('payment_status', 'оплачено')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        
        // Общая выручка за все время
        $totalRevenue = Payment::where('payment_status', 'оплачено')
            ->sum('amount');

        $latestMovies = DB::table('movies')
            ->orderByDesc('id_movie')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'moviesCount', 'sessionsCount', 'usersCount', 
            'activeBookingsCount', 'cancelledBookingsCount', 
            'revenueToday', 'revenueWeek', 'revenueMonth', 'totalRevenue',
            'latestMovies'
        ));
    }

    public function movies()
    {
        return view('admin.movies');
    }

    public function sessions()
    {
        return view('admin.sessions');
    }

    public function users()
    {
        return view('admin.users');
    }

    public function usersList(Request $request)
    {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = DB::table('cinema_users')->count();
        $users = DB::table('cinema_users')
            ->orderByDesc('id_user')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $html = view('admin.partials.users_table', compact('users'))->render();

        return response()->json([
            'html' => $html,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ]);
    }
}
