@extends('layout')

@section('title', 'Quản lý Facebook Pages - Meta Ads Hub')

@section('header_actions')
    <a href="{{ route('pages.publish.index') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-paper-plane text-xs mr-1.5"></i> Đăng bài & Hẹn giờ
    </a>

    <form action="{{ route('pages.sync') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-arrows-rotate text-xs mr-1.5"></i> Đồng bộ từ Facebook
        </button>
    </form>
    
    <a href="{{ route('auth.facebook') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-brands fa-facebook text-xs mr-1.5"></i> Kết nối lại Facebook (Cấp quyền Pages)
    </a>

    <button type="button" 
            onclick="openPageModal('token')"
            class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-key text-xs mr-1.5"></i> Nhập Token cá nhân
    </button>

    <button type="button" 
            onclick="openPageModal('manual')"
            class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-plus text-xs mr-1.5 text-sky-400"></i> Thêm Page ID
    </button>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Alert Banner khi chưa cấp quyền đọc Trang cá nhân -->
    @if(!$hasPersonalPagePermission)
        <div class="bg-amber-500/10 border-2 border-amber-500/30 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-start space-x-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 flex items-center justify-center text-lg flex-shrink-0 mt-0.5 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900 flex items-center gap-1.5">
                        <span>Chưa cấp quyền lấy Fanpage thuộc tài khoản cá nhân</span>
                        <span class="text-[10px] bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full font-mono">pages_show_list</span>
                    </h3>
                    <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                        Facebook yêu cầu cấp quyền <code class="bg-amber-100 px-1 py-0.5 rounded font-bold">pages_show_list</code> để ứng dụng có thể đọc danh sách các Trang do nick cá nhân sở hữu. 
                        Hiện tại hệ thống chỉ đọc được các Page trong BM. Bạn vui lòng bấm <strong>"Cấp quyền qua Facebook"</strong> hoặc <strong>"Nhập Token có quyền Pages"</strong> để tải toàn bộ các Page cá nhân.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <a href="{{ route('auth.facebook') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                    <i class="fa-brands fa-facebook text-sm mr-2"></i> Cấp quyền qua Facebook
                </a>
                <button type="button" 
                        onclick="openPageModal('token')" 
                        class="inline-flex items-center px-3.5 py-2.5 bg-white hover:bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold rounded-xl shadow-sm transition whitespace-nowrap">
                    <i class="fa-solid fa-key mr-1.5 text-amber-600"></i> Nhập Token cá nhân
                </button>
            </div>
        </div>
    @endif

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Pages</span>
                <i class="fa-solid fa-flag text-sky-500"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ number_format($stats['total']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Trang đã kết nối</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Trang Cá Nhân</span>
                <i class="fa-solid fa-user-tag text-purple-500"></i>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['personal_count']) }}</div>
            <div class="text-[11px] text-purple-600 mt-1">Thuộc nick cá nhân</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">BM Sở Hữu</span>
                <i class="fa-solid fa-building-user text-emerald-600"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-700">{{ number_format($stats['owned_count']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Trang chính chủ BM</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Đối Tác Ủy Quyền</span>
                <i class="fa-solid fa-handshake text-indigo-500"></i>
            </div>
            <div class="text-2xl font-bold text-indigo-600">{{ number_format($stats['client_count']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Trang đối tác/Agency</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Lượt Thích / Fan</span>
                <i class="fa-solid fa-thumbs-up text-blue-500"></i>
            </div>
            <div class="text-2xl font-bold text-blue-600 truncate">{{ number_format($stats['total_fans']) }}</div>
            <div class="text-[11px] text-slate-500 mt-1">{{ number_format($stats['total_followers']) }} người theo dõi</div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('pages.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm kiếm theo Tên Trang, Page ID, Chuyên mục..." 
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50">
            </div>

            <!-- Filter Source Type -->
            <div>
                <select name="source_type" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50">
                    <option value="">-- Tất cả Nguồn --</option>
                    <option value="personal" {{ request('source_type') == 'personal' ? 'selected' : '' }}>Trang cá nhân (Personal)</option>
                    <option value="owned" {{ request('source_type') == 'owned' ? 'selected' : '' }}>BM Sở hữu (Owned)</option>
                    <option value="client" {{ request('source_type') == 'client' ? 'selected' : '' }}>Đối tác ủy quyền (Client)</option>
                </select>
            </div>

            <!-- Filter BM -->
            <div>
                <select name="bm_id" class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/50">
                    <option value="">-- Tất cả BM --</option>
                    @foreach($bms as $bm)
                        <option value="{{ $bm->id }}" {{ request('bm_id') == $bm->id ? 'selected' : '' }}>
                            {{ $bm->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Action Buttons -->
            <div class="flex items-center space-x-2">
                <button type="submit" 
                        class="flex-1 py-2 px-3 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center justify-center">
                    <i class="fa-solid fa-filter text-xs mr-1.5"></i> Lọc
                </button>
                @if(request()->hasAny(['search', 'source_type', 'bm_id', 'status']))
                    <a href="{{ route('pages.index') }}" 
                       class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition" 
                       title="Xóa bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Pages List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-flag text-sky-600 mr-2"></i> Danh sách Trang Facebook
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Hiển thị {{ $pages->count() }} / {{ $pages->total() }} Pages tìm thấy.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if(($stats['hot_count'] ?? 0) > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                        <i class="fa-solid fa-fire text-[10px] mr-1.5 text-orange-500"></i>
                        {{ $stats['hot_count'] }} Hot
                    </span>
                @endif
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fa-solid fa-user-tag text-[10px] mr-1.5"></i>
                    {{ $stats['personal_count'] }} Trang cá nhân
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                    <i class="fa-solid fa-circle-dot text-[9px] text-sky-500 mr-1.5"></i>
                    {{ $pages->total() }} Tổng Pages
                </span>
            </div>
        </div>

        @if($pages->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <div class="w-14 h-14 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-sm">
                    <i class="fa-solid fa-flag"></i>
                </div>
                <div class="text-sm font-bold text-slate-800">Chưa tìm thấy Facebook Page nào</div>
                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto mb-4">
                    Nhấn "Kết nối lại Facebook" để cấp quyền đọc Page hoặc bấm "Nhập Token cá nhân" để tải toàn bộ các Page cá nhân của bạn.
                </p>
                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('auth.facebook') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i class="fa-brands fa-facebook mr-1.5"></i> Kết nối lại Facebook
                    </a>
                    <button type="button" 
                            onclick="openPageModal('token')" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-key mr-1.5"></i> Nhập Token
                    </button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">Fanpage</th>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">Nguồn & Doanh nghiệp</th>
                            <th class="px-4 py-2.5 text-right whitespace-nowrap">Lượt Thích / Fan</th>
                            <th class="px-4 py-2.5 text-left whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5">
                                    <span>Lần đăng gần nhất</span>
                                    <button type="button"
                                            id="btnRefreshLastPosts"
                                            onclick="refreshLastPosts(true)"
                                            title="Làm mới thời gian đăng bài gần nhất từ Facebook"
                                            class="w-5 h-5 rounded text-slate-400 hover:text-sky-600 hover:bg-sky-50 inline-flex items-center justify-center transition">
                                        <i id="iconRefreshLastPosts" class="fa-solid fa-arrows-rotate text-[10px]"></i>
                                    </button>
                                </span>
                            </th>
                            <th class="px-4 py-2.5 text-center whitespace-nowrap">Trạng thái</th>
                            <th class="px-4 py-2.5 text-center whitespace-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($pages as $page)
                            <tr class="{{ $page->is_hot ? 'bg-orange-50/40 hover:bg-orange-50/70' : 'hover:bg-slate-50/80' }} transition">
                                <!-- Fanpage Column -->
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $page->display_avatar }}" 
                                             alt="{{ $page->name }}" 
                                             class="w-9 h-9 rounded-xl object-cover border border-slate-200 flex-shrink-0 shadow-sm"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($page->name) }}&background=0284c7&color=ffffff&size=100'">
                                        <div class="min-w-0">
                                            <div class="flex items-center space-x-1.5">
                                                <a href="{{ $page->facebook_url }}" target="_blank" class="font-bold text-sm text-slate-800 hover:text-sky-600 transition truncate max-w-xs block" title="{{ $page->name }}">
                                                    {{ $page->name }}
                                                </a>
                                                @if($page->is_verified)
                                                    <span class="text-sky-500 text-xs flex-shrink-0" title="Đã xác minh (Verified)">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center mt-0.5">
                                                <span class="inline-flex items-center gap-1.5 text-[11px] font-mono text-slate-500">
                                                    <span>ID: {{ $page->page_id }}</span>
                                                    <button type="button" 
                                                            onclick="copyToClipboard('{{ $page->page_id }}', 'Đã sao chép Page ID!')" 
                                                            class="text-slate-400 hover:text-sky-600 transition" 
                                                            title="Sao chép Page ID">
                                                        <i class="fa-regular fa-copy text-[10px]"></i>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Nguồn & Doanh nghiệp -->
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $page->source_badge_class }}">
                                            @if($page->source_type === 'owned')
                                                <i class="fa-solid fa-building-user text-[9px] mr-1"></i>
                                            @elseif($page->source_type === 'client')
                                                <i class="fa-solid fa-handshake text-[9px] mr-1"></i>
                                            @else
                                                <i class="fa-solid fa-user-tag text-[9px] mr-1 text-purple-600"></i>
                                            @endif
                                            {{ $page->source_label }}
                                        </span>

                                        @if($page->businessManager && !$page->businessManager->is_personal)
                                            <div class="text-[11px] text-slate-600 flex items-center">
                                                <i class="fa-solid fa-briefcase text-[10px] text-slate-400 mr-1.5"></i>
                                                <a href="{{ route('bms.show', $page->business_manager_id) }}" class="hover:text-blue-600 hover:underline truncate max-w-[180px]" title="{{ $page->businessManager->name }}">
                                                    {{ $page->businessManager->name }}
                                                </a>
                                            </div>
                                        @elseif($page->source_type === 'personal')
                                            <div class="text-[11px] text-purple-600 flex items-center">
                                                <i class="fa-solid fa-user text-[9px] text-purple-400 mr-1"></i>
                                                <span>Nick Facebook cá nhân</span>
                                            </div>
                                        @endif

                                        @if(!empty($otherBmsByPageId[$page->page_id]))
                                            <div class="flex flex-wrap gap-1 pt-0.5">
                                                @foreach($otherBmsByPageId[$page->page_id] as $otherBm)
                                                    @if($otherBm['bm_id'] !== $page->business_manager_id)
                                                        <a href="{{ route('bms.show', $otherBm['bm_id']) }}"
                                                           class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-700 border border-slate-200 hover:border-blue-300 transition"
                                                           title="Page này cũng liên kết với BM: {{ $otherBm['bm_name'] }}">
                                                            <i class="fa-solid fa-share-nodes text-[9px] mr-1 text-blue-500"></i>
                                                            <span class="truncate max-w-[140px]">{{ $otherBm['bm_name'] }}</span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Lượt thích / Fan -->
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <div class="font-bold text-slate-900">
                                        {{ number_format($page->fan_count) }} <span class="text-[11px] font-normal text-slate-500">fans</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ number_format($page->followers_count) }} followers
                                    </div>
                                </td>

                                <!-- Lần đăng gần nhất -->
                                <td class="px-4 py-2.5 whitespace-nowrap"
                                    data-last-post-cell="{{ $page->page_id }}"
                                    data-pending-sync="{{ (!empty($page->access_token) && $page->last_post_synced_at === null) ? '1' : '0' }}">
                                    @if($page->last_post_at)
                                        <div class="font-semibold text-slate-800">
                                            @if($page->last_post_url)
                                                <a href="{{ $page->last_post_url }}" target="_blank" rel="noopener noreferrer"
                                                   class="hover:text-sky-600 hover:underline inline-flex items-center gap-1 transition"
                                                   title="Mở bài đăng gần nhất trên Facebook">
                                                    <span>{{ $page->last_post_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</span>
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400"></i>
                                                </a>
                                            @else
                                                {{ $page->last_post_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $page->last_post_at->copy()->locale('vi')->diffForHumans() }}
                                        </div>
                                    @elseif($page->last_post_synced_at)
                                        <span class="text-[11px] text-slate-400 italic">Chưa có bài đăng</span>
                                    @elseif(!empty($page->access_token))
                                        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-400">
                                            <i class="fa-solid fa-circle-notch fa-spin text-[10px] text-sky-500"></i> Đang tải...
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs" title="Chưa có Page Access Token để đọc bài đăng">—</span>
                                    @endif
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    @if($page->is_published)
                                        <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Đang hoạt động
                                        </span>
                                    @else
                                        <span class="inline-flex items-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-slate-400 rounded-full"></span> Bản nháp
                                        </span>
                                    @endif
                                </td>

                                <!-- Thao tác -->
                                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center space-x-1.5">
                                        <a href="{{ $page->facebook_url }}" 
                                           target="_blank" 
                                           class="inline-flex items-center px-2.5 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-xs font-semibold transition whitespace-nowrap"
                                           title="Mở Fanpage trên Facebook">
                                            <i class="fa-brands fa-facebook text-sm mr-1.5 text-blue-600"></i> Xem Page
                                        </a>

                                        <a href="https://business.facebook.com/latest/home?asset_id={{ $page->page_id }}" 
                                           target="_blank" 
                                           class="inline-flex items-center px-2 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition"
                                           title="Mở trong Meta Business Suite">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                        </a>

                                        <form action="{{ route('pages.toggleHot', $page->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition cursor-pointer whitespace-nowrap {{ $page->is_hot ? 'bg-gradient-to-r from-orange-500 to-rose-500 text-white border-orange-500 shadow-sm hover:from-orange-600 hover:to-rose-600' : 'bg-white hover:bg-orange-50 text-slate-500 hover:text-orange-600 border-slate-200 hover:border-orange-300' }}"
                                                    title="{{ $page->is_hot ? 'Đang đánh dấu Hot - Bấm để bỏ tick Hot' : 'Tick đánh dấu Hot để đưa lên đầu danh sách' }}">
                                                <i class="{{ $page->is_hot ? 'fa-solid fa-square-check' : 'fa-regular fa-square' }} text-xs mr-1.5"></i>
                                                <span>Hot</span>
                                                @if($page->is_hot)
                                                    <i class="fa-solid fa-fire text-[11px] ml-1 text-amber-200"></i>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($pages->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $pages->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL 1: NHẬP ACCESS TOKEN CÁ NHÂN (CÓ QUYỀN PAGES)            -->
<!-- ============================================================== -->
<div id="modalAddToken" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-indigo-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-key mr-2 text-indigo-400"></i> Đồng bộ bằng Access Token Cá Nhân
            </h3>
            <button onclick="closePageModal('token')" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('pages.token') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Nhập Facebook User Access Token <span class="text-rose-500">*</span>
                </label>
                <textarea name="access_token" 
                          rows="4" 
                          required 
                          placeholder="Dán mã Access Token của nick cá nhân (bắt đầu bằng EAAB... hoặc EAA...)" 
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <div class="mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                    <div class="font-bold text-slate-800"><i class="fa-solid fa-circle-info text-indigo-500 mr-1"></i> Cách lấy Token có quyền đọc Page cá nhân:</div>
                    <ul class="list-disc list-inside text-[11px] text-slate-500 space-y-0.5">
                        <li>Truy cập <a href="https://developers.facebook.com/tools/explorer/" target="_blank" class="text-blue-600 underline">Meta Graph API Explorer</a>.</li>
                        <li>Chọn ứng dụng của bạn, bấm <strong>Add a Permission</strong> và tick: <code>pages_show_list</code>, <code>pages_read_engagement</code>.</li>
                        <li>Bấm <strong>Generate Access Token</strong> và sao chép dán vào đây.</li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closePageModal('token')" 
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ ngay
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: THÊM PAGE THỦ CÔNG BẰNG PAGE ID                       -->
<!-- ============================================================== -->
<div id="modalAddManual" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-plus mr-2 text-sky-400"></i> Thêm Fanpage Cá Nhân Bằng ID
            </h3>
            <button onclick="closePageModal('manual')" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('pages.manual') }}" method="POST" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Facebook Page ID <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       name="page_id" 
                       required 
                       placeholder="Ví dụ: 104933219070049 hoặc 853464124512349" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-sky-500">
                <p class="text-[11px] text-slate-400 mt-1">ID hiển thị trên đường link Fanpage hoặc trong phần Giới thiệu của Trang.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Tên Fanpage (Tùy chọn)
                </label>
                <input type="text" 
                       name="name" 
                       placeholder="Nhập tên Trang để nhận diện dễ dàng hơn" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Chuyên mục (Tùy chọn)
                </label>
                <input type="text" 
                       name="category" 
                       placeholder="Ví dụ: Bất động sản, Dịch vụ kinh doanh..." 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closePageModal('manual')" 
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-check mr-1.5"></i> Thêm vào danh sách
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPageModal(type) {
        if (type === 'token') {
            document.getElementById('modalAddToken').classList.remove('hidden');
        } else if (type === 'manual') {
            document.getElementById('modalAddManual').classList.remove('hidden');
        }
    }

    function closePageModal(type) {
        if (type === 'token') {
            document.getElementById('modalAddToken').classList.add('hidden');
        } else if (type === 'manual') {
            document.getElementById('modalAddManual').classList.add('hidden');
        }
    }

    let isRefreshingLastPosts = false;

    function renderLastPostCell(cell, payload) {
        cell.textContent = '';
        cell.dataset.pendingSync = '0';

        if (!payload || !payload.has_post) {
            const span = document.createElement('span');
            span.className = 'text-[11px] text-slate-400 italic';
            span.textContent = (payload && payload.date_text) ? payload.date_text : 'Chưa có bài đăng';
            cell.appendChild(span);
            return;
        }

        const dateWrap = document.createElement('div');
        dateWrap.className = 'font-semibold text-slate-800';

        if (payload.permalink_url && /^https?:\/\//i.test(payload.permalink_url)) {
            const link = document.createElement('a');
            link.href = payload.permalink_url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.className = 'hover:text-sky-600 hover:underline inline-flex items-center gap-1 transition';
            link.title = 'Mở bài đăng gần nhất trên Facebook';

            const dateSpan = document.createElement('span');
            dateSpan.textContent = payload.date_text;
            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-arrow-up-right-from-square text-[9px] text-slate-400';

            link.appendChild(dateSpan);
            link.appendChild(icon);
            dateWrap.appendChild(link);
        } else {
            dateWrap.textContent = payload.date_text;
        }

        const relWrap = document.createElement('div');
        relWrap.className = 'text-[11px] text-slate-500 mt-0.5';
        relWrap.textContent = payload.relative_text || '';

        cell.appendChild(dateWrap);
        cell.appendChild(relWrap);
    }

    async function refreshLastPosts(forceAll = false) {
        if (isRefreshingLastPosts) return;

        const cells = Array.from(document.querySelectorAll('[data-last-post-cell]'));
        const targetIds = forceAll
            ? cells.map(c => c.dataset.lastPostCell).filter(Boolean)
            : cells.filter(c => c.dataset.pendingSync === '1').map(c => c.dataset.lastPostCell).filter(Boolean);

        if (!forceAll && targetIds.length === 0) {
            return;
        }

        isRefreshingLastPosts = true;
        const icon = document.getElementById('iconRefreshLastPosts');
        const btn = document.getElementById('btnRefreshLastPosts');
        if (icon) icon.classList.add('fa-spin');
        if (btn) btn.disabled = true;

        try {
            const body = forceAll ? { only_unsynced: false } : { page_ids: targetIds, only_unsynced: true };
            const res = await fetch('{{ route('pages.syncLastPosts') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(body),
            });

            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }

            const data = await res.json();
            const posts = data.posts || {};

            cells.forEach(cell => {
                const pageId = cell.dataset.lastPostCell;
                if (posts[pageId]) {
                    renderLastPostCell(cell, posts[pageId]);
                } else if (!forceAll && cell.dataset.pendingSync === '1') {
                    cell.textContent = '';
                    const dash = document.createElement('span');
                    dash.className = 'text-slate-400 text-xs';
                    dash.textContent = '—';
                    cell.appendChild(dash);
                }
            });

            if (forceAll && typeof window.showToast === 'function') {
                window.showToast('Đã cập nhật lần đăng gần nhất cho ' + (data.updated_count || 0) + ' Pages!', 'success');
            }
        } catch (err) {
            if (forceAll && typeof window.showToast === 'function') {
                window.showToast('Không thể cập nhật lần đăng gần nhất!', 'error');
            }
        } finally {
            isRefreshingLastPosts = false;
            if (icon) icon.classList.remove('fa-spin');
            if (btn) btn.disabled = false;
        }
    }

    @if($hasPendingLastPostSync ?? false)
    document.addEventListener('DOMContentLoaded', function () {
        refreshLastPosts(false);
    });
    @endif
</script>
@endpush
@endsection
