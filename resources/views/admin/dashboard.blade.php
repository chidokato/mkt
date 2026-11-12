@extends('admin.layout')

@section('title', 'Bảng điều khiển Quản trị viên - Meta Ads Hub')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800/60 p-6 rounded-2xl border border-slate-700/60">
        <div>
            <div class="flex items-center space-x-2 text-xs text-amber-400 font-semibold mb-1">
                <i class="fa-solid fa-crown"></i>
                <span>TRUNG TÂM KIỂM SOÁT HỆ THỐNG</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Xin chào, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-slate-400 mt-1">Theo dõi hoạt động của tất cả người dùng và tài khoản quảng cáo trên toàn bộ hệ thống.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bms.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition">
                <i class="fa-solid fa-briefcase mr-2 text-amber-400"></i> Quản lý BM
            </a>
            <a href="{{ route('admin.users') }}" 
               class="inline-flex items-center px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold rounded-xl border border-slate-600 transition">
                <i class="fa-solid fa-users mr-2 text-amber-400"></i> Quản lý Người dùng
            </a>
            <a href="{{ route('admin.settings') }}" 
               class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-amber-500/10">
                <i class="fa-solid fa-gears mr-2"></i> Cấu hình Meta API
            </a>
        </div>
    </div>

    <!-- Platform Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Người dùng (Users)</div>
            <div class="text-2xl font-bold text-white mt-1">{{ $stats['total_users'] }}</div>
        </div>
        <a href="{{ route('admin.bms.index') }}" class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60 hover:border-amber-500/40 transition group block">
            <div class="flex items-center justify-between">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider group-hover:text-amber-400 transition">Tổng BM kết nối</div>
                <i class="fa-solid fa-arrow-right text-[10px] text-slate-500 group-hover:text-amber-400 transition"></i>
            </div>
            <div class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['total_bms'] }}</div>
        </a>
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng Ad Accounts</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['total_accounts'] }}</div>
        </div>
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tài khoản Active</div>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['active_accounts'] }}</div>
        </div>
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Vô hiệu hóa (Die)</div>
            <div class="text-2xl font-bold text-rose-400 mt-1">{{ $stats['disabled_accounts'] }}</div>
        </div>
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng chi tiêu nền tảng</div>
            <div class="text-lg font-bold text-purple-400 mt-1">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-lg">
        <div class="px-6 py-4 border-b border-slate-700/60 flex justify-between items-center bg-slate-800">
            <h2 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-users-gear text-amber-400 mr-2"></i> Người dùng tham gia gần đây
            </h2>
            <a href="{{ route('admin.users') }}" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
                Xem toàn bộ &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60 text-sm">
                <thead class="bg-slate-900/60 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Người dùng</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-center">Vai trò</th>
                        <th class="px-6 py-3 text-center">Số BM kết nối</th>
                        <th class="px-6 py-3 text-center">Số Ad Accounts</th>
                        <th class="px-6 py-3 text-right">Ngày tham gia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 bg-slate-800/40">
                    @foreach($recentUsers as $user)
                        <tr class="hover:bg-slate-800 transition">
                            <td class="px-6 py-4 font-semibold text-white flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center font-bold text-xs text-amber-400">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <span>{{ $user->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-300 font-mono text-xs">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                        ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                        USER
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-amber-400">
                                {{ $user->business_managers_count }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-blue-400">
                                {{ $user->ad_accounts_count }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-400">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
