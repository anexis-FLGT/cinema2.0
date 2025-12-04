@extends('layouts.app')

@section('title', 'Личный кабинет')

@section('content')
<style>
    /* Netflix-like стили для личного кабинета */
    .user-dashboard {
        background: var(--bg-primary);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .dashboard-card {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 4px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .dashboard-title {
        color: var(--accent-primary);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        letter-spacing: -0.5px;
    }
    
    .profile-section h5 {
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    
    .profile-section p {
        color: var(--text-primary);
        font-size: 1rem;
        margin-bottom: 1.5rem;
        font-weight: 400;
    }
    
    .btn-netflix {
        background: var(--accent-primary);
        color: var(--text-primary);
        border: none;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 4px;
        transition: none;
    }
    
    .btn-netflix:hover {
        background: var(--accent-hover);
        color: var(--text-primary);
    }
    
    .btn-netflix-outline {
        background: transparent;
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 4px;
        transition: none;
    }
    
    .btn-netflix-outline:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }
    
    .btn-netflix-danger {
        background: transparent;
        color: var(--accent-primary);
        border: 1px solid var(--accent-primary);
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 4px;
        font-size: 0.875rem;
        transition: none;
    }
    
    .btn-netflix-danger:hover {
        background: var(--accent-primary);
        color: var(--text-primary);
    }
    
    .btn-netflix-primary {
        background: var(--accent-primary);
        color: var(--text-primary);
        border: 1px solid var(--accent-primary);
        padding: 0.5rem 1rem;
        font-weight: 600;
        border-radius: 4px;
        font-size: 0.875rem;
        transition: none;
        display: inline-flex;
        align-items: center;
    }
    
    .btn-netflix-primary:hover {
        background: var(--accent-hover);
        border-color: var(--accent-hover);
        color: var(--text-primary);
    }
    
    .bookings-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        letter-spacing: -0.5px;
    }
    
    .booking-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-secondary);
        border-radius: 4px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .booking-card h5 {
        color: var(--text-primary);
        font-weight: 600;
        font-size: 1.125rem;
        margin-bottom: 1rem;
    }
    
    .booking-info {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .booking-info strong {
        color: var(--text-primary);
        font-weight: 600;
    }
    
    .booking-value {
        color: var(--text-primary);
        font-size: 0.9375rem;
        margin-bottom: 0.75rem;
    }
    
    .badge-netflix {
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-success {
        background: #46d369;
        color: #000;
    }
    
    .badge-warning {
        background: #ffa00a;
        color: #000;
    }
    
    .badge-info {
        background: #0071eb;
        color: #fff;
    }
    
    .alert-netflix {
        background: var(--bg-secondary);
        border: 1px solid var(--border-secondary);
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }
    
    .alert-success {
        border-left: 4px solid #46d369;
    }
    
    .alert-danger {
        border-left: 4px solid var(--accent-primary);
    }
    
    .alert-info {
        border-left: 4px solid #0071eb;
    }
    
    .modal-netflix .modal-content {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 4px;
    }
    
    .modal-netflix .modal-header {
        border-bottom: 1px solid var(--border-secondary);
        padding: 1.5rem;
    }
    
    .modal-netflix .modal-title {
        color: var(--accent-primary);
        font-weight: 700;
    }
    
    .modal-netflix .modal-body {
        padding: 1.5rem;
    }
    
    .modal-netflix .form-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .modal-netflix .form-control {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--text-primary);
        border-radius: 4px;
        padding: 0.75rem;
    }
    
    .modal-netflix .form-control:focus {
        background: var(--input-bg);
        border-color: var(--input-focus-border);
        color: var(--text-primary);
        box-shadow: 0 0 0 0.2rem rgba(229, 9, 20, 0.25);
    }
    
    .modal-netflix .modal-footer {
        border-top: 1px solid var(--border-secondary);
        padding: 1.5rem;
    }
    
    .text-muted-netflix {
        color: var(--text-secondary);
    }
    
    .delete-account-section {
        border-top: 1px solid var(--border-secondary);
        padding-top: 2rem;
        margin-top: 2rem;
    }
</style>

<div class="user-dashboard">
    <div class="container">
        <div class="dashboard-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="dashboard-title mb-0">Личный кабинет</h2>
                <button class="btn-netflix-outline" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    Редактировать профиль
                </button>
            </div>

            @if (session('success'))
                <div class="alert-netflix alert-success">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-netflix alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <div class="profile-section row">
                <div class="col-md-6">
                    <h5>Фамилия</h5>
                    <p>{{ $user->last_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Имя</h5>
                    <p>{{ $user->first_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Отчество</h5>
                    <p>{{ $user->middle_name ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Телефон</h5>
                    <p>{{ $user->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Логин</h5>
                    <p>{{ $user->login }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Роль</h5>
                    <p>{{ $user->role_id == 1 ? 'Администратор' : 'Пользователь' }}</p>
                </div>
            </div>

            {{-- Секция с бронированиями --}}
            <div class="mt-5">
                <h3 class="bookings-title">
                    <i class="bi bi-ticket-perforated me-2"></i>Мои бронирования
                </h3>

                @if($bookings->isEmpty())
                    <div class="alert-netflix alert-info">
                        <i class="bi bi-info-circle me-2"></i>У вас пока нет активных бронирований.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($bookings as $booking)
                            <div class="col-md-6">
                                <div class="booking-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div style="flex: 1;">
                                            <h5>{{ $booking->movie->movie_title ?? 'Фильм не найден' }}</h5>
                                            <div class="booking-info">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($booking->show_date)->locale('ru')->isoFormat('D MMMM YYYY') }}
                                            </div>
                                            <div class="booking-info">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($booking->show_time)->format('H:i') }}
                                            </div>
                                        </div>
                                        <div class="text-end ms-3">
                                            @if($booking->payment)
                                                @if($booking->payment->payment_status === 'оплачено')
                                                    <span class="badge-netflix badge-success">Оплачено</span>
                                                @elseif($booking->payment->payment_status === 'ожидание')
                                                    <span class="badge-netflix badge-warning">Ожидание оплаты</span>
                                                @elseif($booking->payment->payment_status === 'ожидает_подтверждения')
                                                    <span class="badge-netflix badge-info">Ожидает подтверждения</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <div class="booking-info">Зал:</div>
                                            <div class="booking-value">{{ $booking->hall->hall_name ?? 'Не указан' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="booking-info">Место:</div>
                                            <div class="booking-value">
                                                Ряд {{ $booking->seat->row_number ?? '?' }}, 
                                                Место {{ $booking->seat->seat_number ?? '?' }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($booking->payment && $booking->payment->amount)
                                        <div class="mb-3">
                                            <div class="booking-info">Стоимость:</div>
                                            <div class="booking-value" style="font-size: 1.125rem; font-weight: 600;">
                                                {{ number_format($booking->payment->amount, 2, ',', ' ') }} ₽
                                            </div>
                                        </div>
                                    @endif

                                        <div class="d-flex justify-content-between align-items-center pt-3" style="border-top: 1px solid var(--border-secondary);">
                                        <div class="text-muted-netflix" style="font-size: 0.75rem;">
                                            Номер бронирования: #{{ $booking->id_booking }}
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if($booking->payment && $booking->payment->payment_status === 'ожидание')
                                                <a href="{{ route('payment.retry', $booking->id_booking) }}" class="btn-netflix-primary" style="text-decoration: none;">
                                                    <i class="bi bi-credit-card me-1"></i>Оплатить
                                                </a>
                                            @endif
                                            @if($booking->payment && ($booking->payment->payment_status === 'оплачено' || $booking->payment->payment_status === 'ожидание' || $booking->payment->payment_status === 'ожидает_подтверждения'))
                                                <form action="{{ route('user.booking.cancel', $booking->id_booking) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-netflix-danger" 
                                                            onclick="return confirm('Вы уверены, что хотите отменить бронирование? Место будет освобождено.')">
                                                        <i class="bi bi-x-circle me-1"></i>Отменить
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="delete-account-section text-end">
                <form action="{{ route('user.deleteAccount') }}" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-netflix-danger">Удалить аккаунт</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования профиля -->
<div class="modal fade modal-netflix" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Редактировать профиль</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <form action="{{ route('user.updateProfile') }}" method="POST" id="profileForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Имя</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Отчество</label>
                            <input type="text" name="middle_name" class="form-control" value="{{ $user->middle_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Телефон</label>
                            <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" value="{{ $user->login }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Новый пароль</label>
                            <div class="position-relative">
                                <input type="password" name="password" class="form-control" id="passwordField">
                                <i class="bi bi-eye password-toggle" onclick="togglePassword('passwordField', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #b3b3b3;"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Подтверждение пароля</label>
                            <div class="position-relative">
                                <input type="password" name="password_confirmation" class="form-control" id="passwordConfirmationField">
                                <i class="bi bi-eye password-toggle" onclick="togglePassword('passwordConfirmationField', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #b3b3b3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-netflix-outline" data-bs-dismiss="modal">Закрыть</button>
                    <button type="submit" class="btn-netflix">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        el.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        el.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

document.getElementById('deleteForm').addEventListener('submit', function (e) {
    if (!confirm('Вы уверены, что хотите удалить аккаунт?')) {
        e.preventDefault();
    }
});

const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
        const password = document.getElementById('passwordField');
        const confirm = document.getElementById('passwordConfirmationField');
        if (password && confirm && password.value !== confirm.value) {
            e.preventDefault();
            alert('Пароли не совпадают!');
        }
    });
}
</script>
@endsection
