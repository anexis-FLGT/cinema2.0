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

        {{-- Форма поиска и фильтрации --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Поиск по ФИО</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Введите фамилию, имя или отчество..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Фильтр по роли</label>
                    <select name="role_id" class="form-select">
                        <option value="">Все роли</option>
                        @foreach($allRoles as $role)
                            <option value="{{ $role->id_role }}" @if(request('role_id') == $role->id_role) selected @endif>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info text-white w-100">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                </div>
                @if(request('search') || request('role_id'))
                    <div class="col-12">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Сбросить фильтры
                        </a>
                    </div>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-info">
                    <tr>
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
                            <td>{{ $user->last_name }}</td>
                            <td>{{ $user->first_name }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>{{ $user->role->role_name }}</td>
                            <td>{{ $user->login }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id_user }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
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

                        {{-- Модалка редактирования пользователя --}}
                        <div class="modal fade" id="editUserModal{{ $user->id_user }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.users.update', $user->id_user) }}" method="POST" id="editUserForm{{ $user->id_user }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать пользователя</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Фамилия <span class="text-danger">*</span></label>
                                                <input type="text" name="last_name" id="edit_last_name{{ $user->id_user }}" class="form-control" value="{{ $user->last_name }}" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Имя <span class="text-danger">*</span></label>
                                                <input type="text" name="first_name" id="edit_first_name{{ $user->id_user }}" class="form-control" value="{{ $user->first_name }}" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Отчество</label>
                                                <input type="text" name="middle_name" id="edit_middle_name{{ $user->id_user }}" class="form-control" value="{{ $user->middle_name ?? '' }}" pattern="[a-zA-Zа-яА-ЯёЁ\s]*" title="Только буквы (русские или английские)">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Телефон <span class="text-danger">*</span></label>
                                                <input type="text" name="phone" id="edit_phone{{ $user->id_user }}" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}" placeholder="+7 (___) ___-__-__" required>
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Логин <span class="text-danger">*</span></label>
                                                <input type="text" name="login" id="edit_login{{ $user->id_user }}" class="form-control @error('login') is-invalid @enderror" value="{{ old('login', $user->login) }}" required>
                                                @error('login')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="changePasswordCheckbox{{ $user->id_user }}">
                                                    <label class="form-check-label" for="changePasswordCheckbox{{ $user->id_user }}">
                                                        Сменить пароль
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3 position-relative" id="passwordFields{{ $user->id_user }}" style="display: none;">
                                                <label class="form-label">Пароль <span class="text-danger">*</span></label>
                                                <input type="password" name="password" id="edit_password{{ $user->id_user }}" class="form-control">
                                                <i class="bi bi-eye password-toggle" onclick="togglePassword('edit_password{{ $user->id_user }}', this)"></i>
                                                <div id="edit_passwordRequirements{{ $user->id_user }}" class="form-text mt-2"></div>
                                            </div>
                                            <div class="mb-3 position-relative" id="passwordConfirmationFields{{ $user->id_user }}" style="display: none;">
                                                <label class="form-label">Повторите пароль <span class="text-danger">*</span></label>
                                                <input type="password" name="password_confirmation" id="edit_password_confirmation{{ $user->id_user }}" class="form-control">
                                                <i class="bi bi-eye password-toggle" onclick="togglePassword('edit_password_confirmation{{ $user->id_user }}', this)"></i>
                                                <div class="invalid-feedback" id="edit_passwordMismatch{{ $user->id_user }}">Пароли не совпадают.</div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Роль <span class="text-danger">*</span></label>
                                                @if(auth()->user()->id_user == $user->id_user && $user->role_id == 1)
                                                    {{-- Для активного администратора поле роли отключено --}}
                                                    <select name="role_id" class="form-select" required disabled>
                                                        @foreach($roles as $role)
                                                            @if($role->role_name != 'Гость')
                                                                <option value="{{ $role->id_role }}" @if($role->id_role == $user->role_id) selected @endif>{{ $role->role_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                                                    <small class="form-text text-muted">Роль активного администратора нельзя изменить</small>
                                                @else
                                                    <select name="role_id" class="form-select" required>
                                                        @foreach($roles as $role)
                                                            @if($role->role_name != 'Гость')
                                                                <option value="{{ $role->id_role }}" @if($role->id_role == $user->role_id) selected @endif>{{ $role->role_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-info text-white">Сохранить</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

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
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-1"></i> Пользователи пока не добавлены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Информация о количестве найденных пользователей --}}
        @if(request('search') || request('role_id'))
            <div class="mt-3">
                <small class="text-muted">
                    Найдено пользователей: <strong>{{ $users->total() }}</strong>
                    @if(request('search'))
                        по запросу "{{ request('search') }}"
                    @endif
                    @if(request('role_id'))
                        с ролью "{{ $allRoles->where('id_role', request('role_id'))->first()->role_name ?? '' }}"
                    @endif
                </small>
            </div>
        @endif

        {{-- Пагинация --}}
        @if($users->hasPages())
            <div class="mt-4">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                        <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Имя <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required pattern="[a-zA-Zа-яА-ЯёЁ\s]+" title="Только буквы (русские или английские)">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Отчество</label>
                        <input type="text" name="middle_name" id="middle_name" class="form-control @error('middle_name') is-invalid @enderror" value="{{ old('middle_name') }}" pattern="[a-zA-Zа-яА-ЯёЁ\s]*" title="Только буквы (русские или английские)">
                        @error('middle_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+7 (___) ___-__-__" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Логин <span class="text-danger">*</span></label>
                        <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" required>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
        
        // Автоматически открываем модальное окно добавления, если есть ошибки валидации
        @if($errors->any() && !session('edit_user_id'))
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        @endif
        
        // Автоматически открываем модальное окно редактирования, если есть ошибки валидации для конкретного пользователя
        @if(session('edit_user_id') && $errors->any())
            @php
                $editUserId = session('edit_user_id');
            @endphp
            const editUserModal{{ $editUserId }} = new bootstrap.Modal(document.getElementById('editUserModal{{ $editUserId }}'));
            editUserModal{{ $editUserId }}.show();
        @endif
    });

    // Инициализация при открытии модалки добавления
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

    // Инициализация форм редактирования пользователей
    function initEditUserForm(userId) {
        const changePasswordCheckbox = document.getElementById('changePasswordCheckbox' + userId);
        const passwordFields = document.getElementById('passwordFields' + userId);
        const passwordConfirmationFields = document.getElementById('passwordConfirmationFields' + userId);
        const password = document.getElementById('edit_password' + userId);
        const passwordConfirmation = document.getElementById('edit_password_confirmation' + userId);
        const passwordMismatch = document.getElementById('edit_passwordMismatch' + userId);
        const passwordRequirements = document.getElementById('edit_passwordRequirements' + userId);
        const editForm = document.getElementById('editUserForm' + userId);

        // Валидация ФИО - только буквы
        const nameFields = ['edit_last_name', 'edit_first_name', 'edit_middle_name'];
        nameFields.forEach(fieldPrefix => {
            const field = document.getElementById(fieldPrefix + userId);
            if (field && !field.dataset.nameValidationAdded) {
                field.dataset.nameValidationAdded = 'true';
                field.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^a-zA-Zа-яА-ЯёЁ\s]/g, '');
                });
                field.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = (e.clipboardData || window.clipboardData).getData('text');
                    const cleaned = paste.replace(/[^a-zA-Zа-яА-ЯёЁ\s]/g, '');
                    this.value = cleaned;
                });
            }
        });

        // Маска телефона для редактирования
        const editPhoneInput = document.getElementById('edit_phone' + userId);
        if (editPhoneInput && !editPhoneInput.dataset.maskInitialized) {
            editPhoneInput.dataset.maskInitialized = 'true';
            editPhoneInput.addEventListener('input', function(e) {
                let value = editPhoneInput.value.replace(/\D/g, '');
                if (!value.startsWith('7') && value.length > 0) {
                    value = '7' + value;
                }
                let formatted = '+7 (';
                if (value.length > 1) formatted += value.substring(1, 4);
                if (value.length >= 5) formatted += ') ' + value.substring(4, 7);
                if (value.length >= 8) formatted += '-' + value.substring(7, 9);
                if (value.length >= 10) formatted += '-' + value.substring(9, 11);
                editPhoneInput.value = formatted;
            });
        }

        // Переключение видимости полей пароля
        if (changePasswordCheckbox && passwordFields && passwordConfirmationFields && !changePasswordCheckbox.dataset.listenerAdded) {
            changePasswordCheckbox.dataset.listenerAdded = 'true';
            changePasswordCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    passwordFields.style.display = 'block';
                    passwordConfirmationFields.style.display = 'block';
                    if (password) password.required = true;
                    if (passwordConfirmation) passwordConfirmation.required = true;
                    if (password) password.value = '';
                    if (passwordConfirmation) passwordConfirmation.value = '';
                } else {
                    passwordFields.style.display = 'none';
                    passwordConfirmationFields.style.display = 'none';
                    if (password) password.required = false;
                    if (passwordConfirmation) passwordConfirmation.required = false;
                    if (password) password.value = '';
                    if (passwordConfirmation) passwordConfirmation.value = '';
                    if (passwordMismatch) passwordMismatch.style.display = 'none';
                    if (passwordConfirmation) passwordConfirmation.classList.remove('is-invalid');
                    if (passwordRequirements) passwordRequirements.innerHTML = '';
                }
            }, { once: false });

            // Проверка надежности пароля
            if (password && passwordRequirements) {
                password.addEventListener('input', function() {
                    if (changePasswordCheckbox.checked) {
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
                    }
                });
            }

            // Проверка совпадения паролей
            function checkEditPasswords() {
                if (changePasswordCheckbox.checked && password && passwordConfirmation) {
                    if (password.value && passwordConfirmation.value) {
                        if (password.value !== passwordConfirmation.value) {
                            if (passwordMismatch) passwordMismatch.style.display = 'block';
                            if (passwordConfirmation) passwordConfirmation.classList.add('is-invalid');
                        } else {
                            if (passwordMismatch) passwordMismatch.style.display = 'none';
                            if (passwordConfirmation) passwordConfirmation.classList.remove('is-invalid');
                        }
                    }
                }
            }

            if (password && passwordConfirmation) {
                password.addEventListener('input', checkEditPasswords);
                passwordConfirmation.addEventListener('input', checkEditPasswords);
            }

            // Проверка при отправке формы
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    if (changePasswordCheckbox.checked) {
                        if (password && password.value) {
                            const minLength = password.value.length >= 8;
                            const hasUpper = /[A-ZА-Я]/.test(password.value);
                            const hasLower = /[a-zа-я]/.test(password.value);
                            const hasNumber = /\d/.test(password.value);
                            const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password.value);
                            const valid = minLength && hasUpper && hasLower && hasNumber && hasSymbol;
                            
                            if (!valid) {
                                e.preventDefault();
                                alert('Пароль должен содержать минимум 8 символов, заглавные и строчные буквы, цифры и символы.');
                                if (password) password.focus();
                                return;
                            }
                        }
                        
                        if (password && passwordConfirmation && password.value !== passwordConfirmation.value) {
                            e.preventDefault();
                            if (passwordMismatch) passwordMismatch.style.display = 'block';
                            if (passwordConfirmation) passwordConfirmation.classList.add('is-invalid');
                            if (passwordConfirmation) passwordConfirmation.focus();
                        } else if (password && password.value && !passwordConfirmation.value) {
                            e.preventDefault();
                            alert('Пожалуйста, подтвердите пароль');
                            if (passwordConfirmation) passwordConfirmation.focus();
                        } else if (!password.value && passwordConfirmation && passwordConfirmation.value) {
                            e.preventDefault();
                            alert('Пожалуйста, введите новый пароль');
                            if (password) password.focus();
                        }
                    }
                });
            }
        }
    }

    // Инициализация всех форм редактирования при открытии модалок
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($users as $user)
            const editUserModal{{ $user->id_user }} = document.getElementById('editUserModal{{ $user->id_user }}');
            if (editUserModal{{ $user->id_user }}) {
                    editUserModal{{ $user->id_user }}.addEventListener('shown.bs.modal', function() {
                        // Сбрасываем чекбокс и поля пароля при открытии
                        const changePasswordCheckbox = document.getElementById('changePasswordCheckbox{{ $user->id_user }}');
                        if (changePasswordCheckbox) {
                            changePasswordCheckbox.checked = false;
                            changePasswordCheckbox.dataset.listenerAdded = '';
                        }
                        const passwordFields = document.getElementById('passwordFields{{ $user->id_user }}');
                        if (passwordFields) passwordFields.style.display = 'none';
                        const passwordConfirmationFields = document.getElementById('passwordConfirmationFields{{ $user->id_user }}');
                        if (passwordConfirmationFields) passwordConfirmationFields.style.display = 'none';
                        const password = document.getElementById('edit_password{{ $user->id_user }}');
                        if (password) {
                            password.value = '';
                            password.required = false;
                        }
                        const passwordConfirmation = document.getElementById('edit_password_confirmation{{ $user->id_user }}');
                        if (passwordConfirmation) {
                            passwordConfirmation.value = '';
                            passwordConfirmation.required = false;
                            passwordConfirmation.classList.remove('is-invalid');
                        }
                        const passwordMismatch = document.getElementById('edit_passwordMismatch{{ $user->id_user }}');
                        if (passwordMismatch) passwordMismatch.style.display = 'none';
                        const passwordRequirements = document.getElementById('edit_passwordRequirements{{ $user->id_user }}');
                        if (passwordRequirements) passwordRequirements.innerHTML = '';
                        
                        // Сбрасываем флаги инициализации
                        const editPhoneInput = document.getElementById('edit_phone{{ $user->id_user }}');
                        if (editPhoneInput) editPhoneInput.dataset.maskInitialized = '';
                        const nameFields = ['edit_last_name', 'edit_first_name', 'edit_middle_name'];
                        nameFields.forEach(fieldPrefix => {
                            const field = document.getElementById(fieldPrefix + '{{ $user->id_user }}');
                            if (field) field.dataset.nameValidationAdded = '';
                        });
                        
                        initEditUserForm({{ $user->id_user }});
                    });
                }
        @endforeach
    });
