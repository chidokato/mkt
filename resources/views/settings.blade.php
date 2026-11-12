@extends('layout')

@section('title', 'Cấu hình Website & Meta App - Meta Ads Hub')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Cấu hình Website & Meta App</h1>
            <p class="text-sm text-slate-500 mt-1">Quản lý khóa API Facebook Developers và tài khoản quản trị.</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-lg shadow-sm transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại Dashboard
            </a>
        </div>
    </div>

    <!-- Section 1: Cấu hình Meta Graph API -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-brands fa-facebook"></i>
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Cấu hình Meta Developer App</h2>
                    <p class="text-xs text-slate-500">Thông số ứng dụng kết nối trực tiếp với Meta Graph API</p>
                </div>
            </div>
            <a href="https://developers.facebook.com/apps" target="_blank" 
               class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center">
                Mở Meta Apps <i class="fa-solid fa-arrow-up-right-from-square ml-1.5"></i>
            </a>
        </div>

        <form action="{{ route('settings.meta') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- URI OAuth Box -->
            <div class="bg-blue-50/60 border border-blue-200 rounded-xl p-4">
                <label class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-1">
                    <i class="fa-solid fa-link mr-1"></i> URI chuyển hướng OAuth hợp lệ (Redirect URI)
                </label>
                <p class="text-xs text-blue-700 mb-2">
                    Đường dẫn nhận mã xác thực từ Facebook. Bạn có thể sao chép để dán vào Meta Developer > Đăng nhập bằng Facebook > Cài đặt.
                </p>
                <div class="flex items-center gap-2">
                    <input type="text" id="oauthUrlInput" value="{{ $callbackUrl }}" readonly
                           class="w-full px-3 py-2 bg-white border border-blue-300 rounded-lg text-xs font-mono text-slate-700 focus:outline-none">
                    <button type="button" onclick="copyOAuthUrl()" 
                            class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center">
                        <i class="fa-regular fa-copy mr-1.5"></i> Sao chép
                    </button>
                </div>
                <span id="copyNotice" class="text-xs text-emerald-600 font-medium hidden mt-1 inline-block">
                    <i class="fa-solid fa-check mr-1"></i> Đã sao chép vào bộ nhớ tạm!
                </span>
            </div>

            <!-- Form Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        ID ứng dụng (Meta App ID) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="app_id" value="{{ old('app_id', $metaSettings['app_id']) }}" required
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Khóa bí mật (App Secret) <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="app_secret" value="{{ old('app_secret', $metaSettings['app_secret']) }}" required
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Phiên bản Graph API (Meta Graph Version)
                    </label>
                    <input type="text" name="version" value="{{ old('version', $metaSettings['version']) }}" required
                           placeholder="v21.0"
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">Mặc định khuyên dùng: <code>v21.0</code></p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu cấu hình Meta
                </button>
            </div>
        </form>
    </div>

    <!-- Section 2: Tài khoản Admin & Đổi mật khẩu -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center space-x-3">
            <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-shield"></i>
            </span>
            <div>
                <h2 class="text-base font-bold text-slate-900">Tài khoản Quản trị & Bảo mật</h2>
                <p class="text-xs text-slate-500">Thay đổi tên, email đăng nhập và mật khẩu quản trị viên</p>
            </div>
        </div>

        <form action="{{ route('settings.profile') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Tên Quản trị viên <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Email Đăng nhập <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Change Password Sub-section -->
            <div class="pt-4 border-t border-slate-100">
                <h3 class="text-xs font-bold uppercase text-slate-500 tracking-wider mb-4">
                    <i class="fa-solid fa-key mr-1"></i> Đổi mật khẩu (Bỏ trống nếu không thay đổi)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" placeholder="••••••••"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mật khẩu mới</label>
                        <input type="password" name="new_password" placeholder="Tối thiểu 6 ký tự"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Xác nhận mật khẩu mới</label>
                        <input type="password" name="new_password_confirmation" placeholder="Nhập lại mật khẩu mới"
                               class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg shadow-sm transition flex items-center">
                    <i class="fa-solid fa-user-check mr-2"></i> Cập nhật thông tin Admin
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function copyOAuthUrl() {
        const input = document.getElementById('oauthUrlInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const notice = document.getElementById('copyNotice');
            notice.classList.remove('hidden');
            setTimeout(() => {
                notice.classList.add('hidden');
            }, 3000);
        });
    }
</script>
@endpush
