<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $moviesCount = DB::table('movies')->count();
        $sessionsCount = DB::table('cinema_sessions')->count();
        $usersCount = DB::table('cinema_users')->count();
        $bookingsCount = DB::table('bookings')->count();

        $latestMovies = DB::table('movies')
            ->orderByDesc('id_movie')
            ->limit(6)
            ->get();

        return view('admin.dashboard', compact(
            'moviesCount', 'sessionsCount', 'usersCount', 'bookingsCount', 'latestMovies'
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
