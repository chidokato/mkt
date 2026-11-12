@extends('admin.layout')

@section('title', 'Quản lý Business Managers Toàn Hệ Thống - Meta Ads Hub')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800/60 p-6 rounded-2xl border border-slate-700/60">
        <div>
            <div class="flex items-center space-x-2 text-xs text-amber-400 font-semibold mb-1 uppercase tracking-wider">
                <i class="fa-solid fa-briefcase"></i>
                <span>QUẢN LÝ BM HỆ THỐNG</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Danh sách Business Managers Toàn Sàn</h1>
            <p class="text-sm text-slate-400 mt-1">Giám sát toàn bộ các Business Manager, System User Token và tài khoản quảng cáo của tất cả người dùng.</p>
        </div>
        <div class="flex items-center gap-3">
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

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng BM kết nối</div>
            <div class="text-2xl font-bold text-amber-400 mt-1">{{ $stats['total_bms'] }}</div>
        </div>

        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">BM Đang hoạt động</div>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['active_bms'] }}</div>
        </div>

        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng Ad Accounts</div>
            <div class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['total_accounts'] }}</div>
        </div>

        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tài khoản Active</div>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ $stats['active_accounts'] }}</div>
        </div>

        <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700/60">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng Chi Tiêu Sàn</div>
            <div class="text-lg font-bold text-purple-400 mt-1 truncate">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700/60 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form action="{{ route('admin.bms.index') }}" method="GET" class="flex flex-1 flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Tìm theo Tên BM, BM ID hoặc tên/email người dùng..." 
                       class="w-full pl-9 pr-4 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="w-full sm:w-56">
                <select name="user_id" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Tất cả người dùng --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-44">
                <select name="status" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động (Active)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Hết hạn / Lỗi</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl transition">
                Lọc
            </button>

            @if(request('search') || request('user_id') || request('status'))
                <a href="{{ route('admin.bms.index') }}" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs font-medium rounded-xl transition flex items-center justify-center">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Xóa lọc
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-400 font-medium whitespace-nowrap">
            Tổng cộng: <span class="font-bold text-amber-400">{{ $bms->total() }}</span> BM
        </div>
    </div>

    <!-- BM Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-700/60 text-sm">
                <thead class="bg-slate-900/60 text-slate-400 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5 text-left">Business Manager</th>
                        <th class="px-6 py-3.5 text-left">Người sở hữu</th>
                        <th class="px-6 py-3.5 text-center">Trạng thái</th>
                        <th class="px-6 py-3.5 text-center">Số Ad Accounts</th>
                        <th class="px-6 py-3.5 text-right">Chi tiêu BM</th>
                        <th class="px-6 py-3.5 text-center">Cập nhật</th>
                        <th class="px-6 py-3.5 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/40 bg-slate-800/40">
                    @forelse($bms as $bm)
                        <tr class="hover:bg-slate-800 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white text-sm">{{ $bm->name }}</div>
                                <div class="flex items-center space-x-2 mt-1">
                                    @if($bm->bm_id)
                                        <span class="text-[11px] font-mono text-slate-400 bg-slate-900 px-2 py-0.5 rounded border border-slate-700">
                                            ID: {{ $bm->bm_id }}
                                        </span>
                                    @endif
                                    @if($bm->is_oauth)
                                        <span class="text-[10px] bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-semibold border border-blue-500/30">OAuth</span>
                                    @else
                                        <span class="text-[10px] bg-purple-500/20 text-purple-400 px-1.5 py-0.5 rounded font-semibold border border-purple-500/30">System User</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($bm->user)
                                    <div class="font-semibold text-slate-200 text-xs">{{ $bm->user->name }}</div>
                                    <div class="text-[11px] font-mono text-slate-400">{{ $bm->user->email }}</div>
                                @else
                                    <span class="text-slate-500 italic text-xs">Vô chủ (User đã bị xóa)</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($bm->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 mr-1 bg-emerald-400 rounded-full"></span> ACTIVE
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        {{ strtoupper($bm->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="font-bold text-slate-200 text-sm">
                                    <span class="text-blue-400" title="TK của BM">{{ $bm->owned_accounts_count }} BM</span>
                                    <span class="text-slate-500">/</span>
                                    <span class="text-purple-400" title="TK được share">{{ $bm->client_accounts_count }} Share</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $bm->active_accounts_count }} active / {{ $bm->disabled_accounts_count }} die
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right font-medium text-slate-200 text-xs">
                                {{ number_format($bm->total_spent, 0, ',', '.') }} đ
                            </td>

                            <td class="px-6 py-4 text-center text-xs text-slate-400">
                                {{ $bm->updated_at->diffForHumans() }}
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- Sync -->
                                    <form action="{{ route('admin.bms.sync', $bm->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Đồng bộ lại tài khoản" 
                                                class="p-1.5 text-emerald-400 hover:bg-slate-700 rounded-lg transition">
                                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Check Token -->
                                    <form action="{{ route('admin.bms.checkToken', $bm->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" title="Kiểm tra Token với Meta" 
                                                class="p-1.5 text-blue-400 hover:bg-slate-700 rounded-lg transition">
                                            <i class="fa-solid fa-stethoscope text-xs"></i>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.bms.destroy', $bm->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa BM [{{ $bm->name }}] của user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Xóa BM" 
                                                class="p-1.5 text-rose-400 hover:bg-slate-700 rounded-lg transition">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm">
                                Không tìm thấy Business Manager nào trong hệ thống.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bms->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/60 bg-slate-900/60">
                {{ $bms->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
