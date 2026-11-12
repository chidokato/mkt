@extends('layout')

@section('title', 'Bảng điều khiển - Quản lý Facebook Ads')

@section('content')
<div class="space-y-6">

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <a href="{{ route('bms.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-blue-300 hover:shadow-md transition group block">
            <div class="flex items-center justify-between">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-blue-600 transition">Quản lý BM</div>
                <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:text-blue-600 transition"></i>
            </div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_bms'] }}</div>
        </a>
        <a href="{{ route('personal-accounts.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group block">
            <div class="flex items-center justify-between">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-indigo-600 transition">TK Cá nhân</div>
                <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:text-indigo-600 transition"></i>
            </div>
            <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['personal_accounts_count'] ?? 0 }}</div>
        </a>
        <a href="{{ route('accounts.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition group block">
            <div class="flex items-center justify-between">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Tổng Ad Accounts</div>
                <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:text-emerald-600 transition"></i>
            </div>
            <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_accounts'] }}</div>
        </a>
        <a href="{{ route('accounts.index', ['account_status' => 1]) }}" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition group block">
            <div class="flex items-center justify-between">
                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider group-hover:text-emerald-600 transition">Tài khoản Active</div>
                <i class="fa-solid fa-arrow-right text-xs text-slate-400 group-hover:text-emerald-600 transition"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active_accounts'] }}</div>
        </a>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tổng tiền đã tiêu</div>
            <div class="text-xl font-bold text-blue-600 mt-1 truncate">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tổng số dư nợ</div>
            <div class="text-xl font-bold text-amber-600 mt-1 truncate">{{ number_format($stats['total_balance'], 0, ',', '.') }} đ</div>
        </div>
    </div>

    <!-- Business Managers & Ad Accounts List -->
    @if($businessManagers->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-1">Chưa có Business Manager nào</h3>
            <p class="text-slate-500 text-sm max-w-md mx-auto mb-6">
                Hãy thêm Business Manager đầu tiên cùng System User Token để bắt đầu đồng bộ và quản lý các tài khoản quảng cáo.
            </p>
            <div class="flex items-center justify-center">
                <a href="{{ route('bms.index') }}" 
                   class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <i class="fa-solid fa-plus-circle text-sm mr-2"></i> Đến Trang Quản lý & Thêm BM
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <i class="fa-solid fa-building text-blue-600"></i>
                <span>Trang quản lý tài sản doanh nghiệp (Business Managers)</span>
            </div>

            <div class="space-y-6">
            @foreach($businessManagers as $bm)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- BM Header -->
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                                <i class="fa-solid fa-building"></i>
                            </span>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('bms.show', $bm->id) }}" class="text-lg font-bold text-slate-900 hover:text-blue-600 transition">
                                        {{ $bm->name }}
                                    </a>
                                    @if($bm->bm_id)
                                        <span class="text-xs bg-slate-200 text-slate-700 px-2 py-0.5 rounded font-mono">ID: {{ $bm->bm_id }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-3 mt-0.5">
                                    <span title="TK của BM sở hữu"><i class="fa-solid fa-building text-blue-600 mr-1"></i> {{ $bm->owned_accounts_count }} của BM</span>
                                    <span>&bull;</span>
                                    <span title="TK được share từ BM khác"><i class="fa-solid fa-share-nodes text-purple-600 mr-1"></i> {{ $bm->client_accounts_count }} được share</span>
                                    <span>&bull;</span>
                                    <span><i class="fa-solid fa-clock mr-1"></i> Cập nhật: {{ $bm->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- BM Actions -->
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('bms.show', $bm->id) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 transition">
                                <i class="fa-solid fa-folder-open mr-1.5"></i> Chi tiết BM
                            </a>

                            <!-- Sync Button -->
                            <form action="{{ route('bms.sync', $bm->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg shadow-sm transition">
                                    <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ tài khoản
                                </button>
                            </form>

                            <!-- Delete BM Button -->
                            <form action="{{ route('bms.destroy', $bm->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa BM này và toàn bộ danh sách tài khoản liên kết?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-medium rounded-lg transition">
                                    <i class="fa-solid fa-trash-can mr-1.5"></i> Xóa BM
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Ad Accounts Table -->
                    @if($bm->adAccounts->isEmpty())
                        <div class="p-6 text-center text-slate-500 text-sm">
                            Chưa có tài khoản quảng cáo nào được đồng bộ. Bấm <strong>Đồng bộ tài khoản</strong> ở trên để tải về từ Meta.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-100/70 text-slate-600 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="px-6 py-3 text-left">Tên tài khoản</th>
                                        <th class="px-6 py-3 text-left">Ad Account ID</th>
                                        <th class="px-6 py-3 text-left">Trạng thái</th>
                                        <th class="px-6 py-3 text-right">Đã chi tiêu</th>
                                        <th class="px-6 py-3 text-right">Số dư nợ</th>
                                        <th class="px-6 py-3 text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @foreach($bm->adAccounts as $acc)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-6 py-4 font-semibold text-slate-800">
                                                {{ $acc->name }}
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                                {{ $acc->ad_account_id }}
                                            </td>
                                            <td class="px-6 py-4">
                                                @if($acc->account_status == 1)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Hoạt động
                                                    </span>
                                                @elseif($acc->account_status == 2)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-rose-500 rounded-full"></span> Vô hiệu hóa
                                                    </span>
                                                @elseif($acc->account_status == 3)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                        <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full"></span> Nợ tiền
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                        {{ $acc->status_label }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right font-medium text-slate-800">
                                                {{ number_format($acc->amount_spent, 0, ',', '.') }} {{ $acc->currency }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-medium {{ $acc->balance > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                                {{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition">
                                                    <i class="fa-solid fa-list-check mr-1.5"></i> Quản lý Chiến dịch
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
            </div>
        </div>
    @endif

    <!-- Tài sản khác: Tài khoản cá nhân (Độc lập, tách riêng khỏi BM) -->
    @if(isset($personalBm) && $personalBm->adAccounts->isNotEmpty())
        <div class="space-y-4 pt-4 border-t border-slate-200">
            <div class="flex items-center space-x-2 text-xs font-bold text-indigo-700 uppercase tracking-wider">
                <i class="fa-solid fa-layer-group text-indigo-500"></i>
                <span>Tài sản khác (Tài khoản cá nhân)</span>
            </div>

            <div class="bg-white rounded-xl border border-indigo-200 shadow-sm overflow-hidden">
                <!-- Header -->
                <div class="bg-indigo-50/60 px-6 py-4 border-b border-indigo-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('personal-accounts.index') }}" class="text-lg font-bold text-slate-900 hover:text-indigo-600 transition">
                                    {{ $personalBm->name }}
                                </a>
                                <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded font-mono font-semibold">Tài sản cá nhân</span>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center gap-3 mt-0.5">
                                <span><i class="fa-solid fa-rectangle-ad mr-1 text-indigo-500"></i> {{ $personalBm->adAccounts->count() }} tài khoản</span>
                                <span>&bull;</span>
                                <span><i class="fa-solid fa-clock mr-1"></i> Cập nhật: {{ $personalBm->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('personal-accounts.index') }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg border border-indigo-200 transition">
                            <i class="fa-solid fa-id-badge mr-1.5"></i> Quản lý TK Cá nhân
                        </a>

                        <!-- Sync Button -->
                        <form action="{{ route('personal-accounts.sync') }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg shadow-sm transition">
                                <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ tài khoản
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Ad Accounts Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-indigo-50/30 text-slate-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-6 py-3 text-left">Tên tài khoản</th>
                                <th class="px-6 py-3 text-left">Ad Account ID</th>
                                <th class="px-6 py-3 text-left">Trạng thái</th>
                                <th class="px-6 py-3 text-right">Đã chi tiêu</th>
                                <th class="px-6 py-3 text-right">Số dư nợ</th>
                                <th class="px-6 py-3 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach($personalBm->adAccounts as $acc)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-800">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">
                                                <i class="fa-solid fa-user"></i>
                                            </span>
                                            <span>{{ $acc->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                        {{ $acc->ad_account_id }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($acc->account_status == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Hoạt động
                                            </span>
                                        @elseif($acc->account_status == 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-rose-500 rounded-full"></span> Vô hiệu hóa
                                            </span>
                                        @elseif($acc->account_status == 3)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full"></span> Nợ tiền
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $acc->status_label }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-800">
                                        {{ number_format($acc->amount_spent, 0, ',', '.') }} {{ $acc->currency }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium {{ $acc->balance > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                        {{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition">
                                            <i class="fa-solid fa-list-check mr-1.5"></i> Quản lý Chiến dịch
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
