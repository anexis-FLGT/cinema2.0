<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — MaxTicket</title>
    
    {{-- Применяем тему сразу, до загрузки CSS --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/register.css') }}">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <h2 class="text-center mb-4 text-light fw-bold">Создать аккаунт</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Фамилия</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Имя</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label">Отчество</label>
                    <input type="text" name="middle_name" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="text" name="phone" id="phone" class="form-control" placeholder="+7 (___) ___-__-__" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <i class="bi bi-eye password-toggle" onclick="togglePassword('password', this)"></i>
                    <div id="passwordRequirements" class="form-text mt-2"></div>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Подтверждение пароля</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    <i class="bi bi-eye password-toggle" onclick="togglePassword('password_confirmation', this)"></i>
                    <div class="invalid-feedback" id="passwordMismatch">Пароли не совпадают.</div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit" class="btn btn-danger w-100 mt-4 fw-semibold">Зарегистрироваться</button>
                <p class="text-center text-secondary mt-3">Уже есть аккаунт? 
                    <a href="{{ route('login') }}" class="text-light fw-semibold">Войти</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        // === Переключение видимости пароля ===
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

        const form = document.querySelector('form');
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const mismatch = document.getElementById('passwordMismatch');
        const requirements = document.getElementById('passwordRequirements');
        const phoneInput = document.getElementById('phone');

        mismatch.style.display = 'none';

        form.addEventListener('submit', function(e) {
            if (password.value !== confirm.value) {
                e.preventDefault();
                mismatch.style.display = 'block';
            } else {
                mismatch.style.display = 'none';
            }
        });

        password.addEventListener('input', function() {
            const value = password.value;
            const minLength = value.length >= 8;
            const hasUpper = /[A-ZА-Я]/.test(value);
            const hasLower = /[a-zа-я]/.test(value);
            const hasNumber = /\d/.test(value);
            const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(value);
            const valid = minLength && hasUpper && hasLower && hasNumber && hasSymbol;

            requirements.innerHTML = value ? `
                <span style="color: ${valid ? 'limegreen' : '#ff9800'};">
                    ${valid 
                        ? 'Пароль надёжный!' 
                        : 'Минимум 8 символов, заглавные, строчные, цифры и символы.'}
                </span>
            ` : '';
        });

        // === Маска телефона ===
        phoneInput.addEventListener('input', function(e) {
            let value = phoneInput.value.replace(/\D/g, '');
            if (!value.startsWith('7')) value = '7' + value;
            let formatted = '+7 (';
            if (value.length > 1) formatted += value.substring(1, 4);
            if (value.length >= 5) formatted += ') ' + value.substring(4, 7);
            if (value.length >= 8) formatted += '-' + value.substring(7, 9);
            if (value.length >= 10) formatted += '-' + value.substring(9, 11);
            phoneInput.value = formatted;
        });
    </script>
    <script src="{{ asset('assets/js/theme.js') }}"></script>
</body>
</html>
