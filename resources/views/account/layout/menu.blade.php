<div id="sidebarMain">
  <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered default navbar-vertical-aside-initialized ">
  <div class="navbar-vertical-container">
    <div class="navbar-vertical-footer-offset">
      <div class="navbar-brand-wrapper justify-content-between">
        <!-- Logo -->
          <a class="navbar-brand" href="{{asset('')}}" aria-label="Front">
            <img class="navbar-brand-logo" src="account/img/logo/logo.svg" alt="Logo">
            <img class="navbar-brand-logo-mini" src="account/img/logo/logomini.svg" alt="Logo">
          </a>
        
        <!-- End Logo -->

        <!-- Navbar Vertical Toggle -->
        <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
          <i class="tio-clear tio-lg"></i>
        </button>
        <!-- End Navbar Vertical Toggle -->
      </div>

      <!-- Content -->
      <div class="navbar-vertical-content">
        <ul class="navbar-nav navbar-nav-lg nav-tabs">
          <!-- Dashboards -->
          <li class="navbar-vertical-aside-has-menu show">
            <a class="js-navbar-vertical-aside-menu-link nav-link active" href="./" title="Trang chủ">
              <i class="tio-home-vs-1-outlined nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Trang chủ</span>
            </a>
          </li>
          <!-- End Dashboards -->

          <li class="nav-item">
            <small class="nav-subtitle" title="Marketing">Marketing</small>
            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
          </li>

          <!-- Pages -->
          
          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="mkt-register" title="Đăng ký Marketing" data-placement="left">
              <i class="tio-add-circle nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Đăng ký Marketing</span>
            </a>
          </li>

          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="actualcosts" title="Quản lý Marketing" data-placement="left">
               <i class="tio-layers-outlined nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Quản lý Marketing</span>
            </a>
          </li>

          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="ns-debt" title="Danh sách nợ tiền" data-placement="left">
               <i class="tio-user-outlined nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Danh sách nợ tiền</span>
            </a>
          </li>

          
          @if(auth()->check() && in_array((int)auth()->user()->rank, [1], true))
          <li class="nav-item">
            <small class="nav-subtitle" title="Quản lý">Quản lý</small>
            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
          </li>
          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="report" title="Duyệt Marketing" data-placement="left">
              <i class="tio-checkmark-circle nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Duyệt Marketing</span>
            </a>
          </li>
          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="deposits" title="Duyệt đóng tiền" data-placement="left">
              <i class="tio-credit-card-outlined nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Duyệt đóng tiền</span>
            </a>
          </li>
          <li class="nav-item ">
            <a class="js-nav-tooltip-link nav-link " href="withdrawals" title="Duyệt rút tiền" data-placement="left">
              <i class="tio-money-vs nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Duyệt rút tiền</span>
            </a>
          </li>
          @endif

          @php
            $isWalletActive = request()->routeIs('wallet.*');
          @endphp
          <li class="navbar-vertical-aside-has-menu {{ $isWalletActive ? 'show' : '' }}">
            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle {{ $isWalletActive ? 'active' : 'collapsed' }}"
               href="javascript:;"
               data-toggle="collapse"
               data-target="#sidebarWalletMenu"
               aria-expanded="{{ $isWalletActive ? 'true' : 'false' }}"
               aria-controls="sidebarWalletMenu"
               title="Quản lý tiền">
              <i class="tio-wallet nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Quản lý tiền</span>
            </a>

            <ul id="sidebarWalletMenu" class="js-navbar-vertical-aside-submenu nav nav-sub collapse {{ $isWalletActive ? 'show' : '' }}">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('wallet.index') ? 'active' : '' }}" href="wallet" title="Ví tiền">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Ví tiền</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('wallet.deposit.*') ? 'active' : '' }}" href="wallet/deposit" title="Nạp tiền">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Nạp tiền</span>
                </a>
              </li>
              @if(auth()->check() && in_array((int)auth()->user()->rank, [1], true))
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('wallet.bulk.*') ? 'active' : '' }}" href="wallet/transfer" title="Chuyển tiền">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Chuyển tiền</span>
                </a>
              </li>
              @endif
            </ul>
          </li>

          <li class="navbar-vertical-aside-has-menu">
            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle collapsed"
               href="javascript:;"
               data-toggle="collapse"
               data-target="#sidebarInternalMenu"
               aria-expanded="false"
               aria-controls="sidebarInternalMenu"
               title="Nội bộ">
              <i class="tio-book-opened nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Nội bộ</span>
            </a>

            <ul id="sidebarInternalMenu" class="js-navbar-vertical-aside-submenu nav nav-sub collapse">
              <li class="nav-item">
                <a class="nav-link" href="{{ route('account.learning') }}" target="_blank" title="Học tập">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Học tập</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link"
                   href="{{ route('account.izi') }}"
                   @if(optional($setting)->izi_url) target="_blank" @else onclick="event.preventDefault(); if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'info', title: 'Tính năng đang được hoàn thiện', text: 'Vui lòng quay lại sau.' }); } else { alert('Tính năng đang được hoàn thiện. Vui lòng quay lại sau.'); }" @endif
                   title="HCC">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">HCC</span>
                </a>
              </li>
            </ul>
          </li>

          @if(auth()->check() && ((int)auth()->user()->rank === 1 || (int)auth()->user()->permission <= 2))
          @php
            $isConfigActive = request()->routeIs(
              'account.users.*',
              'admin.bulk_mail.*',
              'account.wallets',
              'wallets.*',
              'task_cost_period.*',
              'departments.*',
              'suppliers.*',
              'channels.*',
              'duan.*'
            );
          @endphp
          <li class="navbar-vertical-aside-has-menu {{ $isConfigActive ? 'show' : '' }}">
            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle {{ $isConfigActive ? 'active' : 'collapsed' }}"
               href="javascript:;"
               data-toggle="collapse"
               data-target="#sidebarConfigMenu"
               aria-expanded="{{ $isConfigActive ? 'true' : 'false' }}"
               aria-controls="sidebarConfigMenu"
               title="Cấu hình">
              <i class="tio-settings nav-icon"></i>
              <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Cấu hình</span>
            </a>

            <ul id="sidebarConfigMenu" class="js-navbar-vertical-aside-submenu nav nav-sub collapse {{ $isConfigActive ? 'show' : '' }}">
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('account.users.*') ? 'active' : '' }}" href="{{ route('account.users.index') }}" title="Người dùng">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Người dùng</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.bulk_mail.*') ? 'active' : '' }}" href="{{ route('admin.bulk_mail.create') }}" title="Gửi mail">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Gửi mail</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('account.wallets', 'wallets.*') ? 'active' : '' }}" href="{{ route('account.wallets') }}" title="Quản lý ví tiền">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Quản lý ví tiền</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('task_cost_period.*') ? 'active' : '' }}" href="{{ route('task_cost_period.index') }}" title="Thống kê">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Thống kê</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('duan.*') ? 'active' : '' }}" href="{{ route('duan.index') }}" title="Quản lý dự án">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Quản lý dự án</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}" title="Phòng ban">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Phòng ban</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}" title="Nhà cung cấp">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Nhà cung cấp</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('channels.*') ? 'active' : '' }}" href="{{ route('channels.index') }}" title="Kênh chạy">
                  <span class="tio-circle nav-indicator-icon"></span>
                  <span class="text-truncate">Kênh chạy</span>
                </a>
              </li>
            </ul>
          </li>
          @endif
        </ul>
      </div>
      <!-- End Content -->
    </div>
  </div>
</aside></div>
