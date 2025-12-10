@extends('layouts.app')

@section('title', 'Личный кабинет')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">

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

            <div class="profile-section row g-4">
                <div class="col-md-4 col-sm-6">
                    <h5>Фамилия</h5>
                    <p class="mb-0">{{ $user->last_name }}</p>
                </div>
                <div class="col-md-4 col-sm-6">
                    <h5>Имя</h5>
                    <p class="mb-0">{{ $user->first_name }}</p>
                </div>
                <div class="col-md-4 col-sm-6">
                    <h5>Отчество</h5>
                    <p class="mb-0">{{ $user->middle_name ?? '-' }}</p>
                </div>
                <div class="col-md-4 col-sm-6">
                    <h5>Телефон</h5>
                    <p class="mb-0">{{ $user->phone ?? '-' }}</p>
                </div>
                <div class="col-md-4 col-sm-6">
                    <h5>Логин</h5>
                    <p class="mb-0">{{ $user->login }}</p>
                </div>
            </div>

            {{-- Секция с активными бронированиями --}}
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="bookings-title mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>Активные бронирования
                    </h3>
                    <a href="{{ route('user.history') }}" class="btn-netflix-outline">
                        <i class="bi bi-clock-history me-2"></i>История бронирований
                    </a>
                </div>

                @if($activeBookings->isEmpty())
                    <div class="alert-netflix alert-info">
                        <i class="bi bi-info-circle me-2"></i>У вас пока нет активных бронирований.
                    </div>
                @else
                    {{-- выпадающий список для активных бронирований --}}
                    <div class="accordion" id="activeBookingsAccordion">
                        @foreach($activeBookingsGrouped as $sessionDateTime => $bookingsGroup)
                            @php
                                $firstBooking = $bookingsGroup->first();
                                $movieTitle = $firstBooking->session->movie->movie_title ?? 'Фильм не найден';
                                $sessionDate = \Carbon\Carbon::parse($sessionDateTime)->locale('ru')->isoFormat('D MMMM YYYY, HH:mm');
                                $accordionId = 'session_' . str_replace([' ', ':', ','], '_', $sessionDateTime);
                            @endphp
                            <div class="accordion-item mb-2" style="background-color: var(--bg-secondary); border: 1px solid var(--border-secondary); border-radius: 8px;">
                                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse{{ $loop->index }}" style="background-color: var(--bg-secondary); color: var(--text-primary); font-weight: 600;">
                                        <div class="w-100">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                <span style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                                                    Сеанс: {{ $sessionDate }}
                                                </span> 
                                                <span class="ms-3" style="font-size: 0.95rem; font-weight: 500; text-transform: none;">
                                                 {{ $movieTitle }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $loop->index }}" data-bs-parent="#activeBookingsAccordion">
                                    <div class="accordion-body" style="background-color: var(--bg-primary);">
                                        <div class="row g-2">
                                            @foreach($bookingsGroup as $booking)
                                                <div class="col-12 col-md-6 col-lg-4">
                                                    <div class="booking-card">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div style="flex: 1;">
                                                                <h5>{{ $booking->session->movie->movie_title ?? 'Фильм не найден' }}</h5>
                                                                <div class="d-flex flex-wrap gap-3 mb-2">
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-calendar-event me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($booking->session->date_time_session)->locale('ru')->isoFormat('D MMM YYYY') }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-clock me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($booking->session->date_time_session)->format('H:i') }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-door-open me-1"></i>
                                                                        {{ $booking->hall->hall_name ?? 'Не указан' }}
                                                                    </div>
                                                                    <div class="booking-info">
                                                                        <i class="bi bi-seat me-1"></i>
                                                                        Ряд {{ $booking->seat->row_number ?? '?' }}, Место {{ $booking->seat->seat_number ?? '?' }}
                                                                    </div>
                                                                    @if($booking->payment && $booking->payment->amount)
                                                                        <div class="booking-info">
                                                                            <i class="bi bi-currency-ruble me-1"></i>
                                                                            <strong>{{ number_format($booking->payment->amount, 0, ',', ' ') }} ₽</strong>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="text-end ms-2">
                                                                @if($booking->payment)
                                                                    @if($booking->payment->payment_status === 'оплачено')
                                                                        <span class="badge-netflix badge-success">Оплачено</span>
                                                                    @elseif($booking->payment->payment_status === 'ожидание')
                                                                        <span class="badge-netflix badge-warning">Ожидание</span>
                                                                    @elseif($booking->payment->payment_status === 'ожидает_подтверждения')
                                                                        <span class="badge-netflix badge-info">Ожидает</span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="d-flex justify-content-end align-items-center pt-2" style="border-top: 1px solid var(--border-secondary);">
                                                            <div class="d-flex gap-2">
                                                                @if($booking->payment && $booking->payment->payment_status === 'оплачено')
                                                                    <a href="{{ route('user.ticket.pdf', $booking->id_booking) }}" class="btn-ticket-print" style="text-decoration: none;" target="_blank">
                                                                        <i class="bi bi-file-earmark-pdf me-1"></i>Печать билета
                                                                    </a>
                                                                @endif
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    {{-- Пагинация для активных бронирований --}}
                    @if($activeBookings->hasPages())
                        <div class="mt-4">
                            {{ $activeBookings->appends(request()->except('active_page'))->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                @endif
            </div>

            <div class="delete-account-section text-end">
                <form action="{{ route('user.deleteAccount') }}" method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="confirm_with_bookings" id="confirmWithBookings" value="0">
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
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ $user->phone }}" placeholder="+7 (___) ___-__-__">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" value="{{ $user->login }}" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="changePasswordCheckbox">
                                <label class="form-check-label" for="changePasswordCheckbox">
                                    Сменить пароль
                                </label>
                            </div>
                        </div>

                        <div id="passwordFields" style="display: none;" class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Новый пароль</label>
                                    <div class="position-relative">
                                        <input type="password" name="password" class="form-control" id="passwordField">
                                        <i class="bi bi-eye password-toggle" onclick="togglePassword('passwordField', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #b3b3b3;"></i>
                                        <div id="passwordRequirements" class="form-text mt-2"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Подтверждение пароля</label>
                                    <div class="position-relative">
                                        <input type="password" name="password_confirmation" class="form-control" id="passwordConfirmationField">
                                        <i class="bi bi-eye password-toggle" onclick="togglePassword('passwordConfirmationField', this)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #b3b3b3;"></i>
                                        <div class="invalid-feedback" id="passwordMismatch" style="display: none;">Пароли не совпадают.</div>
                                    </div>
                                </div>
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

