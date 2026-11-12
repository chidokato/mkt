@extends('layout')

@section('title', 'Tài khoản QC: ' . $account->name . ' - Meta Ads Hub')

@section('header_actions')
    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 text-xs font-medium whitespace-nowrap"
         title="Lần cập nhật gần nhất: {{ $account->updated_at->format('d/m/Y H:i:s') }}">
        <i class="fa-regular fa-clock text-slate-400 mr-1.5 text-xs"></i>
        <span class="hidden sm:inline text-slate-500">Cập nhật:</span>
        <span class="font-semibold text-slate-800 ml-1">{{ $account->updated_at->diffForHumans() }}</span>
    </div>

    <!-- Nút Danh sách TKQC -->
    <a href="{{ route('accounts.index') }}" 
       class="inline-flex items-center px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition whitespace-nowrap"
       title="Về danh sách Quản lý TKQC">
        <i class="fa-solid fa-rectangle-ad mr-1.5 text-emerald-600"></i>
        <span>DS TKQC</span>
    </a>

    <!-- Nút Quay lại BM -->
    <a href="{{ route('bms.show', $account->business_manager_id) }}" 
       class="inline-flex items-center px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 transition whitespace-nowrap"
       title="Quay lại trang quản trị BM {{ $bm->name ?? '' }}">
        <i class="fa-solid fa-briefcase mr-1.5 text-slate-500"></i>
        <span>Về BM: {{ \Illuminate\Support\Str::limit($bm->name ?? 'BM', 18) }}</span>
    </a>

    <!-- Meta Billing Hub của Ad Account -->
    <a href="{{ $account->billing_url }}" target="_blank" rel="noopener noreferrer" 
       class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-semibold rounded-lg border border-amber-300 transition whitespace-nowrap"
       title="Mở Trung tâm Lập hóa đơn và thanh toán trên Meta Business (Billing Hub)">
        <i class="fa-solid fa-credit-card mr-1.5 text-amber-600"></i> Thanh toán FB
    </a>

    <!-- Link Trình quản lý QC trực tiếp trên Meta -->
    <a href="{{ $account->ads_manager_url }}" target="_blank" rel="noopener noreferrer" 
       class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition whitespace-nowrap"
       title="Mở Trình quản lý quảng cáo Meta trong tab mới">
        <i class="fa-solid fa-arrow-up-right-from-square mr-1.5"></i> Trình quản lý QC
    </a>
@endsection

