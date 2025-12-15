<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hall;
use App\Models\Seat;
use App\Models\Session;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HallController extends Controller
{
    /**
     * Отображение списка залов
     */
    public function index()
    {
        $halls = Hall::withCount('seats')->paginate(10);
        return view('admin.halls.index', compact('halls'));
    }

    /**
     * Сохранение нового зала
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'hall_name' => 'required|string|max:255',
                'type_hall' => 'required|in:большой,средний,малый',
                'description_hall' => 'nullable|string',
                'hall_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'seats_data' => 'required|string', // JSON строка с данными о местах
            ], [
                'hall_photo.required' => 'Поле "Фото зала" обязательно для заполнения.',
                'seats_data.required' => 'Необходимо создать схему зала. Нажмите "Сгенерировать схему" и настройте места.',
            ]);

            // Дополнительная проверка JSON
            $seatsData = json_decode($validated['seats_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($seatsData) || empty($seatsData)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Схема зала не может быть пустой. Создайте хотя бы одно место.');
            }

            DB::transaction(function () use ($validated, $request, $seatsData) {
                // Загрузка фото зала
                $hallPhotoPath = null;
                if ($request->hasFile('hall_photo')) {
                    $hallsDir = public_path('images/halls');
                    if (!file_exists($hallsDir)) {
                        mkdir($hallsDir, 0755, true);
                    }
                    $hallPhotoPath = '/images/halls/' . $request->file('hall_photo')->hashName();
                    $request->file('hall_photo')->move($hallsDir, basename($hallPhotoPath));
                }

                // Создание зала
                $hall = Hall::create([
                    'hall_name' => $validated['hall_name'],
                    'type_hall' => $validated['type_hall'],
                    'description_hall' => $validated['description_hall'] ?? null,
                    'hall_photo' => $hallPhotoPath,
                    'quantity_seats' => 0, // Будет обновлено после создания мест
                ]);

                // Используем уже распарсенные данные о местах
                $totalSeats = 0;

                // Создаем места
                foreach ($seatsData as $seat) {
                    if (isset($seat['row_number']) && isset($seat['seat_number'])) {
                        Seat::create([
                            'hall_id' => $hall->id_hall,
                            'row_number' => $seat['row_number'],
                            'seat_number' => $seat['seat_number'],
                            'status' => 'Свободно',
                        ]);
                        $totalSeats++;
                    }
                }

                // Обновляем количество мест
                $hall->quantity_seats = $totalSeats;
                $hall->save();
            });

            return redirect()->route('admin.halls.index')->with('success', 'Зал добавлен.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Ошибка валидации данных. Проверьте правильность заполнения полей.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Произошла ошибка при добавлении зала: ' . $e->getMessage());
        }
    }

    /**
     * Обновление существующего зала
     */
    public function update(Request $request, $id)
    {
        try {
            $hall = Hall::findOrFail($id);

            // Проверяем, есть ли сеансы с этим залом
            $sessionsCount = Session::where('hall_id', $hall->id_hall)->count();
            if ($sessionsCount > 0) {
                return redirect()->route('admin.halls.index')
                    ->with('error', "Невозможно изменить схему зала! На этот зал запланировано {$sessionsCount} сеансов.");
            }

            // Валидация с условными правилами для фото
            $rules = [
                'hall_name' => 'required|string|max:255',
                'type_hall' => 'required|in:большой,средний,малый',
                'description_hall' => 'nullable|string',
                'seats_data' => 'required|json',
            ];
            
            // Если у зала нет фото, то фото обязательно
            if (!$hall->hall_photo) {
                $rules['hall_photo'] = 'required|image|mimes:jpg,jpeg,png|max:2048';
            } else {
                // Если фото есть, то новое фото опционально
                // Правила image и mimes применяются только если файл действительно загружен
                if ($request->hasFile('hall_photo')) {
                    $rules['hall_photo'] = 'image|mimes:jpg,jpeg,png|max:2048';
                } else {
                    $rules['hall_photo'] = 'nullable';
                }
            }
            
            $validated = $request->validate($rules, [
                'hall_photo.required' => 'Поле "Фото зала" обязательно для заполнения.',
                'hall_photo.image' => 'Файл должен быть изображением.',
                'hall_photo.mimes' => 'Фото должно быть в формате: jpg, jpeg, png.',
                'hall_photo.max' => 'Размер фото не должен превышать 2 МБ.',
            ]);

            DB::transaction(function () use ($hall, $validated, $request) {
            // Загрузка нового фото, если загружено
            if ($request->hasFile('hall_photo')) {
                // Удаляем старое фото, если есть
                if ($hall->hall_photo && file_exists(public_path($hall->hall_photo))) {
                    unlink(public_path($hall->hall_photo));
                }
                
                $hallsDir = public_path('images/halls');
                if (!file_exists($hallsDir)) {
                    mkdir($hallsDir, 0755, true);
                }
                
                $hallPhotoPath = '/images/halls/' . $request->file('hall_photo')->hashName();
                $request->file('hall_photo')->move($hallsDir, basename($hallPhotoPath));
                $hall->hall_photo = $hallPhotoPath;
            }

            // Обновляем данные зала
            $hall->update([
                'hall_name' => $validated['hall_name'],
                'type_hall' => $validated['type_hall'],
                'description_hall' => $validated['description_hall'] ?? null,
            ]);

            // Удаляем все старые места
            Seat::where('hall_id', $hall->id_hall)->delete();

            // Парсим JSON с данными о местах
            $seatsData = json_decode($validated['seats_data'], true);
            $totalSeats = 0;

            // Создаем новые места
            foreach ($seatsData as $seat) {
                if (isset($seat['row_number']) && isset($seat['seat_number'])) {
                    Seat::create([
                        'hall_id' => $hall->id_hall,
                        'row_number' => $seat['row_number'],
                        'seat_number' => $seat['seat_number'],
                        'status' => 'Свободно',
                    ]);
                    $totalSeats++;
                }
            }

            // Обновляем количество мест
            $hall->quantity_seats = $totalSeats;
            $hall->save();
            });

            return redirect()->route('admin.halls.index')->with('success', 'Зал обновлён.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Ошибка валидации данных. Проверьте правильность заполнения полей.')
                ->with('editing_hall_id', $id);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Произошла ошибка при обновлении зала: ' . $e->getMessage())
                ->with('editing_hall_id', $id);
        }
    }

    /**
     * Удаление зала
     */
    public function destroy($id)
    {
        $hall = Hall::findOrFail($id);

        // Проверяем наличие сеансов
        $sessionsCount = Session::where('hall_id', $hall->id_hall)->count();
        if ($sessionsCount > 0) {
            return redirect()->route('admin.halls.index')
                ->with('error', "Невозможно удалить зал! На этот зал запланировано {$sessionsCount} сеансов.");
        }

        DB::transaction(function () use ($hall) {
            // Удаляем все места зала
            Seat::where('hall_id', $hall->id_hall)->delete();

            // Удаляем фото, если есть
            if ($hall->hall_photo && file_exists(public_path($hall->hall_photo))) {
                unlink(public_path($hall->hall_photo));
            }

            // Удаляем зал
            $hall->delete();
        });

        return redirect()->route('admin.halls.index')->with('success', 'Зал удалён.');
    }

    /**
     * Получение схемы зала для редактирования (AJAX)
     */
    public function getSeats($id)
    {
        $hall = Hall::with('seats')->findOrFail($id);
        $seats = $hall->seats->map(function($seat) {
            return [
                'row_number' => $seat->row_number,
                'seat_number' => $seat->seat_number,
            ];
        })->toArray();
        
        return response()->json(['seats' => $seats]);
    }
}

