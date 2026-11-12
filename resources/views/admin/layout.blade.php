<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Khu vực Quản trị Hệ thống - Meta Ads Hub')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CDN (Thư viện thông báo & Toast chuẩn quốc tế) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar for sidebar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b1120; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }

        /* Tối ưu giao diện SweetAlert2 Toast cho Admin Portal (Dark Mode) */
        div.swal2-container.swal2-bottom-start {
            z-index: 999999 !important;
        }

        @media (min-width: 768px) {
            div.swal2-container.swal2-bottom-start {
                left: 17.5rem !important;
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
            background: #0f172a !important;
            border-radius: 1rem !important;
            padding: 0.85rem 1.15rem !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(51, 65, 85, 0.8) !important;
            align-items: center !important;
        }

        .swal2-popup.swal2-toast .swal2-title {
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            color: #f8fafc !important;
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
<body class="bg-slate-900 text-slate-100 min-h-screen overflow-x-hidden">

    <div class="flex h-screen overflow-hidden">

        <!-- ============================================== -->
        <!-- SIDEBAR TRÁI (LEFT SIDEBAR - Chuẩn Bootstrap/AdminLTE) -->
        <!-- ============================================== -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 border-r border-slate-800/80 flex flex-col transition-transform duration-300 transform -translate-x-full md:translate-x-0 md:static md:inset-auto md:flex-shrink-0 shadow-2xl md:shadow-none">
            
            <!-- Brand / Logo Header -->
            <div class="h-20 min-h-[80px] flex-shrink-0 px-5 border-b border-slate-800/80 flex items-center justify-between bg-slate-950" style="height: 80px; min-height: 80px; flex-shrink: 0;">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 text-amber-400 group">
                    <span class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-xl shadow-sm shadow-amber-500/10 group-hover:bg-amber-500/20 transition">
                        <i class="fa-solid fa-shield-halved text-amber-400"></i>
                    </span>
                    <div>
                        <span class="font-bold text-base text-white tracking-tight block leading-tight">Admin Portal</span>
                        <span class="text-[10px] text-amber-400 font-mono tracking-widest uppercase font-semibold">Meta Ads Hub</span>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Sidebar Navigation Menu -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                
                <!-- Nhóm 1: Quản trị chính -->
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-2">
                        Bảng điều khiển
                    </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.dashboard') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900' }}">
                                <span class="w-7 text-center text-sm mr-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-amber-400' : 'text-slate-500 group-hover:text-amber-400' }} transition">
                                    <i class="fa-solid fa-chart-line"></i>
                                </span>
                                <span>Tổng quan Hệ thống</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.users') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.users*') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900' }}">
                                <div class="flex items-center">
                                    <span class="w-7 text-center text-sm mr-2.5 {{ request()->routeIs('admin.users*') ? 'text-amber-400' : 'text-slate-500 group-hover:text-amber-400' }} transition">
                                        <i class="fa-solid fa-users-gear"></i>
                                    </span>
                                    <span>Quản lý Người dùng</span>
                                </div>
                                <span class="text-[10px] bg-slate-800 text-amber-400 px-2 py-0.5 rounded-full font-mono font-bold">
                                    {{ \App\Models\User::count() }}
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.bms.index') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.bms*') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900' }}">
                                <div class="flex items-center">
                                    <span class="w-7 text-center text-sm mr-2.5 {{ request()->routeIs('admin.bms*') ? 'text-amber-400' : 'text-slate-500 group-hover:text-amber-400' }} transition">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </span>
                                    <span>Quản lý BM Hệ thống</span>
                                </div>
                                <span class="text-[10px] bg-slate-800 text-amber-400 px-2 py-0.5 rounded-full font-mono font-bold">
                                    {{ \App\Models\BusinessManager::count() }}
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('admin.settings') }}" 
                               class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold transition group {{ request()->routeIs('admin.settings*') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30 font-bold' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-900' }}">
                                <span class="w-7 text-center text-sm mr-2.5 {{ request()->routeIs('admin.settings*') ? 'text-amber-400' : 'text-slate-500 group-hover:text-amber-400' }} transition">
                                    <i class="fa-solid fa-gears"></i>
                                </span>
                                <span>Cấu hình Meta API</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Nhóm 2: Chuyển đổi & Tiện ích -->
                <div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider px-3 mb-2">
                        Tiện ích & Cổng ngoài
                    </div>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center px-3 py-2.5 rounded-xl text-xs font-semibold text-blue-400 bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 transition group">
                                <span class="w-7 text-center text-sm mr-2.5 text-blue-400">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </span>
                                <span>Giao diện Người dùng</span>
                            </a>
                        </li>

                        <li>
                            <a href="https://developers.facebook.com/apps" target="_blank" 
                               class="flex items-center px-3 py-2.5 rounded-xl text-xs font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-900 transition group">
                                <span class="w-7 text-center text-sm mr-2.5 text-slate-500 group-hover:text-slate-300">
                                    <i class="fa-brands fa-facebook"></i>
                                </span>
                                <span>Meta for Developers</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </nav>

            <!-- Bottom Admin Profile Card -->
            <div class="p-3 border-t border-slate-800/80 bg-slate-950/80">
                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-900 border border-slate-800">
                    <div class="flex items-center space-x-2.5 overflow-hidden">
                        <span class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/30 flex items-center justify-center font-bold text-xs text-amber-400 flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-amber-400 font-mono uppercase tracking-wider">Super Admin</div>
                        </div>
                    </div>

                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                title="Đăng xuất Admin" 
                                class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition">
                            <i class="fa-solid fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 hidden md:hidden"></div>

        <!-- ============================================== -->
        <!-- KHU VỰC NỘI DUNG CHÍNH (RIGHT MAIN CONTENT) -->
        <!-- ============================================== -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-900">
            
            <!-- Top Header Bar (Rộng rãi & Bề thế, khóa cứng chiều cao 80px) -->
            <header class="h-20 min-h-[80px] flex-shrink-0 bg-slate-950/90 backdrop-blur border-b border-slate-800/80 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-sm" style="height: 80px; min-height: 80px; flex-shrink: 0;">
                
                <!-- Left: Toggle Mobile Button & Title / Breadcrumbs -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <span class="text-sm font-medium text-slate-500 flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-xs text-amber-500/80"></i>
                            <span class="hidden sm:inline">Admin</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[11px] text-slate-700"></i>
                        
                        <div class="flex items-center space-x-2.5">
                            @if(request()->routeIs('admin.dashboard'))
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm border border-amber-500/20">
                                    <i class="fa-solid fa-chart-line"></i>
                                </span>
                                <span class="text-white font-bold text-base sm:text-lg tracking-tight">Tổng quan Hệ thống</span>
                            @elseif(request()->routeIs('admin.bms*'))
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm border border-amber-500/20">
                                    <i class="fa-solid fa-briefcase"></i>
                                </span>
                                <span class="text-white font-bold text-base sm:text-lg tracking-tight">Quản lý BM</span>
                            @elseif(request()->routeIs('admin.users*'))
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm border border-amber-500/20">
                                    <i class="fa-solid fa-users-gear"></i>
                                </span>
                                <span class="text-white font-bold text-base sm:text-lg tracking-tight">Quản lý Người dùng</span>
                            @elseif(request()->routeIs('admin.settings*'))
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm border border-amber-500/20">
                                    <i class="fa-solid fa-gears"></i>
                                </span>
                                <span class="text-white font-bold text-base sm:text-lg tracking-tight">Cấu hình Meta API</span>
                            @else
                                <span class="text-white font-bold text-base sm:text-lg tracking-tight">Quản trị</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Status Badges & Quick Action -->
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex items-center space-x-2 px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-emerald-400 text-xs font-mono font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>API v21.0 Online</span>
                    </div>

                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1.5 text-blue-400"></i>
                        <span class="hidden sm:inline">Giao diện User</span>
                    </a>

                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition">
                            <i class="fa-solid fa-power-off mr-1 sm:mr-1.5"></i>
                            <span class="hidden sm:inline">Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 w-full">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-slate-950/80 border-t border-slate-800/80 py-4 px-6 mt-auto">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                    <div>Meta Ads Hub &bull; Bảng Quản trị Hệ thống Toàn quyền</div>
                    <div>Laravel v{{ app()->version() }} &bull; PHP v{{ PHP_VERSION }}</div>
                </div>
            </footer>

        </div>

    </div>

    <!-- Floating Toast Notifications (Góc dưới bên trái) -->
    <div id="toastContainer" 
         class="fixed bottom-6 left-6 md:left-72 z-50 flex flex-col-reverse gap-3 max-w-sm sm:max-w-md w-[calc(100%-3rem)] md:w-auto pointer-events-none transition-all">
        
        @if(session('success'))
            <div class="toast-item pointer-events-auto flex items-start space-x-3 bg-slate-900 border border-emerald-500/40 shadow-2xl shadow-emerald-500/10 rounded-2xl p-4 transition-all duration-300 transform translate-y-0 opacity-100">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <div class="text-xs font-bold text-white tracking-tight">Thành công</div>
                    <div class="text-xs text-emerald-200 mt-0.5 leading-relaxed font-medium">{{ session('success') }}</div>
                </div>
                <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="text-slate-400 hover:text-white p-1 rounded-lg transition flex-shrink-0" title="Đóng">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-item pointer-events-auto flex items-start space-x-3 bg-slate-900 border border-rose-500/40 shadow-2xl shadow-rose-500/10 rounded-2xl p-4 transition-all duration-300 transform translate-y-0 opacity-100">
                <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-sm flex-shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div class="flex-1 min-w-0 pr-1">
                    <div class="text-xs font-bold text-white tracking-tight">Đã xảy ra lỗi</div>
                    <div class="text-xs text-rose-200 mt-0.5 leading-relaxed font-medium">{{ session('error') }}</div>
                </div>
                <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="text-slate-400 hover:text-white p-1 rounded-lg transition flex-shrink-0" title="Đóng">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        @endif
    </div>

    <!-- Mobile Sidebar Toggle & Toast Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
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

        // Hàm showToast sử dụng SweetAlert2 chuẩn Admin
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
    </script>

    @stack('scripts')
</body>
</html>
