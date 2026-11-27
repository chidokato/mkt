<div class="users">
    <div><img src="{{ Auth::user()->avatar }}" alt="user"></div>
    <div>
        <h4 class="text-truncate-set text-truncate-set-1">{{ Auth::user()->yourname }}</h4>
        <p class="text-truncate-set text-truncate-set-1">{{ Auth::user()->email }}</p>
        <p>{{Auth::user()->phone}}</p>
    </div>
    <button class="edit" onclick="window.location.href='{{ route('account.profile.edit') }}'">sửa</button>
</div>
<div class="blance widget-list mb-3">
    <h4>Số dự tài khoản</h4>
    <ul>
        <li>
            <span>Tiền hiện có:</span>
            <span><strong>{{ number_format(Auth::user()->wallet?->balance ?? 0, 0, ',', '.') }}</strong></span>
        </li>
        <li>
            <span>Tiền tạm giữ:</span>
            <span><strong>{{ number_format(Auth::user()->wallet?->held_balance, 0, ',', '.') }}</strong></span>
        </li>
    </ul>
    <a href="{{ route('wallet.deposit.form') }}" class="btn btn-deposit">
        <i class="bi bi-wallet2 me-2"></i>
        Nạp tiền
    </a>
</div>


<div class="row-btn">
    <button class="btn btn-dangky" onclick="window.location.href='{{route('account.marketing.register')}}'">
        <i class="bi bi-megaphone-fill"></i> ĐĂNG KÝ MARKETING
    </button>

    <button class="btn btn-dangky" onclick="window.location.href='{{route('wallet.index')}}'">
        <i class="bi bi-wallet2"></i> VÍ TIỀN
    </button>

    <button class="btn btn-quanly" onclick="window.location.href='{{route('task.index')}}'">
        <i class="bi bi-kanban-fill"></i> QUẢN LÝ MARKETING
    </button>

    @if(Auth::User()->rank == 1)
        <button class="btn btn-quanly" onclick="window.location.href='{{route('report.index')}}'">
            <i class="bi bi-speedometer2"></i> QUẢN LÝ TỔNG
        </button>
    @endif
</div>

<div class="widget widget-list mb-3">
    <h4><span><i class="bi bi-headset me-2"></i>Liên hệ</span></h4>
    <ul>
        <li>Hỗ trợ kỹ thuật:
            <a href="https://zalo.me/0977572947" target="_blank">
                <i class="bi bi-chat-dots-fill"></i><strong>Nguyễn Tuấn</strong>
            </a>
        </li>

        <li>Hỗ trợ đóng tiền MKT:
            <a href="https://zalo.me/0977572947" target="_blank">
                <i class="bi bi-cash-stack"></i><strong>Hằng Phan</strong>
            </a>
        </li>

        <li>Hỗ trợ đăng ký MKT:
            <a href="https://zalo.me/0977572947" target="_blank">
                <i class="bi bi-person-plus-fill"></i><strong>Tống Hồ Phương Thúy</strong>
            </a>
        </li>

        <li>Hỗ trợ nạp tiền bds.com:
            <a href="https://zalo.me/09775729047" target="_blank">
                <i class="bi bi-chat-dots-fill"></i><strong>Tống Hồ Phương Thúy</strong>
            </a>
            <a href="https://zalo.me/0977572947" target="_blank">
                <i class="bi bi-chat-dots-fill"></i><strong>Nguyễn Tuấn</strong>
            </a>
        </li>
    </ul>
</div>
