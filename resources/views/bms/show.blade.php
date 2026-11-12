@extends('layout')

@section('title', 'Chi tiết BM: ' . $bm->name . ' - Meta Ads Hub')

@section('header_actions')
    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium whitespace-nowrap"
         title="Lần cập nhật gần nhất: {{ $bm->updated_at->format('d/m/Y H:i:s') }}">
        <i class="fa-regular fa-clock text-slate-400 mr-1.5 text-xs"></i>
        <span class="hidden sm:inline text-slate-500">Cập nhật:</span>
        <span class="font-semibold text-slate-800 ml-1">{{ $bm->updated_at->diffForHumans() }}</span>
    </div>

    <!-- Sync Button -->
    <form action="{{ route('bms.sync', $bm->id) }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="inline-flex items-center px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition whitespace-nowrap">
            <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ
        </button>
    </form>

    <!-- Check Token Live -->
    <button type="button" 
            onclick="checkTokenLive({{ $bm->id }}, '{{ addslashes($bm->name) }}')"
            class="inline-flex items-center px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 transition whitespace-nowrap">
        <i class="fa-solid fa-stethoscope mr-1.5"></i> Check Token
    </button>

    <!-- Meta Billing Hub của BM -->
    <a href="{{ $bm->billing_hub_url }}" target="_blank" rel="noopener noreferrer" 
       class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-300 transition whitespace-nowrap"
       title="Mở Trung tâm Lập hóa đơn và thanh toán trên Meta Business">
        <i class="fa-solid fa-credit-card mr-1.5 text-amber-600"></i> Thanh toán FB
    </a>

    <!-- Edit BM -->
    <button type="button" 
            onclick="openEditBMModal({{ $bm->id }}, '{{ addslashes($bm->name) }}', '{{ addslashes($bm->bm_id ?? '') }}')"
            class="inline-flex items-center px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition whitespace-nowrap">
        <i class="fa-solid fa-pen-to-square mr-1.5 text-slate-500"></i> Sửa BM
    </button>

    <!-- Delete BM -->
    <form action="{{ route('bms.destroy', $bm->id) }}" method="POST" class="inline"
          onsubmit="return confirm('Bạn có chắc chắn muốn xóa BM này? Mọi tài khoản quảng cáo thuộc BM cũng sẽ bị gỡ bỏ.');">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-lg border border-rose-200 transition whitespace-nowrap">
            <i class="fa-solid fa-trash-can mr-1.5"></i> Xóa BM
        </button>
    </form>
@endsection

