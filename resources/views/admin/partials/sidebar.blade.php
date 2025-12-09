<aside class="admin-sidebar">
    <div class="sidebar-header px-3 py-3 d-flex align-items-center">
        
    </div>

    <nav class="nav flex-column px-2">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 me-2"></i> Панель управления
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="bi bi-people me-2"></i> Пользователи
        </a>
        <a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions*') ? 'active' : '' }}">
            <i class="bi bi-clock-history me-2"></i> Сеансы
        </a>
        <a href="{{ route('admin.movies.index') }}" class="nav-link {{ request()->routeIs('admin.movies*') ? 'active' : '' }}">
            <i class="bi bi-film me-2"></i> Фильмы
        </a>
        <a href="{{ route('admin.genres.index') }}" class="nav-link {{ request()->routeIs('admin.genres*') ? 'active' : '' }}">
            <i class="bi bi-tags me-2"></i> Жанры
        </a>
        <a href="{{ route('admin.halls.index') }}" class="nav-link {{ request()->routeIs('admin.halls*') ? 'active' : '' }}">
            <i class="bi bi-building me-2"></i> Залы
        </a>
        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line me-2"></i> Отчёты
        </a>
    </nav>

</aside>
