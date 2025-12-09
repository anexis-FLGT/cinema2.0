<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\Session;
use App\Models\User;
use App\Models\Hall;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ReportController extends Controller
{
    /**
     * Главная страница отчетов
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Отчет по выручке
     */
    public function revenue(Request $request)
    {
        $period = $request->input('period', 'month'); // today, week, month, year, custom
        $startDate = null;
        $endDate = null;

        // Если выбраны кастомные даты
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        // Общая выручка за период
        $totalRevenue = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Выручка по фильмам
        $revenueByMovie = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as bookings_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('revenue')
            ->get();

        // Выручка по залам
        $revenueByHall = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('halls', 'cinema_sessions.hall_id', '=', 'halls.id_hall')
            ->select('halls.id_hall', 'halls.hall_name', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as bookings_count'))
            ->groupBy('halls.id_hall', 'halls.hall_name')
            ->orderByDesc('revenue')
            ->get();

        // Динамика выручки по дням
        $dailyRevenue = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dateFrom = $request->input('date_from', $startDate->format('Y-m-d'));
        $dateTo = $request->input('date_to', $endDate->format('Y-m-d'));

        return view('admin.reports.revenue', compact('totalRevenue', 'revenueByMovie', 'revenueByHall', 'dailyRevenue', 'period', 'startDate', 'endDate', 'dateFrom', 'dateTo'));
    }

    /**
     * Отчет по посещаемости
     */
    public function attendance(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;

        // Если выбраны кастомные даты
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        // Общее количество проданных билетов
        $totalTickets = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Заполняемость залов
        $hallOccupancy = Session::whereBetween('date_time_session', [$startDate, $endDate])
            ->with(['hall', 'bookings' => function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'оплачено');
                });
            }])
            ->get()
            ->map(function($session) {
                $totalSeats = $session->hall->seats()->count();
                $bookedSeats = $session->bookings->count();
                $occupancy = $totalSeats > 0 ? ($bookedSeats / $totalSeats) * 100 : 0;
                
                return [
                    'session_id' => $session->id_session,
                    'movie_title' => $session->movie->movie_title ?? 'Неизвестно',
                    'hall_name' => $session->hall->hall_name ?? 'Неизвестно',
                    'date_time' => $session->date_time_session,
                    'booked_seats' => $bookedSeats,
                    'total_seats' => $totalSeats,
                    'occupancy' => round($occupancy, 2)
                ];
            })
            ->sortByDesc('occupancy')
            ->take(20);

        // Самые популярные сеансы
        $popularSessions = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('cinema_sessions.id_session', 'movies.movie_title', 'cinema_sessions.date_time_session', DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('cinema_sessions.id_session', 'movies.movie_title', 'cinema_sessions.date_time_session')
            ->orderByDesc('tickets_count')
            ->take(20)
            ->get();

        $dateFrom = $request->input('date_from', $startDate->format('Y-m-d'));
        $dateTo = $request->input('date_to', $endDate->format('Y-m-d'));

        return view('admin.reports.attendance', compact('totalTickets', 'hallOccupancy', 'popularSessions', 'period', 'startDate', 'endDate', 'dateFrom', 'dateTo'));
    }

    /**
     * Отчет по фильмам
     */
    public function movies(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;

        // Если выбраны кастомные даты
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        // Самые популярные фильмы (по количеству билетов)
        $popularMovies = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('tickets_count')
            ->take(20)
            ->get();

        // Самые прибыльные фильмы
        $profitableMovies = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('revenue')
            ->take(20)
            ->get();

        // Средняя заполняемость по фильмам
        $movieOccupancy = Session::whereBetween('date_time_session', [$startDate, $endDate])
            ->with(['movie', 'hall', 'bookings' => function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'оплачено');
                });
            }])
            ->get()
            ->groupBy('movie_id')
            ->map(function($sessions, $movieId) {
                $movie = $sessions->first()->movie;
                $totalSessions = $sessions->count();
                $totalOccupancy = 0;
                
                foreach ($sessions as $session) {
                    $totalSeats = $session->hall->seats()->count();
                    $bookedSeats = $session->bookings->count();
                    if ($totalSeats > 0) {
                        $totalOccupancy += ($bookedSeats / $totalSeats) * 100;
                    }
                }
                
                $avgOccupancy = $totalSessions > 0 ? $totalOccupancy / $totalSessions : 0;
                
                return [
                    'movie_id' => $movieId,
                    'movie_title' => $movie->movie_title ?? 'Неизвестно',
                    'sessions_count' => $totalSessions,
                    'avg_occupancy' => round($avgOccupancy, 2)
                ];
            })
            ->sortByDesc('avg_occupancy')
            ->take(20);

        $dateFrom = $request->input('date_from', $startDate->format('Y-m-d'));
        $dateTo = $request->input('date_to', $endDate->format('Y-m-d'));

        return view('admin.reports.movies', compact('popularMovies', 'profitableMovies', 'movieOccupancy', 'period', 'startDate', 'endDate', 'dateFrom', 'dateTo'));
    }

    /**
     * Экспорт отчета по выручке в PDF
     */
    public function revenuePdf(Request $request)
    {
        // Используем ту же логику, что и в методе revenue
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        $totalRevenue = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $revenueByMovie = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as bookings_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('revenue')
            ->get();

        $revenueByHall = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('halls', 'cinema_sessions.hall_id', '=', 'halls.id_hall')
            ->select('halls.id_hall', 'halls.hall_name', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as bookings_count'))
            ->groupBy('halls.id_hall', 'halls.hall_name')
            ->orderByDesc('revenue')
            ->get();

        $dailyRevenue = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $pdf = PDF::loadView('admin.reports.pdf.revenue', compact('totalRevenue', 'revenueByMovie', 'revenueByHall', 'dailyRevenue', 'period', 'startDate', 'endDate'));
        $filename = 'report_revenue_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Экспорт отчета по посещаемости в PDF
     */
    public function attendancePdf(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        $totalTickets = Payment::where('payment_status', 'оплачено')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $hallOccupancy = Session::whereBetween('date_time_session', [$startDate, $endDate])
            ->with(['hall', 'bookings' => function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'оплачено');
                });
            }])
            ->get()
            ->map(function($session) {
                $totalSeats = $session->hall->seats()->count();
                $bookedSeats = $session->bookings->count();
                $occupancy = $totalSeats > 0 ? ($bookedSeats / $totalSeats) * 100 : 0;
                
                return [
                    'session_id' => $session->id_session,
                    'movie_title' => $session->movie->movie_title ?? 'Неизвестно',
                    'hall_name' => $session->hall->hall_name ?? 'Неизвестно',
                    'date_time' => $session->date_time_session,
                    'booked_seats' => $bookedSeats,
                    'total_seats' => $totalSeats,
                    'occupancy' => round($occupancy, 2)
                ];
            })
            ->sortByDesc('occupancy')
            ->take(20);

        $popularSessions = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('cinema_sessions.id_session', 'movies.movie_title', 'cinema_sessions.date_time_session', DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('cinema_sessions.id_session', 'movies.movie_title', 'cinema_sessions.date_time_session')
            ->orderByDesc('tickets_count')
            ->take(20)
            ->get();

        $pdf = PDF::loadView('admin.reports.pdf.attendance', compact('totalTickets', 'hallOccupancy', 'popularSessions', 'period', 'startDate', 'endDate'));
        $filename = 'report_attendance_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Экспорт отчета по фильмам в PDF
     */
    public function moviesPdf(Request $request)
    {
        $period = $request->input('period', 'month');
        $startDate = null;
        $endDate = null;

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $startDate = Carbon::parse($request->input('date_from'))->startOfDay();
            $endDate = Carbon::parse($request->input('date_to'))->endOfDay();
            $period = 'custom';
        } else {
            switch ($period) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        $popularMovies = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('tickets_count')
            ->take(20)
            ->get();

        $profitableMovies = Payment::where('payment_status', 'оплачено')
            ->whereBetween('payments.created_at', [$startDate, $endDate])
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id_booking')
            ->join('cinema_sessions', 'bookings.session_id', '=', 'cinema_sessions.id_session')
            ->join('movies', 'cinema_sessions.movie_id', '=', 'movies.id_movie')
            ->select('movies.id_movie', 'movies.movie_title', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(payments.id_payment) as tickets_count'))
            ->groupBy('movies.id_movie', 'movies.movie_title')
            ->orderByDesc('revenue')
            ->take(20)
            ->get();

        $movieOccupancy = Session::whereBetween('date_time_session', [$startDate, $endDate])
            ->with(['movie', 'hall', 'bookings' => function($query) {
                $query->whereHas('payment', function($q) {
                    $q->where('payment_status', 'оплачено');
                });
            }])
            ->get()
            ->groupBy('movie_id')
            ->map(function($sessions, $movieId) {
                $movie = $sessions->first()->movie;
                $totalSessions = $sessions->count();
                $totalOccupancy = 0;
                
                foreach ($sessions as $session) {
                    $totalSeats = $session->hall->seats()->count();
                    $bookedSeats = $session->bookings->count();
                    if ($totalSeats > 0) {
                        $totalOccupancy += ($bookedSeats / $totalSeats) * 100;
                    }
                }
                
                $avgOccupancy = $totalSessions > 0 ? $totalOccupancy / $totalSessions : 0;
                
                return [
                    'movie_id' => $movieId,
                    'movie_title' => $movie->movie_title ?? 'Неизвестно',
                    'sessions_count' => $totalSessions,
                    'avg_occupancy' => round($avgOccupancy, 2)
                ];
            })
            ->sortByDesc('avg_occupancy')
            ->take(20);

        $pdf = PDF::loadView('admin.reports.pdf.movies', compact('popularMovies', 'profitableMovies', 'movieOccupancy', 'period', 'startDate', 'endDate'));
        $filename = 'report_movies_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}