@section('content')
<div class="space-y-3">

    <!-- BM Stat Cards (Gọn gàng, tối ưu diện tích, bấm nhanh để lọc trạng thái) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
        <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('account_status', 'page'))) }}" 
           class="bg-white p-3 rounded-xl border {{ empty($accountStatus) ? 'border-slate-300 ring-1 ring-slate-200' : 'border-slate-200/90' }} hover:border-blue-300 transition shadow-xs group block"
           title="Bấm để xem tất cả tài khoản">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-blue-600 transition">Tổng TKQC</div>
            <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $stats['total_accounts'] }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate">
                <span class="text-blue-600 font-semibold">{{ $stats['owned_accounts'] ?? 0 }} BM</span> &bull; 
                <span class="text-purple-600 font-semibold">{{ $stats['client_accounts'] ?? 0 }} share</span>
            </div>
        </a>

        <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('page'), ['account_status' => 1])) }}" 
           class="bg-white p-3 rounded-xl border {{ ($accountStatus ?? '') == '1' ? 'border-emerald-500 ring-2 ring-emerald-200 bg-emerald-50/20' : 'border-slate-200/90' }} hover:border-emerald-400 transition shadow-xs group block"
           title="Bấm để lọc tài khoản đang Hoạt động">
            <div class="text-[10px] font-bold {{ ($accountStatus ?? '') == '1' ? 'text-emerald-700' : 'text-slate-400' }} uppercase tracking-wider group-hover:text-emerald-600 transition">Tài khoản Active</div>
            <div class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['active_accounts'] }}</div>
            <div class="text-[10px] text-emerald-600 mt-0.5 font-medium">Sẵn sàng chạy Ads</div>
        </a>

        <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('page'), ['account_status' => 2])) }}" 
           class="bg-white p-3 rounded-xl border {{ ($accountStatus ?? '') == '2' ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/20' : 'border-slate-200/90' }} hover:border-rose-400 transition shadow-xs group block"
           title="Bấm để lọc tài khoản Vô hiệu hóa (Die)">
            <div class="text-[10px] font-bold {{ ($accountStatus ?? '') == '2' ? 'text-rose-700' : 'text-slate-400' }} uppercase tracking-wider group-hover:text-rose-600 transition">Tài khoản Vô hiệu</div>
            <div class="text-xl font-extrabold text-rose-600 mt-0.5">{{ $stats['disabled_accounts'] }}</div>
            <div class="text-[10px] text-rose-500 mt-0.5 font-medium">Bị cấm (Die)</div>
        </a>

        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tổng Đã Tiêu</span>
                <i class="fa-solid fa-money-bill-wave text-indigo-500 text-xs"></i>
            </div>
            <div class="text-xl font-extrabold text-indigo-600 mt-0.5 truncate">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
            <div class="text-[10px] text-indigo-600 font-semibold mt-0.5 truncate" title="Thời gian lọc: {{ $dateLabel }}">
                <i class="fa-regular fa-calendar-check mr-1"></i> {{ $dateLabel }}
            </div>
        </div>

        <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('page'), ['account_status' => 3])) }}" 
           class="bg-white p-3 rounded-xl border {{ ($accountStatus ?? '') == '3' ? 'border-amber-500 ring-2 ring-amber-200 bg-amber-50/20' : 'border-slate-200/90' }} hover:border-amber-400 transition shadow-xs group block"
           title="Bấm để lọc tài khoản Nợ tiền">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold {{ ($accountStatus ?? '') == '3' ? 'text-amber-700' : 'text-slate-400' }} uppercase tracking-wider group-hover:text-amber-600 transition">Số TK Nợ Tiền</span>
                <i class="fa-solid fa-file-invoice-dollar text-amber-500 text-xs"></i>
            </div>
            <div class="text-xl font-extrabold {{ ($stats['unsettled_accounts'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-700' }} mt-0.5 truncate">
                {{ $stats['unsettled_accounts'] ?? 0 }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate" title="Tổng dư nợ Meta: {{ number_format($stats['total_balance'], 0, ',', '.') }} đ">
                Nợ: <span class="font-semibold {{ $stats['total_balance'] > 0 ? 'text-amber-600' : 'text-slate-600' }}">{{ number_format($stats['total_balance'], 0, ',', '.') }} đ</span>
            </div>
        </a>
    </div>

    <!-- Container Card chung cho Tabs & Nội dung: Liền mạch, hiện đại -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- 3 Tabs Điều hướng: TKQC, Đối tác, Pages -->
        <div class="flex items-center space-x-1 border-b border-slate-200 bg-slate-50/70 px-4 pt-1.5">
            <button type="button" 
                    onclick="switchBmTab('accounts')" 
                    id="tabBtn-accounts"
                    class="flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-blue-600 text-blue-600 transition">
                <i class="fa-solid fa-rectangle-ad text-sm"></i>
                <span>Tài khoản QC</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-blue-100 text-blue-700">
                    {{ $bm->adAccounts->count() }}
                </span>
            </button>

            <button type="button" 
                    onclick="switchBmTab('partners')" 
                    id="tabBtn-partners"
                    class="flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition">
                <i class="fa-solid fa-handshake text-sm text-purple-600"></i>
                <span>Đối tác</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-700">
                    {{ $stats['partners_count'] ?? count($partners ?? []) }}
                </span>
            </button>

            <button type="button" 
                    onclick="switchBmTab('pages')" 
                    id="tabBtn-pages"
                    class="flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition">
                <i class="fa-solid fa-flag text-sm text-sky-600"></i>
                <span>Pages</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-sky-100 text-sky-700">
                    {{ $stats['pages_count'] ?? $bmPages->count() }}
                </span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: DANH SÁCH TÀI KHOẢN QUẢNG CÁO (TKQC)     -->
        <!-- ============================================== -->
        <div id="tabContent-accounts" class="space-y-0">
            <!-- Toolbar Lọc & Tìm kiếm Gọn nhẹ -->
            <div class="p-2.5 sm:px-4 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                <!-- Filter pills: Tất cả / TK của BM / TK được share -->
                <div class="flex items-center space-x-1.5 flex-wrap">
                    <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('source', 'page'))) }}" 
                       class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition {{ empty(request('source')) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>Tất cả</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ empty(request('source')) ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-700' }}">{{ $stats['total_accounts'] }}</span>
                    </a>

                    <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('page'), ['source' => 'owned'])) }}" 
                       class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition {{ request('source') === 'owned' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }}">
                        <i class="fa-solid fa-building text-[10px] mr-1"></i>
                        <span>TK của BM</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'owned' ? 'bg-blue-800 text-blue-100' : 'bg-blue-200 text-blue-800' }}">{{ $stats['owned_accounts'] ?? 0 }}</span>
                    </a>

                    <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->except('page'), ['source' => 'client'])) }}" 
                       class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition {{ request('source') === 'client' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200' }}">
                        <i class="fa-solid fa-share-nodes text-[10px] mr-1"></i>
                        <span>TK được share</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] {{ request('source') === 'client' ? 'bg-purple-800 text-purple-100' : 'bg-purple-200 text-purple-800' }}">{{ $stats['client_accounts'] ?? 0 }}</span>
                    </a>
                </div>

                <!-- Form Search & Date Filter & Per Page -->
                <form action="{{ route('bms.show', $bm->id) }}" method="GET" class="flex flex-wrap items-center gap-2">
                    @if(request('source'))
                        <input type="hidden" name="source" value="{{ request('source') }}">
                    @endif

                    <!-- Date/Time Filter for Spend -->
                    <div class="relative">
                        <select name="date_preset" id="bmShowDatePreset" onchange="handleDatePresetChange(this)" 
                                class="pl-7 pr-2.5 py-1 border border-slate-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-indigo-700">
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
                        <i class="fa-regular fa-calendar-days absolute left-2 top-2 text-indigo-500 text-xs pointer-events-none"></i>
                    </div>

                    <!-- Custom date range inputs -->
                    <div id="customDateRangeBox" class="{{ ($datePreset ?? '') === 'custom' ? 'flex' : 'hidden' }} items-center gap-1">
                        <input type="date" name="since" value="{{ $since ?? '' }}" class="px-2 py-1 border border-slate-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                        <span class="text-xs text-slate-400">-</span>
                        <input type="date" name="until" value="{{ $until ?? '' }}" class="px-2 py-1 border border-slate-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <!-- Filter by Account Status -->
                    <select name="account_status" onchange="this.form.submit()" 
                            class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-700" title="Lọc theo trạng thái tài khoản">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="1" {{ ($accountStatus ?? '') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="2" {{ ($accountStatus ?? '') == '2' ? 'selected' : '' }}>Vô hiệu hóa (Die)</option>
                        <option value="3" {{ ($accountStatus ?? '') == '3' ? 'selected' : '' }}>Nợ tiền</option>
                    </select>

                    <!-- Search Input -->
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Lọc tên hoặc ID..." 
                               class="pl-7 pr-2.5 py-1 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 w-36 sm:w-44">
                    </div>

                    <!-- Per page dropdown: 20, 50, 100, 200 -->
                    <select name="per_page" onchange="this.form.submit()" 
                            class="px-2 py-1 border border-slate-300 rounded-lg text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-600" title="Số lượng hiển thị mỗi trang">
                        <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20 / trang</option>
                        <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50 / trang</option>
                        <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100 / trang</option>
                        <option value="200" {{ ($perPage ?? 20) == 200 ? 'selected' : '' }}>200 / trang</option>
                    </select>

                    <button type="submit" class="px-3 py-1 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-900 transition whitespace-nowrap">
                        Lọc
                    </button>
                    @if(request('search') || (request('account_status') !== null && request('account_status') !== '') || (($datePreset ?? 'last_30d') !== 'last_30d') || request('since') || (request('per_page') && request('per_page') != 20))
                        <a href="{{ route('bms.show', array_merge(['bm' => $bm->id], request()->only('source'))) }}" class="p-1 text-slate-400 hover:text-slate-600 text-xs" title="Xóa lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </form>
            </div>

            @if($accounts->isEmpty())
                <div class="p-10 text-center text-slate-500">
                    <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-2 text-lg">
                        <i class="fa-solid fa-rectangle-ad"></i>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">Chưa có tài khoản quảng cáo nào</div>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto mb-3">
                        Bấm nút "Đồng bộ" ở trên để tải toàn bộ tài khoản quảng cáo từ Meta Business Suite về hệ thống.
                    </p>
                    <form action="{{ route('bms.sync', $bm->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Đồng bộ tài khoản ngay
                        </button>
                    </form>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Tên tài khoản</th>
                                <th class="px-3.5 py-2.5 text-left">Ad Account ID</th>
                                <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Phân loại</th>
                                <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Trạng thái</th>
                                <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Thanh toán</th>
                                <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Đã chi tiêu ({{ $dateLabel }})</th>
                                <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Số dư nợ</th>
                                <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Giới hạn tiêu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($accounts as $acc)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-3.5 py-2 font-semibold text-slate-800">
                                        <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                           class="font-bold text-blue-600 hover:text-blue-800 hover:underline transition flex items-center gap-1.5 truncate max-w-[260px] group" 
                                           title="Bấm để xem chi tiết tài khoản, chiến dịch & đối tác: {{ $acc->name }}">
                                            <span class="truncate">{{ $acc->name }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px] text-blue-400 group-hover:text-blue-600 opacity-60 group-hover:opacity-100 flex-shrink-0 transition"></i>
                                        </a>
                                    </td>
                                    <td class="px-3.5 py-2 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <span>{{ $acc->ad_account_id }}</span>
                                            <button onclick="copyToClipboard('{{ $acc->ad_account_id }}', 'Đã sao chép ID tài khoản QC!')" 
                                                    class="text-slate-400 hover:text-blue-600" title="Sao chép">
                                                <i class="fa-regular fa-copy text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        @if($acc->source_type === 'client')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700 border border-purple-200" title="{{ $acc->owner_business_name ? 'Từ BM: '.$acc->owner_business_name : 'Được share' }}">
                                                <i class="fa-solid fa-share-nodes text-[9px] mr-1"></i>Được share
                                            </span>
                                        @elseif($acc->source_type === 'personal')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                                <i class="fa-solid fa-user text-[9px] mr-1"></i>Cá nhân
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-building text-[9px] mr-1"></i>BM
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        @if($acc->account_status == 1)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full"></span> Hoạt động
                                            </span>
                                        @elseif($acc->account_status == 2)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 mr-1 bg-rose-500 rounded-full"></span> Vô hiệu hóa
                                            </span>
                                        @elseif($acc->account_status == 3)
                                            <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 transition group shadow-2xs" 
                                               title="Tài khoản đang nợ tiền - Bấm để chuyển tới trang thanh toán Facebook (Billing Hub)">
                                                <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full animate-pulse"></span>
                                                <span>Nợ tiền</span>
                                                <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[9px] text-amber-600 group-hover:translate-x-0.5 transition-transform"></i>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ $acc->status_label }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        @if($acc->payment_method_display || $acc->card_last_four)
                                            <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-blue-50 text-slate-800 hover:text-blue-700 border border-slate-200 hover:border-blue-300 font-mono shadow-2xs transition group"
                                               title="Thẻ thanh toán: {{ $acc->payment_method_display ?? ('*'.$acc->card_last_four) }} - Bấm để mở Billing Hub trên Facebook">
                                                <i class="fa-regular fa-credit-card text-blue-600 mr-1.5 text-xs group-hover:scale-110 transition-transform"></i>
                                                <span>{{ $acc->payment_method_display ?? ('*' . $acc->card_last_four) }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs italic" title="Chưa gắn thẻ thanh toán">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-semibold text-slate-900 whitespace-nowrap">
                                        {{ number_format($acc->filtered_spend ?? $acc->amount_spent, 0, ',', '.') }} {{ $acc->currency }}
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-medium whitespace-nowrap {{ $acc->account_status == 3 ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">
                                        @if($acc->account_status == 3)
                                            <div class="inline-flex items-center justify-end space-x-1">
                                                <span>{{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}</span>
                                                <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                                   class="text-amber-600 hover:text-amber-800 p-0.5 hover:scale-110 transition inline-flex items-center" 
                                                   title="Thanh toán nợ trên Facebook">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-slate-400">0 {{ $acc->currency }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-right text-slate-500 whitespace-nowrap">
                                        @if($acc->spend_cap)
                                            {{ number_format($acc->spend_cap, 0, ',', '.') }} {{ $acc->currency }}
                                        @else
                                            <span class="text-slate-400">0 {{ $acc->currency }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-2 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                    <div>
                        Hiển thị <span class="font-bold text-slate-800">{{ $accounts->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-800">{{ $accounts->lastItem() ?? 0 }}</span> trên tổng số <span class="font-bold text-slate-800">{{ $accounts->total() }}</span> tài khoản ({{ $perPage ?? 100 }} / trang)
                    </div>
                    @if($accounts->hasPages())
                        <div>
                            {{ $accounts->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

        <!-- ============================================== -->
        <!-- TAB 2: QUẢN LÝ ĐỐI TÁC CỦA BM (PARTNERS)        -->
        <!-- ============================================== -->
        <div id="tabContent-partners" class="space-y-0 hidden">
            <!-- Header & Filter for Partners -->
            <div class="p-2.5 sm:px-4 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-handshake text-purple-600 mr-1.5"></i> Danh sách Đối tác liên kết của BM
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               id="tabPartnerSearchInput" 
                               oninput="filterTabPartners()" 
                               placeholder="Lọc tên hoặc ID..." 
                               class="pl-7 pr-2.5 py-1 border border-slate-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-purple-500 w-36 sm:w-48">
                    </div>

                    <button type="button" 
                            id="btnTabSyncPartners"
                            onclick="syncTabPartnersDirect()"
                            class="inline-flex items-center px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                        <i class="fa-solid fa-arrows-rotate mr-1" id="iconTabSyncPartners"></i> 
                        <span>Đồng bộ đối tác</span>
                    </button>

                    @if($bm->bm_id)
                        <a href="https://business.facebook.com/settings/partners?business_id={{ $bm->bm_id }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Suite ↗
                        </a>
                    @endif
                </div>
            </div>

            @if(empty($partners) || count($partners) === 0)
                <div class="p-10 text-center text-slate-500" id="tabPartnersEmptyBox">
                    <div class="w-10 h-10 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mx-auto mb-2 text-lg">
                        <i class="fa-regular fa-handshake"></i>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">Chưa có đối tác nào được liên kết</div>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto mb-3">
                        BM này chưa chia sẻ tài sản với đối tác nào hoặc đối tác chưa được phân quyền trên Meta Business Suite.
                    </p>
                    @if($bm->bm_id)
                        <a href="https://business.facebook.com/settings/partners?business_id={{ $bm->bm_id }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <i class="fa-solid fa-plus mr-1"></i> Thêm đối tác trên Meta Suite
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Tên đối tác</th>
                                <th class="px-3.5 py-2.5 text-left">Partner Business ID</th>
                                <th class="px-3.5 py-2.5 text-left">Tài khoản QC phân quyền</th>
                                <th class="px-3.5 py-2.5 text-center">Trạng thái</th>
                                <th class="px-3.5 py-2.5 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white" id="tabPartnersTbody">
                            @foreach($partners as $p)
                                <tr class="hover:bg-slate-50/80 transition tab-partner-row" data-name="{{ strtolower($p['name'] ?? '') }}" data-id="{{ strtolower($p['id'] ?? '') }}">
                                    <!-- Partner Name -->
                                    <td class="px-3.5 py-2 font-semibold text-slate-900">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                <i class="fa-solid fa-building-user"></i>
                                            </span>
                                            <span class="truncate max-w-xs">{{ $p['name'] ?? 'Đối tác chưa đặt tên' }}</span>
                                        </div>
                                    </td>

                                    <!-- Partner ID -->
                                    <td class="px-3.5 py-2 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <span>{{ $p['id'] }}</span>
                                            <button type="button" onclick="copyToClipboard('{{ $p['id'] }}', 'Đã sao chép ID đối tác!')" 
                                                    class="text-slate-400 hover:text-blue-600" title="Sao chép ID">
                                                <i class="fa-regular fa-copy text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Accounts -->
                                    <td class="px-3.5 py-2 text-xs">
                                        <div class="flex items-center space-x-1.5 flex-wrap">
                                            <span class="font-semibold text-purple-700">
                                                {{ $p['accounts_count'] ?? count($p['accounts'] ?? []) }} TK
                                            </span>
                                            @if(!empty($p['accounts']))
                                                @foreach(array_slice($p['accounts'], 0, 3) as $acc)
                                                    <span class="px-1.5 py-0.2 rounded font-mono text-[10px] bg-slate-100 text-slate-700 border border-slate-200" 
                                                          title="Quyền: {{ !empty($acc['tasks']) ? implode(', ', $acc['tasks']) : 'Chung' }}">
                                                        {{ $acc['account_id'] }}
                                                    </span>
                                                @endforeach
                                                @if(count($p['accounts']) > 3)
                                                    <span class="text-[10px] text-purple-600 font-semibold">
                                                        +{{ count($p['accounts']) - 3 }} khác
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full"></span> Đã xác nhận
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        @if($bm->bm_id)
                                            <a href="https://business.facebook.com/latest/settings/partners?business_id={{ $bm->bm_id }}&selected_partner_id={{ $p['id'] }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition"
                                               title="Mở cài đặt đối tác này trên Meta Business Suite">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Suite
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">---</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-2 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex items-center justify-between">
                    <span id="tabPartnersCountSummary">Tổng cộng {{ count($partners) }} đối tác</span>
                    @if($bm->bm_id)
                        <a href="https://business.facebook.com/settings/partners?business_id={{ $bm->bm_id }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                            <span>Quản trị đối tác trên Meta Suite</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- ============================================== -->
        <!-- TAB 3: DANH SÁCH PAGES THUỘC BM                -->
        <!-- ============================================== -->
        <div id="tabContent-pages" class="space-y-0 hidden">
            <div class="p-2.5 sm:px-4 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-flag text-sky-600 mr-1.5"></i> Facebook Pages thuộc BM
                    </h2>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('pages.index', ['bm_id' => $bm->id]) }}" 
                       class="inline-flex items-center px-2.5 py-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Mở Quản lý Pages
                    </a>
                </div>
            </div>

            @if($bmPages->isEmpty())
                <div class="p-10 text-center text-slate-500">
                    <div class="w-10 h-10 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center mx-auto mb-2 text-lg">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">Chưa có Facebook Page nào được liên kết với BM này</div>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto mb-3">
                        Bấm nút bên dưới để đồng bộ toàn bộ Pages từ Facebook về hệ thống.
                    </p>
                    <form action="{{ route('pages.sync') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <i class="fa-solid fa-arrows-rotate mr-1"></i> Đồng bộ Pages
                        </button>
                    </form>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Fanpage</th>
                                <th class="px-3.5 py-2.5 text-left">Page ID</th>
                                <th class="px-3.5 py-2.5 text-left">Phân loại</th>
                                <th class="px-3.5 py-2.5 text-right">Lượt thích / Fan</th>
                                <th class="px-3.5 py-2.5 text-center">Trạng thái</th>
                                <th class="px-3.5 py-2.5 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($bmPages as $p)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="px-3.5 py-2">
                                        <div class="flex items-center space-x-2">
                                            <img src="{{ $p->display_avatar }}" 
                                                 alt="{{ $p->name }}" 
                                                 class="w-7 h-7 rounded-lg object-cover border border-slate-200 flex-shrink-0"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($p->name) }}&background=0284c7&color=ffffff&size=100'">
                                            <div class="min-w-0">
                                                <div class="font-bold text-slate-800 truncate max-w-xs" title="{{ $p->name }}">
                                                    {{ $p->name }}
                                                </div>
                                                <div class="text-[10px] text-slate-500 truncate max-w-xs">
                                                    {{ $p->category ?? 'Trang cộng đồng / Doanh nghiệp' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-2 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                        <div class="flex items-center space-x-1">
                                            <span>{{ $p->page_id }}</span>
                                            <button type="button" 
                                                    onclick="copyToClipboard('{{ $p->page_id }}', 'Đã sao chép Page ID!')" 
                                                    class="text-slate-400 hover:text-sky-600" 
                                                    title="Sao chép ID">
                                                <i class="fa-regular fa-copy text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-3.5 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $p->source_badge_class }}">
                                            {{ $p->source_label }}
                                        </span>
                                    </td>
                                    <td class="px-3.5 py-2 text-right whitespace-nowrap">
                                        <div class="font-bold text-slate-900">{{ number_format($p->fan_count) }}</div>
                                        <div class="text-[10px] text-slate-500">{{ number_format($p->followers_count) }} followers</div>
                                    </td>
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        @if($p->is_published)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                                Bản nháp
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-center whitespace-nowrap">
                                        <a href="{{ $p->facebook_url }}" 
                                           target="_blank" 
                                           class="inline-flex items-center px-2 py-1 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-[11px] font-semibold transition">
                                            <i class="fa-brands fa-facebook text-xs mr-1 text-blue-600"></i> Xem Page
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-2 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex items-center justify-between">
                    <span>Tổng cộng {{ $bmPages->count() }} Facebook Pages</span>
                    <a href="{{ route('pages.index', ['bm_id' => $bm->id]) }}" class="text-sky-600 hover:text-sky-800 font-semibold flex items-center gap-1">
                        <span>Xem chi tiết trên Quản lý Pages</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            @endif
        </div>
        </div>
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

        <form id="formEditBM" action="{{ route('bms.update', $bm->id) }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Tên Business Manager <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="edit_name" 
                       name="name" 
                       value="{{ $bm->name }}" 
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
                       value="{{ $bm->bm_id }}" 
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                    Cập nhật Access Token mới (Để trống nếu giữ nguyên)
                </label>
                <textarea name="access_token" 
                          rows="3" 
                          placeholder="Nhập Access Token mới nếu token cũ đã hết hạn hoặc bị đổi mật khẩu..." 
                          class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                <p class="text-[11px] text-amber-600 font-medium mt-1">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Nếu nhập token mới, hệ thống sẽ tự động cập nhật và đồng bộ lại danh sách tài khoản.
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
<!-- MODAL: KẾT QUẢ KIỂM TRA TOKEN                                 -->
<!-- ============================================================== -->
<div id="modalTokenInspector" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in duration-200">
        <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-base flex items-center">
                <i class="fa-solid fa-stethoscope mr-2 text-emerald-400"></i> Kiểm tra Token Meta API
            </h3>
            <button onclick="closeTokenModal()" class="text-white/80 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <div class="p-6">
            <!-- Loading -->
            <div id="tokenLoading" class="py-12 text-center">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-3"></i>
                <div class="text-sm font-semibold text-slate-800">Đang kết nối Facebook Graph API...</div>
                <div class="text-xs text-slate-400 mt-1">Đang kiểm tra tính hợp lệ và hạn dùng của Token.</div>
            </div>

            <!-- Result -->
            <div id="tokenResult" class="hidden space-y-4">
                <div id="tokenStatusBanner" class="p-4 rounded-xl flex items-center space-x-3"></div>

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
                        <span class="text-slate-500 font-medium">Meta App:</span>
                        <span id="tokenApp" class="font-semibold text-slate-700"></span>
                    </div>
                    <div class="py-1">
                        <span class="text-slate-500 font-medium block mb-1.5">Quyền hạn (Scopes):</span>
                        <div id="tokenScopes" class="flex flex-wrap gap-1.5"></div>
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

@push('scripts')
<script>
    function openEditBMModal(bmId, name, bmCode) {
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
                        <div class="text-xs text-emerald-700">Token đã được Meta xác thực hợp lệ.</div>
                    </div>
                `;
            } else {
                statusBanner.className = "p-4 rounded-xl flex items-center space-x-3 bg-rose-50 border border-rose-200 text-rose-800";
                statusBanner.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div>
                        <div class="font-bold text-sm">Token Không Hợp Lệ!</div>
                        <div class="text-xs text-rose-700">Token đã hết hạn hoặc bị thu hồi quyền.</div>
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
                    <div class="text-xs text-rose-700">${err.message || 'Không thể kết nối máy chủ'}</div>
                </div>
            `;
        });
    }

    // ==========================================
    // CHUYỂN TAB: TÀI KHOẢN QC & ĐỐI TÁC & PAGES
    // ==========================================
    function switchBmTab(tab) {
        const btnAccounts = document.getElementById('tabBtn-accounts');
        const btnPartners = document.getElementById('tabBtn-partners');
        const btnPages = document.getElementById('tabBtn-pages');
        const paneAccounts = document.getElementById('tabContent-accounts');
        const panePartners = document.getElementById('tabContent-partners');
        const panePages = document.getElementById('tabContent-pages');

        // Reset class styles
        const baseClass = "flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition";
        btnAccounts.className = baseClass;
        btnPartners.className = baseClass;
        if (btnPages) btnPages.className = baseClass;

        paneAccounts.classList.add('hidden');
        panePartners.classList.add('hidden');
        if (panePages) panePages.classList.add('hidden');

        if (tab === 'partners') {
            btnPartners.className = "flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-purple-600 text-purple-700 transition";
            panePartners.classList.remove('hidden');
            window.location.hash = 'partners';
        } else if (tab === 'pages') {
            if (btnPages) btnPages.className = "flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-sky-600 text-sky-700 transition";
            if (panePages) panePages.classList.remove('hidden');
            window.location.hash = 'pages';
        } else {
            btnAccounts.className = "flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-blue-600 text-blue-600 transition";
            paneAccounts.classList.remove('hidden');
            window.location.hash = 'accounts';
        }
    }

    function filterTabPartners() {
        const query = (document.getElementById('tabPartnerSearchInput').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.tab-partner-row');
        let count = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const id = row.dataset.id || '';
            if (name.includes(query) || id.includes(query)) {
                row.classList.remove('hidden');
                count++;
            } else {
                row.classList.add('hidden');
            }
        });

        const summary = document.getElementById('tabPartnersCountSummary');
        if (summary) {
            summary.textContent = `Hiển thị ${count} / ${rows.length} đối tác`;
        }
    }

    function syncTabPartnersDirect() {
        const btn = document.getElementById('btnTabSyncPartners');
        const icon = document.getElementById('iconTabSyncPartners');

        btn.disabled = true;
        btn.classList.add('opacity-75');
        icon.classList.add('fa-spin');

        fetch("{{ route('bms.syncPartners', $bm->id) }}", {
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
                showToast(data.message || 'Đồng bộ đối tác thành công!', 'success', 3000);
                window.location.hash = 'partners';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast('Lỗi: ' + (data.message || 'Không thể đồng bộ'), 'error', 4000);
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

    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'partners' || window.location.hash === '#partners') {
            switchBmTab('partners');
        } else if (urlParams.get('tab') === 'pages' || window.location.hash === '#pages') {
            switchBmTab('pages');
        }
    });
</script>
@endpush
@endsection