@section('content')
<div class="space-y-3">

    @php
        $activeCampaigns = collect($campaigns)->filter(fn($c) => ($c['status'] ?? '') === 'ACTIVE')->values();
        $pausedCampaigns = collect($campaigns)->filter(fn($c) => ($c['status'] ?? '') !== 'ACTIVE')->values();
        $activeCount = $activeCampaigns->count();
        $pausedCount = $pausedCampaigns->count();
        $hasDebt = ($account->account_status == 3 || $account->balance > 0);
    @endphp

    <!-- ============================================== -->
    <!-- 5 STAT CARDS: GỌN GÀNG, ĐỒNG BỘ VỚI TRANG BM  -->
    <!-- ============================================== -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
        
        <!-- Card 1: Trạng thái tài khoản -->
        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Trạng thái TKQC</div>
            <div class="text-base sm:text-lg font-extrabold mt-0.5 truncate">
                @if($account->account_status == 1)
                    <span class="text-emerald-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-sm"></i> Hoạt động
                    </span>
                @elseif($account->account_status == 3)
                    <span class="text-amber-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-exclamation text-sm animate-pulse"></i> Nợ tiền
                    </span>
                @else
                    <span class="text-rose-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-xmark text-sm"></i> {{ $account->status_label }}
                    </span>
                @endif
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate">
                <span class="font-medium text-slate-700">{{ $account->source_label }}</span> &bull; 
                <span class="font-mono text-slate-500">{{ $account->currency }}</span>
            </div>
        </div>

        <!-- Card 2: Tổng chi phí theo thời gian -->
        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Chi Phí ({{ $dateLabel }})</span>
                <i class="fa-solid fa-money-bill-wave text-indigo-500 text-xs"></i>
            </div>
            <div class="text-xl font-extrabold text-indigo-600 mt-0.5 truncate">
                {{ number_format($totalCampaignSpend ?? 0, 0, ',', '.') }} {{ $account->currency }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate" title="Chi tiêu tích lũy toàn thời gian: {{ number_format($account->amount_spent, 0, ',', '.') }} {{ $account->currency }}">
                <span class="text-slate-400">Tích lũy:</span> <span class="font-semibold text-slate-700">{{ number_format($account->amount_spent, 0, ',', '.') }} {{ $account->currency }}</span>
            </div>
        </div>

        <!-- Card 3: Số dư nợ -->
        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $hasDebt ? 'text-amber-700' : 'text-slate-400' }}">Số Dư Nợ</span>
                <i class="fa-solid fa-file-invoice-dollar {{ $hasDebt ? 'text-amber-500' : 'text-slate-400' }} text-xs"></i>
            </div>
            <div class="text-xl font-extrabold mt-0.5 truncate {{ $hasDebt ? 'text-amber-600' : 'text-slate-700' }}">
                {{ number_format($account->balance, 0, ',', '.') }} {{ $account->currency }}
            </div>
            <div class="text-[10px] mt-0.5 truncate">
                @if($hasDebt)
                    <a href="{{ $account->billing_url }}" target="_blank" rel="noopener noreferrer" 
                       class="text-amber-600 hover:text-amber-800 font-bold inline-flex items-center underline">
                        <span>Thanh toán ngay</span>
                        <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[9px]"></i>
                    </a>
                @else
                    <span class="text-emerald-600 font-medium"><i class="fa-solid fa-check mr-0.5"></i> Không có dư nợ</span>
                @endif
            </div>
        </div>

        <!-- Card 4: Thẻ thanh toán (4 số cuối) -->
        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Thẻ Thanh Toán</span>
                <i class="fa-solid fa-credit-card text-blue-500 text-xs"></i>
            </div>
            <div class="text-base sm:text-lg font-extrabold text-slate-800 mt-0.5 truncate flex items-center space-x-1.5">
                @if($account->card_display)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-bold bg-slate-900 text-white shadow-2xs">
                        <i class="fa-regular fa-credit-card mr-1 text-slate-300"></i>
                        {{ $account->card_display }}
                    </span>
                @else
                    <span class="text-slate-400 text-xs font-medium">Chưa gắn thẻ</span>
                @endif
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate">
                @if($account->card_display)
                    <a href="{{ $account->billing_url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">
                        Xem chi tiết thẻ ↗
                    </a>
                @else
                    <span>Phương thức thanh toán</span>
                @endif
            </div>
        </div>

        <!-- Card 5: Chiến dịch -->
        <div class="bg-white p-3 rounded-xl border border-slate-200/90 shadow-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Chiến Dịch</span>
                <i class="fa-solid fa-bullhorn text-emerald-500 text-xs"></i>
            </div>
            <div class="text-xl font-extrabold text-slate-900 mt-0.5">
                {{ count($campaigns) }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5 truncate">
                <span class="text-emerald-600 font-semibold">{{ $activeCount }} đang bật</span> &bull; 
                <span class="text-slate-500">{{ $pausedCount }} đang tắt</span>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TABS CONTAINER: CHIẾN DỊCH & ĐỐI TÁC           -->
    <!-- ============================================== -->
    <div id="campaignsTableCard" class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        
        <!-- Tab Navigation Bar -->
        <div class="flex items-center space-x-1 border-b border-slate-200 bg-slate-50/70 px-4 pt-1.5">
            <!-- Tab 1: Chiến dịch -->
            <button type="button" 
                    onclick="switchAccountTab('campaigns')" 
                    id="tabBtn-campaigns"
                    class="flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-blue-600 text-blue-600 transition">
                <i class="fa-solid fa-bullhorn text-sm"></i>
                <span>Chiến dịch</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-blue-100 text-blue-700" id="tabBadge-campaigns">
                    {{ count($campaigns) }}
                </span>
            </button>

            <!-- Tab 2: Đối tác -->
            <button type="button" 
                    onclick="switchAccountTab('partners')" 
                    id="tabBtn-partners"
                    class="flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition">
                <i class="fa-solid fa-handshake text-sm text-purple-600"></i>
                <span>Đối tác</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-700" id="tabBadge-partners">
                    {{ count($accountPartners) }}
                </span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: DANH SÁCH CHIẾN DỊCH QUẢNG CÁO          -->
        <!-- ============================================== -->
        <div id="tabContent-campaigns" class="space-y-0">
            <!-- Toolbar Lọc & Tìm kiếm Chiến dịch -->
            <div class="p-2.5 sm:px-4 border-b border-slate-200/80 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2.5 bg-white">
                
                <!-- Filter Pills: Tất cả / Đang bật / Đang tắt -->
                <div class="flex items-center space-x-1.5 flex-wrap flex-shrink-0">
                    <button type="button" 
                            onclick="filterCampaignStatus('all')" 
                            id="campFilter-all"
                            class="camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-slate-900 text-white">
                        <span>Tất cả</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-slate-700 text-slate-200">{{ count($campaigns) }}</span>
                    </button>

                    <button type="button" 
                            onclick="filterCampaignStatus('ACTIVE')" 
                            id="campFilter-ACTIVE"
                            class="camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200">
                        <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full"></span>
                        <span>Đang bật</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800">{{ $activeCount }}</span>
                    </button>

                    <button type="button" 
                            onclick="filterCampaignStatus('PAUSED')" 
                            id="campFilter-PAUSED"
                            class="camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200">
                        <span class="w-1.5 h-1.5 mr-1 bg-slate-400 rounded-full"></span>
                        <span>Đang tắt</span>
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">{{ $pausedCount }}</span>
                    </button>
                </div>

                <!-- Bộ lọc theo thời gian (Date Preset & Custom Range) -->
                <form action="{{ route('accounts.campaigns', $account->id) }}" method="GET" id="campaignDateFilterForm" class="flex flex-wrap items-center gap-1.5">
                    <div class="relative">
                        <select name="date_preset" id="campDatePreset" onchange="handleCampDatePresetChange(this)" 
                                class="pl-7 pr-3 py-1.5 border border-slate-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold text-indigo-700 shadow-2xs">
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

                    <!-- Ô chọn khoảng ngày nếu chọn custom -->
                    <div id="campCustomDateRange" class="{{ ($datePreset ?? '') === 'custom' ? 'flex' : 'hidden' }} items-center gap-1">
                        <input type="date" name="since" value="{{ $since ?? '' }}" class="px-2 py-1 border border-slate-300 rounded-lg text-xs bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <span class="text-xs text-slate-400">-</span>
                        <input type="date" name="until" value="{{ $until ?? '' }}" class="px-2 py-1 border border-slate-300 rounded-lg text-xs bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <button type="submit" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition">
                            Lọc
                        </button>
                    </div>

                    @if(($datePreset ?? 'last_30d') !== 'last_30d' || !empty($since))
                        <a href="{{ route('accounts.campaigns', $account->id) }}" class="p-1.5 text-slate-400 hover:text-slate-700 text-xs rounded-lg hover:bg-slate-100 transition" title="Đặt lại về mặc định (30 ngày qua)">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </form>

                <!-- Ô tìm kiếm chiến dịch & Link Meta -->
                <div class="flex items-center space-x-2">
                    <div class="relative w-full sm:w-56">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" 
                               id="campaignSearchInput" 
                               oninput="filterCampaignKeyword(this.value)" 
                               placeholder="Lọc tên hoặc ID chiến dịch..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <a href="{{ $account->ads_manager_url }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center px-2.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xs transition whitespace-nowrap"
                       title="Mở Trình quản lý quảng cáo của tài khoản này trên Meta">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Ads
                    </a>
                </div>
            </div>

            @if(!empty($campaignError))
                <div class="p-4 bg-amber-50 border-b border-amber-200 text-xs text-amber-800 flex items-start space-x-2.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-sm mt-0.5 flex-shrink-0"></i>
                    <div>
                        <div class="font-bold">Thông báo từ Meta Graph API:</div>
                        <div class="text-[11px] text-amber-700 mt-0.5">{{ $campaignError }}</div>
                        <div class="text-[10px] text-amber-600 mt-1">
                            Nếu mã truy cập (Access Token) hết hạn hoặc tài khoản chưa cấp quyền ads_read, bạn có thể kiểm tra lại token ở trang BM.
                        </div>
                    </div>
                </div>
            @endif

            @if(empty($campaigns))
                <div class="p-12 text-center text-slate-500">
                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <div class="text-sm font-bold text-slate-800">Chưa có chiến dịch nào</div>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Tài khoản này chưa có chiến dịch quảng cáo nào hoặc chưa được đồng bộ từ Meta Ads Manager.
                    </p>
                    <div class="mt-4">
                        <a href="{{ $account->ads_manager_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <i class="fa-solid fa-plus mr-1.5"></i> Tạo chiến dịch trên Meta Ads Manager
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Tên Chiến dịch</th>
                                <th class="px-3.5 py-2.5 text-left">Mục tiêu (Objective)</th>
                                <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Chi phí ({{ $dateLabel }})</th>
                                <th class="px-3.5 py-2.5 text-right whitespace-nowrap">Ngân sách</th>
                                <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Trạng thái</th>
                                <th class="px-3.5 py-2.5 text-center whitespace-nowrap">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white" id="campaignsTbody">
                            @foreach($campaigns as $camp)
                                @php
                                    $status = $camp['status'] ?? 'UNKNOWN';
                                    $isActive = $status === 'ACTIVE';
                                    $spendVal = (float) ($camp['spend'] ?? 0);
                                    $divisor = \App\Services\FacebookAdsService::getCurrencyDivisor($account->currency ?? 'VND');
                                    $dailyBudget = isset($camp['daily_budget']) ? ((float) $camp['daily_budget'] / $divisor) : null;
                                    $lifetimeBudget = isset($camp['lifetime_budget']) ? ((float) $camp['lifetime_budget'] / $divisor) : null;
                                    $campaignMetaUrl = "https://adsmanager.facebook.com/adsmanager/manage/campaigns?act=" . $account->numeric_account_id . "&selected_campaign_ids=" . ($camp['id'] ?? '');
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition campaign-row" 
                                    id="row-{{ $camp['id'] }}"
                                    data-id="{{ $camp['id'] }}" 
                                    data-name="{{ mb_strtolower($camp['name'] ?? '') }}" 
                                    data-status="{{ $isActive ? 'ACTIVE' : 'PAUSED' }}"
                                    data-spend="{{ $spendVal }}">
                                    
                                    <!-- Tên chiến dịch & ID -->
                                    <td class="px-3.5 py-2.5">
                                        <div class="flex items-start space-x-2.5">
                                            <span class="w-7 h-7 rounded-lg {{ $isActive ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }} flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                                                <i class="fa-solid fa-bullhorn text-[11px]"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ $campaignMetaUrl }}" 
                                                   target="_blank" 
                                                   rel="noopener noreferrer" 
                                                   class="font-bold text-slate-900 hover:text-blue-600 transition block truncate max-w-sm sm:max-w-md"
                                                   title="{{ $camp['name'] ?? 'Không tên' }}">
                                                    {{ $camp['name'] ?? 'Không tên' }}
                                                </a>
                                                <div class="flex items-center space-x-1.5 mt-0.5 text-[10px] font-mono text-slate-400">
                                                    <span>ID: {{ $camp['id'] }}</span>
                                                    <button type="button" 
                                                            onclick="copyToClipboard('{{ $camp['id'] }}', 'Đã sao chép ID chiến dịch!')" 
                                                            class="text-slate-400 hover:text-blue-600 transition" 
                                                            title="Sao chép ID">
                                                        <i class="fa-regular fa-copy text-[10px]"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Mục tiêu (Objective) -->
                                    <td class="px-3.5 py-2.5 text-slate-600 whitespace-nowrap">
                                        <span class="inline-block bg-slate-100 text-slate-700 text-[11px] px-2 py-0.5 rounded font-mono border border-slate-200">
                                            {{ $camp['objective'] ?? 'OUTCOME_TRAFFIC' }}
                                        </span>
                                    </td>

                                    <!-- Chi phí (Spend) -->
                                    <td class="px-3.5 py-2.5 text-right whitespace-nowrap">
                                        @if($spendVal > 0)
                                            <div class="font-extrabold text-indigo-600 text-xs">
                                                {{ number_format($spendVal, 0, ',', '.') }}
                                                <span class="text-[10px] font-mono font-normal text-slate-400">{{ $account->currency }}</span>
                                            </div>
                                            <span class="inline-flex items-center text-[9px] text-emerald-700 font-semibold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200 mt-0.5">
                                                <i class="fa-solid fa-arrow-trend-up text-[8px] mr-1"></i> Có chi tiêu
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-[11px] font-mono">0 {{ $account->currency }}</span>
                                        @endif
                                    </td>

                                    <!-- Ngân sách -->
                                    <td class="px-3.5 py-2.5 text-right font-semibold text-slate-900 whitespace-nowrap">
                                        @if($dailyBudget)
                                            <div>
                                                <span>{{ number_format($dailyBudget, 0, ',', '.') }}</span>
                                                <span class="text-[10px] text-slate-500 font-normal">{{ $account->currency }}/ngày</span>
                                            </div>
                                        @elseif($lifetimeBudget)
                                            <div>
                                                <span>{{ number_format($lifetimeBudget, 0, ',', '.') }}</span>
                                                <span class="text-[10px] text-slate-500 font-normal">{{ $account->currency }}/trọn đời</span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-[11px] font-normal italic">Cài ở nhóm QC</span>
                                        @endif
                                    </td>

                                    <!-- Trạng thái hiện tại -->
                                    <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                        <span id="badge-{{ $camp['id'] }}" 
                                              class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 mr-1 rounded-full {{ $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                            {{ $isActive ? 'Đang bật' : 'Đang tắt' }}
                                        </span>
                                    </td>

                                    <!-- Nút Thao tác Bật/Tắt -->
                                    <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <button type="button" 
                                                    onclick="toggleStatus('{{ $camp['id'] }}', '{{ $isActive ? 'PAUSED' : 'ACTIVE' }}')" 
                                                    id="btn-{{ $camp['id'] }}"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-2xs {{ $isActive ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                                @if($isActive)
                                                    <i class="fa-solid fa-pause mr-1"></i> Tắt
                                                @else
                                                    <i class="fa-solid fa-play mr-1"></i> Bật
                                                @endif
                                            </button>

                                            <a href="{{ $campaignMetaUrl }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition border border-slate-200" 
                                               title="Mở chiến dịch này trên Meta Ads Manager">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="campaignsNoMatch" class="hidden p-8 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-filter-circle-xmark text-2xl text-slate-300 mb-2 block"></i>
                        Không tìm thấy chiến dịch nào khớp với bộ lọc hiện tại.
                    </div>
                </div>

                <!-- Thanh phân trang 20 dòng / trang chuẩn SaaS -->
                <div id="campaignsPaginationBar" class="px-4 py-3 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-3">
                        <span id="campaignsSummaryText" class="text-slate-600 font-medium">
                            Đang tải chiến dịch...
                        </span>
                        <div class="flex items-center space-x-1.5 text-slate-500 text-[11px]">
                            <span>Hiển thị:</span>
                            <select id="campaignPageSizeSelect" 
                                    onchange="changeCampaignPageSize(this.value)" 
                                    class="px-2 py-1 border border-slate-300 rounded-lg bg-white text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700">
                                <option value="20" selected>20 dòng / trang</option>
                                <option value="50">50 dòng / trang</option>
                                <option value="100">100 dòng / trang</option>
                                <option value="all">Tất cả</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div id="campaignPaginationControls" class="flex items-center space-x-1">
                            <!-- Rendered by JS -->
                        </div>
                        <a href="{{ $account->ads_manager_url }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="hidden md:inline-flex text-blue-600 hover:text-blue-800 font-medium items-center gap-1 ml-2 text-xs">
                            <span>Meta Ads Manager</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- ============================================== -->
        <!-- TAB 2: DANH SÁCH ĐỐI TÁC CỦA TÀI KHOẢN QC      -->
        <!-- ============================================== -->
        <div id="tabContent-partners" class="space-y-0 hidden">
            <!-- Toolbar Lọc & Tìm kiếm Đối tác -->
            <div class="p-2.5 sm:px-4 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-2.5 bg-white">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-bold text-slate-800">
                        <i class="fa-solid fa-handshake text-purple-600 mr-1.5"></i> Đối tác liên kết với tài khoản này
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-100 text-purple-700">
                        {{ count($accountPartners) }}
                    </span>
                </div>

                <div class="flex items-center space-x-2">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" 
                               id="partnerSearchInput" 
                               oninput="filterPartners(this.value)" 
                               placeholder="Lọc tên hoặc ID đối tác..." 
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-white transition">
                    </div>

                    @php
                        $bmSettingsId = $account->owner_business_id ?: ($bm->bm_id ?? '');
                    @endphp
                    @if($bmSettingsId)
                        <a href="https://business.facebook.com/settings/partners?business_id={{ $bmSettingsId }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-2.5 py-1.5 bg-purple-700 hover:bg-purple-800 text-white text-xs font-semibold rounded-lg shadow-xs transition whitespace-nowrap"
                           title="Mở Cài đặt đối tác trên Meta Business Suite">
                            <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Suite ↗
                        </a>
                    @endif
                </div>
            </div>

            @if(empty($accountPartners) || count($accountPartners) === 0)
                <div class="p-12 text-center text-slate-500">
                    <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                        <i class="fa-regular fa-handshake"></i>
                    </div>
                    <div class="text-sm font-bold text-slate-800">Chưa có đối tác nào được phân quyền</div>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Tài khoản quảng cáo này chưa chia sẻ tài sản hoặc phân quyền cho đối tác nào trên Meta Business Suite.
                    </p>
                    @if(!empty($bmSettingsId))
                        <div class="mt-4">
                            <a href="https://business.facebook.com/settings/partners?business_id={{ $bmSettingsId }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="inline-flex items-center px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                                <i class="fa-solid fa-plus mr-1.5"></i> Thêm đối tác trên Meta Suite
                            </a>
                        </div>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/90 text-slate-600 text-[11px] uppercase font-semibold">
                            <tr>
                                <th class="px-3.5 py-2.5 text-left">Tên đối tác</th>
                                <th class="px-3.5 py-2.5 text-left">Partner Business ID</th>
                                <th class="px-3.5 py-2.5 text-left">Quyền hạn (Tasks)</th>
                                <th class="px-3.5 py-2.5 text-center">Trạng thái</th>
                                <th class="px-3.5 py-2.5 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white" id="partnersTbody">
                            @foreach($accountPartners as $partner)
                                @php
                                    $isOwner = ($partner['type'] ?? '') === 'owner_business';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition partner-row" 
                                    data-name="{{ mb_strtolower($partner['name'] ?? '') }}" 
                                    data-id="{{ mb_strtolower($partner['id'] ?? '') }}">
                                    
                                    <!-- Partner Name -->
                                    <td class="px-3.5 py-2.5 font-semibold text-slate-900">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="w-7 h-7 rounded-lg {{ $isOwner ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-purple-50 text-purple-700 border border-purple-200' }} flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                <i class="fa-solid {{ $isOwner ? 'fa-crown text-amber-500' : 'fa-building-user' }}"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="truncate max-w-xs font-bold text-slate-900">
                                                    {{ $partner['name'] ?? 'Đối tác chưa đặt tên' }}
                                                </div>
                                                @if($isOwner)
                                                    <span class="inline-flex items-center text-[10px] text-blue-700 font-semibold bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200 mt-0.5">
                                                        Doanh nghiệp sở hữu gốc
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center text-[10px] text-purple-700 font-semibold bg-purple-50 px-1.5 py-0.2 rounded border border-purple-200 mt-0.5">
                                                        Đối tác được phân quyền
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Partner ID -->
                                    <td class="px-3.5 py-2.5 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                        <div class="flex items-center space-x-1.5">
                                            <span>{{ $partner['id'] ?? '—' }}</span>
                                            @if(!empty($partner['id']))
                                                <button type="button" 
                                                        onclick="copyToClipboard('{{ $partner['id'] }}', 'Đã sao chép ID đối tác!')" 
                                                        class="text-slate-400 hover:text-blue-600 transition" 
                                                        title="Sao chép ID">
                                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Quyền hạn (Tasks) -->
                                    <td class="px-3.5 py-2.5 text-xs">
                                        <div class="flex items-center space-x-1 flex-wrap gap-1">
                                            @forelse($partner['tasks'] ?? [] as $task)
                                                @php
                                                    $taskBadgeColor = match($task) {
                                                        'MANAGE', 'ADMIN', 'OWNER' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        'ADVERTISE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'ANALYZE' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'DRAFT' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                                    };
                                                    $taskLabel = match($task) {
                                                        'MANAGE', 'ADMIN' => 'Quản trị (MANAGE)',
                                                        'ADVERTISE' => 'Tạo QC (ADVERTISE)',
                                                        'ANALYZE' => 'Báo cáo (ANALYZE)',
                                                        'DRAFT' => 'Soạn thảo (DRAFT)',
                                                        'OWNER' => 'Toàn quyền sở hữu (OWNER)',
                                                        default => $task
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium border {{ $taskBadgeColor }}">
                                                    {{ $taskLabel }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400 text-[11px] italic">Quyền mặc định</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1 bg-emerald-500 rounded-full"></span>
                                            {{ $partner['status'] === 'CONFIRMED' ? 'Đã xác nhận' : ($partner['status'] ?? 'Hoạt động') }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                        @if(!empty($partner['id']) && !empty($bmSettingsId))
                                            <a href="https://business.facebook.com/latest/settings/partners?business_id={{ $bmSettingsId }}&selected_partner_id={{ $partner['id'] }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center px-2 py-1 text-[11px] font-semibold text-purple-700 hover:text-purple-900 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition"
                                               title="Mở cài đặt đối tác này trên Meta Business Suite">
                                                <i class="fa-solid fa-arrow-up-right-from-square mr-1 text-[10px]"></i> Meta Suite
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div id="partnersNoMatch" class="hidden p-8 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-filter-circle-xmark text-2xl text-slate-300 mb-2 block"></i>
                        Không tìm thấy đối tác nào khớp với từ khóa tìm kiếm.
                    </div>
                </div>

                <div class="px-4 py-2 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex items-center justify-between">
                    <span id="partnersSummaryText">Tổng cộng {{ count($accountPartners) }} đối tác</span>
                    @if(!empty($bmSettingsId))
                        <a href="https://business.facebook.com/settings/partners?business_id={{ $bmSettingsId }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-purple-600 hover:text-purple-800 font-medium flex items-center gap-1">
                            <span>Quản trị tất cả đối tác trên Meta Business Suite</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    // ==============================================================
    // CHUYỂN TAB: CHIẾN DỊCH & ĐỐI TÁC
    // ==============================================================
    function switchAccountTab(tabName) {
        const tabContentCamp = document.getElementById('tabContent-campaigns');
        const tabContentPartners = document.getElementById('tabContent-partners');
        const tabBtnCamp = document.getElementById('tabBtn-campaigns');
        const tabBtnPartners = document.getElementById('tabBtn-partners');

        if (tabName === 'campaigns') {
            tabContentCamp.classList.remove('hidden');
            tabContentPartners.classList.add('hidden');

            tabBtnCamp.className = "flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-blue-600 text-blue-600 transition";
            tabBtnPartners.className = "flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition";
        } else {
            tabContentCamp.classList.add('hidden');
            tabContentPartners.classList.remove('hidden');

            tabBtnPartners.className = "flex items-center space-x-1.5 px-4 py-2 font-bold text-xs border-b-2 border-purple-600 text-purple-600 transition";
            tabBtnCamp.className = "flex items-center space-x-1.5 px-4 py-2 font-semibold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 transition";
        }
    }

    // ==============================================================
    // LỌC & PHÂN TRANG CHIẾN DỊCH (MẶC ĐỊNH 20 DÒNG / TRANG)
    // ==============================================================
    let currentCampStatusFilter = 'all';
    let currentCampPage = 1;
    let campPageSize = 20;

    function handleCampDatePresetChange(selectEl) {
        const customBox = document.getElementById('campCustomDateRange');
        if (selectEl.value === 'custom') {
            if (customBox) {
                customBox.classList.remove('hidden');
                customBox.classList.add('flex');
            }
        } else {
            if (customBox) {
                customBox.classList.add('hidden');
                customBox.classList.remove('flex');
            }
            selectEl.form.submit();
        }
    }

    function sortCampaignRows() {
        const tbody = document.getElementById('campaignsTbody');
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll('.campaign-row'));
        rows.sort((a, b) => {
            const aActive = (a.dataset.status === 'ACTIVE') ? 1 : 0;
            const bActive = (b.dataset.status === 'ACTIVE') ? 1 : 0;
            if (aActive !== bActive) {
                return bActive - aActive; // 1. Đang bật lên trên
            }

            const aSpend = parseFloat(a.dataset.spend) || 0;
            const bSpend = parseFloat(b.dataset.spend) || 0;
            if (aSpend !== bSpend) {
                return bSpend - aSpend; // 2. Phát sinh chi phí cao hơn lên trước
            }

            return (b.dataset.id || '').localeCompare(a.dataset.id || '');
        });
        rows.forEach(r => tbody.appendChild(r));
    }

    function filterCampaignStatus(status) {
        currentCampStatusFilter = status;
        currentCampPage = 1;

        // Cập nhật trạng thái nút lọc
        ['all', 'ACTIVE', 'PAUSED'].forEach(s => {
            const btn = document.getElementById('campFilter-' + s);
            if (!btn) return;
            if (s === status) {
                btn.className = "camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-slate-900 text-white shadow-2xs";
            } else if (s === 'ACTIVE') {
                btn.className = "camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200";
            } else if (s === 'PAUSED') {
                btn.className = "camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200";
            } else {
                btn.className = "camp-filter-btn inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold transition bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200";
            }
        });

        applyCampaignFilters();
    }

    function filterCampaignKeyword(val) {
        currentCampPage = 1;
        applyCampaignFilters();
    }

    function changeCampaignPageSize(val) {
        campPageSize = val;
        currentCampPage = 1;
        applyCampaignFilters();
    }

    function goToCampaignPage(p) {
        currentCampPage = p;
        applyCampaignFilters();
        const tableCard = document.getElementById('campaignsTableCard');
        if (tableCard) {
            tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function applyCampaignFilters() {
        const keyword = (document.getElementById('campaignSearchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.campaign-row');
        const matchedRows = [];

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowName = row.dataset.name || '';
            const rowId = row.dataset.id || '';

            const matchStatus = (currentCampStatusFilter === 'all') || (rowStatus === currentCampStatusFilter);
            const matchKeyword = !keyword || rowName.includes(keyword) || rowId.includes(keyword);

            if (matchStatus && matchKeyword) {
                matchedRows.push(row);
            } else {
                row.classList.add('hidden');
            }
        });

        const totalMatched = matchedRows.length;
        const noMatch = document.getElementById('campaignsNoMatch');
        if (noMatch) {
            noMatch.classList.toggle('hidden', totalMatched > 0);
        }

        // Tính toán phân trang
        const effectiveSize = (campPageSize === 'all') ? (totalMatched || 1) : parseInt(campPageSize);
        const totalPages = Math.ceil(totalMatched / effectiveSize) || 1;

        if (currentCampPage > totalPages) {
            currentCampPage = totalPages;
        }
        if (currentCampPage < 1) {
            currentCampPage = 1;
        }

        const startIndex = (currentCampPage - 1) * effectiveSize;
        const endIndex = Math.min(startIndex + effectiveSize, totalMatched);

        matchedRows.forEach((row, idx) => {
            if (idx >= startIndex && idx < endIndex) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });

        // Cập nhật text tổng kết
        const summaryText = document.getElementById('campaignsSummaryText');
        if (summaryText) {
            if (totalMatched === 0) {
                summaryText.innerText = '0 chiến dịch';
            } else {
                summaryText.innerHTML = `Hiển thị <strong>${startIndex + 1} - ${endIndex}</strong> trên <strong>${totalMatched}</strong> chiến dịch`;
            }
        }

        renderCampaignPaginationControls(totalPages, currentCampPage);
    }

    function renderCampaignPaginationControls(totalPages, currentPage) {
        const container = document.getElementById('campaignPaginationControls');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        // Nút trang trước (Prev)
        const prevDisabled = (currentPage === 1);
        html += `<button type="button" 
                         onclick="goToCampaignPage(${currentPage - 1})" 
                         ${prevDisabled ? 'disabled' : ''} 
                         class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${prevDisabled ? 'text-slate-300 cursor-not-allowed bg-slate-50' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs'}"
                         title="Trang trước">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                 </button>`;

        // Hiển thị các nút số trang
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
        if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            html += `<button type="button" onclick="goToCampaignPage(1)" class="w-7 h-7 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs">1</button>`;
            if (startPage > 2) {
                html += `<span class="px-1 text-slate-400 text-xs">...</span>`;
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            if (p === currentPage) {
                html += `<button type="button" class="w-7 h-7 rounded-lg text-xs font-bold bg-blue-600 text-white shadow-xs">${p}</button>`;
            } else {
                html += `<button type="button" onclick="goToCampaignPage(${p})" class="w-7 h-7 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs transition">${p}</button>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="px-1 text-slate-400 text-xs">...</span>`;
            }
            html += `<button type="button" onclick="goToCampaignPage(${totalPages})" class="w-7 h-7 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs transition">${totalPages}</button>`;
        }

        // Nút trang kế (Next)
        const nextDisabled = (currentPage === totalPages);
        html += `<button type="button" 
                         onclick="goToCampaignPage(${currentPage + 1})" 
                         ${nextDisabled ? 'disabled' : ''} 
                         class="px-2.5 py-1 rounded-lg text-xs font-semibold transition ${nextDisabled ? 'text-slate-300 cursor-not-allowed bg-slate-50' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-2xs'}"
                         title="Trang tiếp">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                 </button>`;

        container.innerHTML = html;
    }

    // Tự động kích hoạt phân trang ngay khi tải trang
    document.addEventListener('DOMContentLoaded', function() {
        applyCampaignFilters();
    });
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        applyCampaignFilters();
    }

    // ==============================================================
    // LỌC ĐỐI TÁC THEO TỪ KHÓA
    // ==============================================================
    function filterPartners(val) {
        const keyword = (val || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.partner-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const id = row.dataset.id || '';

            if (!keyword || name.includes(keyword) || id.includes(keyword)) {
                row.classList.remove('hidden');
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        const noMatch = document.getElementById('partnersNoMatch');
        if (noMatch) {
            noMatch.classList.toggle('hidden', visibleCount > 0);
        }

        const summaryText = document.getElementById('partnersSummaryText');
        if (summaryText) {
            summaryText.innerText = `Hiển thị ${visibleCount} / {{ count($accountPartners) }} đối tác`;
        }
    }

    // ==============================================================
    // BẬT / TẮT CHIẾN DỊCH TỨC THÌ QUA AJAX
    // ==============================================================
    async function toggleStatus(campaignId, newStatus) {
        const btn = document.getElementById('btn-' + campaignId);
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang đổi...';

        try {
            const response = await fetch("{{ route('campaigns.toggle', ':id') }}".replace(':id', campaignId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: newStatus,
                    ad_account_id: {{ $account->id }}
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                const isNowActive = newStatus === 'ACTIVE';
                const badge = document.getElementById('badge-' + campaignId);
                const row = document.getElementById('row-' + campaignId);

                if (row) {
                    row.dataset.status = isNowActive ? 'ACTIVE' : 'PAUSED';
                    sortCampaignRows();
                    applyCampaignFilters();
                }

                if (isNowActive) {
                    if (badge) {
                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200';
                        badge.innerHTML = '<span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-500 animate-pulse"></span> Đang bật';
                    }
                    
                    btn.className = 'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-2xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200';
                    btn.innerHTML = '<i class="fa-solid fa-pause mr-1"></i> Tắt';
                    btn.onclick = () => toggleStatus(campaignId, 'PAUSED');
                } else {
                    if (badge) {
                        badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200';
                        badge.innerHTML = '<span class="w-1.5 h-1.5 mr-1 rounded-full bg-slate-400"></span> Đang tắt';
                    }

                    btn.className = 'inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold transition shadow-2xs bg-emerald-600 hover:bg-emerald-700 text-white';
                    btn.innerHTML = '<i class="fa-solid fa-play mr-1"></i> Bật';
                    btn.onclick = () => toggleStatus(campaignId, 'ACTIVE');
                }

                showToast(data.message || 'Đã cập nhật trạng thái chiến dịch!', 'success', 3000);
            } else {
                showToast('Lỗi: ' + (data.message || 'Không thể đổi trạng thái chiến dịch.'), 'error', 4000);
                btn.innerHTML = originalText;
            }
        } catch (err) {
            showToast('Lỗi kết nối: ' + err.message, 'error', 4000);
            btn.innerHTML = originalText;
        } finally {
            btn.disabled = false;
        }
    }
</script>
@endpush
