{{-- Модалка добавления зала --}}
<div class="modal fade" id="addHallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-height: 90vh; margin: 1.75rem auto;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
            <form action="{{ route('admin.halls.store') }}" method="POST" id="addHallForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">
                @csrf
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title fw-bold text-success">Добавить зал</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; flex: 1; min-height: 0;">
                    {{-- Сообщения об ошибках --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Основная информация --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Название зала <span class="text-danger">*</span></label>
                            <input type="text" name="hall_name" class="form-control @error('hall_name') is-invalid @enderror" value="{{ old('hall_name') }}" required>
                            @error('hall_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Тип зала <span class="text-danger">*</span></label>
                            <select name="type_hall" class="form-select @error('type_hall') is-invalid @enderror" required>
                                <option value="">Выберите тип зала</option>
                                <option value="большой" {{ old('type_hall') == 'большой' ? 'selected' : '' }}>Большой</option>
                                <option value="средний" {{ old('type_hall') == 'средний' ? 'selected' : '' }}>Средний</option>
                                <option value="малый" {{ old('type_hall') == 'малый' ? 'selected' : '' }}>Малый</option>
                            </select>
                            @error('type_hall')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Описание</label>
                            <textarea name="description_hall" class="form-control" rows="3">{{ old('description_hall') }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Фото зала <span class="text-danger">*</span></label>
                            <input type="file" name="hall_photo" class="form-control @error('hall_photo') is-invalid @enderror" accept="image/*" required>
                            @error('hall_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    <input type="number" id="rowsCount" class="form-control" min="1" max="30" value="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Мест в ряду</label>
                                    <input type="number" id="seatsPerRow" class="form-control" min="1" max="30" value="15">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="generateHallLayout()">
                                        <i class="bi bi-magic me-2"></i>Сгенерировать схему
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-success w-100" onclick="selectAllSeats()" id="selectAllBtn" style="display: none;">
                                        <i class="bi bi-check-all me-2"></i>Выбрать все
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-warning w-100" onclick="deselectAllSeats()" id="deselectAllBtn" style="display: none;">
                                        <i class="bi bi-x-circle me-2"></i>Снять все
                                    </button>
                                </div>
                            </div>

                            {{-- Визуальный редактор --}}
                            <div class="hall-editor-container">
                                <div class="text-center mb-3">
                                    <div class="screen-preview">ЭКРАН</div>
                                </div>
                                <div id="hallLayout" class="hall-layout">
                                    <p class="text-muted text-center">Нажмите "Сгенерировать схему" для создания схемы зала</p>
                                </div>
                                <input type="hidden" name="seats_data" id="seatsData" required>
                                @error('seats_data')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
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
                    <button type="submit" class="btn btn-success">Добавить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Стили для прокрутки модального окна */
#addHallModal .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#addHallModal .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

#addHallModal form {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

#addHallModal .modal-header {
    flex-shrink: 0;
}

#addHallModal .modal-body {
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}

#addHallModal .modal-footer {
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
let hallLayout = {}; // Объект для хранения схемы: {row_number: [seat_numbers]}

function generateHallLayout() {
    const rowsCount = parseInt(document.getElementById('rowsCount').value) || 10;
    const seatsPerRow = parseInt(document.getElementById('seatsPerRow').value) || 15;
    
    if (rowsCount < 1 || rowsCount > 30 || seatsPerRow < 1 || seatsPerRow > 30) {
        alert('Количество рядов и мест должно быть от 1 до 30');
        return;
    }

    // Очищаем текущую схему
    hallLayout = {};
    const container = document.getElementById('hallLayout');
    container.innerHTML = '';

    // Генерируем схему - все места создаются по умолчанию
    for (let row = 1; row <= rowsCount; row++) {
        hallLayout[row] = [];
        const rowDiv = document.createElement('div');
        rowDiv.className = 'seat-row-editor';
        
        const rowLabel = document.createElement('div');
        rowLabel.className = 'row-label';
        rowLabel.textContent = row;
        rowDiv.appendChild(rowLabel);

        for (let seat = 1; seat <= seatsPerRow; seat++) {
            const seatDiv = document.createElement('div');
            seatDiv.className = 'seat-editor seat-available';
            seatDiv.textContent = seat;
            seatDiv.dataset.row = row;
            seatDiv.dataset.seat = seat;
            seatDiv.onclick = function() { toggleSeat(this); };
            rowDiv.appendChild(seatDiv);
            // Все места добавляются в схему по умолчанию
            hallLayout[row].push(seat);
        }

        container.appendChild(rowDiv);
    }

    updateSeatsData();
    
    // Показываем кнопки управления
    document.getElementById('selectAllBtn').style.display = 'block';
    document.getElementById('deselectAllBtn').style.display = 'block';
}

function selectAllSeats() {
    const container = document.getElementById('hallLayout');
    const seats = container.querySelectorAll('.seat-editor');
    
    seats.forEach(seat => {
        if (!seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-empty');
            seat.classList.add('seat-available');
            
            if (!hallLayout[row]) {
                hallLayout[row] = [];
            }
            if (!hallLayout[row].includes(seatNum)) {
                hallLayout[row].push(seatNum);
                hallLayout[row].sort((a, b) => a - b);
            }
        }
    });
    
    updateSeatsData();
}

function deselectAllSeats() {
    const container = document.getElementById('hallLayout');
    const seats = container.querySelectorAll('.seat-editor');
    
    seats.forEach(seat => {
        if (seat.classList.contains('seat-available')) {
            const row = parseInt(seat.dataset.row);
            const seatNum = parseInt(seat.dataset.seat);
            
            seat.classList.remove('seat-available');
            seat.classList.add('seat-empty');
            
            if (hallLayout[row]) {
                hallLayout[row] = hallLayout[row].filter(s => s !== seatNum);
            }
        }
    });
    
    updateSeatsData();
}

function toggleSeat(element) {
    const row = parseInt(element.dataset.row);
    const seat = parseInt(element.dataset.seat);
    
    if (element.classList.contains('seat-available')) {
        // Удаляем место
        element.classList.remove('seat-available');
        element.classList.add('seat-empty');
        hallLayout[row] = hallLayout[row].filter(s => s !== seat);
    } else {
        // Добавляем место
        element.classList.remove('seat-empty');
        element.classList.add('seat-available');
        if (!hallLayout[row].includes(seat)) {
            hallLayout[row].push(seat);
            hallLayout[row].sort((a, b) => a - b);
        }
    }
    
    updateSeatsData();
}

function updateSeatsData() {
    const seatsArray = [];
    for (const row in hallLayout) {
        hallLayout[row].forEach(seat => {
            seatsArray.push({
                row_number: parseInt(row),
                seat_number: seat
            });
        });
    }
    document.getElementById('seatsData').value = JSON.stringify(seatsArray);
}

// Валидация формы
document.getElementById('addHallForm').addEventListener('submit', function(e) {
    const seatsData = document.getElementById('seatsData').value;
    if (!seatsData || seatsData === '[]') {
        e.preventDefault();
        alert('Необходимо создать схему зала! Нажмите "Сгенерировать схему" и настройте места.');
        return false;
    }
});
</script>

