<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MaxTicket')</title>
    
    {{-- Применяем тему сразу, до загрузки CSS --}}
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    {{-- Bootstrap и иконки --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Swiper CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">

    {{-- Основные стили --}}
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>


<body class="d-flex flex-column min-vh-100" style="background-color: var(--bg-primary); color: var(--text-primary);">

    {{-- ======== HEADER ======== --}}
    <header class="header py-3 border-bottom" style="background-color: var(--bg-header); border-color: var(--border-color) !important;">
        <div class="container d-flex align-items-center justify-content-between">
            {{-- Логотип --}}
            <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none">
                <h4 class="fw-bold mb-0" style="color: var(--text-primary);">MaxTicket</h4>
            </a>

            {{-- Навигация --}}
            <nav class="d-flex align-items-center">
                <ul class="nav me-4">
                    <li class="nav-item"><a href="{{ route('home') }}" class="nav-link px-3" style="color: var(--text-primary);">Главная</a></li>
                    <li class="nav-item"><a href="{{ route('halls') }}" class="nav-link px-3" style="color: var(--text-primary);">Залы</a></li>
                    <li class="nav-item"><a href="{{ route('sessions') }}" class="nav-link px-3" style="color: var(--text-primary);">Сеансы</a></li>
                    <li class="nav-item"><a href="{{ route('contacts') }}" class="nav-link px-3" style="color: var(--text-primary);">О нас</a></li>
                </ul>

                {{-- Кнопки входа / профиля --}}
                <div class="action-buttons text-end d-flex align-items-center gap-2">
                    {{-- Переключатель темы --}}
                    <button id="theme-toggle" class="theme-toggle" title="Переключить тему">
                        <i class="bi bi-sun-fill"></i>
                    </button>
                    
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-light me-2">Войти</a>
                        <a href="{{ route('register') }}" class="btn btn-danger">Регистрация</a>
                    @else
                        <div class="dropdown">
                            <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->first_name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="background-color: var(--bg-secondary); border-color: var(--border-color) !important;">
                                @if(Auth::user()->role_id == 1)
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}" style="color: var(--text-primary);">Админ панель</a></li>
                                @endif
                                @if(Auth::user()->role_id == 2)
                                    <li><a class="dropdown-item" href="{{ route('user.dashboard') }}" style="color: var(--text-primary);">Личный кабинет</a></li>
                                @endif
                                <li><hr class="dropdown-divider" style="border-color: var(--border-color) !important;"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger">Выйти</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endguest
                </div>
            </nav>
        </div>
    </header>

    {{-- ======== CONTENT ======== --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- ======== FOOTER ======== --}}
    <footer class="py-4 border-top mt-auto" style="background-color: var(--bg-footer); border-color: var(--border-color) !important;">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center text-center text-md-start">
            <div class="mb-3 mb-md-0">
                <p class="mb-1">&copy; 2025 MaxTicket. Все права защищены.</p>
                <small class="text-secondary">Сеть кинотеатров нового поколения</small>
            </div>

            <div class="social-links">
                <a href="#" class="mx-2" style="color: var(--text-secondary);"><i class="fa-brands fa-vk fa-lg"></i></a>
                <a href="#" class="mx-2" style="color: var(--text-secondary);"><i class="fa-brands fa-telegram fa-lg"></i></a>
                <a href="#" class="mx-2" style="color: var(--text-secondary);"><i class="fa-brands fa-whatsapp fa-lg"></i></a>
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Theme Toggle JS --}}
    <script src="{{ asset('assets/js/theme.js') }}"></script>
</body>
</html>
