@php
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Admin';
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'mark' => 'DB'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'mark' => 'US'],
        ['label' => 'Content', 'route' => 'admin.content.index', 'active' => 'admin.content.*', 'mark' => 'CT'],
        ['label' => 'Progress', 'route' => 'admin.progress.index', 'active' => 'admin.progress.*', 'mark' => 'PR'],
        ['label' => 'Vouchers', 'route' => 'admin.vouchers.index', 'active' => 'admin.vouchers.*', 'mark' => 'VC'],
        ['label' => 'Certificates', 'route' => 'admin.certificates.index', 'active' => 'admin.certificates.*', 'mark' => 'CF'],
        ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'mark' => 'RP'],
        ['label' => 'Notifications', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*', 'mark' => 'NT'],
        ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'active' => 'admin.audit-logs.*', 'mark' => 'AL'],
        ['label' => 'Settings', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'mark' => 'ST'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} | LearnCSS Admin</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --sidebar: #0f172a;
            --sidebar-soft: #17223a;
            --text: #1f2937;
            --muted: #64748b;
            --line: #e2e8f0;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0891b2;
            --radius: 14px;
            --shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            background: var(--bg);
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .admin-shell {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--sidebar);
            color: #dbeafe;
            padding: 24px 18px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 8px 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.09);
            margin-bottom: 18px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #ffffff;
            font-weight: 800;
            letter-spacing: 0;
        }

        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #94a3b8;
        }

        .nav-list {
            display: grid;
            gap: 6px;
            margin-top: 14px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 12px;
            color: #cbd5e1;
            border-radius: 12px;
            transition: background .2s ease, color .2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--sidebar-soft);
            color: #ffffff;
        }

        .nav-link.active {
            box-shadow: inset 3px 0 0 #60a5fa;
        }

        .nav-mark {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            font-size: 11px;
            font-weight: 800;
        }

        .admin-main {
            min-width: 0;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--line);
        }

        .kicker {
            margin: 0 0 3px;
            font-size: 12px;
            font-weight: 800;
            color: var(--brand);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        h1 {
            margin: 0;
            font-size: 26px;
            line-height: 1.2;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .admin-user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .avatar {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #dbeafe;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
        }

        .content {
            padding: 30px;
        }

        .notice {
            padding: 14px 16px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
            border-radius: var(--radius);
            margin-bottom: 18px;
            font-size: 14px;
        }

        .page-grid,
        .stat-grid,
        .split-grid {
            display: grid;
            gap: 18px;
        }

        .stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .page-grid.two,
        .split-grid {
            grid-template-columns: 1.35fr .85fr;
        }

        .page-grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .panel,
        .metric-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .panel {
            padding: 20px;
        }

        .metric-card {
            padding: 18px;
        }

        .metric-label,
        .panel-label {
            margin: 0 0 8px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .metric-value {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
        }

        .metric-note {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .panel-title {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .panel-subtitle {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 14px;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .toolbar-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            color: var(--text);
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 10px;
            outline: none;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 9px 13px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #ffffff;
            color: var(--text);
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--brand);
            border-color: var(--brand);
            color: #ffffff;
        }

        .btn-muted {
            color: var(--muted);
            background: var(--surface-soft);
        }

        .btn-danger {
            color: #ffffff;
            background: var(--danger);
            border-color: var(--danger);
        }

        .btn-warning {
            color: #ffffff;
            background: var(--warning);
            border-color: var(--warning);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .data-table th,
        .data-table td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
            font-size: 14px;
        }

        .data-table th {
            color: var(--muted);
            background: var(--surface-soft);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .data-table tr:last-child td {
            border-bottom: 0;
        }

        .muted {
            color: var(--muted);
        }

        .status {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status.success {
            background: #dcfce7;
            color: #166534;
        }

        .status.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .status.danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .status.info {
            background: #e0f2fe;
            color: #075985;
        }

        .status.neutral {
            background: #f1f5f9;
            color: #475569;
        }

        /* Modal Popup System */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.open {
            display: flex;
        }
        .modal-content {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--line);
            width: 100%;
            max-width: 600px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            max-height: 90vh;
            animation: modalFadeIn 0.25s ease-out;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--muted);
            line-height: 1;
            padding: 0;
        }
        .modal-close:hover {
            color: var(--text);
        }
        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--line);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Dropdown Actions Menu System */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-trigger {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--muted);
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            transition: background 0.2s, color 0.2s;
            padding: 0;
            line-height: 1;
        }
        .dropdown-trigger:hover {
            background: var(--surface-soft);
            color: var(--text);
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            z-index: 100;
            min-width: 150px;
            padding: 6px 0;
            margin-top: 4px;
        }
        .dropdown-menu.open {
            display: block;
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 8px 16px;
            border: none;
            background: none;
            text-align: left;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
            cursor: pointer;
            transition: background 0.15s;
            box-sizing: border-box;
        }
        .dropdown-item:hover {
            background: var(--surface-soft);
        }
        .dropdown-item.danger {
            color: var(--danger);
        }
        .dropdown-item.danger:hover {
            background: #fef2f2;
        }
        .dropdown-divider {
            height: 1px;
            background: var(--line);
            margin: 4px 0;
            border: none;
        }

        .tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .tab {
            padding: 9px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid var(--line);
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .tab.active {
            background: #dbeafe;
            color: var(--brand-dark);
            border-color: #bfdbfe;
        }

        .progress-track {
            height: 9px;
            width: 100%;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #16a34a);
            border-radius: inherit;
        }

        .list-stack {
            display: grid;
            gap: 12px;
        }

        .list-item {
            padding: 13px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .list-item strong {
            display: block;
            margin-bottom: 3px;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-footer-note {
            margin-top: 24px;
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .admin-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
                height: auto;
            }

            .nav-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .stat-grid,
            .page-grid.two,
            .page-grid.three,
            .split-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .topbar,
            .content {
                padding: 18px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .stat-grid,
            .page-grid.two,
            .page-grid.three,
            .split-grid,
            .form-grid,
            .nav-list {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">LC</div>
                <div>
                    <div class="brand-title">LearnCSS</div>
                    <div class="brand-subtitle">Admin panel</div>
                </div>
            </div>

            <nav class="nav-list" aria-label="Admin navigation">
                @foreach ($navItems as $item)
                    <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <span class="nav-mark">{{ $item['mark'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <main class="admin-main">
            <header class="topbar">
                <div>
                    <p class="kicker">@yield('kicker', 'Admin Panel')</p>
                    <h1>{{ $pageTitle }}</h1>
                </div>
                <div class="topbar-actions">
                    @yield('header_actions')
                    <div class="admin-user-chip" style="gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="avatar">{{ strtoupper(substr(Auth::user()->first_name ?? 'A', 0, 1) . substr(Auth::user()->last_name ?? 'D', 0, 1)) }}</span>
                            <span>{{ Auth::user()->name }}</span>
                        </div>
                        <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0; display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-muted" style="min-height: 30px; padding: 4px 8px; font-size: 11px;">Log out</button>
                        </form>
                    </div>
                </div>
            </header>

            <section class="content">
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

                <p class="admin-footer-note">
                    Securely connected. Protected by Laravel Session Auth, custom Admin middleware, and real-time database audit logging.
                </p>
            </section>
        </main>
    </div>
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
    </script>
</body>
</html>
