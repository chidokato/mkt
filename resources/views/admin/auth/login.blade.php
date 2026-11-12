<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Đăng nhập Quản trị viên - Meta Ads Hub</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Admin Shield Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 mb-4 text-3xl shadow-lg shadow-amber-500/10">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Khu vực Quản trị Hệ thống</h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-widest font-semibold">Admin Portal &bull; Meta Ads Hub</p>
        </div>

        <!-- Admin Login Card -->
        <div class="bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 p-8">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                <h2 class="text-base font-bold text-slate-200">Xác thực Quyền Admin</h2>
                <span class="text-xs bg-amber-500/20 text-amber-400 px-2.5 py-0.5 rounded-full font-mono font-medium">Bảo mật cao</span>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-5 p-3 rounded-lg bg-emerald-950 border border-emerald-800 text-emerald-300 text-xs flex items-center">
                    <i class="fa-solid fa-circle-check text-emerald-400 mr-2 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-3 rounded-lg bg-rose-950 border border-rose-800 text-rose-300 text-xs flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 mr-2 text-sm"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3 rounded-lg bg-rose-950 border border-rose-800 text-rose-300 text-xs flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 mr-2 text-sm"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Email Quản trị viên
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-user-gear"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'admin@gmail.com') }}" required autofocus
                               class="w-full pl-10 pr-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-lg text-sm text-slate-100 focus:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                               placeholder="admin@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Mật khẩu Quản trị
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-3.5 py-2.5 bg-slate-800/80 border border-slate-700 rounded-lg text-sm text-slate-100 focus:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-amber-500 rounded border-slate-700 bg-slate-800 focus:ring-amber-500 mr-2">
                        <span>Ghi nhớ phiên Admin</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-lg shadow-lg shadow-amber-500/20 text-sm transition flex items-center justify-center">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i> Truy cập Bảng Quản trị
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                <p class="text-xs text-slate-400">
                    Tài khoản mặc định: <span class="text-amber-400 font-mono font-semibold">admin@gmail.com</span> / <span class="text-amber-400 font-mono font-semibold">password</span>
                </p>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-slate-300 transition">
                &larr; Quay lại trang Người dùng đăng nhập
            </a>
        </div>
    </div>

</body>
</html>
