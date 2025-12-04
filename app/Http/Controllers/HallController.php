<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HallController extends Controller
{
    public function index()
    {
        // Получаем все залы из таблицы halls
        $halls = DB::table('halls')->get();

        return view('halls', compact('halls'));
    }
}