</script>

<style>
    /* Стили для поиска по ФИО - адаптация под темы */
    
    /* Темная тема */
    [data-theme="dark"] .input-group-text {
        background-color: var(--bg-secondary) !important;
        border-color: var(--border-color) !important;
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group-text i {
        color: #ffffff !important;
    }
    
    [data-theme="dark"] .input-group .form-control {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .input-group .form-control::placeholder {
        color: #ffffff !important;
        opacity: 0.7;
    }
    
    [data-theme="dark"] .input-group .form-control:focus {
        background-color: var(--input-bg) !important;
        border-color: var(--input-focus-border) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="dark"] .input-group .form-control:focus::placeholder {
        color: #ffffff !important;
        opacity: 0.5;
    }
    
    /* Светлая тема */
    [data-theme="light"] .input-group-text {
        background-color: var(--bg-secondary) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="light"] .input-group-text i {
        color: var(--text-primary) !important;
    }
    
    [data-theme="light"] .input-group .form-control {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="light"] .input-group .form-control::placeholder {
        color: var(--text-secondary) !important;
        opacity: 0.6;
    }
    
    [data-theme="light"] .input-group .form-control:focus {
        background-color: var(--input-bg) !important;
        border-color: var(--input-focus-border) !important;
        color: var(--text-primary) !important;
    }
    
    [data-theme="light"] .input-group .form-control:focus::placeholder {
        color: var(--text-secondary) !important;
        opacity: 0.5;
    }
</style>
@endsection
