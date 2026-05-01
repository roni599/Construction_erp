@php 
    $globalAdmin = \App\Models\User::where('role', 'admin')->first();
    $displayBusinessName = ($globalAdmin && $globalAdmin->business_name) ? $globalAdmin->business_name : 'Construction ERP';
    $displayBusinessLogo = ($globalAdmin && $globalAdmin->business_logo) ? $globalAdmin->business_logo : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ $displayBusinessName }}</title>
    <link rel="icon" type="image/png" href="{{ $displayBusinessLogo ?: asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        @media print {
            .sidebar, 
            .no-print, 
            .mobile-header, 
            .sidebar-overlay, 
            #theme-toggle, 
            .custom-pagination, 
            .ajax-entries-group,
            .glass-panel:has(form),
            form,
            .btn,
            .sidebar-header,
            .mobile-header button { 
                display: none !important; 
            }
            
            .main-content { 
                padding: 0 !important; 
                margin: 0 !important; 
                width: 100% !important; 
                display: block !important;
                overflow: visible !important;
                height: auto !important;
                border: none !important;
                background: white !important;
            }
            
            .app-container { 
                display: block !important; 
                overflow: visible !important; 
                height: auto !important; 
                background: white !important;
            }
            
            body { 
                background: white !important; 
                color: black !important; 
                overflow: visible !important; 
                height: auto !important; 
            }
            
            * { 
                opacity: 1 !important; 
                visibility: visible !important; 
                animation: none !important; 
                transition: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
            
            .glass-panel { 
                border: none !important; 
                background: white !important; 
                color: black !important; 
                box-shadow: none !important;
                padding: 0 !important;
                margin-bottom: 20px !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
            
            .table-wrapper {
                overflow: visible !important;
                background: white !important;
            }
            
            .table { 
                width: 100% !important; 
                border-collapse: collapse !important; 
                margin: 0 !important; 
                background: white !important;
            }
            
            .table th, .table td { 
                color: black !important; 
                border: 1px solid #000 !important; 
                padding: 8px !important; 
                background: white !important;
                font-size: 11px !important;
            }
            
            .table thead th { 
                background: #f1f1f1 !important; 
                font-weight: bold !important;
            }

            .badge {
                border: 1px solid #000 !important;
                background: transparent !important;
                color: #000 !important;
                padding: 2px 5px !important;
            }
        }

        /* Toastr custom styling for dark theme */
        #toast-container > .toast {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            opacity: 1 !important;
        }

        #toast-container > .toast-error {
            background-color: #dc3545 !important;
            color: #ffffff !important;
            border-color: #bd2130;
        }

        #toast-container > .toast-success {
            background-color: #28a745 !important;
            color: #ffffff !important;
            border-color: #1e7e34;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 215, 0, 0.1);
            color: var(--accent-yellow);
        }

        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-item span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="mobile-header" style="justify-content: flex-start !important; gap: 15px !important; padding-left: 15px !important;">
        <button id="sidebar-toggle" style="background: none; border: none; color: var(--accent-yellow); font-size: 20px; cursor: pointer; padding: 5px; margin: 0; display: flex; align-items: center;">
            <i class="fas fa-bars"></i>
        </button>
        <div style="display: flex; align-items: center; gap: 10px;">
            @if($displayBusinessLogo)
                <img src="{{ $displayBusinessLogo }}" alt="Logo" style="width: 32px; height: 32px; object-fit: contain;">
            @endif
            <div class="sidebar-logo" style="margin: 0; font-size: 18px; white-space: nowrap; line-height: 1;">{{ $displayBusinessName }}</div>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; gap: 10px;">
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('manager.dashboard') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none; cursor: pointer;">
                    @if($displayBusinessLogo)
                        <img src="{{ $displayBusinessLogo }}" alt="Logo" style="width: 35px; height: 35px; object-fit: contain;">
                    @endif
                    <div class="sidebar-logo" style="margin-bottom: 0; white-space: nowrap; font-size: 20px;">{{ $displayBusinessName }}</div>
                </a>
                <button id="sidebar-close" class="mobile-only" style="background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; flex-shrink: 0; padding: 5px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> <span>Dashboard</span>
                    </a>
                    <div class="nav-item-group" style="display: flex; flex-direction: column;">
                        <a href="javascript:void(0)" class="nav-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" onclick="toggleSubmenu('projects-submenu')">
                            <i class="fas fa-project-diagram"></i> <span>Projects</span>
                            <i class="fas fa-chevron-down" style="margin-left: auto; width: auto; font-size: 12px;"></i>
                        </a>
                        <div id="projects-submenu" class="submenu" style="display: {{ request()->routeIs('admin.projects.*') ? 'flex' : 'none' }}; flex-direction: column; padding-left: 15px; margin-top: 5px; gap: 5px;">
                            <a href="{{ route('admin.projects.index') }}" class="nav-item {{ request()->routeIs('admin.projects.index') || request()->routeIs('admin.projects.show') || request()->routeIs('admin.projects.edit') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-list" style="font-size: 14px; width: 20px;"></i> <span>All Projects</span>
                            </a>
                            <a href="{{ route('admin.projects.payments.create') }}" class="nav-item {{ request()->routeIs('admin.projects.payments.*') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-hand-holding-usd" style="font-size: 14px; width: 20px;"></i> <span>Client Payment</span>
                            </a>
                            <a href="{{ route('admin.projects.funds.create') }}" class="nav-item {{ request()->routeIs('admin.projects.funds.*') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-money-bill-wave" style="font-size: 14px; width: 20px;"></i> <span>Disburse Fund</span>
                            </a>
                            <a href="{{ route('admin.projects.all_expenses') }}" class="nav-item {{ request()->routeIs('admin.projects.all_expenses') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 14px; width: 20px;"></i> <span>Expense List</span>
                            </a>
                            <a href="{{ route('admin.projects.all_returns') }}" class="nav-item {{ request()->routeIs('admin.projects.all_returns') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-undo-alt" style="font-size: 14px; width: 20px;"></i> <span>Fund Receive List</span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('admin.employees.index') }}" class="nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> <span>Project Managers</span>
                    </a>
                    <div class="nav-item-group" style="display: flex; flex-direction: column;">
                        <a href="javascript:void(0)" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" onclick="toggleSubmenu('reports-submenu')">
                            <i class="fas fa-chart-bar"></i> <span>Reports</span>
                            <i class="fas fa-chevron-down" style="margin-left: auto; width: auto; font-size: 12px;"></i>
                        </a>
                        <div id="reports-submenu" class="submenu" style="display: {{ request()->routeIs('admin.reports.*') ? 'flex' : 'none' }}; flex-direction: column; padding-left: 15px; margin-top: 5px; gap: 5px;">
                            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-file-alt" style="font-size: 14px; width: 20px;"></i> <span>Financial Summary</span>
                            </a>
                            <a href="{{ route('admin.reports.client_receive') }}" class="nav-item {{ request()->routeIs('admin.reports.client_receive') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-hand-holding-usd" style="font-size: 14px; width: 20px;"></i> <span>Client Receive</span>
                            </a>
                            <a href="{{ route('admin.reports.fund_transferred') }}" class="nav-item {{ request()->routeIs('admin.reports.fund_transferred') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-money-bill-wave" style="font-size: 14px; width: 20px;"></i> <span>Fund Disbursed PM</span>
                            </a>
                            <a href="{{ route('admin.reports.fund_returned') }}" class="nav-item {{ request()->routeIs('admin.reports.fund_returned') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-undo-alt" style="font-size: 14px; width: 20px;"></i> <span>Fund Returned PM</span>
                            </a>
                            <a href="{{ route('admin.reports.project_expense') }}" class="nav-item {{ request()->routeIs('admin.reports.project_expense') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 14px; width: 20px;"></i> <span>Project Expense</span>
                            </a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('manager.dashboard') }}" class="nav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i> <span>My Dashboard</span>
                    </a>
                    <div class="nav-item-group" style="display: flex; flex-direction: column;">
                        <a href="javascript:void(0)" class="nav-item {{ request()->routeIs('manager.projects.*') || request()->routeIs('manager.returns.*') || request()->routeIs('manager.funds.*') || request()->routeIs('manager.expenses.*') ? 'active' : '' }}" onclick="toggleSubmenu('manager-projects-submenu')">
                            <i class="fas fa-tasks"></i> <span>My Projects</span>
                            <i class="fas fa-chevron-down" style="margin-left: auto; width: auto; font-size: 12px;"></i>
                        </a>
                        <div id="manager-projects-submenu" class="submenu" style="display: {{ request()->routeIs('manager.projects.*') || request()->routeIs('manager.returns.*') || request()->routeIs('manager.funds.*') || request()->routeIs('manager.expenses.*') ? 'flex' : 'none' }}; flex-direction: column; padding-left: 15px; margin-top: 5px; gap: 5px;">
                            <a href="{{ route('manager.projects.index') }}" class="nav-item {{ request()->routeIs('manager.projects.index') || request()->routeIs('manager.projects.show') || request()->routeIs('manager.projects.ledger') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-list" style="font-size: 14px; width: 20px;"></i> <span>All My Projects</span>
                            </a>
                            <a href="{{ route('manager.funds.index') }}" class="nav-item {{ request()->routeIs('manager.funds.*') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-hand-holding-usd" style="font-size: 14px; width: 20px;"></i> <span>Fund Receive</span>
                            </a>
                            <a href="{{ route('manager.expenses.index') }}" class="nav-item {{ request()->routeIs('manager.expenses.*') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-file-invoice-dollar" style="font-size: 14px; width: 20px;"></i> <span>Fund Expense List</span>
                            </a>
                            <a href="{{ route('manager.returns.create') }}" class="nav-item {{ request()->routeIs('manager.returns.*') ? 'active' : '' }}" style="padding: 8px 12px; font-size: 14px;">
                                <i class="fas fa-undo" style="font-size: 14px; width: 20px;"></i> <span>Fund Return</span>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('manager.categories.index') }}" class="nav-item {{ request()->routeIs('manager.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> <span>Expense Category</span>
                    </a>
                    <a href="{{ route('manager.reports.index') }}" class="nav-item {{ request()->routeIs('manager.reports.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i> <span>Reports</span>
                    </a>
                @endif
            </nav>

            <div class="logout-mobile" style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border-color); display: none;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item" style="width: 100%; background: none; border: none; color: var(--danger); cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 12px;">
                        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; border-radius: 12px; overflow: visible !important; position: relative; z-index: 100;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <button id="sidebar-collapse-toggle" class="btn btn-outline" style="padding: 8px 12px; border-radius: 8px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color); background: rgba(255,255,255,0.03); color: var(--accent-yellow); cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(255, 215, 0, 0.1)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'; this.style.transform='scale(1)'">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div style="display: flex; align-items: center; gap: 8px; padding: 6px 16px; background: rgba(255, 255, 255, 0.05); border-radius: 20px; border: 1px solid var(--border-color);">
                        <span style="font-size: 13px; font-weight: 600; color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.5px;">@yield('title')</span>
                    </div>
                    <div style="height: 24px; width: 1px; background: var(--border-color);"></div>
                    <button id="theme-toggle" class="btn btn-outline" style="padding: 8px 12px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                    <div style="height: 24px; width: 1px; background: var(--border-color);"></div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: 500; font-size: 14px;">{{ Auth::user()->name }}</span>
                    </div>
                </div>
                
                <!-- Profile Dropdown (Right Corner) -->
                <div style="position: relative;" id="profile-dropdown-container">
                    <div id="profile-trigger" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; border: 2px solid var(--accent-yellow); background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        @if(Auth::user()->image)
                            <img src="{{ Auth::user()->image }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fas fa-user-tie" style="font-size: 18px; color: var(--accent-yellow);"></i>
                        @endif
                    </div>

                    <!-- Dropdown Menu -->
                    <div id="profile-menu" class="dropdown-menu" style="width: 220px; right: 0; top: calc(100% + 12px);">
                        <div style="padding: 15px; border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.02);">
                            <div style="font-weight: 600; color: var(--text-primary); font-size: 14px;">{{ Auth::user()->name }}</div>
                            <div style="font-size: 11px; color: var(--text-secondary); margin-top: 2px;">{{ Auth::user()->business_name ?? 'Administrator' }}</div>
                        </div>
                        
                        <div style="padding: 5px 0;">
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.profile.index') }}" class="dropdown-item">
                                    <i class="fas fa-cog" style="color: var(--accent-blue);"></i> Settings
                                </a>
                            @endif
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                @keyframes slideDown {
                    from { opacity: 0; transform: translateY(-8px); }
                    to { opacity: 1; transform: translateY(0); }
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const trigger = document.getElementById('profile-trigger');
                    const menu = document.getElementById('profile-menu');
                    
                    if(trigger && menu) {
                        trigger.addEventListener('click', function(e) {
                            e.stopPropagation();
                            const isVisible = menu.style.display === 'block';
                            menu.style.display = isVisible ? 'none' : 'block';
                        });

                        document.addEventListener('click', function() {
                            menu.style.display = 'none';
                        });
                    }
                });
            </script>

            <div class="animate-fade-in">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Toastr Configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                toastr.error("{{ $error }}");
            @endforeach
        @endif

        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.add('active');
            document.getElementById('sidebar-overlay').classList.add('active');
        });

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('active');
            document.getElementById('sidebar-overlay').classList.remove('active');
        }

        document.getElementById('sidebar-close').addEventListener('click', closeSidebar);
        document.getElementById('sidebar-overlay').addEventListener('click', closeSidebar);

        function confirmDelete(formId, message = 'Are you sure you want to delete this?') {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF4C4C',
                cancelButtonColor: '#a0a0a0',
                confirmButtonText: 'Yes, delete it!',
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#fff' : '#1e1e1e',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#000' : '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;

        function setTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            themeIcon.className = theme === 'light' ? 'fas fa-sun' : 'fas fa-moon';
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            setTheme(currentTheme === 'light' ? 'dark' : 'light');
        });

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        setTheme(savedTheme);

        // Sidebar Collapse Logic
        const sidebar = document.getElementById('sidebar');
        const collapseBtn = document.getElementById('sidebar-collapse-toggle');
        
        function toggleSidebarCollapse() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }

        if (collapseBtn) {
            collapseBtn.addEventListener('click', toggleSidebarCollapse);
        }

        // Initialize sidebar state from localStorage
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        // Dropdown Logic
        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                m.classList.remove('show');
            });
        }

        document.addEventListener('click', function(event) {
            const dropdownToggle = event.target.closest('.dropdown-toggle');
            const dropdownMenu = event.target.closest('.dropdown-menu');
            
            if (dropdownToggle) {
                const parent = dropdownToggle.closest('.dropdown');
                const menu = parent.querySelector('.dropdown-menu');

                const isOpen = menu.classList.contains('show');
                closeAllDropdowns();

                if (!isOpen) {
                    menu.classList.add('show');
                }
                event.stopPropagation();
            } else if (!dropdownMenu) {
                closeAllDropdowns();
            }
        });

        // Custom Server-Side AJAX Table Logic
        $(document).ready(function() {
            function loadTable($wrapper, url, data = {}) {
                $wrapper.css('opacity', '0.5');
                $.ajax({
                    url: url,
                    data: data,
                    success: function(response) {
                        const $newContent = $(response);
                        const tableId = $wrapper.find('.table').attr('id');
                        let $newTable;
                        
                        if (tableId) {
                            $newTable = $newContent.find('#' + tableId);
                        } else {
                            $newTable = $newContent.find('.table').first();
                        }

                        $wrapper.find('.table tbody').html($newTable.find('tbody').html());
                        
                        // Also update TFOOT if it exists
                        const $newFoot = $newTable.find('tfoot');
                        if ($newFoot.length) {
                            if ($wrapper.find('.table tfoot').length) {
                                $wrapper.find('.table tfoot').html($newFoot.html());
                            } else {
                                $wrapper.find('.table').append($newFoot);
                            }
                        } else {
                            $wrapper.find('.table tfoot').remove();
                        }

                        // Update Dashboard Stat Cards if they exist
                        $newContent.find('.stat-card').each(function() {
                            const statType = $(this).data('stat-type');
                            if (statType) {
                                const $currentCard = $('.stat-card[data-stat-type="' + statType + '"]');
                                if ($currentCard.length) {
                                    // Update everything: title, value, description, and container style
                                    $currentCard.find('.stat-title').html($(this).find('.stat-title').html());
                                    $currentCard.find('.stat-value').html($(this).find('.stat-value').html());
                                    $currentCard.find('.stat-desc').html($(this).find('.stat-desc').html());
                                    $currentCard.attr('style', $(this).attr('style'));
                                }
                            } else {
                                // Fallback for cards without data-stat-type
                                const title = $(this).find('.stat-title').text().trim();
                                const value = $(this).find('.stat-value').html();
                                $('.stat-card').each(function() {
                                    if ($(this).find('.stat-title').text().trim() === title) {
                                        $(this).find('.stat-value').html(value);
                                    }
                                });
                            }
                        });

                        // Update Pagination links for THIS table
                        const $paginationContainer = $wrapper.next('.custom-pagination');
                        if ($paginationContainer.length) {
                            const $newPagination = $newContent.find('.custom-pagination').first();
                            $paginationContainer.html($newPagination.html());
                        }
                        
                        $wrapper.css('opacity', '1');
                        applyRequiredAsterisks();
                    },
                    error: function() {
                        $wrapper.css('opacity', '1');
                        toastr.error('Failed to load table data.');
                    }
                });
            }

            $('.table').each(function(index) {
                const $table = $(this);
                const $wrapper = $table.closest('.table-wrapper');
                // Look for a form specifically in the preceding glass-panel
                let $filterForm = $wrapper.parents('.glass-panel').find('form').first();
                if ($filterForm.length === 0) {
                    $filterForm = $wrapper.prevAll('.glass-panel').find('form').first();
                }

                const entriesHtml = `
                    <div class="form-group ajax-entries-group" style="margin: 0; max-width: 200px;">
                        <label class="form-label">Show Records</label>
                        <select name="per_page" class="entries-select form-control">
                            <option value="10">10 Entries</option>
                            <option value="20">20 Entries</option>
                            <option value="30">30 Entries</option>
                            <option value="40">40 Entries</option>
                            <option value="all">Show All</option>
                        </select>
                    </div>
                `;

                if ($filterForm.length > 0 && $filterForm.attr('method').toLowerCase() === 'get') {
                    if (!$filterForm.find('.entries-select').length) {
                        // Prepend the entries select but maintain the existing flex-wrap layout
                        // We wrap it in a div that will play nicely with flex-wrap
                        const $entriesContainer = $(entriesHtml).css({
                            'flex': '1',
                            'min-width': '200px',
                            'margin': '0'
                        });
                        // Remove the bottom margin from the original entriesHtml
                        $entriesContainer.css('margin-bottom', '0');
                        $filterForm.prepend($entriesContainer);
                    }
                }

                // Individual form submission - ONLY hijack GET forms (filters)
                if ($filterForm.length > 0 && $filterForm.attr('method').toLowerCase() === 'get') {
                    $filterForm.on('submit', function(e) {
                        e.preventDefault();
                        loadTable($wrapper, $(this).attr('action'), $(this).serialize());
                    });
                }
            });

            // Global pagination click handler
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const $link = $(this);
                const $wrapper = $link.parents('.glass-panel').find('.table-wrapper');
                const $form = $link.parents('.glass-panel').find('form');
                loadTable($wrapper, $link.attr('href'), $form.serialize());
                $('html, body').animate({ scrollTop: $wrapper.offset().top - 100 }, 200);
            });

            // Global per_page change handler
            $(document).on('change', '.entries-select', function() {
                const $form = $(this).closest('form');
                if ($form.length) {
                    $form.submit();
                } else {
                    const $wrapper = $(this).nextAll('.table-wrapper').first() || $('.table-wrapper').first();
                    loadTable($wrapper, window.location.href, { per_page: $(this).val() });
                }
            });


            // Auto-add asterisk to required fields - DISABLED as per user request
            function applyRequiredAsterisks() {
                // $('input[required], select[required], textarea[required]').each(function() {
                //     const $label = $(this).parent().find('label');
                //     if ($label.length && !$label.find('.required-star').length) {
                //         $label.append(' <span class="required-star" style="color: var(--danger);">*</span>');
                //     }
                // });
            }

            applyRequiredAsterisks();
            
            // Re-apply when modals are shown
            $(document).on('click', '[onclick*="style.display="]', function() {
                // setTimeout(applyRequiredAsterisks, 100);
            });
        });
        function toggleSubmenu(id) {
            const menus = ['projects-submenu', 'reports-submenu', 'manager-projects-submenu'];
            menus.forEach(menuId => {
                const el = document.getElementById(menuId);
                if (el) {
                    if (menuId === id) {
                        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'flex' : 'none';
                    } else {
                        el.style.display = 'none';
                    }
                }
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