// === Маска телефона ===
const phoneInput = document.getElementById('phone');
if (phoneInput) {
    phoneInput.addEventListener('input', function(e) {
        let value = phoneInput.value.replace(/\D/g, '');
        if (!value.startsWith('7') && value.length > 0) {
            value = '7' + value;
        }
        let formatted = '+7 (';
        if (value.length > 1) formatted += value.substring(1, 4);
        if (value.length >= 5) formatted += ') ' + value.substring(4, 7);
        if (value.length >= 8) formatted += '-' + value.substring(7, 9);
        if (value.length >= 10) formatted += '-' + value.substring(9, 11);
        phoneInput.value = formatted;
    });
}

document.getElementById('deleteForm').addEventListener('submit', function (e) {
    e.preventDefault();
    
    // Проверяем наличие активных бронирований
    const hasActiveBookings = @json($activeBookings->count() > 0);
    const confirmWithBookingsInput = document.getElementById('confirmWithBookings');
    
    // Первое подтверждение
    if (!confirm('Вы уверены, что хотите удалить аккаунт?')) {
        return;
    }
    
    // Если есть активные бронирования, показываем дополнительное предупреждение
    if (hasActiveBookings) {
        if (!confirm('⚠️ ВНИМАНИЕ! У вас есть активные бронирования. Все ваши бронирования будут удалены. Вы действительно хотите удалить аккаунт?')) {
            return;
        }
        // Устанавливаем флаг подтверждения для отправки на сервер
        confirmWithBookingsInput.value = '1';
    }
    
    // Отправляем форму
    this.submit();
});

// Переключение видимости полей пароля
const changePasswordCheckbox = document.getElementById('changePasswordCheckbox');
const passwordFields = document.getElementById('passwordFields');
const password = document.getElementById('passwordField');
const passwordConfirmation = document.getElementById('passwordConfirmationField');

// Сброс чекбокса при открытии модального окна
const editProfileModal = document.getElementById('editProfileModal');
if (editProfileModal) {
    editProfileModal.addEventListener('show.bs.modal', function() {
        if (changePasswordCheckbox) {
            changePasswordCheckbox.checked = false;
        }
        if (passwordFields) {
            passwordFields.style.display = 'none';
        }
        if (password) {
            password.value = '';
            password.classList.remove('is-invalid');
        }
        if (passwordConfirmation) {
            passwordConfirmation.value = '';
            passwordConfirmation.classList.remove('is-invalid');
        }
        const passwordMismatch = document.getElementById('passwordMismatch');
        if (passwordMismatch) passwordMismatch.style.display = 'none';
        if (passwordRequirements) passwordRequirements.innerHTML = '';
    });
}

if (changePasswordCheckbox && passwordFields) {
    changePasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordFields.style.display = 'block';
            // Очищаем поля при показе
            if (password) password.value = '';
            if (passwordConfirmation) passwordConfirmation.value = '';
        } else {
            passwordFields.style.display = 'none';
            // Очищаем поля и ошибки при скрытии
            if (password) {
                password.value = '';
                password.classList.remove('is-invalid');
            }
            if (passwordConfirmation) {
                passwordConfirmation.value = '';
                passwordConfirmation.classList.remove('is-invalid');
            }
            const passwordMismatch = document.getElementById('passwordMismatch');
            if (passwordMismatch) passwordMismatch.style.display = 'none';
        }
    });
}

