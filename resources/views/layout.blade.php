<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản lý Quảng cáo Facebook - Meta Ads Hub')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CDN (Thư viện thông báo & Toast chuẩn quốc tế) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Tối ưu giao diện SweetAlert2 Toast chuẩn SaaS hiện đại */
        div.swal2-container.swal2-bottom-start {
            z-index: 999999 !important;
        }

        @media (min-width: 768px) {
            div.swal2-container.swal2-bottom-start {
                left: 17.5rem !important; /* Đặt sau sidebar 256px + khoảng cách 24px */
                bottom: 1.5rem !important;
            }
        }

        @media (max-width: 767px) {
            div.swal2-container.swal2-bottom-start {
                left: 1rem !important;
                right: 1rem !important;
                bottom: 1rem !important;
                width: auto !important;
            }
        }

        .swal2-popup.swal2-toast {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            background: #ffffff !important;
            border-radius: 1rem !important;
            padding: 0.85rem 1.15rem !important;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08) !important;
            border: 1px solid #e2e8f0 !important;
            align-items: center !important;
        }

        .swal2-popup.swal2-toast .swal2-title {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            line-height: 1.4 !important;
            margin: 0 0.5rem !important;
            padding: 0 !important;
        }

        .swal2-popup.swal2-toast .swal2-icon {
            margin: 0 !important;
            transform: scale(0.8) !important;
        }

        .swal2-popup.swal2-toast .swal2-timer-progress-bar {
            height: 3px !important;
            border-bottom-left-radius: 1rem !important;
            border-bottom-right-radius: 1rem !important;
        }
        
        .swal2-popup.swal2-toast.swal2-icon-success .swal2-timer-progress-bar {
            background: #10b981 !important;
        }
        
        .swal2-popup.swal2-toast.swal2-icon-error .swal2-timer-progress-bar {
            background: #f43f5e !important;
        }
        
        .swal2-popup.swal2-toast.swal2-icon-warning .swal2-timer-progress-bar {
            background: #f59e0b !important;
        }
        
        .swal2-popup.swal2-toast.swal2-icon-info .swal2-timer-progress-bar {
            background: #3b82f6 !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen overflow-x-hidden">

    <div class="flex h-screen overflow-hidden">

        <!-- ============================================== -->
        <!-- SIDEBAR TRÁI CHO NGƯỜI DÙNG (LEFT SIDEBAR)     -->
        <!-- ============================================== -->
        <aside id="userSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 border-r border-slate-800 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:inset-auto md:flex-shrink-0 shadow-2xl md:shadow-none">
            
            <!-- Brand / Logo Header -->
            <div class="h-20 min-h-[80px] flex-shrink-0 px-5 border-b border-slate-800 flex items-center justify-between bg-slate-950" style="height: 80px; min-height: 80px; flex-shrink: 0;">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 text-blue-500 group">
                    <span class="w-11 h-11 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-2xl text-blue-500 shadow-sm shadow-blue-500/10 group-hover:bg-blue-600/30 transition flex-shrink-0">
                        <i class="fa-brands fa-facebook"></i>
                    </span>
                    <div>
                        <span class="font-bold text-base text-white tracking-tight block leading-tight">Meta Ads Hub</span>
                        <span class="text-[10px] text-blue-400 font-mono tracking-widest uppercase font-semibold">User Dashboard</span>
                    </div>
                </a>
                <button onclick="toggleUserSidebar()" class="md:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                
                <!-- Nhóm 1: Quản lý Chính -->
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">
                        BẢNG ĐIỀU KHIỂN
                    </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <span class="w-7 text-center text-sm mr-2 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-slate-400 group-hover:text-blue-400' }} transition">
                                    <i class="fa-solid fa-gauge"></i>
                                </span>
                                <span class="truncate">Tổng quan</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('bms.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ request()->routeIs('bms.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <div class="flex items-center min-w-0">
                                    <span class="w-7 text-center text-sm mr-2 {{ request()->routeIs('bms.*') ? 'text-blue-400' : 'text-slate-400 group-hover:text-blue-400' }} transition">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </span>
                                    <span class="truncate">Quản lý BM</span>
                                </div>
                                @auth
                                    @php
                                        $userBmCount = \App\Models\BusinessManager::where('user_id', auth()->id())->realBms()->count();
                                    @endphp
                                    <span class="text-[10px] {{ request()->routeIs('bms.*') ? 'bg-blue-600 text-white' : 'bg-slate-800 text-blue-400' }} px-2 py-0.5 rounded-full font-mono font-bold flex-shrink-0 ml-2">
                                        {{ $userBmCount }}
                                    </span>
                                @endauth
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('accounts.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ request()->routeIs('accounts.*') ? 'bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <div class="flex items-center min-w-0">
                                    <span class="w-7 text-center text-sm mr-2 {{ request()->routeIs('accounts.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }} transition">
                                        <i class="fa-solid fa-rectangle-ad"></i>
                                    </span>
                                    <span class="truncate">Quản lý TKQC</span>
                                </div>
                                @auth
                                    @php
                                        $userAccountsCount = \App\Models\AdAccount::whereHas('businessManager', function ($q) {
                                            $q->where('user_id', auth()->id());
                                        })->distinct('ad_account_id')->count('ad_account_id');
                                    @endphp
                                    <span class="text-[10px] {{ request()->routeIs('accounts.*') ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-emerald-400' }} px-2 py-0.5 rounded-full font-mono font-bold flex-shrink-0 ml-2">
                                        {{ $userAccountsCount }}
                                    </span>
                                @endauth
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('personal-accounts.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ request()->routeIs('personal-accounts.*') ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <div class="flex items-center min-w-0">
                                    <span class="w-7 text-center text-sm mr-2 {{ request()->routeIs('personal-accounts.*') ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-400' }} transition">
                                        <i class="fa-solid fa-id-badge"></i>
                                    </span>
                                    <span class="truncate">TK Cá nhân</span>
                                </div>
                                @auth
                                    @php
                                        $personalAccCount = \App\Models\BusinessManager::where('user_id', auth()->id())->personal()->first()?->adAccounts()->count() ?? 0;
                                    @endphp
                                    <span class="text-[10px] {{ request()->routeIs('personal-accounts.*') ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-indigo-400' }} px-2 py-0.5 rounded-full font-mono font-bold flex-shrink-0 ml-2">
                                        {{ $personalAccCount }}
                                    </span>
                                @endauth
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('pages.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ (request()->routeIs('pages.*') && !request()->routeIs('pages.publish.*', 'pages.posts.*')) ? 'bg-sky-600/20 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <div class="flex items-center min-w-0">
                                    <span class="w-7 text-center text-sm mr-2 {{ (request()->routeIs('pages.*') && !request()->routeIs('pages.publish.*', 'pages.posts.*')) ? 'text-sky-400' : 'text-slate-400 group-hover:text-sky-400' }} transition">
                                        <i class="fa-solid fa-flag"></i>
                                    </span>
                                    <span class="truncate">Quản lý Pages</span>
                                </div>
                                @auth
                                    @php
                                        $userPagesCount = \App\Models\FacebookPage::where('user_id', auth()->id())->distinct('page_id')->count('page_id');
                                    @endphp
                                    <span class="text-[10px] {{ (request()->routeIs('pages.*') && !request()->routeIs('pages.publish.*', 'pages.posts.*')) ? 'bg-sky-600 text-white' : 'bg-slate-800 text-sky-400' }} px-2 py-0.5 rounded-full font-mono font-bold flex-shrink-0 ml-2">
                                        {{ $userPagesCount }}
                                    </span>
                                @endauth
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('pages.publish.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group whitespace-nowrap {{ request()->routeIs('pages.publish.*', 'pages.posts.*') ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800' }}">
                                <div class="flex items-center min-w-0">
                                    <span class="w-7 text-center text-sm mr-2 {{ request()->routeIs('pages.publish.*', 'pages.posts.*') ? 'text-orange-400' : 'text-slate-400 group-hover:text-orange-400' }} transition">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </span>
                                    <span class="truncate">Đăng bài & Hẹn giờ</span>
                                </div>
                                @auth
                                    @php
                                        $scheduledPostsCount = \App\Models\PagePost::where('user_id', auth()->id())->where('status', 'scheduled')->count();
                                    @endphp
                                    @if($scheduledPostsCount > 0)
                                        <span class="text-[10px] {{ request()->routeIs('pages.publish.*', 'pages.posts.*') ? 'bg-orange-500 text-white' : 'bg-slate-800 text-orange-400' }} px-2 py-0.5 rounded-full font-mono font-bold flex-shrink-0 ml-2">
                                            {{ $scheduledPostsCount }}
                                        </span>
                                    @endif
                                @endauth
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Nhóm 2: Dành cho Admin (nếu tài khoản là Admin) -->
                @auth
                    @if(auth()->user()->isAdmin())
                        <div>
                            <div class="text-[10px] font-bold text-amber-500 uppercase tracking-wider px-3 mb-2">
                                QUẢN TRỊ VIÊN
                            </div>
                            <ul class="space-y-1">
                                <li>
                                    <a href="{{ route('admin.dashboard') }}" 
                                       class="flex items-center px-3 py-2.5 rounded-xl text-xs font-bold text-amber-300 bg-amber-500/15 border border-amber-500/30 hover:bg-amber-500/25 transition group">
                                        <span class="w-7 text-center text-sm mr-2.5 text-amber-400">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </span>
                                        <span>Khu vực Admin Portal</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                @endauth

            </nav>

            <!-- Bottom User Profile Card -->
            <div class="p-3 border-t border-slate-800 bg-slate-950">
                @auth
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center space-x-2.5 overflow-hidden">
                            <span class="w-8 h-8 rounded-lg bg-blue-600/20 border border-blue-500/30 flex items-center justify-center font-bold text-xs text-blue-400 flex-shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <div class="truncate">
                                <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</div>
                            </div>
                        </div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    title="Đăng xuất" 
                                    class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition">
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>

        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div id="userSidebarBackdrop" onclick="toggleUserSidebar()" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-40 hidden md:hidden"></div>

        <!-- ============================================== -->
        <!-- KHU VỰC NỘI DUNG CHÍNH (RIGHT MAIN CONTENT)    -->
        <!-- ============================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-50">
            
            <!-- Top Header Bar (Khóa cứng chiều cao 80px, chống co rút flex) -->
            <header class="h-20 min-h-[80px] flex-shrink-0 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm transition-all" style="height: 80px; min-height: 80px; flex-shrink: 0;">
                
                <!-- Left: Toggle Mobile Button & Breadcrumbs (To, Rõ nét) -->
                <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 mr-2 sm:mr-4">
                    <button onclick="toggleUserSidebar()" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 transition flex-shrink-0">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                        @if(request()->routeIs('bms.show'))
                            <!-- Nút quay lại danh sách Quản lý BM -->
                            <a href="{{ route('bms.index') }}" 
                               class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs transition flex-shrink-0 border border-slate-200/80 shadow-2xs" 
                               title="Quay lại danh sách Quản lý BM">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>

                            @php
                                $headerBms = $userBms ?? \App\Models\BusinessManager::where('user_id', auth()->id())->realBms()->orderBy('name')->get();
                                $currentParams = request()->only('date_preset', 'since', 'until');
                            @endphp

                            <!-- Custom Dropdown Switcher chọn BM cao cấp chuẩn SaaS -->
                            <div class="relative inline-block text-left" id="bmSwitcherContainer">
                                <button type="button" 
                                        id="bmSwitcherBtn" 
                                        onclick="toggleBmSwitcherMenu(event)" 
                                        class="group inline-flex items-center space-x-2.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl shadow-xs transition-all text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <div class="min-w-0 flex flex-col text-left">
                                        <div class="flex items-center space-x-1.5">
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-blue-600 transition truncate max-w-[130px] sm:max-w-[200px] md:max-w-[240px]">
                                                {{ $bm->name }}
                                            </span>
                                            <i id="bmSwitcherChevron" class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200"></i>
                                        </div>
                                        @if($bm->bm_id)
                                            <span class="text-[10px] font-mono text-slate-400 truncate">
                                                ID: {{ $bm->bm_id }}
                                            </span>
                                        @endif
                                    </div>
                                </button>

                                <!-- Dropdown Menu Popover -->
                                <div id="bmSwitcherDropdown" 
                                     class="hidden absolute left-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                                    
                                    <!-- Search input trong dropdown -->
                                    <div class="p-2.5 border-b border-slate-100 bg-slate-50/70">
                                        <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   id="bmSwitcherSearch" 
                                                   oninput="filterBmSwitcherList(this.value)" 
                                                   placeholder="Tìm kiếm Business Manager..." 
                                                   class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                    </div>

                                    <!-- Danh sách các BM -->
                                    <div class="max-h-72 overflow-y-auto p-1.5 space-y-1" id="bmSwitcherList">
                                        @foreach($headerBms as $item)
                                            @php
                                                $isCurrent = (isset($bm) && $bm->id == $item->id);
                                                $unsettled = $item->unsettled_accounts_count ?? 0;
                                            @endphp
                                            <a href="{{ route('bms.show', array_merge(['bm' => $item->id], $currentParams)) }}" 
                                               class="bm-switcher-item flex items-center justify-between p-2 rounded-xl transition {{ $isCurrent ? 'bg-blue-50/80 border border-blue-200 text-blue-900 font-semibold shadow-2xs' : 'hover:bg-slate-50 text-slate-700' }}"
                                               data-name="{{ mb_strtolower($item->name) }}"
                                               data-id="{{ $item->bm_id }}">
                                                <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                                    <div class="w-8 h-8 rounded-lg {{ $isCurrent ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }} flex items-center justify-center text-xs flex-shrink-0 transition">
                                                        <i class="fa-solid fa-building"></i>
                                                    </div>
                                                    <div class="min-w-0 text-left">
                                                        <div class="text-xs font-bold truncate {{ $isCurrent ? 'text-blue-900' : 'text-slate-800' }}">
                                                            {{ $item->name }}
                                                        </div>
                                                        <div class="flex items-center space-x-2 mt-0.5 text-[10px] text-slate-400 font-mono">
                                                            <span>ID: {{ $item->bm_id }}</span>
                                                            @if($unsettled > 0)
                                                                <span class="text-amber-700 font-bold bg-amber-50 px-1 py-0.2 rounded border border-amber-200 flex items-center gap-0.5">
                                                                    <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> {{ $unsettled }} nợ
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($isCurrent)
                                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] flex-shrink-0 shadow-2xs">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                @endif
                                            </a>
                                        @endforeach
                                        <div id="bmSwitcherNoResult" class="hidden p-4 text-center text-xs text-slate-400">
                                            Không tìm thấy Business Manager nào phù hợp
                                        </div>
                                    </div>

                                    <!-- Footer dropdown -->
                                    <div class="p-2.5 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 font-medium text-[11px]">
                                            Tổng cộng: <strong class="text-slate-800">{{ $headerBms->count() }}</strong> BM
                                        </span>
                                        <a href="{{ route('bms.index') }}" class="inline-flex items-center font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                            <span>Quản lý tất cả BM</span>
                                            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @if(isset($bm) && $bm->status === 'active')
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full animate-pulse"></span> Hoạt động
                                </span>
                            @elseif(isset($bm))
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200 flex-shrink-0">
                                    {{ ucfirst($bm->status) }}
                                </span>
                            @endif

                            @if(isset($bm) && $bm->bm_id)
                                <button type="button" 
                                        onclick="copyToClipboard('{{ $bm->bm_id }}', 'Đã sao chép ID BM!')" 
                                        class="hidden xl:inline-flex text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-lg font-mono border border-slate-200 items-center gap-1.5 flex-shrink-0 transition" 
                                        title="Bấm để sao chép ID BM">
                                    <span>ID: {{ $bm->bm_id }}</span>
                                    <i class="fa-regular fa-copy text-[10px] text-slate-400"></i>
                                </button>
                            @endif
                        @elseif(request()->routeIs('accounts.campaigns') && isset($account))
                            <!-- Nút quay lại BM -->
                            <a href="{{ route('bms.show', $account->business_manager_id) }}" 
                               class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs transition flex-shrink-0 border border-slate-200/80 shadow-2xs" 
                               title="Quay lại BM {{ $account->businessManager->name ?? '' }}">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>

                            @php
                                $siblingAccounts = $bmAccounts ?? ($account->businessManager ? $account->businessManager->adAccounts()->orderBy('name')->get() : collect([$account]));
                            @endphp

                            <!-- Custom Dropdown Switcher chọn TKQC chuẩn SaaS -->
                            <div class="relative inline-block text-left" id="accountSwitcherContainer">
                                <button type="button" 
                                        id="accountSwitcherBtn" 
                                        onclick="toggleAccountSwitcherMenu(event)" 
                                        class="group inline-flex items-center space-x-2.5 px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl shadow-xs transition-all text-left focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs group-hover:scale-105 transition-transform">
                                        <i class="fa-solid fa-rectangle-ad"></i>
                                    </div>
                                    <div class="min-w-0 flex flex-col text-left">
                                        <div class="flex items-center space-x-1.5">
                                            <span class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-blue-600 transition truncate max-w-[130px] sm:max-w-[200px] md:max-w-[240px]">
                                                {{ $account->name }}
                                            </span>
                                            <i id="accountSwitcherChevron" class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200"></i>
                                        </div>
                                        <div class="flex items-center space-x-1.5 text-[10px] font-mono text-slate-400 truncate">
                                            <span>ID: {{ $account->ad_account_id }}</span>
                                            @if($account->card_display)
                                                <span class="text-slate-600 font-sans font-semibold">• {{ $account->card_display }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </button>

                                <!-- Dropdown Menu Popover -->
                                <div id="accountSwitcherDropdown" 
                                     class="hidden absolute left-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200/90 z-50 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                                    
                                    <!-- Search input trong dropdown -->
                                    <div class="p-2.5 border-b border-slate-100 bg-slate-50/70">
                                        <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   id="accountSwitcherSearch" 
                                                   oninput="filterAccountSwitcherList(this.value)" 
                                                   placeholder="Tìm kiếm tài khoản QC..." 
                                                   class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        </div>
                                    </div>

                                    <!-- Danh sách các Ad Account trong BM -->
                                    <div class="max-h-72 overflow-y-auto p-1.5 space-y-1" id="accountSwitcherList">
                                        @foreach($siblingAccounts as $item)
                                            @php
                                                $isCurrent = ($account->id == $item->id);
                                                $hasDebt = ($item->account_status == 3 || $item->balance > 0);
                                            @endphp
                                            <a href="{{ route('accounts.campaigns', $item->id) }}" 
                                               class="account-switcher-item flex items-center justify-between p-2 rounded-xl transition {{ $isCurrent ? 'bg-blue-50/80 border border-blue-200 text-blue-900 font-semibold shadow-2xs' : 'hover:bg-slate-50 text-slate-700' }}"
                                               data-name="{{ mb_strtolower($item->name) }}"
                                               data-id="{{ $item->ad_account_id }}">
                                                <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                                    <div class="w-8 h-8 rounded-lg {{ $isCurrent ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }} flex items-center justify-center text-xs flex-shrink-0 transition">
                                                        <i class="fa-solid fa-rectangle-ad"></i>
                                                    </div>
                                                    <div class="min-w-0 text-left">
                                                        <div class="text-xs font-bold truncate {{ $isCurrent ? 'text-blue-900' : 'text-slate-800' }}">
                                                            {{ $item->name }}
                                                        </div>
                                                        <div class="flex items-center space-x-2 mt-0.5 text-[10px] text-slate-400 font-mono">
                                                            <span>{{ $item->ad_account_id }}</span>
                                                            @if($item->card_display)
                                                                <span class="text-slate-600 font-sans font-medium">{{ $item->card_display }}</span>
                                                            @endif
                                                            @if($hasDebt)
                                                                <span class="text-amber-700 font-bold bg-amber-50 px-1 py-0.2 rounded border border-amber-200 flex items-center gap-0.5 font-sans">
                                                                    <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> Nợ tiền
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($isCurrent)
                                                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] flex-shrink-0 shadow-2xs">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                @endif
                                            </a>
                                        @endforeach
                                        <div id="accountSwitcherNoResult" class="hidden p-4 text-center text-xs text-slate-400">
                                            Không tìm thấy tài khoản quảng cáo nào phù hợp
                                        </div>
                                    </div>

                                    <!-- Footer dropdown -->
                                    <div class="p-2.5 border-t border-slate-100 bg-slate-50/80 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 font-medium text-[11px]">
                                            BM: <strong class="text-slate-800">{{ $account->businessManager->name ?? '—' }}</strong> ({{ $siblingAccounts->count() }} TK)
                                        </span>
                                        <a href="{{ route('bms.show', $account->business_manager_id) }}" class="inline-flex items-center font-bold text-blue-600 hover:text-blue-700 hover:underline">
                                            <span>Xem tất cả TK</span>
                                            <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Status badge -->
                            @if($account->account_status == 1)
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 flex-shrink-0">
                                    <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full animate-pulse"></span> Hoạt động
                                </span>
                            @elseif($account->account_status == 3)
                                <a href="{{ $account->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                   class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-300 flex-shrink-0 hover:bg-amber-200 transition">
                                    <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full animate-pulse"></span> Nợ tiền
                                </a>
                            @else
                                <span class="hidden md:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200 flex-shrink-0">
                                    {{ $account->status_label }}
                                </span>
                            @endif

                            <!-- Copy Account ID -->
                            <button type="button" 
                                    onclick="copyToClipboard('{{ $account->ad_account_id }}', 'Đã sao chép ID tài khoản!')" 
                                    class="hidden xl:inline-flex text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-lg font-mono border border-slate-200 items-center gap-1.5 flex-shrink-0 transition" 
                                    title="Bấm để sao chép ID tài khoản QC">
                                <span>ID: {{ $account->ad_account_id }}</span>
                                <i class="fa-regular fa-copy text-[10px] text-slate-400"></i>
                            </button>
                        @else
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-400 hover:text-blue-600 transition flex items-center gap-1.5 flex-shrink-0">
                                <i class="fa-solid fa-house text-xs"></i>
                                <span class="hidden sm:inline">Trang chủ</span>
                            </a>
                            <i class="fa-solid fa-chevron-right text-[11px] text-slate-300 flex-shrink-0"></i>
                            
                            <div class="flex items-center space-x-2.5 min-w-0">
                                @if(request()->routeIs('accounts.campaigns'))
                                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-list-check"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight truncate">Chiến dịch Quảng cáo</span>
                                @elseif(request()->routeIs('accounts.*'))
                                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-rectangle-ad"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight">Quản lý TKQC</span>
                                @elseif(request()->routeIs('bms.*'))
                                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight">Quản lý BM</span>
                                @elseif(request()->routeIs('personal-accounts.*'))
                                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-id-badge"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight">TK Cá nhân</span>
                                @elseif(request()->routeIs('pages.*'))
                                    <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-flag"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight">Quản lý Pages</span>
                                @else
                                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-sm flex-shrink-0">
                                        <i class="fa-solid fa-gauge-high"></i>
                                    </span>
                                    <span class="text-slate-900 font-bold text-base sm:text-lg tracking-tight">Tổng quan</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right: Header Actions (Các nút thao tác nhanh trên cùng) -->
                <div class="flex items-center space-x-2 sm:space-x-2.5 flex-shrink-0">
                    @yield('header_actions')
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 w-full">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 py-4 px-6 mt-auto">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                    <div>Meta Ads Hub &bull; Nền tảng quản lý Facebook Ads Đa người dùng</div>
                    <div>Laravel v{{ app()->version() }} &bull; Meta Graph API v21.0</div>
                </div>
            </footer>

        </div>

    </div>

    <!-- Floating Toast Notifications (Góc dưới bên trái) -->
    <div id="toastContainer" 
         class="fixed bottom-6 left-6 md:left-72 z-50 flex flex-col-reverse gap-3 max-w-sm sm:max-w-md w-[calc(100%-3rem)] md:w-auto pointer-events-none transition-all">
        
        @if(session('success'))
            <div class="toast-item pointer-events-auto flex items-start space-x-3 bg-white border border-emerald-200/90 shadow-2xl shadow-emerald-500/10 rounded-2xl p-4 transition-all duration-300 transform translate-y-0 opacity-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <div class="text-xs font-bold text-slate-900 tracking-tight">Thành công</div>
                    <div class="text-xs text-slate-600 mt-0.5 leading-relaxed font-medium">{{ session('success') }}</div>
                </div>
                <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition flex-shrink-0" title="Đóng">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-item pointer-events-auto flex items-start space-x-3 bg-white border border-rose-200/90 shadow-2xl shadow-rose-500/10 rounded-2xl p-4 transition-all duration-300 transform translate-y-0 opacity-100">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <div class="text-xs font-bold text-slate-900 tracking-tight">Đã xảy ra lỗi</div>
                    <div class="text-xs text-rose-600 mt-0.5 leading-relaxed font-medium">{{ session('error') }}</div>
                </div>
                <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition flex-shrink-0" title="Đóng">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('warning'))
            <div class="toast-item pointer-events-auto flex items-start space-x-3 bg-white border border-amber-200/90 shadow-2xl shadow-amber-500/10 rounded-2xl p-4 transition-all duration-300 transform translate-y-0 opacity-100">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <div class="text-xs font-bold text-slate-900 tracking-tight">Cảnh báo</div>
                    <div class="text-xs text-amber-700 mt-0.5 leading-relaxed font-medium">{{ session('warning') }}</div>
                </div>
                <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition flex-shrink-0" title="Đóng">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

    </div>

    <!-- Script điều khiển Sidebar, Modal & Toast -->
    <script>
        function toggleUserSidebar() {
            const sidebar = document.getElementById('userSidebar');
            const backdrop = document.getElementById('userSidebarBackdrop');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function openAddBMModal() {
            const modal = document.getElementById('modalAddBM');
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                window.location.href = "{{ route('dashboard') }}";
            }
        }

        function dismissToast(el) {
            if (!el) return;
            el.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => {
                el.remove();
            }, 300);
        }

        window.dismissToast = dismissToast;

        // ==========================================
        // SWEETALERT2: HỆ THỐNG TOAST NOTIFICATION 3RD-PARTY
        // ==========================================
        const SwalToast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        window.SwalToast = SwalToast;

        // Hàm hiển thị Toast Notification chuyên nghiệp sử dụng SweetAlert2
        window.showToast = function(message, type = 'success', duration = 3200) {
            let icon = 'success';
            if (type === 'error' || type === 'danger') icon = 'error';
            else if (type === 'warning') icon = 'warning';
            else if (type === 'info') icon = 'info';

            return SwalToast.fire({
                icon: icon,
                title: message,
                timer: duration
            });
        };

        // Hàm sao chép văn bản & hiển thị SweetAlert2 Toast mượt mà
        window.copyToClipboard = function(text, successMessage = 'Đã sao chép vào bộ nhớ tạm!') {
            if (!text) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast(successMessage, 'success', 2800);
                }).catch(() => {
                    fallbackCopy(text, successMessage);
                });
            } else {
                fallbackCopy(text, successMessage);
            }
        };

        function fallbackCopy(text, successMessage) {
            try {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-999999px";
                textArea.style.top = "-999999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (successful) {
                    showToast(successMessage, 'success', 2800);
                } else {
                    showToast('Không thể sao chép văn bản!', 'error', 3000);
                }
            } catch (err) {
                showToast('Không thể sao chép văn bản: ' + err.message, 'error', 3000);
            }
        }

        // Tự động chuyển toàn bộ lệnh alert() mặc định sang SweetAlert2 Toast
        window.alert = function(msg) {
            const str = String(msg || '');
            const isError = /lỗi|thất bại|không thể|error|fail|invalid|hết hạn/i.test(str);
            const isWarning = /cảnh báo|warning|chú ý/i.test(str);
            const type = isError ? 'error' : (isWarning ? 'warning' : 'success');
            showToast(str, type, 3500);
        };

        document.addEventListener('DOMContentLoaded', function () {
            const toasts = document.querySelectorAll('.toast-item');
            toasts.forEach((toast) => {
                setTimeout(() => {
                    dismissToast(toast);
                }, 4000);
            });
        });

        // ==============================================================
        // LOGIC CHO CUSTOM BM SWITCHER DROPDOWN
        // ==============================================================
        function toggleBmSwitcherMenu(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('bmSwitcherDropdown');
            const chevron = document.getElementById('bmSwitcherChevron');
            if (!dropdown) return;
            const isHidden = dropdown.classList.contains('hidden');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
                const search = document.getElementById('bmSwitcherSearch');
                if (search) {
                    search.value = '';
                    filterBmSwitcherList('');
                    setTimeout(() => search.focus(), 60);
                }
            } else {
                dropdown.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function filterBmSwitcherList(query) {
            const q = (query || '').toLowerCase().trim();
            const items = document.querySelectorAll('.bm-switcher-item');
            let visibleCount = 0;
            items.forEach(item => {
                const name = item.dataset.name || '';
                const id = item.dataset.id || '';
                if (!q || name.includes(q) || id.includes(q)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            const noRes = document.getElementById('bmSwitcherNoResult');
            if (noRes) {
                noRes.classList.toggle('hidden', visibleCount > 0);
            }
        }

        // ==============================================================
        // LOGIC CHO CUSTOM AD ACCOUNT SWITCHER DROPDOWN ("CHỌN TỪNG TKQC")
        // ==============================================================
        function toggleAccountSwitcherMenu(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('accountSwitcherDropdown');
            const chevron = document.getElementById('accountSwitcherChevron');
            if (!dropdown) return;
            const isHidden = dropdown.classList.contains('hidden');
            if (isHidden) {
                dropdown.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
                const search = document.getElementById('accountSwitcherSearch');
                if (search) {
                    search.value = '';
                    filterAccountSwitcherList('');
                    setTimeout(() => search.focus(), 60);
                }
            } else {
                dropdown.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function filterAccountSwitcherList(query) {
            const q = (query || '').toLowerCase().trim();
            const items = document.querySelectorAll('.account-switcher-item');
            let visibleCount = 0;
            items.forEach(item => {
                const name = item.dataset.name || '';
                const id = item.dataset.id || '';
                if (!q || name.includes(q) || id.includes(q)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            const noRes = document.getElementById('accountSwitcherNoResult');
            if (noRes) {
                noRes.classList.toggle('hidden', visibleCount > 0);
            }
        }

        document.addEventListener('click', function(e) {
            const container = document.getElementById('bmSwitcherContainer');
            const dropdown = document.getElementById('bmSwitcherDropdown');
            const chevron = document.getElementById('bmSwitcherChevron');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                if (container && !container.contains(e.target)) {
                    dropdown.classList.add('hidden');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
            }

            const accContainer = document.getElementById('accountSwitcherContainer');
            const accDropdown = document.getElementById('accountSwitcherDropdown');
            const accChevron = document.getElementById('accountSwitcherChevron');
            if (accDropdown && !accDropdown.classList.contains('hidden')) {
                if (accContainer && !accContainer.contains(e.target)) {
                    accDropdown.classList.add('hidden');
                    if (accChevron) accChevron.classList.remove('rotate-180');
                }
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const dropdown = document.getElementById('bmSwitcherDropdown');
                const chevron = document.getElementById('bmSwitcherChevron');
                if (dropdown && !dropdown.classList.contains('hidden')) {
                    dropdown.classList.add('hidden');
                    if (chevron) chevron.classList.remove('rotate-180');
                }

                const accDropdown = document.getElementById('accountSwitcherDropdown');
                const accChevron = document.getElementById('accountSwitcherChevron');
                if (accDropdown && !accDropdown.classList.contains('hidden')) {
                    accDropdown.classList.add('hidden');
                    if (accChevron) accChevron.classList.remove('rotate-180');
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
