@extends('admin.layouts.admin')

@section('title', 'Управление пользователями')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/users.css') }}">

<div class="container-fluid">
    {{-- Сообщения об успехе/ошибках --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="card shadow-sm border-0 p-4 rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-info"><i class="bi bi-people me-2"></i> Управление пользователями</h4>
            <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i> Добавить пользователя
            </button>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-info">
                    <tr>
                        <th>№</th>
                        <th>Фамилия</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Логин</th>
                        <th class="text-center">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id_user }}</td>
                            <td>{{ $user->last_name }}</td>
                            <td>{{ $user->first_name }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>{{ $user->role->role_name }}</td>
                            <td>{{ $user->login }}</td>
                            <td class="text-center">
                                @if(auth()->user()->id_user != $user->id_user || $user->role_id != 1)
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $user->id_user }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Нельзя изменить роль активного администратора">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->id_user != $user->id_user || $user->role_id != 1)
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id_user }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-danger" disabled title="Нельзя удалить активного администратора">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        {{-- Модалка редактирования роли --}}
                        @if(auth()->user()->id_user != $user->id_user || $user->role_id != 1)
                        <div class="modal fade" id="editRoleModal{{ $user->id_user }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.users.update', $user->id_user) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Изменить роль пользователя</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Роль</label>
                                            <select name="role_id" class="form-select" required>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id_role }}" @if($role->id_role == $user->role_id) selected @endif>{{ $role->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Сохранить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Модалка удаления --}}
                        @if(auth()->user()->id_user != $user->id_user || $user->role_id != 1)
                        <div class="modal fade" id="deleteUserModal{{ $user->id_user }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.users.destroy', $user->id_user) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Удалить пользователя?</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Вы уверены, что хотите удалить пользователя <strong>{{ $user->last_name }} {{ $user->first_name }}</strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-danger">Удалить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Пользователи пока не добавлены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        @if($users->hasPages())
            <div class="mt-4">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

{{-- Модалка добавления пользователя --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Добавить пользователя</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Фамилия <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Отчество</label>
                        <input type="text" name="middle_name" id="middle_name" class="form-control" pattern="[a-zA-Zа-яА-ЯёЁ\s]*" title="Только буквы (русские или английские)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="+7 (___) ___-__-__" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Логин <span class="text-danger">*</span></label>
                        <input type="text" name="login" class="form-control" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="form-label">Пароль <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                        <i class="bi bi-eye password-toggle" onclick="togglePassword('password', this)"></i>
                        <div id="passwordRequirements" class="form-text mt-2"></div>
                    </div>
                    <div class="mb-3 position-relative">
                        <label class="form-label">Повторите пароль <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        <i class="bi bi-eye password-toggle" onclick="togglePassword('password_confirmation', this)"></i>
                        <div class="invalid-feedback" id="passwordMismatch">Пароли не совпадают.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Роль <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                                @if($role->role_name != 'Гость')
                                    <option value="{{ $role->id_role }}">{{ $role->role_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info text-white">Добавить</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // === Переключение видимости пароля ===
    function togglePassword(id, el) {
        const input = document.getElementById(id);
        if (input && input.type === "password") {
            input.type = "text";
            el.classList.replace('bi-eye', 'bi-eye-slash');
        } else if (input) {
            input.type = "password";
            el.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }

    // === Инициализация маски телефона и проверки паролей ===
    function initUserForm() {
        // Валидация ФИО - только буквы
        const nameFields = ['last_name', 'first_name', 'middle_name'];
        nameFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.dataset.nameValidationAdded) {
                field.dataset.nameValidationAdded = 'true';
                field.addEventListener('input', function(e) {
                    // Удаляем все символы, кроме букв и пробелов
                    this.value = this.value.replace(/[^a-zA-Zа-яА-ЯёЁ\s]/g, '');
                });
                // Блокируем вставку недопустимых символов
                field.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = paste.replace(/[^a-zA-Zа-яА-ЯёЁ\s]/g, '');
                    this.value = cleaned;
                });
            }
        });

        // Маска телефона
        const phoneInput = document.getElementById('phone');
        if (phoneInput && !phoneInput.dataset.maskInitialized) {
            phoneInput.dataset.maskInitialized = 'true';
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

        // Проверка совпадения паролей и надежности
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordMismatch = document.getElementById('passwordMismatch');
        const passwordRequirements = document.getElementById('passwordRequirements');

        if (password && passwordConfirmation && passwordMismatch) {
            // Сбрасываем состояние при открытии модалки
            passwordMismatch.style.display = 'none';
            passwordConfirmation.classList.remove('is-invalid');

            // Проверка надежности пароля
            if (password && passwordRequirements) {
                password.addEventListener('input', function() {
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
                });
            }

            // Функция проверки паролей
            function checkPasswords() {
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

            // Проверка при вводе (только если обработчики еще не добавлены)
            if (!password.dataset.listenerAdded) {
                password.dataset.listenerAdded = 'true';
                passwordConfirmation.dataset.listenerAdded = 'true';
                
                password.addEventListener('input', checkPasswords);
                passwordConfirmation.addEventListener('input', checkPasswords);
            }

            // Проверка при отправке формы
            const addUserForm = document.querySelector('#addUserModal form');
            if (addUserForm && !addUserForm.dataset.submitListenerAdded) {
                addUserForm.dataset.submitListenerAdded = 'true';
                addUserForm.addEventListener('submit', function(e) {
                    if (password.value !== passwordConfirmation.value) {
                        e.preventDefault();
                        passwordMismatch.style.display = 'block';
                        passwordConfirmation.classList.add('is-invalid');
                        passwordConfirmation.focus();
                    } else {
                        passwordMismatch.style.display = 'none';
                        passwordConfirmation.classList.remove('is-invalid');
                    }
                });
            }
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        initUserForm();
    });

    // Инициализация при открытии модалки
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.addEventListener('shown.bs.modal', function() {
            // Сбрасываем флаги при открытии модалки
            const phoneInput = document.getElementById('phone');
            if (phoneInput) phoneInput.dataset.maskInitialized = '';
            const password = document.getElementById('password');
            if (password) password.dataset.listenerAdded = '';
            const passwordConfirmation = document.getElementById('password_confirmation');
            if (passwordConfirmation) passwordConfirmation.dataset.listenerAdded = '';
            const addUserForm = document.querySelector('#addUserModal form');
            if (addUserForm) addUserForm.dataset.submitListenerAdded = '';
            
            initUserForm();
        });
    }
</script>
@endsection
