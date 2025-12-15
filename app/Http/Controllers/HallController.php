<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Hall;

class HallController extends Controller
{
    public function index()
    {
        // Получаем все залы
        $halls = Hall::all();

        return view('halls', compact('halls'));
    }

    /**
     * Детальный просмотр зала со схемой мест
     */
    public function show($id)
    {
        $hall = Hall::with('seats')->findOrFail($id);
        
        // Группируем места по рядам для отображения
        $seatsByRow = $hall->seats->groupBy('row_number');
        
        // Сортируем ряды
        $sortedRows = $seatsByRow->keys()->sort(function($a, $b) {
            return (int)$a - (int)$b;
        });

        return view('halls.show', compact('hall', 'seatsByRow', 'sortedRows'));
    }
}
