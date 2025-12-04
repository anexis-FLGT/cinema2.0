<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Session;
use App\Models\Movie;
use App\Models\Hall;

class SessionController extends Controller
{
    /**
     * Отображение списка сеансов
     */
    public function index()
    {
        $sessions = Session::with(['movie', 'hall'])
            ->orderBy('date_time_session', 'asc')
            ->paginate(10);
        
        $movies = Movie::all();
        $halls = Hall::all();

        return view('admin.sessions', compact('sessions', 'movies', 'halls'));
    }

    /**
     * Сохранение нового сеанса
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id_movie',
            'hall_id' => 'required|exists:halls,id_hall',
            'date_time_session' => 'required|date|after_or_equal:now',
        ]);

        Session::create([
            'movie_id' => $validated['movie_id'],
            'hall_id' => $validated['hall_id'],
            'date_time_session' => $validated['date_time_session'],
        ]);

        return redirect()->route('admin.sessions.index')->with('success', 'Сеанс добавлен.');
    }

    /**
     * Обновление существующего сеанса
     */
    public function update(Request $request, $id)
    {
        $session = Session::findOrFail($id);

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id_movie',
            'hall_id' => 'required|exists:halls,id_hall',
            'date_time_session' => 'required|date',
        ]);

        $session->update([
            'movie_id' => $validated['movie_id'],
            'hall_id' => $validated['hall_id'],
            'date_time_session' => $validated['date_time_session'],
        ]);

        return redirect()->route('admin.sessions.index')->with('success', 'Сеанс обновлён.');
    }

    /**
     * Удаление сеанса
     */
    public function destroy($id)
    {
        $session = Session::findOrFail($id);
        $session->delete();

        return redirect()->route('admin.sessions.index')->with('success', 'Сеанс удалён.');
    }
}

