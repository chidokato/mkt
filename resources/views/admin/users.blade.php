@extends('admin.layout')

@section('title', 'Quản lý Người dùng - Meta Ads Hub')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-amber-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span>&rsaquo;</span>
                <span class="text-white">Quản lý Người dùng</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Danh sách Người dùng trong Hệ thống</h1>
            <p class="text-sm text-slate-400 mt-1">Quản lý toàn bộ tài khoản thành viên và kiểm soát dữ liệu tài khoản quảng cáo.</p>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-lg">
        <div class="px-6 py-4 border-b border-slate-700/60 flex justify-between items-center bg-slate-800">
            <h2 class="text-sm font-bold text-white flex items-center">
                <i class="fa-solid fa-users text-amber-400 mr-2"></i> Tổng số: {{ $users->total() }} thành viên
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60 text-sm">
                <thead class="bg-slate-900/60 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3 text-left">Họ và Tên</th>
                        <th class="px-6 py-3 text-left">Email</th>
                        <th class="px-6 py-3 text-center">Vai trò</th>
                        <th class="px-6 py-3 text-center">Số BM sở hữu</th>
                        <th class="px-6 py-3 text-center">Số Ad Accounts</th>
                        <th class="px-6 py-3 text-left">Ngày tham gia</th>
                        <th class="px-6 py-3 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 bg-slate-800/40">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-800 transition">
                            <td class="px-6 py-4 font-semibold text-white flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center font-bold text-xs text-amber-400">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                                <div>
                                    <div>{{ $user->name }}</div>
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] bg-amber-500/20 text-amber-400 px-1.5 py-0.2 rounded font-semibold">Tài khoản của bạn</span>
                                    @endif
                                </div>
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
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng [{{ $user->name }}]? Toàn bộ tài khoản quảng cáo của họ cũng sẽ bị xóa khỏi hệ thống.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-xs font-semibold transition border border-rose-500/20">
                                            <i class="fa-solid fa-trash-can mr-1"></i> Xóa
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500 italic">Không khả dụng</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-700/60 bg-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
