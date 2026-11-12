@extends('layout')

@section('title', 'Tài khoản Quảng cáo Cá nhân - Meta Ads Hub')

@section('header_actions')
    <form action="{{ route('personal-accounts.sync') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
            <i class="fa-solid fa-arrows-rotate text-xs mr-1.5"></i> Đồng bộ tài khoản
        </button>
    </form>
    <a href="{{ route('bms.index') }}" 
       class="inline-flex items-center px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition whitespace-nowrap">
        <i class="fa-solid fa-briefcase text-xs mr-1.5 text-blue-400"></i> Xem danh sách BM &rarr;
    </a>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng TK Cá Nhân</span>
                <i class="fa-solid fa-id-badge text-indigo-500"></i>
            </div>
            <div class="text-2xl font-bold text-slate-900">{{ $stats['total_accounts'] }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Tài khoản riêng lẻ</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">TK Active</span>
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $stats['active_accounts'] }}</div>
            <div class="text-[11px] text-emerald-600 mt-1">Đang hoạt động</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">TK Vô hiệu hóa</span>
                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
            </div>
            <div class="text-2xl font-bold text-rose-600">{{ $stats['disabled_accounts'] }}</div>
            <div class="text-[11px] text-rose-500 mt-1">Bị khóa (Die)</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng Đã Tiêu</span>
                <i class="fa-solid fa-money-bill-wave text-indigo-500"></i>
            </div>
            <div class="text-lg font-bold text-indigo-600 truncate">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
            <div class="text-[11px] text-slate-500 mt-1">Toàn bộ chi tiêu</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Số Dư Nợ</span>
                <i class="fa-solid fa-file-invoice-dollar text-amber-500"></i>
            </div>
            <div class="text-lg font-bold {{ $stats['total_balance'] > 0 ? 'text-amber-600' : 'text-slate-700' }} truncate">
                {{ number_format($stats['total_balance'], 0, ',', '.') }} đ
            </div>
            <div class="text-[11px] text-slate-500 mt-1">Chờ thanh toán</div>
        </div>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 sm:px-6 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-user-tag text-indigo-600 mr-2"></i> Danh sách Tài khoản Quảng cáo Cá nhân
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tài sản khác &bull; Không thuộc quyền sở hữu của bất kỳ Doanh nghiệp (BM) nào.</p>
            </div>
        </div>

        @if($accounts->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto mb-3 text-xl">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="text-sm font-semibold text-slate-700">Chưa có tài khoản quảng cáo cá nhân nào</div>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto mb-4">
                    Nếu nick Facebook của bạn có tài khoản quảng cáo cá nhân, bấm "Đồng bộ tài khoản" để tải về.
                </p>
                <form action="{{ route('personal-accounts.sync') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        <i class="fa-solid fa-arrows-rotate mr-1.5"></i> Đồng bộ ngay
                    </button>
                </form>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="px-6 py-3.5 text-left">Tên tài khoản</th>
                            <th class="px-6 py-3.5 text-left">Ad Account ID</th>
                            <th class="px-6 py-3.5 text-left">Loại tài khoản</th>
                            <th class="px-6 py-3.5 text-left">Trạng thái</th>
                            <th class="px-6 py-3.5 text-center">Thanh toán</th>
                            <th class="px-6 py-3.5 text-right">Đã chi tiêu</th>
                            <th class="px-6 py-3.5 text-right">Số dư nợ</th>
                            <th class="px-6 py-3.5 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach($accounts as $acc)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-user"></i>
                                        </span>
                                        <span>{{ $acc->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                    <div class="flex items-center space-x-1.5">
                                        <span>{{ $acc->ad_account_id }}</span>
                                        <button type="button" onclick="copyToClipboard('{{ $acc->ad_account_id }}', 'Đã sao chép ID tài khoản QC!')" 
                                                class="text-slate-400 hover:text-blue-600" title="Sao chép">
                                            <i class="fa-regular fa-copy text-[11px]"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Tài sản khác (Cá nhân)
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($acc->account_status == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span> Hoạt động
                                        </span>
                                    @elseif($acc->account_status == 2)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-rose-500 rounded-full"></span> Vô hiệu hóa
                                        </span>
                                    @elseif($acc->account_status == 3)
                                        <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 transition group" 
                                           title="Bấm để đến trang thanh toán Facebook (Billing Hub)">
                                            <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full animate-pulse"></span> Nợ tiền
                                            <i class="fa-solid fa-arrow-up-right-from-square ml-1 text-[10px] text-amber-700"></i>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $acc->status_label }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if($acc->payment_method_display || $acc->card_last_four)
                                        <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                           class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-blue-50 text-slate-800 hover:text-blue-700 border border-slate-200 hover:border-blue-300 font-mono transition group"
                                           title="Thẻ thanh toán: {{ $acc->payment_method_display ?? ('*'.$acc->card_last_four) }} - Bấm để mở Billing Hub trên Facebook">
                                            <i class="fa-regular fa-credit-card text-blue-600 mr-1.5 text-xs group-hover:scale-110 transition-transform"></i>
                                            <span>{{ $acc->payment_method_display ?? ('*' . $acc->card_last_four) }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs italic" title="Chưa gắn thẻ thanh toán">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-slate-800">
                                    {{ number_format($acc->amount_spent, 0, ',', '.') }} {{ $acc->currency }}
                                </td>
                                <td class="px-6 py-4 text-right font-medium {{ $acc->account_status == 3 ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">
                                    @if($acc->account_status == 3)
                                        <div class="inline-flex items-center justify-end space-x-1">
                                            <span>{{ number_format($acc->balance, 0, ',', '.') }} {{ $acc->currency }}</span>
                                            <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="text-amber-600 hover:text-amber-800 p-0.5" title="Thanh toán nợ trên Facebook">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400">0 {{ $acc->currency }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center space-x-2">
                                        @if($acc->account_status == 3)
                                            <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-xs transition" 
                                               title="Chuyển đến trang thanh toán trên FB">
                                                <i class="fa-solid fa-credit-card mr-1.5"></i> Thanh toán
                                            </a>
                                        @else
                                            <a href="{{ $acc->billing_url }}" target="_blank" rel="noopener noreferrer" 
                                               class="inline-flex items-center px-2.5 py-1.5 bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg text-xs font-medium border border-slate-200 hover:border-amber-300 transition" 
                                               title="Cài đặt thanh toán trên FB">
                                                <i class="fa-regular fa-credit-card mr-1 text-slate-400"></i> Thanh toán
                                            </a>
                                        @endif
                                        <a href="{{ route('accounts.campaigns', $acc->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition">
                                            <i class="fa-solid fa-list-check mr-1.5"></i> Quản lý Chiến dịch
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
