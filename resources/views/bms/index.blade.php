@extends('layout')

@section('title', 'Quản lý BM - Meta Ads Hub')

@section('header_actions')
    @if(isset($latestUpdate))
        <div class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium whitespace-nowrap"
             title="Thời điểm cập nhật dữ liệu BM gần nhất: {{ $latestUpdate->format('d/m/Y H:i:s') }}">
            <i class="fa-regular fa-clock text-slate-400 mr-1.5 text-xs"></i>
            <span class="hidden sm:inline text-slate-500">Cập nhật:</span>
            <span class="font-semibold text-slate-800 ml-1">{{ $latestUpdate->diffForHumans() }}</span>
        </div>
    @endif

    <form action="{{ route('bms.syncAllFacebook') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                title="Quét lại toàn bộ các BM và tài khoản từ tài khoản Facebook đã kết nối"
                class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-arrows-rotate text-xs mr-1.5"></i> Đồng bộ tất cả
        </button>
    </form>
    <a href="{{ route('auth.facebook') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-brands fa-facebook text-xs mr-1.5"></i> Kết nối FB
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng BM</span>
                <i class="fa-solid fa-building text-slate-400"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_bms'] }}</div>
            <div class="text-[11px] text-emerald-600 font-medium mt-1">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ $stats['active_bms'] }} đang hoạt động
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Ad Accounts</span>
                <i class="fa-solid fa-rectangle-ad text-blue-500"></i>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['total_accounts'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1 truncate" title="{{ $stats['owned_accounts'] ?? 0 }} sở hữu • {{ $stats['client_accounts'] ?? 0 }} được share">
                <span class="text-blue-600 font-medium">{{ $stats['owned_accounts'] ?? 0 }} sở hữu</span> &bull; <span class="text-purple-600 font-medium">{{ $stats['client_accounts'] ?? 0 }} share</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">TKQC Active</span>
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['active_accounts'] }}</div>
            <div class="text-[11px] text-emerald-600 mt-1">Sẵn sàng chạy Ads</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">TKQC Vô hiệu hóa</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            </div>
            <div class="text-2xl font-bold text-rose-600">{{ $stats['disabled_accounts'] }}</div>
            <div class="text-[11px] text-rose-500 mt-1">Tài khoản bị cấm (Die)</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Đã Tiêu</span>
                <div class="flex items-center gap-1.5">
                    @if($needsApiSpend ?? false)
                        <button type="button"
                                id="btnRefreshBmSpends"
                                onclick="loadBmSpends(true)"
                                title="Làm mới chi tiêu trực tiếp từ Meta API"
                                class="w-5 h-5 rounded-md text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 inline-flex items-center justify-center transition">
                            <i id="iconRefreshBmSpends" class="fa-solid fa-arrows-rotate text-[11px]"></i>
                        </button>
                    @endif
                    <i class="fa-solid fa-money-bill-wave text-indigo-500"></i>
                </div>
            </div>
            <div id="statsTotalSpent" class="text-lg font-bold text-indigo-600 truncate transition-opacity">
                @if($spendReady ?? true)
                    {{ number_format($stats['total_spent'], 0, ',', '.') }} đ
                @else
                    <span class="inline-flex items-center gap-1.5 text-sm text-indigo-400 font-medium">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i> Đang tải...
                    </span>
                @endif
            </div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-1 truncate" title="Thời gian lọc: {{ $dateLabel }}">
                <i class="fa-regular fa-calendar-check mr-1"></i> {{ $dateLabel }}
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form action="{{ route('bms.index') }}" method="GET" class="flex flex-1 flex-col sm:flex-row flex-wrap items-center gap-2.5">
            <!-- Search by Name/ID -->
            <div class="relative flex-1 min-w-[200px] w-full sm:w-auto">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Tìm theo Tên BM hoặc ID..." 
                       class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Filter by Status -->
            <div class="w-full sm:w-44">
                <select name="status" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-700">
                    <option value="">-- Trạng thái --</option>
                    <option value="has_debt" {{ ($statusFilter ?? '') === 'has_debt' ? 'selected' : '' }}>⚠️ Có TK nợ tiền</option>
                    <option value="active" {{ ($statusFilter ?? '') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="expired" {{ ($statusFilter ?? '') === 'expired' ? 'selected' : '' }}>Hết hạn Token</option>
                    <option value="disabled" {{ ($statusFilter ?? '') === 'disabled' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>

            <!-- Date/Time Filter for Spend -->
            <div class="w-full sm:w-44">
                <div class="relative">
                    <select name="date_preset" id="datePresetSelect" onchange="handleDatePresetChange(this)" 
                            class="w-full pl-7 pr-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-indigo-700">
                        <option value="last_30d" {{ ($datePreset ?? 'last_30d') === 'last_30d' ? 'selected' : '' }}>30 ngày qua (Mặc định)</option>
                        <option value="today" {{ ($datePreset ?? '') === 'today' ? 'selected' : '' }}>Hôm nay</option>
                        <option value="yesterday" {{ ($datePreset ?? '') === 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                        <option value="last_7d" {{ ($datePreset ?? '') === 'last_7d' ? 'selected' : '' }}>7 ngày qua</option>
                        <option value="this_month" {{ ($datePreset ?? '') === 'this_month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="last_month" {{ ($datePreset ?? '') === 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                        <option value="this_year" {{ ($datePreset ?? '') === 'this_year' ? 'selected' : '' }}>Năm nay</option>
                        <option value="last_year" {{ ($datePreset ?? '') === 'last_year' ? 'selected' : '' }}>Năm trước</option>
                        <option value="maximum" {{ ($datePreset ?? '') === 'maximum' ? 'selected' : '' }}>Toàn thời gian</option>
                        <option value="custom" {{ ($datePreset ?? '') === 'custom' ? 'selected' : '' }}>Tùy chọn ngày...</option>
                    </select>
                    <i class="fa-regular fa-calendar-days absolute left-2.5 top-2.5 text-indigo-500 text-xs pointer-events-none"></i>
                </div>
            </div>

            <!-- Custom date range inputs (hiển thị khi chọn Tùy chọn ngày) -->
            <div id="customDateRangeBox" class="{{ ($datePreset ?? '') === 'custom' ? 'flex' : 'hidden' }} items-center gap-1.5 w-full sm:w-auto">
                <input type="date" name="since" value="{{ $since ?? '' }}" class="px-2.5 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                <span class="text-xs text-slate-400">-</span>
                <input type="date" name="until" value="{{ $until ?? '' }}" class="px-2.5 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition whitespace-nowrap">
                Lọc
            </button>

            @if(!empty($search) || !empty($statusFilter) || (($datePreset ?? 'last_30d') !== 'last_30d') || !empty($since))
                <a href="{{ route('bms.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition text-center flex items-center justify-center whitespace-nowrap" title="Đặt lại mọi bộ lọc">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Xóa lọc
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium whitespace-nowrap">
            Hiển thị <span class="font-bold text-slate-800">{{ $bms->total() }}</span> BM
        </div>
    </div>

    <!-- BM List Table -->
    @if($bms->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">Chưa tìm thấy Business Manager nào</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
                @if(!empty($search) || !empty($statusFilter))
                    Không có BM nào phù hợp với bộ lọc tìm kiếm của bạn. Hãy thử thay đổi từ khóa.
                @else
                    Bạn chưa thêm Business Manager nào. Hãy thêm BM bằng System User Token hoặc kết nối tài khoản Facebook để bắt đầu quản lý.
                @endif
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <button onclick="openAddBMModal()" 
                        class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-plus-circle text-sm mr-2"></i> Thêm BM bằng Token
                </button>
                <a href="{{ route('auth.facebook') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-brands fa-facebook text-sm mr-2"></i> Kết nối Facebook (1-Click)
                </a>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/80 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Business Manager</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Trạng thái</th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="flex items-center justify-center gap-1.5" title="Tài khoản quảng cáo do BM sở hữu (Active / Die / Nợ tiền)">
                                    <i class="fa-solid fa-building text-blue-600"></i>
                                    <span>TK của BM</span>
                                </span>
                            </th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">
                                <span class="flex items-center justify-center gap-1.5" title="Tài khoản quảng cáo được share từ BM khác (Active / Die / Nợ tiền)">
                                    <i class="fa-solid fa-share-nodes text-purple-600"></i>
                                    <span>TK được share</span>
                                </span>
                            </th>
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">Quản lý Đối tác</th>
                            <th class="px-4 py-2.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Chi tiêu</span>
                                    @if(($datePreset ?? 'maximum') !== 'maximum')
                                        <span class="text-[10px] text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded font-bold border border-indigo-200" title="{{ $dateLabel }}">({{ $dateLabel }})</span>
                                    @endif
                                    <span class="text-slate-300">/</span>
                                    <span>Nợ</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($bms as $bm)
                            <tr class="hover:bg-slate-50/80 transition group">
                                <!-- BM Name & ID -->
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                                            @if($bm->is_oauth)
                                                <i class="fa-brands fa-facebook"></i>
                                            @else
                                                <i class="fa-solid fa-building"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('bms.show', $bm->id) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm block transition">
                                                {{ $bm->name }}
                                            </a>
                                            <div class="flex items-center space-x-2 mt-0.5">
                                                @if($bm->bm_id)
                                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded font-mono border border-slate-200 flex items-center gap-1">
                                                        <span>ID: {{ $bm->bm_id }}</span>
                                                        <button type="button" onclick="copyToClipboard('{{ $bm->bm_id }}', 'Đã copy BM ID!')" class="hover:text-blue-600" title="Sao chép ID">
                                                            <i class="fa-regular fa-copy text-[9px]"></i>
                                                        </button>
                                                    </span>
                                                @endif

                                                @if($bm->is_oauth)
                                                    <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.2 rounded font-semibold">OAuth</span>
                                                @else
                                                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.2 rounded font-semibold">System User</span>
                                                @endif

                                                @if($bm->unsettled_accounts_count > 0)
                                                    <a href="{{ route('bms.show', ['bm' => $bm->id, 'account_status' => 3]) }}" 
                                                       class="text-[10px] bg-amber-50 hover:bg-amber-100 text-amber-700 px-1.5 py-0.2 rounded font-semibold border border-amber-300 flex items-center gap-1 transition"
                                                       title="BM này có {{ $bm->unsettled_accounts_count }} tài khoản đang nợ tiền - Bấm để xem chi tiết">
                                                        <i class="fa-solid fa-file-invoice-dollar text-[9px] text-amber-600"></i>
                                                        <span>{{ $bm->unsettled_accounts_count }} TK nợ</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    <div id="status-badge-{{ $bm->id }}" class="inline-flex items-center space-x-1.5">
                                        @if($bm->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full animate-pulse"></span> Hoạt động
                                            </span>
                                        @elseif($bm->status === 'expired')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                <i class="fa-solid fa-circle-xmark mr-1"></i> Hết hạn
                                            </span>
                                        @elseif($bm->status === 'error')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Lỗi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                {{ ucfirst($bm->status) }}
                                            </span>
                                        @endif
                                        <button type="button" 
                                                onclick="checkTokenLive({{ $bm->id }}, '{{ addslashes($bm->name) }}')"
                                                class="w-5 h-5 rounded text-slate-400 hover:text-blue-600 hover:bg-blue-50 inline-flex items-center justify-center transition" 
                                                title="Kiểm tra trạng thái Token với Facebook">
                                            <i class="fa-solid fa-stethoscope text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Cột 1: TK của BM (Sở hữu) -->
                                <td class="px-3 py-2.5 text-center">
                                    @if($bm->owned_accounts_count > 0)
                                        <div class="inline-flex flex-col items-center">
                                            <a href="{{ route('bms.show', ['bm' => $bm->id, 'source' => 'owned']) }}" 
                                               class="font-bold text-blue-700 hover:text-blue-900 text-sm transition"
                                               title="Xem {{ $bm->owned_accounts_count }} tài khoản do BM trực tiếp sở hữu">
                                                {{ $bm->owned_accounts_count }}
                                            </a>
                                            <div class="flex items-center space-x-1 mt-0.5 text-[10px]">
                                                <span class="text-emerald-600 font-semibold" title="TK sở hữu Active">
                                                    <i class="fa-solid fa-circle-check text-[9px]"></i> {{ $bm->owned_active_accounts_count }}
                                                </span>
                                                <span class="text-slate-300">/</span>
                                                <span class="text-rose-600 font-semibold" title="TK sở hữu Die">
                                                    <i class="fa-solid fa-circle-xmark text-[9px]"></i> {{ $bm->owned_disabled_accounts_count }}
                                                </span>
                                                <span class="text-slate-300">/</span>
                                                @if($bm->owned_unsettled_accounts_count > 0)
                                                    <a href="{{ route('bms.show', ['bm' => $bm->id, 'source' => 'owned', 'account_status' => 3]) }}"
                                                       class="text-amber-600 hover:text-amber-700 font-bold hover:underline inline-flex items-center" 
                                                       title="TK sở hữu Nợ tiền: {{ $bm->owned_unsettled_accounts_count }} - Bấm để lọc">
                                                        <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> {{ $bm->owned_unsettled_accounts_count }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300" title="TK sở hữu Nợ tiền: 0">
                                                        <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> 0
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col items-center text-slate-400">
                                            <span class="font-bold text-xs">0</span>
                                            <div class="flex items-center space-x-1 mt-0.5 text-[10px] text-slate-300">
                                                <span><i class="fa-solid fa-circle-check text-[9px]"></i> 0</span>
                                                <span>/</span>
                                                <span><i class="fa-solid fa-circle-xmark text-[9px]"></i> 0</span>
                                                <span>/</span>
                                                <span><i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> 0</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Cột 2: TK được share từ BM khác (Client) -->
                                <td class="px-3 py-2.5 text-center">
                                    @if($bm->client_accounts_count > 0)
                                        <div class="inline-flex flex-col items-center">
                                            <a href="{{ route('bms.show', ['bm' => $bm->id, 'source' => 'client']) }}" 
                                               class="font-bold text-purple-700 hover:text-purple-900 text-sm transition"
                                               title="Xem {{ $bm->client_accounts_count }} tài khoản được share từ BM khác">
                                                {{ $bm->client_accounts_count }}
                                            </a>
                                            <div class="flex items-center space-x-1 mt-0.5 text-[10px]">
                                                <span class="text-emerald-600 font-semibold" title="TK được share Active">
                                                    <i class="fa-solid fa-circle-check text-[9px]"></i> {{ $bm->client_active_accounts_count }}
                                                </span>
                                                <span class="text-slate-300">/</span>
                                                <span class="text-rose-600 font-semibold" title="TK được share Die">
                                                    <i class="fa-solid fa-circle-xmark text-[9px]"></i> {{ $bm->client_disabled_accounts_count }}
                                                </span>
                                                <span class="text-slate-300">/</span>
                                                @if($bm->client_unsettled_accounts_count > 0)
                                                    <a href="{{ route('bms.show', ['bm' => $bm->id, 'source' => 'client', 'account_status' => 3]) }}"
                                                       class="text-amber-600 hover:text-amber-700 font-bold hover:underline inline-flex items-center" 
                                                       title="TK được share Nợ tiền: {{ $bm->client_unsettled_accounts_count }} - Bấm để lọc">
                                                        <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> {{ $bm->client_unsettled_accounts_count }}
                                                    </a>
                                                @else
                                                    <span class="text-slate-300" title="TK được share Nợ tiền: 0">
                                                        <i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> 0
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col items-center text-slate-400">
                                            <span class="font-bold text-xs">0</span>
                                            <div class="flex items-center space-x-1 mt-0.5 text-[10px] text-slate-300">
                                                <span><i class="fa-solid fa-circle-check text-[9px]"></i> 0</span>
                                                <span>/</span>
                                                <span><i class="fa-solid fa-circle-xmark text-[9px]"></i> 0</span>
                                                <span>/</span>
                                                <span><i class="fa-solid fa-file-invoice-dollar text-[9px]"></i> 0</span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                <!-- Quản lý Đối tác BM (Partners) -->
                                <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                    @if($bm->partners_count > 0)
                                        <div class="inline-flex items-center space-x-1">
                                            <button type="button" 
                                                    onclick="openPartnersModal({{ $bm->id }})" 
                                                     class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition group/btn"
                                                    title="Xem chi tiết {{ $bm->partners_count }} đối tác được chia sẻ tài sản của BM">
                                                <i class="fa-solid fa-handshake text-purple-600 group-hover/btn:scale-110 transition text-[11px]"></i>
                                                <span class="font-bold font-mono text-xs">{{ $bm->partners_count }}</span>
                                                <span>đối tác</span>
                                            </button>
                                            @if($bm->bm_id)
                                                <a href="https://business.facebook.com/settings/partners?business_id={{ $bm->bm_id }}" 
                                                   target="_blank" 
                                                   rel="noopener noreferrer"
                                                   title="Mở Quản lý Đối tác trên Facebook Business Suite"
                                                   class="w-6 h-6 rounded-md bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-200 text-slate-400 hover:text-purple-600 inline-flex items-center justify-center transition">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="inline-flex items-center space-x-1">
                                            <button type="button" 
                                                    onclick="openPartnersModal({{ $bm->id }})" 
                                                    class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-xs bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-500 transition"
                                                    title="Xem thông tin đối tác">
                                                <i class="fa-regular fa-handshake text-slate-400 text-[11px]"></i>
                                                <span>0 đối tác</span>
                                            </button>
                                            @if($bm->bm_id)
                                                <a href="https://business.facebook.com/settings/partners?business_id={{ $bm->bm_id }}" 
                                                   target="_blank" 
                                                   rel="noopener noreferrer"
                                                   title="Thêm đối tác mới trên Meta Business Suite"
                                                   class="w-6 h-6 rounded-md bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 text-slate-400 hover:text-blue-600 inline-flex items-center justify-center transition">
                                                    <i class="fa-solid fa-plus text-[9px]"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <!-- Spend & Balance -->
                                <td class="px-4 py-2.5 text-right">
                                    <div id="bm-spent-{{ $bm->id }}"
                                         data-bm-spend-id="{{ $bm->id }}"
                                         class="font-bold text-slate-900 text-xs transition-opacity">
                                        @if(!empty($bm->spend_pending))
                                            <span class="inline-flex items-center justify-end gap-1 text-slate-400 font-medium">
                                                <i class="fa-solid fa-circle-notch fa-spin text-[10px]"></i>
                                                <span>Đang tải...</span>
                                            </span>
                                        @else
                                            {{ number_format($bm->filtered_spent ?? $bm->total_spent, 0, ',', '.') }} đ
                                        @endif
                                    </div>
                                    @if($bm->total_balance > 0 || $bm->unsettled_accounts_count > 0)
                                        <div class="text-[10px] mt-0.5 text-rose-600 font-semibold flex items-center justify-end gap-1">
                                            <span>Nợ: {{ number_format($bm->total_balance, 0, ',', '.') }} đ</span>
                                            <a href="{{ route('bms.show', ['bm' => $bm->id, 'account_status' => 3]) }}"
                                               class="inline-flex items-center px-1.5 py-0.2 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 font-bold transition"
                                               title="Bấm để lọc {{ $bm->unsettled_accounts_count }} tài khoản nợ tiền của BM này">
                                                <i class="fa-solid fa-file-invoice-dollar text-[9px] mr-0.5 text-amber-600"></i>{{ $bm->unsettled_accounts_count }} TK nợ
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-[10px] mt-0.5 text-slate-400">
                                            Nợ: 0 đ
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($bms->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                    {{ $bms->links() }}
                </div>
            @endif
        </div>
    @endif

</div>

<!-- ============================================================== -->
<!-- MODAL: THÊM BUSINESS MANAGER MỚI                               -->
<!-- ============================================================== -->
<div id="modalAddBM" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-plus-circle mr-2"></i> Thêm Business Manager Mới
            </h3>
            <button onclick="closeAddBMModal()" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('bms.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Tên gợi nhớ Business Manager <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       required 
                       placeholder="Ví dụ: BM 2500 Agency, BM Khách Hàng X, BM Backup..." 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-[11px] text-slate-500 mt-1">Tên để bạn dễ nhận biết và quản lý giữa các BM khác nhau.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Business Manager ID (Tùy chọn)
                </label>
                <input type="text" 
                       name="bm_id" 
                       placeholder="Ví dụ: 123456789012345" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-[11px] text-slate-500 mt-1">ID BM hiển thị trong Meta Business Suite (Cài đặt doanh nghiệp).</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    System User Access Token <span class="text-rose-500">*</span>
                </label>
                <textarea name="access_token" 
                          rows="4" 
                          required 
                          placeholder="Dán mã System User Token (Never expire) tạo từ Meta Business Suite vào đây..." 
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                
                <div class="mt-2 p-3 bg-blue-50/70 border border-blue-200/60 rounded-xl text-xs text-blue-800 space-y-1">
                    <div class="font-bold flex items-center">
                        <i class="fa-solid fa-circle-info mr-1.5 text-blue-600"></i> Hướng dẫn lấy Token System User:
                    </div>
                    <ol class="list-decimal list-inside text-[11px] text-slate-600 space-y-0.5 ml-1">
                        <li>Truy cập Meta Business Suite &rarr; Cài đặt cho doanh nghiệp &rarr; Người dùng hệ thống.</li>
                        <li>Tạo người dùng mới với vai trò <strong>Quản trị viên</strong>.</li>
                        <li>Bấm <strong>Tạo mã mới</strong>, chọn quyền: <code class="bg-blue-100 text-blue-900 px-1 py-0.5 rounded">ads_management</code>, <code class="bg-blue-100 text-blue-900 px-1 py-0.5 rounded">ads_read</code>, <code class="bg-blue-100 text-blue-900 px-1 py-0.5 rounded">business_management</code>.</li>
                        <li>Chọn hạn dùng <strong>Không bao giờ (Never expire)</strong> rồi dán token vào đây.</li>
                    </ol>
                </div>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeAddBMModal()" 
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Lưu & Đồng bộ ngay
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: CHỈNH SỬA BUSINESS MANAGER                              -->
<!-- ============================================================== -->
<div id="modalEditBM" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-pen-to-square mr-2 text-amber-400"></i> Chỉnh sửa Business Manager
            </h3>
            <button onclick="closeEditBMModal()" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form id="formEditBM" action="" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Tên Business Manager <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="edit_name" 
                       name="name" 
                       required 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Business Manager ID (Tùy chọn)
                </label>
                <input type="text" 
                       id="edit_bm_id" 
                       name="bm_id" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Cập nhật Access Token mới (Để trống nếu không muốn đổi)
                </label>
                <textarea name="access_token" 
                          rows="3" 
                          placeholder="Nhập Access Token mới nếu token cũ đã hết hạn hoặc bị đổi mật khẩu Facebook..." 
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                <p class="text-[11px] text-amber-600 font-medium mt-1">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Nếu nhập token mới, hệ thống sẽ tự động kiểm tra và đồng bộ lại danh sách tài khoản ngay lập tức.
                </p>
            </div>

            <div class="pt-4 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeEditBMModal()" 
                        class="px-4 py-2 text-xs text-slate-600 hover:bg-slate-100 rounded-xl transition font-semibold">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-check mr-1.5"></i> Cập nhật thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: KẾT QUẢ KIỂM TRA TOKEN (TOKEN INSPECTOR)                -->
<!-- ============================================================== -->
<div id="modalTokenInspector" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-stethoscope mr-2 text-emerald-400"></i> Kết quả Kiểm tra Token Meta API
            </h3>
            <button onclick="closeTokenModal()" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <div class="p-6">
            <!-- Loading State -->
            <div id="tokenLoading" class="py-12 text-center">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-3"></i>
                <div class="text-sm font-semibold text-slate-800">Đang kết nối Facebook Graph API...</div>
                <div class="text-xs text-slate-400 mt-1">Đang kiểm tra tính hợp lệ và hạn dùng của Token.</div>
            </div>

            <!-- Result Body -->
            <div id="tokenResult" class="hidden space-y-4">
                <div id="tokenStatusBanner" class="p-4 rounded-xl flex items-center space-x-3">
                    <!-- Dynamic banner from JS -->
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Đối tượng sở hữu:</span>
                        <span id="tokenOwner" class="font-bold text-slate-800"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Hạn sử dụng:</span>
                        <span id="tokenExpires" class="font-bold text-slate-800"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Loại Token:</span>
                        <span id="tokenType" class="font-mono font-semibold text-slate-700"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500 font-medium">Meta App liên kết:</span>
                        <span id="tokenApp" class="font-semibold text-slate-700"></span>
                    </div>
                    <div class="py-1">
                        <span class="text-slate-500 font-medium block mb-1.5">Quyền hạn (Scopes):</span>
                        <div id="tokenScopes" class="flex flex-wrap gap-1.5">
                            <!-- Dynamic Scope Badges -->
                        </div>
                    </div>
                </div>

                <div id="tokenErrorBox" class="hidden p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700">
                    <div class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Chi tiết lỗi từ Meta:</div>
                    <div id="tokenErrorMessage" class="font-mono text-[11px]"></div>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeTokenModal()" 
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                Đóng
            </button>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: QUẢN LÝ ĐỐI TÁC CỦA BUSINESS MANAGER                    -->
<!-- ============================================================== -->
<div id="modalPartners" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-700 via-indigo-700 to-blue-700 px-6 py-4 flex justify-between items-center text-white flex-shrink-0">
            <div class="flex items-center space-x-3">
                <span class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-handshake"></i>
                </span>
                <div>
                    <h3 class="font-bold text-base" id="modalPartnersBmName">Quản lý Đối tác BM</h3>
                    <div class="text-xs text-purple-200 font-mono mt-0.5" id="modalPartnersBmId">ID: ...</div>
                </div>
            </div>
            <button onclick="closePartnersModal()" class="text-white/80 hover:text-white text-2xl font-bold p-1 leading-none">&times;</button>
        </div>

        <!-- Toolbar & Filter -->
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
            <div class="relative w-full sm:w-72">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" 
                       id="filterPartnersInput" 
                       oninput="filterPartnersList()" 
                       placeholder="Tìm theo tên hoặc ID đối tác..." 
                       class="w-full pl-8 pr-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                <button type="button" 
                        id="btnSyncPartners" 
                        onclick="syncPartnersFromMeta()" 
                        class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-arrows-rotate mr-1.5" id="iconSyncPartners"></i> 
                    <span>Đồng bộ từ Meta</span>
                </button>
                <a id="linkMetaPartners" 
                   href="#" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i>
                    <span>Meta Suite ↗</span>
                </a>
            </div>
        </div>

        <!-- Body / Partners List -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6" id="modalPartnersBody">
            <div id="partnersLoading" class="py-12 text-center text-slate-400">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-purple-600 mb-2"></i>
                <p class="text-xs">Đang tải danh sách đối tác...</p>
            </div>

            <div id="partnersEmpty" class="py-12 text-center text-slate-400 hidden">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xl mx-auto mb-2">
                    <i class="fa-regular fa-handshake"></i>
                </div>
                <div class="font-semibold text-slate-700 text-sm">Chưa có đối tác nào được liên kết</div>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">BM này chưa chia sẻ tài sản với đối tác nào hoặc đối tác chưa được cấp quyền.</p>
            </div>

            <div id="partnersContainer" class="space-y-3 hidden">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 flex-shrink-0">
            <span id="partnersSummaryCount">0 đối tác</span>
            <button type="button" onclick="closePartnersModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl transition">
                Đóng
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openAddBMModal() {
        document.getElementById('modalAddBM').classList.remove('hidden');
    }

    function closeAddBMModal() {
        document.getElementById('modalAddBM').classList.add('hidden');
    }

    function openEditBMModal(bmId, name, bmCode) {
        const form = document.getElementById('formEditBM');
        form.action = "{{ url('bms') }}/" + bmId;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_bm_id').value = bmCode;
        document.getElementById('modalEditBM').classList.remove('hidden');
    }

    function closeEditBMModal() {
        document.getElementById('modalEditBM').classList.add('hidden');
    }

    function closeTokenModal() {
        document.getElementById('modalTokenInspector').classList.add('hidden');
    }

    function checkTokenLive(bmId, bmName) {
        const modal = document.getElementById('modalTokenInspector');
        const loading = document.getElementById('tokenLoading');
        const result = document.getElementById('tokenResult');
        const statusBanner = document.getElementById('tokenStatusBanner');
        const errorBox = document.getElementById('tokenErrorBox');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        result.classList.add('hidden');
        errorBox.classList.add('hidden');

        fetch("{{ url('bms') }}/" + bmId + "/check-token", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            result.classList.remove('hidden');

            const info = data.token_info || {};
            const isValid = data.is_valid;

            if (isValid) {
                statusBanner.className = "p-4 rounded-xl flex items-center space-x-3 bg-emerald-50 border border-emerald-200 text-emerald-800";
                statusBanner.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm">Token Hoạt Động Tốt!</div>
                        <div class="text-xs text-emerald-700">Token đã được Meta xác thực hợp lệ và có quyền truy cập đầy đủ.</div>
                    </div>
                `;
            } else {
                statusBanner.className = "p-4 rounded-xl flex items-center space-x-3 bg-rose-50 border border-rose-200 text-rose-800";
                statusBanner.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm">Token Không Hợp Lệ Hoặc Hết Hạn!</div>
                        <div class="text-xs text-rose-700">Vui lòng cập nhật mã Access Token mới để tiếp tục quản lý quảng cáo.</div>
                    </div>
                `;
                if (info.error) {
                    errorBox.classList.remove('hidden');
                    document.getElementById('tokenErrorMessage').textContent = info.error;
                }
            }

            document.getElementById('tokenOwner').textContent = info.name || bmName;
            document.getElementById('tokenExpires').textContent = info.expires_at_text || 'Không xác định';
            document.getElementById('tokenType').textContent = info.type || 'SYSTEM_USER / USER';
            document.getElementById('tokenApp').textContent = (info.application || 'Meta Developer App') + (info.app_id ? ` (ID: ${info.app_id})` : '');

            // Scopes list
            const scopesContainer = document.getElementById('tokenScopes');
            scopesContainer.innerHTML = '';
            const scopes = info.scopes || [];
            if (scopes.length > 0) {
                scopes.forEach(sc => {
                    const badge = document.createElement('span');
                    badge.className = 'px-2 py-0.5 bg-blue-100 text-blue-700 font-mono text-[10px] rounded border border-blue-200 font-medium';
                    badge.textContent = sc;
                    scopesContainer.appendChild(badge);
                });
            } else {
                scopesContainer.innerHTML = '<span class="text-slate-400 italic">Không có scopes cụ thể</span>';
            }

            // Update status badge on row without reload
            const rowBadge = document.getElementById('status-badge-' + bmId);
            if (rowBadge) {
                if (isValid) {
                    rowBadge.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200"><span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Hoạt động</span>`;
                } else {
                    rowBadge.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200"><i class="fa-solid fa-circle-xmark mr-1"></i> Hết hạn</span>`;
                }
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            result.classList.remove('hidden');
            statusBanner.className = "p-4 rounded-xl flex items-center space-x-3 bg-rose-50 border border-rose-200 text-rose-800";
            statusBanner.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="font-bold text-sm">Lỗi Kiểm Tra!</div>
                    <div class="text-xs text-rose-700">${err.message || 'Không thể gửi yêu cầu đến máy chủ'}</div>
                </div>
            `;
        });
    }

    // ==========================================
    // QUẢN LÝ ĐỐI TÁC CỦA BUSINESS MANAGER
    // ==========================================
    let currentPartnerBmId = null;
    let currentPartnersData = [];

    function openPartnersModal(bmId) {
        currentPartnerBmId = bmId;
        const modal = document.getElementById('modalPartners');
        const loading = document.getElementById('partnersLoading');
        const empty = document.getElementById('partnersEmpty');
        const container = document.getElementById('partnersContainer');
        const filterInput = document.getElementById('filterPartnersInput');

        filterInput.value = '';
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        container.classList.add('hidden');

        fetch("{{ url('bms') }}/" + bmId + "/partners", {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            document.getElementById('modalPartnersBmName').textContent = data.bm_name || 'Quản lý Đối tác BM';
            document.getElementById('modalPartnersBmId').textContent = 'ID: ' + (data.bm_meta_id || 'N/A');
            
            const linkMeta = document.getElementById('linkMetaPartners');
            if (data.bm_meta_id) {
                linkMeta.href = `https://business.facebook.com/settings/partners?business_id=${data.bm_meta_id}`;
                linkMeta.classList.remove('hidden');
            } else {
                linkMeta.classList.add('hidden');
            }

            currentPartnersData = data.partners || [];
            renderPartnersList(currentPartnersData);
        })
        .catch(err => {
            loading.classList.add('hidden');
            showToast('Không thể tải danh sách đối tác: ' + err.message, 'error', 4000);
        });
    }

    function closePartnersModal() {
        document.getElementById('modalPartners').classList.add('hidden');
    }

    function renderPartnersList(partners) {
        const container = document.getElementById('partnersContainer');
        const empty = document.getElementById('partnersEmpty');
        const summary = document.getElementById('partnersSummaryCount');

        container.innerHTML = '';
        summary.textContent = `Tổng cộng ${partners.length} đối tác`;

        if (!partners || partners.length === 0) {
            empty.classList.remove('hidden');
            container.classList.add('hidden');
            return;
        }

        empty.classList.add('hidden');
        container.classList.remove('hidden');

        partners.forEach((p) => {
            const card = document.createElement('div');
            card.className = "p-4 rounded-xl border border-slate-200 bg-white hover:border-purple-300 hover:shadow-sm transition partner-card";
            card.dataset.name = (p.name || '').toLowerCase();
            card.dataset.id = (p.id || '').toLowerCase();

            let accountsHtml = '';
            if (p.accounts && p.accounts.length > 0) {
                accountsHtml = `
                    <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex flex-wrap gap-1.5 items-center">
                        <span class="text-[10px] text-slate-400 font-medium">Tài khoản được phân quyền:</span>
                        ${p.accounts.slice(0, 5).map(acc => `
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-100 text-slate-700 border border-slate-200" title="Quyền: ${acc.tasks ? acc.tasks.join(', ') : 'Chung'}">
                                ${acc.account_id}
                            </span>
                        `).join('')}
                        ${p.accounts.length > 5 ? `<span class="text-[10px] text-purple-600 font-semibold">+${p.accounts.length - 5} TK khác</span>` : ''}
                    </div>
                `;
            }

            card.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start space-x-3 min-w-0">
                        <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center font-bold text-sm flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-building-user"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 text-sm truncate">
                                ${p.name || 'Đối tác chưa đặt tên'}
                            </div>
                            <div class="flex items-center space-x-2 text-xs text-slate-500 font-mono mt-0.5">
                                <span>ID: ${p.id}</span>
                                <button type="button" onclick="copyToClipboard('${p.id}', 'Đã sao chép ID đối tác!')" class="text-slate-400 hover:text-blue-600" title="Sao chép ID">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <span class="text-[11px] font-semibold text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-full whitespace-nowrap">
                            ${p.accounts_count || 0} tài khoản QC
                        </span>
                    </div>
                </div>
                ${accountsHtml}
            `;
            container.appendChild(card);
        });
    }

    function filterPartnersList() {
        const query = (document.getElementById('filterPartnersInput').value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.partner-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const id = card.dataset.id || '';
            if (name.includes(query) || id.includes(query)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        document.getElementById('partnersSummaryCount').textContent = `Hiển thị ${visibleCount} / ${cards.length} đối tác`;
    }

    function syncPartnersFromMeta() {
        if (!currentPartnerBmId) return;
        const btn = document.getElementById('btnSyncPartners');
        const icon = document.getElementById('iconSyncPartners');
        
        btn.disabled = true;
        btn.classList.add('opacity-75');
        icon.classList.add('fa-spin');

        fetch("{{ url('bms') }}/" + currentPartnerBmId + "/sync-partners", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            icon.classList.remove('fa-spin');

            if (data.success) {
                currentPartnersData = data.partners || [];
                renderPartnersList(currentPartnersData);
                showToast(data.message || 'Đồng bộ đối tác thành công!', 'success', 3000);
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast('Lỗi: ' + (data.message || 'Không thể đồng bộ đối tác'), 'error', 4000);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            icon.classList.remove('fa-spin');
            showToast('Lỗi kết nối: ' + err.message, 'error', 4000);
        });
    }
    function handleDatePresetChange(select) {
        const box = document.getElementById('customDateRangeBox');
        if (select.value === 'custom') {
            box.classList.remove('hidden');
            box.classList.add('flex');
        } else {
            box.classList.add('hidden');
            box.classList.remove('flex');
            select.form.submit();
        }
    }

    // ==========================================
    // TẢI CHI TIÊU BM BẤT ĐỒNG BỘ (AJAX + HTTP::POOL)
    // ==========================================
    let isLoadingBmSpends = false;

    function loadBmSpends(forceRefresh = false) {
        if (isLoadingBmSpends) return;
        isLoadingBmSpends = true;

        const btn = document.getElementById('btnRefreshBmSpends');
        const icon = document.getElementById('iconRefreshBmSpends');
        const totalEl = document.getElementById('statsTotalSpent');
        const bmSpendEls = document.querySelectorAll('[data-bm-spend-id]');

        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('fa-spin');
        if (forceRefresh && totalEl) totalEl.classList.add('opacity-50');
        if (forceRefresh) {
            bmSpendEls.forEach(el => el.classList.add('opacity-50'));
        }

        const params = new URLSearchParams({
            date_preset: @json($datePreset ?? 'last_30d'),
            since: @json($since ?? ''),
            until: @json($until ?? '')
        });
        if (forceRefresh) {
            params.set('refresh', '1');
        }

        fetch("{{ route('bms.spendSummary') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (totalEl && data.total_spent_formatted) {
                totalEl.textContent = data.total_spent_formatted;
            }

            const bmSpends = data.bm_spends || {};
            bmSpendEls.forEach(el => {
                const bmId = el.getAttribute('data-bm-spend-id');
                const entry = bmSpends[bmId];
                if (!entry) return;

                el.textContent = entry.formatted || '0 đ';
                if (entry.has_error) {
                    const warnIcon = document.createElement('i');
                    warnIcon.className = 'fa-solid fa-triangle-exclamation text-amber-500 ml-1 text-[10px]';
                    warnIcon.title = 'Không thể lấy đầy đủ dữ liệu từ Meta API (token hết hạn hoặc quá thời gian chờ)';
                    el.appendChild(warnIcon);
                }
            });

            if (forceRefresh) {
                if (data.has_errors) {
                    showToast('Đã làm mới chi tiêu, nhưng một số BM không phản hồi từ Meta.', 'warning', 3500);
                } else {
                    showToast('Đã cập nhật chi tiêu mới nhất từ Meta!', 'success', 2500);
                }
            }
        })
        .catch(err => {
            if (totalEl && totalEl.textContent.includes('Đang tải')) {
                totalEl.textContent = '0 đ';
            }
            bmSpendEls.forEach(el => {
                if (el.textContent.includes('Đang tải')) {
                    el.textContent = '—';
                }
            });
            if (forceRefresh) {
                showToast('Không thể cập nhật chi tiêu: ' + err.message, 'error', 3500);
            }
        })
        .finally(() => {
            isLoadingBmSpends = false;
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('fa-spin');
            if (totalEl) totalEl.classList.remove('opacity-50');
            bmSpendEls.forEach(el => el.classList.remove('opacity-50'));
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const shouldAutoLoadSpends = @json(($needsApiSpend ?? false) && !($spendReady ?? true));
        if (shouldAutoLoadSpends) {
            loadBmSpends(false);
        }
    });
</script>
@endpush
@endsection
