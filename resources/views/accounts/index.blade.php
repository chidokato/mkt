@extends('layout')

@section('title', 'Quản lý Tài khoản Quảng cáo (TKQC) - Meta Ads Hub')

@section('header_actions')
    @if(isset($latestUpdate))
        <div class="inline-flex items-center px-3 py-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium whitespace-nowrap"
             title="Thời điểm cập nhật dữ liệu gần nhất: {{ $latestUpdate->format('d/m/Y H:i:s') }}">
            <i class="fa-regular fa-clock text-slate-400 mr-1.5 text-xs"></i>
            <span class="hidden sm:inline text-slate-500">Cập nhật:</span>
            <span class="font-semibold text-slate-800 ml-1">{{ $latestUpdate->diffForHumans() }}</span>
        </div>
    @endif

    <form action="{{ route('bms.syncAllFacebook') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                title="Quét lại toàn bộ các BM và tài khoản từ Facebook"
                class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-arrows-rotate text-xs mr-1.5"></i> Đồng bộ tất cả
        </button>
    </form>

    <a href="{{ route('auth.facebook') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-[#1877F2] hover:bg-[#166fe5] text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-brands fa-facebook text-xs mr-1.5"></i> Kết nối FB
    </a>

    <a href="{{ route('bms.index') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-briefcase text-xs mr-1.5 text-blue-400"></i> Quản lý BM
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Summary Stat Cards (Bấm nhanh để lọc trạng thái) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Tổng TKQC -->
        <a href="{{ route('accounts.index') }}" 
           class="bg-white p-4 rounded-xl border {{ empty($statusFilter) && empty($sourceFilter) ? 'border-emerald-500 ring-2 ring-emerald-200 bg-emerald-50/10' : 'border-slate-200 hover:border-emerald-300' }} shadow-sm transition group block">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Tổng TKQC</span>
                <i class="fa-solid fa-rectangle-ad text-emerald-500 text-sm"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_accounts'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1 truncate" title="Tổng {{ $stats['total_accounts'] }} tài khoản thực tế ({{ $stats['owned_accounts'] }} BM sở hữu • {{ $stats['client_accounts'] }} được share • {{ $stats['personal_accounts'] }} cá nhân)">
                <span class="text-blue-600 font-medium">{{ $stats['owned_accounts'] }} BM</span> &bull; 
                <span class="text-purple-600 font-medium">{{ $stats['client_accounts'] }} share</span>
            </div>
        </a>

        <!-- TK Active -->
        <a href="{{ route('accounts.index', array_merge(request()->except('page'), ['account_status' => 1])) }}" 
           class="bg-white p-4 rounded-xl border {{ ($statusFilter ?? '') === 1 || ($statusFilter ?? '') === '1' ? 'border-emerald-500 ring-2 ring-emerald-200 bg-emerald-50/20' : 'border-slate-200 hover:border-emerald-400' }} shadow-sm transition group block">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold {{ ($statusFilter ?? '') === 1 || ($statusFilter ?? '') === '1' ? 'text-emerald-700' : 'text-slate-500' }} uppercase tracking-wider group-hover:text-emerald-600 transition">TK Active</span>
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['active_accounts'] }}</div>
            <div class="text-[11px] text-emerald-600 mt-1 font-medium">Sẵn sàng chạy Ads</div>
        </a>

        <!-- TK Vô hiệu hóa (Die) -->
        <a href="{{ route('accounts.index', array_merge(request()->except('page'), ['account_status' => 2])) }}" 
           class="bg-white p-4 rounded-xl border {{ ($statusFilter ?? '') === 2 || ($statusFilter ?? '') === '2' ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/20' : 'border-slate-200 hover:border-rose-400' }} shadow-sm transition group block">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold {{ ($statusFilter ?? '') === 2 || ($statusFilter ?? '') === '2' ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider group-hover:text-rose-600 transition">TK Vô hiệu</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
            </div>
            <div class="text-2xl font-bold text-rose-600">{{ $stats['disabled_accounts'] }}</div>
            <div class="text-[11px] text-rose-500 mt-1 font-medium">Bị cấm (Die)</div>
        </a>

        <!-- TK Nợ tiền -->
        <a href="{{ route('accounts.index', array_merge(request()->except('page'), ['account_status' => 3])) }}" 
           class="bg-white p-4 rounded-xl border {{ ($statusFilter ?? '') === 3 || ($statusFilter ?? '') === '3' ? 'border-amber-500 ring-2 ring-amber-200 bg-amber-50/20' : 'border-slate-200 hover:border-amber-400' }} shadow-sm transition group block">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold {{ ($statusFilter ?? '') === 3 || ($statusFilter ?? '') === '3' ? 'text-amber-700' : 'text-slate-500' }} uppercase tracking-wider group-hover:text-amber-600 transition">TK Nợ tiền</span>
                <i class="fa-solid fa-file-invoice-dollar text-amber-500 text-sm"></i>
            </div>
            <div class="text-2xl font-bold text-amber-600">{{ $stats['unsettled_accounts'] }}</div>
            <div class="text-[11px] text-amber-600 mt-1 font-medium">Chờ thanh toán</div>
        </a>

        <!-- Tổng Đã Tiêu -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Đã Tiêu</span>
                <i class="fa-solid fa-money-bill-wave text-indigo-500 text-sm"></i>
            </div>
            <div class="text-xl font-bold text-indigo-600 truncate">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
            <div class="text-[11px] text-slate-500 mt-1 truncate">Chi tiêu tích lũy</div>
        </div>

        <!-- Tổng Dư Nợ -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Dư Nợ</span>
                <i class="fa-solid fa-credit-card text-rose-500 text-sm"></i>
            </div>
            <div class="text-xl font-bold {{ $stats['total_balance'] > 0 ? 'text-rose-600' : 'text-slate-700' }} truncate">
                {{ number_format($stats['total_balance'], 0, ',', '.') }} đ
            </div>
            <div class="text-[11px] text-slate-500 mt-1 truncate">Số dư nợ đọng</div>
        </div>
    </div>

    <!-- Toolbar: Search & Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <!-- Quick source / status tabs -->
        <div class="flex flex-wrap items-center gap-1.5 pb-2 border-b border-slate-100 text-xs">
            <span class="text-slate-400 font-semibold mr-1 text-[11px] uppercase tracking-wider">Nhanh:</span>
            
            <a href="{{ route('accounts.index', request()->except('source', 'account_status', 'page')) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ empty($sourceFilter) && empty($statusFilter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tất cả ({{ $stats['total_accounts'] }})
            </a>

            <a href="{{ route('accounts.index', array_merge(request()->except('page', 'source'), ['account_status' => 1])) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($statusFilter ?? '') === 1 || ($statusFilter ?? '') === '1' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                <i class="fa-solid fa-circle-check text-[10px] mr-1"></i> Active ({{ $stats['active_accounts'] }})
            </a>

            <a href="{{ route('accounts.index', array_merge(request()->except('page', 'source'), ['account_status' => 2])) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($statusFilter ?? '') === 2 || ($statusFilter ?? '') === '2' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                <i class="fa-solid fa-triangle-exclamation text-[10px] mr-1"></i> Vô hiệu ({{ $stats['disabled_accounts'] }})
            </a>

            <a href="{{ route('accounts.index', array_merge(request()->except('page', 'source'), ['account_status' => 3])) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($statusFilter ?? '') === 3 || ($statusFilter ?? '') === '3' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }}">
                <i class="fa-solid fa-file-invoice-dollar text-[10px] mr-1"></i> Nợ tiền ({{ $stats['unsettled_accounts'] }})
            </a>

            <span class="text-slate-300 mx-1">|</span>

            <a href="{{ route('accounts.index', array_merge(request()->except('page', 'account_status'), ['source' => 'owned'])) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($sourceFilter ?? '') === 'owned' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }}">
                <i class="fa-solid fa-building text-[10px] mr-1"></i> Của BM ({{ $stats['owned_accounts'] }})
            </a>

            <a href="{{ route('accounts.index', array_merge(request()->except('page', 'account_status'), ['source' => 'client'])) }}" 
               class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($sourceFilter ?? '') === 'client' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' }}">
                <i class="fa-solid fa-share-nodes text-[10px] mr-1"></i> Được share ({{ $stats['client_accounts'] }})
            </a>

            @if($stats['personal_accounts'] > 0)
                <a href="{{ route('accounts.index', array_merge(request()->except('page', 'account_status'), ['source' => 'personal'])) }}" 
                   class="px-2.5 py-1 rounded-lg font-semibold transition {{ ($sourceFilter ?? '') === 'personal' ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' }}">
                    <i class="fa-solid fa-id-badge text-[10px] mr-1"></i> Cá nhân ({{ $stats['personal_accounts'] }})
                </a>
            @endif
        </div>

        <!-- Filter inputs form -->
        <form action="{{ route('accounts.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
            <!-- Search -->
            <div class="relative flex-1 min-w-[220px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Tìm theo Tên tài khoản, ID hoặc Thẻ..." 
                       class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>

            <!-- Filter BM -->
            <div class="w-full sm:w-48">
                <select name="bm_id" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-700">
                    <option value="">-- Tất cả BM --</option>
                    <optgroup label="Business Managers">
                        @foreach($userBms as $bm)
                            <option value="{{ $bm->id }}" {{ ($bmFilter ?? '') == $bm->id ? 'selected' : '' }}>
                                {{ $bm->name }} ({{ $bm->ad_accounts_count }})
                            </option>
                        @endforeach
                    </optgroup>
                    @if(isset($personalBm))
                        <optgroup label="Tài sản khác">
                            <option value="personal" {{ ($bmFilter ?? '') === 'personal' ? 'selected' : '' }}>
                                TK Cá nhân ({{ $personalBm->ad_accounts_count }})
                            </option>
                        </optgroup>
                    @endif
                </select>
            </div>

            <!-- Filter Status -->
            <div class="w-full sm:w-40">
                <select name="account_status" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-700">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" {{ ($statusFilter ?? '') === '1' || ($statusFilter ?? '') === 1 ? 'selected' : '' }}>Hoạt động</option>
                    <option value="2" {{ ($statusFilter ?? '') === '2' || ($statusFilter ?? '') === 2 ? 'selected' : '' }}>Vô hiệu hóa (Die)</option>
                    <option value="3" {{ ($statusFilter ?? '') === '3' || ($statusFilter ?? '') === 3 ? 'selected' : '' }}>Nợ tiền</option>
                </select>
            </div>

            <!-- Filter Source -->
            <div class="w-full sm:w-36">
                <select name="source" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-700">
                    <option value="">-- Tất cả nguồn --</option>
                    <option value="owned" {{ ($sourceFilter ?? '') === 'owned' ? 'selected' : '' }}>Của BM</option>
                    <option value="client" {{ ($sourceFilter ?? '') === 'client' ? 'selected' : '' }}>Được share</option>
                    <option value="personal" {{ ($sourceFilter ?? '') === 'personal' ? 'selected' : '' }}>TK Cá nhân</option>
                </select>
            </div>

            <!-- Sort -->
            <div class="w-full sm:w-40">
                <select name="sort" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-700">
                    <option value="latest" {{ ($sort ?? 'latest') === 'latest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="spent_desc" {{ ($sort ?? '') === 'spent_desc' ? 'selected' : '' }}>Chi tiêu cao &rarr; thấp</option>
                    <option value="spent_asc" {{ ($sort ?? '') === 'spent_asc' ? 'selected' : '' }}>Chi tiêu thấp &rarr; cao</option>
                    <option value="balance_desc" {{ ($sort ?? '') === 'balance_desc' ? 'selected' : '' }}>Số dư nợ cao nhất</option>
                    <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Tên A &rarr; Z</option>
                </select>
            </div>

            <!-- Per page -->
            <div class="w-24">
                <select name="per_page" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium text-slate-700">
                    <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20 / tr</option>
                    <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50 / tr</option>
                    <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100 / tr</option>
                    <option value="200" {{ ($perPage ?? 20) == 200 ? 'selected' : '' }}>200 / tr</option>
                </select>
            </div>

            <button type="submit" 
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                <i class="fa-solid fa-filter mr-1"></i> Lọc
            </button>

            @if(request('search') || request('bm_id') || (request('account_status') !== null && request('account_status') !== '') || request('source') || request('sort'))
                <a href="{{ route('accounts.index') }}" 
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-xl transition"
                   title="Xóa tất cả bộ lọc">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Ad Accounts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-rectangle-ad text-emerald-600 mr-2"></i> Danh sách Tài khoản Quảng cáo
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hiển thị <strong>{{ $accounts->firstItem() ?? 0 }} - {{ $accounts->lastItem() ?? 0 }}</strong> trên tổng số <strong>{{ $accounts->total() }}</strong> tài khoản quảng cáo.
                </p>
            </div>
        </div>

        @if($accounts->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                    <i class="fa-solid fa-rectangle-ad"></i>
                </div>
                <div class="text-sm font-semibold text-slate-700">Không tìm thấy tài khoản quảng cáo nào</div>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto mb-4">
                    Thử thay đổi bộ lọc tìm kiếm hoặc bấm nút "Đồng bộ tất cả" để cập nhật tài khoản từ Facebook về hệ thống.
                </p>
                <form action="{{ route('bms.syncAllFacebook') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ ngay
                    </button>
                </form>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3 text-left">Tên tài khoản</th>
                            <th class="px-4 py-3 text-left">Ad Account ID</th>
                            <th class="px-4 py-3 text-left">Thuộc BM / Nguồn</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Trạng thái</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Thẻ thanh toán</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Đã chi tiêu</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Số dư nợ</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($accounts as $acc)
                            @php
                                $hasDebt = ($acc->account_status == 3 || $acc->balance > 0);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Tên tài khoản -->
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                       class="font-bold text-blue-600 hover:text-blue-800 hover:underline transition flex items-center gap-1.5 truncate max-w-[280px] group" 
                                       title="Bấm để xem chi tiết tài khoản & quản lý chiến dịch: {{ $acc->name }}">
                                        <span class="truncate">{{ $acc->name }}</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400 group-hover:text-blue-600 opacity-60 group-hover:opacity-100 flex-shrink-0 transition"></i>
                                    </a>
                                </td>

                                <!-- Ad Account ID -->
                                <td class="px-4 py-3 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    <div class="flex items-center space-x-1">
                                        <span>{{ $acc->ad_account_id }}</span>
                                        <button type="button" 
                                                onclick="copyToClipboard('{{ $acc->ad_account_id }}', 'Đã sao chép ID tài khoản QC!')" 
                                                class="text-slate-400 hover:text-blue-600 p-0.5 rounded transition" 
                                                title="Sao chép ID">
                                            <i class="fa-regular fa-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Thuộc BM / Nguồn -->
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        @if($acc->businessManager && !$acc->businessManager->is_personal)
                                            <a href="{{ route('bms.show', $acc->business_manager_id) }}" 
                                               class="text-xs font-semibold text-slate-800 hover:text-blue-600 transition flex items-center gap-1">
                                                <i class="fa-solid fa-building text-[10px] text-slate-400"></i>
                                                <span class="truncate max-w-[170px]">{{ $acc->businessManager->name }}</span>
                                            </a>
                                        @elseif($acc->businessManager)
                                            <span class="text-xs font-semibold text-indigo-700 flex items-center gap-1">
                                                <i class="fa-solid fa-id-badge text-[10px] text-indigo-500"></i>
                                                <span>TK Cá nhân</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-1">
                                            @if($acc->source_type === 'owned')
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    BM sở hữu
                                                </span>
                                            @elseif($acc->source_type === 'client')
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                    Được share
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    Cá nhân
                                                </span>
                                            @endif

                                            {{-- Hiển thị các BM khác cùng liên kết / share tới tài khoản này --}}
                                            @if(!empty($otherBmsByActId[$acc->ad_account_id]))
                                                @foreach($otherBmsByActId[$acc->ad_account_id] as $otherBm)
                                                    @if($otherBm['bm_id'] !== $acc->business_manager_id)
                                                        <a href="{{ route('bms.show', $otherBm['bm_id']) }}"
                                                           class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 hover:bg-purple-50 text-slate-600 hover:text-purple-700 border border-slate-200 hover:border-purple-300 transition"
                                                           title="Tài khoản này cũng được share tới BM: {{ $otherBm['bm_name'] }}">
                                                            <i class="fa-solid fa-share-nodes text-[9px] mr-1 text-purple-500"></i>
                                                            <span class="truncate max-w-[130px]">{{ $otherBm['bm_name'] }}</span>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($acc->account_status == 1)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full animate-pulse"></span> Hoạt động
                                        </span>
                                    @elseif($acc->account_status == 2)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-rose-500 rounded-full"></span> Vô hiệu hóa
                                        </span>
                                    @elseif($acc->account_status == 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full"></span> Nợ tiền
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700">
                                            {{ $acc->status_label }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Thẻ thanh toán -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($acc->card_display)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="Thẻ thanh toán">
                                            <i class="fa-regular fa-credit-card mr-1 text-slate-400"></i>
                                            <span>{{ $acc->card_display }}</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    @endif
                                </td>

                                <!-- Đã chi tiêu -->
                                <td class="px-4 py-3 text-right font-semibold text-slate-800 whitespace-nowrap">
                                    <span class="text-indigo-600 font-bold">{{ number_format($acc->amount_spent, 0, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono ml-0.5">{{ $acc->currency }}</span>
                                </td>

                                <!-- Số dư nợ -->
                                <td class="px-4 py-3 text-right font-medium whitespace-nowrap">
                                    @if($hasDebt)
                                        <span class="text-rose-600 font-bold bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                            {{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">
                                            {{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Thao tác -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1.5">
                                        <!-- Quản lý Chiến dịch -->
                                        <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                           class="inline-flex items-center px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[11px] font-semibold shadow-2xs transition"
                                           title="Xem danh sách chiến dịch & bật/tắt ads">
                                            <i class="fa-solid fa-list-check mr-1"></i> Chiến dịch
                                        </a>

                                        <!-- Billing Hub FB -->
                                        <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-lg text-[11px] font-semibold transition"
                                           title="Mở Trung tâm thanh toán trên Meta (Billing Hub)">
                                            <i class="fa-solid fa-credit-card mr-1 text-amber-600"></i> Thanh toán
                                        </a>

                                        <!-- Ads Manager FB -->
                                        <a href="{{ $acc->ads_manager_url }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 rounded-lg text-[11px] font-medium transition"
                                           title="Mở Trình quản lý quảng cáo Meta trong tab mới">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($accounts->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
                    {{ $accounts->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
