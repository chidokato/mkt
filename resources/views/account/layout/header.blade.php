<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
  <div class="navbar-nav-wrap">
    <div class="navbar-brand-wrapper">
      <!-- Logo -->
      <a class="navbar-brand" href="{{asset('')}}" aria-label="Front">
        <img class="navbar-brand-logo" src="account/img/logo/logo.svg" alt="Logo">
            <img class="navbar-brand-logo-mini" src="account/img/logo/logomini.svg" alt="Logo">
      </a>
      <!-- End Logo -->
    </div>

    <div class="navbar-nav-wrap-content-left">
      <!-- Navbar Vertical Toggle -->
      <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="Collapse"></i>
        <i class="tio-last-page navbar-vertical-aside-toggle-full-align" data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-toggle="tooltip" data-placement="right" title="Expand"></i>
      </button>
      <!-- End Navbar Vertical Toggle -->
    </div>

    <!-- Secondary Content -->
    <div class="navbar-nav-wrap-content-right">
      <!-- Navbar -->
      <ul class="navbar-nav align-items-center flex-row">
        <li class="mr-3">
        <div class="view-balance">
          <span id="menuBalance">{{ number_format(Auth::user()->wallet?->balance ?? 0, 0, ',', '.') }}</span>đ
          <!-- <span id="menuHeld">{{ number_format(Auth::user()->wallet?->held_balance ?? 0, 0, ',', '.') }}</span> đ -->

        </div>
      </li>

        <li class="nav-item">
          <!-- Account -->
          <div class="hs-unfold">
            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;" data-hs-unfold-options='{
                 "target": "#accountNavbarDropdown",
                 "type": "css-animation"
               }'>
              <div class="avatar avatar-sm avatar-circle">
                <img class="avatar-img" src="{{ Auth::user()->avatar }}" alt="Image Description">
                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
              </div>
            </a>

            <div id="accountNavbarDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account" style="width: 16rem;">
              <div class="dropdown-item-text">
                <div class="media align-items-center">
                  <div class="avatar avatar-sm avatar-circle mr-2">
                    <img class="avatar-img" src="{{ Auth::user()->avatar }}" alt="Image Description">
                  </div>
                  <div class="media-body">
                    <span class="card-title h5">{{ Auth::user()->yourname }}</span>
                    <span class="card-text">{{ Auth::user()->email }}</span>
                  </div>
                </div>
              </div>

              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="{{ route('account.profile.edit') }}">
                <span class="text-truncate pr-2" title="Profile &amp; account">Profile &amp; account</span>
              </a>

              <a class="dropdown-item" href="{{ route('account.profile.edit') }}">
                <span class="text-truncate pr-2" title="Settings">Settings</span>
              </a>

              <div class="dropdown-divider"></div>

              <a class="dropdown-item" href="{{asset('')}}logout">
                <span class="text-truncate pr-2" title="Sign out">Sign out</span>
              </a>
            </div>
          </div>
          <!-- End Account -->
        </li>
      </ul>
      <!-- End Navbar -->
    </div>
    <!-- End Secondary Content -->
  </div>
</header>