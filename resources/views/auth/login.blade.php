<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — MaxTicket</title>
    
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
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2 class="text-center mb-4 text-light fw-bold">Войти в систему</h2>

            @if (session('success'))
                <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger text-center">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Логин</label>
                    <input type="text" name="login" class="form-control" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <i class="bi bi-eye password-toggle" onclick="togglePassword()"></i>
                </div>

                <button type="submit" class="btn btn-danger w-100 mt-3 fw-semibold">Войти</button>
            </form>

            <div class="text-center mt-4">
                <p class="text-secondary">Нет аккаунта?
                    <a href="{{ route('register') }}" class="text-light fw-semibold">Зарегистрироваться</a>
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = event.target;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>
