@php
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Admin';
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
        ['label' => 'Content', 'route' => 'admin.content.index', 'active' => 'admin.content.*', 'icon' => 'book-open'],
        ['label' => 'Vouchers', 'route' => 'admin.vouchers.index', 'active' => 'admin.vouchers.*', 'icon' => 'ticket'],
        ['label' => 'Certificates', 'route' => 'admin.certificates.index', 'active' => 'admin.certificates.*', 'icon' => 'award'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} | LearnCSS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="{{ asset('style.css') }}?v=4">
    <style>
        body { margin: 0; overflow: hidden; }
        .admin-shell { display: grid; grid-template-columns: 270px 1fr; height: calc(100vh - 75px); background: var(--bg); overflow: hidden; }
        .sidebar { background: rgba(10,10,15,0.8); backdrop-filter: blur(20px); border-right: 1px solid var(--border); padding: 1.5rem; display: flex; flex-direction: column; overflow-y: auto; gap: 1rem; }
        body.light-mode .sidebar { background: rgba(232,232,232,0.85); }
        .nav-list { display: flex; flex-direction: column; gap: 0.5rem; }
        .nav-link { padding: 0.68rem 0.85rem; border-radius: 8px; font-size: 0.83rem; color: var(--text); display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; text-decoration: none; }
        .nav-link:hover { background: rgba(255,255,255,0.05); }
        body.light-mode .nav-link:hover { background: rgba(0,0,0,0.05); }
        .nav-link.active { background: var(--gradient); color: #fff; font-weight: 600; box-shadow: 0 4px 15px var(--glow); }

        .admin-main { flex: 1; background: var(--bg); display: flex; flex-direction: column; min-width: 0; overflow-y: auto; }
        
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; margin: 0; }
        .kicker { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: var(--accent); margin-bottom: 0.3rem; }

        .content { padding: 2.5rem; max-width: 1200px; margin: 0 auto; width: 100%; }
        
        .admin-user-chip { display: flex; align-items: center; gap: 1rem; padding: 0.35rem 0.85rem; border: 1.5px solid var(--border); border-radius: 12px; background: rgba(255,255,255,0.04); }
        body.light-mode .admin-user-chip { background: rgba(0,0,0,0.04); }
        .avatar { width: 26px; height: 26px; border-radius: 8px; background: var(--gradient); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.7rem; color: #fff; }
        
        /* Dashboard styles */
        .page-grid, .stat-grid, .split-grid { display: grid; gap: 1.25rem; }
        .stat-grid { grid-template-columns: repeat(4, 1fr); }
        .page-grid.two { grid-template-columns: 1.45fr 0.95fr; }
        
        .panel, .metric-card { background: rgba(255,255,255,0.04); border: 1.5px solid var(--border); border-radius: 12px; padding: 1.5rem; }
        body.light-mode .panel, body.light-mode .metric-card { background: rgba(0,0,0,0.03); }
        .panel-label, .metric-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.75rem; }
        .metric-value { font-family: 'Outfit', sans-serif; font-size: 1.8rem; font-weight: 800; margin-bottom: 0.25rem; background: var(--gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .metric-note { font-size: 0.82rem; color: var(--text-muted); }
        
        .panel-title { font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 700; margin-bottom: 0.4rem; }
        .panel-subtitle { font-size: 0.88rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.5rem; }
        
        .list-stack { display: flex; flex-direction: column; gap: 0.85rem; }
        .list-item { background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); border-radius: 10px; padding: 1rem; }
        body.light-mode .list-item { background: rgba(0,0,0,0.02); }
        .list-item strong { display: block; font-size: 0.95rem; margin-bottom: 0.4rem; }
        
        .progress-track { height: 6px; background: rgba(255,255,255,0.1); border-radius: 99px; margin-bottom: 0.5rem; overflow: hidden; }
        body.light-mode .progress-track { background: rgba(0,0,0,0.1); }
        .progress-fill { height: 100%; background: var(--gradient); border-radius: 99px; }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .form-grid .field.full { grid-column: 1 / -1; }
        
        /* Modals overrides */
        .modal-content { background: var(--bg2); border: 1.5px solid var(--border); border-radius: 16px; width: 100%; max-width: 600px; }
        body.light-mode .modal-content { background: #e8e8e8; }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-title { margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.4rem; font-weight: 700; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; }

        /* Tables */
        .table-wrap { overflow-x: auto; border: 1.5px solid var(--border); border-radius: 12px; background: rgba(255,255,255,0.02); margin-top: 1rem; }
        body.light-mode .table-wrap { background: rgba(0,0,0,0.02); }
        .data-table { width: 100%; border-collapse: collapse; min-width: 700px; }
        .data-table th, .data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); text-align: left; font-size: 0.875rem; }
        .data-table th { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: var(--text-muted); background: rgba(255,255,255,0.02); }
        body.light-mode .data-table th { background: rgba(0,0,0,0.02); }
        .data-table tr:last-child td { border-bottom: none; }
        
        .status { display: inline-flex; align-items: center; padding: 0.35rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; background: rgba(255,255,255,0.05); }
        .status.success { color: var(--correct); background: rgba(16,185,129,0.12); }
        .status.warning { color: #f59e0b; background: rgba(245,158,11,0.12); }
        .status.danger { color: var(--wrong); background: rgba(239,68,68,0.12); }
        
        .toolbar { display: flex; gap: 1rem; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 1.25rem; }
        .toolbar-group { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

        .notice { padding: 1rem 1.5rem; border: 1.5px solid var(--border); border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.875rem; background: rgba(255,255,255,0.03); }

        .btn-danger { background: var(--wrong); color: #fff; border: none; padding: .75rem 1.5rem; border-radius: 10px; font-family: inherit; font-weight: 600; font-size: .9rem; cursor: pointer; transition: all .3s; white-space: nowrap; }
        .btn-warning { background: #f59e0b; color: #fff; border: none; padding: .75rem 1.5rem; border-radius: 10px; font-family: inherit; font-weight: 600; font-size: .9rem; cursor: pointer; transition: all .3s; white-space: nowrap; }

        /* Dropdown overrides */
        .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: var(--bg2); border: 1.5px solid var(--border); border-radius: 12px; padding: 0.5rem; min-width: 160px; z-index: 100; margin-top: 0.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        body.light-mode .dropdown-menu { background: #e8e8e8; }
        .dropdown-menu.open { display: block; }
        .dropdown-item { display: block; width: 100%; text-align: left; padding: 0.6rem 1rem; border: none; background: none; color: var(--text); font-size: 0.85rem; border-radius: 8px; cursor: pointer; }
        .dropdown-item:hover { background: rgba(255,255,255,0.05); }
        body.light-mode .dropdown-item:hover { background: rgba(0,0,0,0.05); }
        .dropdown-item.danger { color: var(--wrong); }
        .dropdown-item.danger:hover { background: rgba(239,68,68,0.1); }

        @media (max-width: 900px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar { display: none; } /* On mobile, we might need a hamburger menu */
            .stat-grid, .page-grid.two { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="light-mode">
    <script>
        (function () {
            const saved = localStorage.getItem('cssm_theme');
            if (saved === 'dark') {
                document.body.classList.remove('light-mode');
            }
        })();
    </script>
        <nav class="landing-nav" style="padding: 1rem 2.5rem; z-index: 100;">
        <div class="nav-logo" style="display: flex; align-items: center; gap: 0.5rem;">
            LearnCSS Admin
        </div>
        <div class="nav-actions">
            @yield('header_actions')
            <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-ghost">Log out</button>
            </form>
            <button id="theme-toggle-btn" class="btn-icon-round" title="Toggle light/dark mode" aria-label="Toggle theme">
                <i id="theme-icon" data-lucide="moon"></i>
            </button>
        </div>
    </nav>

    <div class="admin-shell">
        <aside class="sidebar">
            <nav class="nav-list" aria-label="Admin navigation">
                @foreach ($navItems as $item)
                    <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <i data-lucide="{{ $item['icon'] }}" style="width: 18px; height: 18px; opacity: 0.9;"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="admin-main">
            <section class="content">
                <div class="page-header">
                    <p class="kicker">@yield('kicker', 'Admin Panel')</p>
                    <h1>{{ $pageTitle }}</h1>
                </div>
                @if (session('success'))
                    <div class="notice" style="border-color: #bbf7d0; background: #f0fdf4; color: #166534;">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="notice" style="border-color: #fecaca; background: #fef2f2; color: #991b1b;">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="notice" style="border-color: #fecaca; background: #fef2f2; color: #991b1b;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')


            </section>
        </main>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Global Dropdown Toggling Actions
        function toggleDropdown(btn, event) {
            event.stopPropagation();
            const menu = btn.nextElementSibling;
            const isOpen = menu.classList.contains('open');
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open'));
            if (!isOpen) {
                menu.classList.add('open');
            }
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open'));
            }
        });

        // Theme Toggling Logic
        const themeBtn = document.getElementById('theme-toggle-btn');
        const themeIcon = document.getElementById('theme-icon');

        function updateIcon() {
            if (themeIcon) {
                const isLight = document.body.classList.contains('light-mode');
                themeIcon.setAttribute('data-lucide', isLight ? 'moon' : 'sun');
                if (window.lucide) {
                    lucide.createIcons();
                }
            }
        }

        // Initialize icon state
        updateIcon();

        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                const isLight = document.body.classList.toggle('light-mode');
                localStorage.setItem('cssm_theme', isLight ? 'light' : 'dark');
                updateIcon();
            });
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