const profileForm = document.getElementById('profileForm');
if (profileForm) {
    const passwordMismatch = document.getElementById('passwordMismatch');

    if (password && passwordConfirmation && passwordMismatch) {
        // Проверка надежности пароля
        if (password && passwordRequirements) {
            password.addEventListener('input', function() {
                // Проверяем только если поля видны (чекбокс отмечен)
                if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                    const value = password.value;
                    const minLength = value.length >= 8;
                    const hasUpper = /[A-ZА-Я]/.test(value);
                    const hasLower = /[a-zа-я]/.test(value);
                    const hasNumber = /\d/.test(value);
                    const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                    const valid = minLength && hasUpper && hasLower && hasNumber && hasSymbol;

                    passwordRequirements.innerHTML = value ? `
                        <span style="color: ${valid ? 'limegreen' : '#ff9800'};">
                            ${valid 
                                ? 'Пароль надёжный!' 
                                : 'Минимум 8 символов, заглавные, строчные, цифры и символы.'}
                        </span>
                    ` : '';
                } else {
                    passwordRequirements.innerHTML = '';
                }
            });
        }

        // Функция проверки паролей
        function checkPasswords() {
            // Проверяем только если поля видны (чекбокс отмечен)
            if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                if (password.value && passwordConfirmation.value) {
                    if (password.value !== passwordConfirmation.value) {
                        passwordMismatch.style.display = 'block';
                        passwordConfirmation.classList.add('is-invalid');
                    } else {
                        passwordMismatch.style.display = 'none';
                        passwordConfirmation.classList.remove('is-invalid');
                    }
                } else {
                    passwordMismatch.style.display = 'none';
                    passwordConfirmation.classList.remove('is-invalid');
                }
            }
        }

        // Проверка при вводе
        password.addEventListener('input', checkPasswords);
        passwordConfirmation.addEventListener('input', checkPasswords);

        // Проверка при отправке формы
        profileForm.addEventListener('submit', function(e) {
            // Проверяем пароли только если чекбокс отмечен
            if (changePasswordCheckbox && changePasswordCheckbox.checked) {
                // Проверка надежности пароля
                if (password.value) {
                    const minLength = password.value.length >= 8;
                    const hasUpper = /[A-ZА-Я]/.test(password.value);
                    const hasLower = /[a-zа-я]/.test(password.value);
                    const hasNumber = /\d/.test(password.value);
                    const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password.value);
                    const valid = minLength && hasUpper && hasLower && hasNumber && hasSymbol;
                    
                    if (!valid) {
                        e.preventDefault();
                        alert('Пароль должен содержать минимум 8 символов, заглавные и строчные буквы, цифры и символы.');
                        password.focus();
                        return;
                    }
                }
                
                if (password.value && passwordConfirmation.value && password.value !== passwordConfirmation.value) {
                    e.preventDefault();
                    passwordMismatch.style.display = 'block';
                    passwordConfirmation.classList.add('is-invalid');
                    passwordConfirmation.focus();
                } else if (password.value && !passwordConfirmation.value) {
                    e.preventDefault();
                    alert('Пожалуйста, подтвердите пароль');
                    passwordConfirmation.focus();
                } else if (!password.value && passwordConfirmation.value) {
                    e.preventDefault();
                    alert('Пожалуйста, введите новый пароль');
                    password.focus();
                }
            }
        });
    }
}
</script>

<style>
    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    /* Стили для плейсхолдера номера телефона */
    #phone::placeholder {
        color: #ffffff !important; /* Белый для темной темы */
        opacity: 0.7;
    }

    [data-theme="light"] #phone::placeholder {
        color: #000000 !important; /* Черный для светлой темы */
        opacity: 0.6;
    }

    /* Стили для стрелочки аккордеона в темной теме - белый цвет */
    #activeBookingsAccordion .accordion-button::after {
        filter: brightness(0) invert(1);
    }

    /* Для светлой темы возвращаем стандартный цвет */
    [data-theme="light"] #activeBookingsAccordion .accordion-button::after {
        filter: none;
    }

    /* Изменение подсветки активного аккордеона с синего на красный */


    #activeBookingsAccordion .accordion-button:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }

    /* Стили для кнопки печати билета */
    .btn-ticket-print {
        background: #0d6efd;
        color: #ffffff;
        border: 1px solid #0d6efd;
        padding: 0.375rem 0.75rem;
        font-weight: 600;
        border-radius: 4px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-ticket-print:hover {
        background: #0b5ed7;
        border-color: #0b5ed7;
        color: #ffffff;
    }
</style>
@endsection
