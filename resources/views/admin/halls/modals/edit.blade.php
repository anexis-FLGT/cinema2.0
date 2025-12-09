{{-- Модалка редактирования зала --}}
<div class="modal fade" id="editHallModal{{ $hall->id_hall }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-height: 90vh; margin: 1.75rem auto;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
            <form action="{{ route('admin.halls.update', $hall->id_hall) }}" method="POST" id="editHallForm{{ $hall->id_hall }}" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                @csrf
                @method('PUT')
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title fw-bold text-success">Редактировать зал</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                    {{-- Основная информация --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Название зала <span class="text-danger">*</span></label>
                            <input type="text" name="hall_name" class="form-control" value="{{ old('hall_name', $hall->hall_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Тип зала <span class="text-danger">*</span></label>
                            <input type="text" name="type_hall" class="form-control" value="{{ old('type_hall', $hall->type_hall) }}" required>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Описание</label>
                            <textarea name="description_hall" class="form-control" rows="3">{{ old('description_hall', $hall->description_hall) }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Фото зала <span class="text-danger">*</span></label>
                            @if($hall->hall_photo)
                                <div class="mb-2">
                                    <img src="{{ asset($hall->hall_photo) }}" alt="Фото зала" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 5px;">
                                </div>
                            @endif
                            <input type="file" name="hall_photo" class="form-control" accept="image/*" {{ !$hall->hall_photo ? 'required' : '' }}>
                            @if($hall->hall_photo)
                                <small class="text-muted">Оставьте пустым, чтобы сохранить текущее фото</small>
                            @endif
                        </div>
                    </div>

                    {{-- Генератор схемы зала --}}
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-grid me-2"></i>Схема зала</h6>
                        </div>
                        <div class="card-body">
                            {{-- Настройки генерации --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Количество рядов</label>
                                    <input type="number" id="rowsCount{{ $hall->id_hall }}" class="form-control" min="1" max="30" value="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Мест в ряду</label>
                                    <input type="number" id="seatsPerRow{{ $hall->id_hall }}" class="form-control" min="1" max="30" value="15">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="generateHallLayoutEdit({{ $hall->id_hall }})">
                                        <i class="bi bi-magic me-2"></i>Сгенерировать схему
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="btn btn-info btn-sm" onclick="loadExistingLayout({{ $hall->id_hall }})">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Загрузить текущую схему
                                </button>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-success w-100" onclick="selectAllSeatsEdit({{ $hall->id_hall }})" id="selectAllBtn{{ $hall->id_hall }}" style="display: none;">
                                        <i class="bi bi-check-all me-2"></i>Выбрать все
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning w-100" onclick="deselectAllSeatsEdit({{ $hall->id_hall }})" id="deselectAllBtn{{ $hall->id_hall }}" style="display: none;">
                                        <i class="bi bi-x-circle me-2"></i>Снять все
                                    </button>
                                </div>
                            </div>

                            {{-- Визуальный редактор --}}
                            <div class="hall-editor-container">
                                <div class="text-center mb-3">
                                    <div class="screen-preview">ЭКРАН</div>
                                </div>
                                <div id="hallLayout{{ $hall->id_hall }}" class="hall-layout">
                                    <p class="text-muted text-center">Нажмите "Загрузить текущую схему" или "Сгенерировать схему"</p>
                                </div>
                                <input type="hidden" name="seats_data" id="seatsData{{ $hall->id_hall }}" required>
                            </div>

                            {{-- Легенда --}}
                            <div class="mt-3 d-flex gap-4 justify-content-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="seat-preview seat-available"></div>
                                    <small>Место</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="seat-preview seat-empty"></div>
                                    <small>Пусто</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0;">
                    <button type="submit" class="btn btn-success">Сохранить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Стили для прокрутки модального окна */
#editHallModal{{ $hall->id_hall }} .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#editHallModal{{ $hall->id_hall }} .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#editHallModal{{ $hall->id_hall }} form {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

#editHallModal{{ $hall->id_hall }} .modal-header {
    flex-shrink: 0;
}

#editHallModal{{ $hall->id_hall }} .modal-body {
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

#editHallModal{{ $hall->id_hall }} .modal-footer {
    flex-shrink: 0;
}

.hall-editor-container {
    max-height: 500px;
    overflow-y: auto;
    padding: 15px;
    background-color: var(--bg-secondary);
    border-radius: 8px;
}

.screen-preview {
    background: linear-gradient(to bottom, #333, #555);
    color: white;
    padding: 10px 30px;
    border-radius: 5px;
    display: inline-block;
    font-weight: bold;
    margin-bottom: 20px;
}

.hall-layout {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: center;
}

.seat-row-editor {
    display: flex;
    align-items: center;
    gap: 5px;
}

.row-label {
    min-width: 30px;
    text-align: center;
    font-weight: bold;
    color: var(--text-primary);
}

.seat-editor {
    width: 35px;
    height: 35px;
    border: 2px solid #ccc;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 11px;
    transition: all 0.2s;
    background-color: #f0f0f0;
    color: #333;
}

.seat-editor.seat-available {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.seat-editor.seat-empty {
    background-color: transparent;
    border-color: #ddd;
    color: #999;
}

.seat-editor:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.seat-preview {
    width: 25px;
    height: 25px;
    border: 2px solid #ccc;
    border-radius: 4px;
}

.seat-preview.seat-available {
    background-color: #28a745;
    border-color: #28a745;
}

.seat-preview.seat-empty {
    background-color: transparent;
    border-color: #ddd;
}

/* Стили для темной темы */
[data-theme="dark"] .hall-editor-container {
    background-color: var(--bg-tertiary);
}

[data-theme="dark"] .seat-editor {
    background-color: var(--bg-secondary);
    color: var(--text-primary);
    border-color: var(--border-color);
}

[data-theme="dark"] .seat-editor.seat-available {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

[data-theme="dark"] .seat-editor.seat-empty {
    background-color: transparent;
    border-color: var(--border-color);
    color: var(--text-secondary);
}

[data-theme="dark"] .row-label {
    color: var(--text-primary);
}

[data-theme="dark"] .modal .btn-close {
    filter: brightness(0) invert(1);
}
</style>

<script>
let hallLayouts = {}; // Хранилище схем для каждого зала

function generateHallLayoutEdit(hallId) {
    const rowsCount = parseInt(document.getElementById('rowsCount' + hallId).value) || 10;
    const seatsPerRow = parseInt(document.getElementById('seatsPerRow' + hallId).value) || 15;
    
    if (rowsCount < 1 || rowsCount > 30 || seatsPerRow < 1 || seatsPerRow > 30) {
        alert('Количество рядов и мест должно быть от 1 до 30');
        return;
    }

    // Инициализируем схему для этого зала
    if (!hallLayouts[hallId]) {
        hallLayouts[hallId] = {};
    }

    // Очищаем текущую схему
    hallLayouts[hallId] = {};
    const container = document.getElementById('hallLayout' + hallId);
    container.innerHTML = '';

    // Находим максимальные значения для отображения
    let maxRow = rowsCount;
    let maxSeat = seatsPerRow;

            // Генерируем схему - все места создаются по умолчанию
            for (let row = 1; row <= maxRow; row++) {
                if (!hallLayouts[hallId][row]) {
                    hallLayouts[hallId][row] = [];
                }
                const rowDiv = document.createElement('div');
                rowDiv.className = 'seat-row-editor';
                
                const rowLabel = document.createElement('div');
                rowLabel.className = 'row-label';
                rowLabel.textContent = row;
                rowDiv.appendChild(rowLabel);

                for (let seat = 1; seat <= maxSeat; seat++) {
                    const isAvailable = hallLayouts[hallId][row].includes(seat);
                    const seatDiv = document.createElement('div');
                    seatDiv.className = 'seat-editor ' + (isAvailable ? 'seat-available' : 'seat-empty');
                    seatDiv.textContent = seat;
                    seatDiv.dataset.row = row;
                    seatDiv.dataset.seat = seat;
                    seatDiv.onclick = function() { toggleSeatEdit(hallId, this); };
                    rowDiv.appendChild(seatDiv);
                }

                container.appendChild(rowDiv);
            }

    updateSeatsDataEdit(hallId);
    
    // Показываем кнопки управления
    document.getElementById('selectAllBtn' + hallId).style.display = 'block';
    document.getElementById('deselectAllBtn' + hallId).style.display = 'block';
}

function selectAllSeatsEdit(hallId) {
    const container = document.getElementById('hallLayout' + hallId);
    const seats = container.querySelectorAll('.seat-editor');
    
    if (!hallLayouts[hallId]) {
        hallLayouts[hallId] = {};
    }
    
    seats.forEach(seat => {
        if (!seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-empty');
            seat.classList.add('seat-available');
            
            if (!hallLayouts[hallId][row]) {
                hallLayouts[hallId][row] = [];
            }
            if (!hallLayouts[hallId][row].includes(seatNum)) {
                hallLayouts[hallId][row].push(seatNum);
                hallLayouts[hallId][row].sort((a, b) => a - b);
            }
        }
    });
    
    updateSeatsDataEdit(hallId);
}

function deselectAllSeatsEdit(hallId) {
    const container = document.getElementById('hallLayout' + hallId);
    const seats = container.querySelectorAll('.seat-editor');
    
    seats.forEach(seat => {
        if (seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-available');
            seat.classList.add('seat-empty');
            
            if (hallLayouts[hallId] && hallLayouts[hallId][row]) {
                hallLayouts[hallId][row] = hallLayouts[hallId][row].filter(s => s !== seatNum);
            }
        }
    });
    
    updateSeatsDataEdit(hallId);
    
    // Показываем кнопки управления
    document.getElementById('selectAllBtn' + hallId).style.display = 'block';
    document.getElementById('deselectAllBtn' + hallId).style.display = 'block';
}

function selectAllSeatsEdit(hallId) {
    const container = document.getElementById('hallLayout' + hallId);
    const seats = container.querySelectorAll('.seat-editor');
    
    if (!hallLayouts[hallId]) {
        hallLayouts[hallId] = {};
    }
    
    seats.forEach(seat => {
        if (!seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-empty');
            seat.classList.add('seat-available');
            
            if (!hallLayouts[hallId][row]) {
                hallLayouts[hallId][row] = [];
            }
            if (!hallLayouts[hallId][row].includes(seatNum)) {
                hallLayouts[hallId][row].push(seatNum);
                hallLayouts[hallId][row].sort((a, b) => a - b);
            }
        }
    });
    
    updateSeatsDataEdit(hallId);
}

function deselectAllSeatsEdit(hallId) {
    const container = document.getElementById('hallLayout' + hallId);
    const seats = container.querySelectorAll('.seat-editor');
    
    seats.forEach(seat => {
        if (seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-available');
            seat.classList.add('seat-empty');
            
            if (hallLayouts[hallId] && hallLayouts[hallId][row]) {
                hallLayouts[hallId][row] = hallLayouts[hallId][row].filter(s => s !== seatNum);
            }
        }
    });
    
    updateSeatsDataEdit(hallId);
}

function loadExistingLayout(hallId) {
    fetch('{{ route("admin.halls.getSeats", ":id") }}'.replace(':id', hallId))
        .then(response => response.json())
        .then(data => {
            // Инициализируем схему
            if (!hallLayouts[hallId]) {
                hallLayouts[hallId] = {};
            }
            hallLayouts[hallId] = {};

            // Загружаем существующие места
            data.seats.forEach(seat => {
                const row = seat.row_number;
                const seatNum = seat.seat_number;
                if (!hallLayouts[hallId][row]) {
                    hallLayouts[hallId][row] = [];
                }
                hallLayouts[hallId][row].push(seatNum);
            });

            // Находим максимальные значения
            let maxRow = Math.max(...Object.keys(hallLayouts[hallId]).map(Number), 10);
            let maxSeat = 0;
            Object.values(hallLayouts[hallId]).forEach(seats => {
                maxSeat = Math.max(maxSeat, ...seats, 15);
            });

            // Обновляем поля
            document.getElementById('rowsCount' + hallId).value = maxRow;
            document.getElementById('seatsPerRow' + hallId).value = maxSeat;

            // Отображаем схему
            const container = document.getElementById('hallLayout' + hallId);
            container.innerHTML = '';

            for (let row = 1; row <= maxRow; row++) {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'seat-row-editor';
                
                const rowLabel = document.createElement('div');
                rowLabel.className = 'row-label';
                rowLabel.textContent = row;
                rowDiv.appendChild(rowLabel);

                if (!hallLayouts[hallId][row]) {
                    hallLayouts[hallId][row] = [];
                }

                for (let seat = 1; seat <= maxSeat; seat++) {
                    const seatDiv = document.createElement('div');
                    const isAvailable = hallLayouts[hallId][row].includes(seat);
                    seatDiv.className = 'seat-editor ' + (isAvailable ? 'seat-available' : 'seat-empty');
                    seatDiv.textContent = seat;
                    seatDiv.dataset.row = row;
                    seatDiv.dataset.seat = seat;
                    seatDiv.onclick = function() { toggleSeatEdit(hallId, this); };
                    rowDiv.appendChild(seatDiv);
                }

                container.appendChild(rowDiv);
            }

            updateSeatsDataEdit(hallId);
            
            // Показываем кнопки управления
            document.getElementById('selectAllBtn' + hallId).style.display = 'block';
            document.getElementById('deselectAllBtn' + hallId).style.display = 'block';
        })
        .catch(error => {
            console.error('Ошибка загрузки схемы:', error);
            alert('Не удалось загрузить схему зала');
        });
}

function toggleSeatEdit(hallId, element) {
    const row = parseInt(element.dataset.row);
    const seat = parseInt(element.dataset.seat);
    
    if (!hallLayouts[hallId]) {
        hallLayouts[hallId] = {};
    }
    if (!hallLayouts[hallId][row]) {
        hallLayouts[hallId][row] = [];
    }
    
    if (element.classList.contains('seat-available')) {
        // Удаляем место
        element.classList.remove('seat-available');
        element.classList.add('seat-empty');
        hallLayouts[hallId][row] = hallLayouts[hallId][row].filter(s => s !== seat);
    } else {
        // Добавляем место
        element.classList.remove('seat-empty');
        element.classList.add('seat-available');
        if (!hallLayouts[hallId][row].includes(seat)) {
            hallLayouts[hallId][row].push(seat);
            hallLayouts[hallId][row].sort((a, b) => a - b);
        }
    }
    
    updateSeatsDataEdit(hallId);
}

function updateSeatsDataEdit(hallId) {
    if (!hallLayouts[hallId]) {
        hallLayouts[hallId] = {};
    }
    
    const seatsArray = [];
    for (const row in hallLayouts[hallId]) {
        hallLayouts[hallId][row].forEach(seat => {
            seatsArray.push({
                row_number: parseInt(row),
                seat_number: seat
            });
        });
    }
    document.getElementById('seatsData' + hallId).value = JSON.stringify(seatsArray);
}

// Автоматическая загрузка схемы при открытии модалки
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editHallModal{{ $hall->id_hall }}');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            // Загружаем существующую схему при открытии
            loadExistingLayout({{ $hall->id_hall }});
        });
    }
});

// Валидация формы редактирования
document.getElementById('editHallForm{{ $hall->id_hall }}').addEventListener('submit', function(e) {
    const seatsData = document.getElementById('seatsData{{ $hall->id_hall }}').value;
    if (!seatsData || seatsData === '[]') {
        e.preventDefault();
        alert('Необходимо создать схему зала! Нажмите "Загрузить текущую схему" или "Сгенерировать схему" и настройте места.');
        return false;
    }
});
</script>

