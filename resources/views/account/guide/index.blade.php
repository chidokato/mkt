@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')

@endsection

@section('body') data-offset="80" data-hs-scrollspy-options='{"target": "#navbarSettings"}' @endsection

@section('content')

<div class="content container-fluid">
  <!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
    <div class="col-sm mb-2 mb-sm-0">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-no-gutter">
          <li class="breadcrumb-item"><a class="breadcrumb-link" href="./">Account</a></li>
          <li class="breadcrumb-item active" aria-current="page">Hướng dẫn sử dụng</li>
        </ol>
      </nav>

      <h1 class="page-header-title">Hướng dẫn sử dụng</h1>
    </div>

    </div>
    <!-- End Row -->
</div>

<div class="row">
          <div class="col-lg-3">
            <!-- Navbar -->
            <div class="navbar-vertical navbar-expand-lg mb-3 mb-lg-5">
              <!-- Navbar Toggle -->
              <button type="button" class="navbar-toggler btn btn-block btn-white mb-3" aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu" data-toggle="collapse" data-target="#navbarVerticalNavMenu">
                <span class="d-flex justify-content-between align-items-center">
                  <span class="h5 mb-0">Nav menu</span>

                  <span class="navbar-toggle-default">
                    <i class="tio-menu-hamburger"></i>
                  </span>

                  <span class="navbar-toggle-toggled">
                    <i class="tio-clear"></i>
                  </span>
                </span>
              </button>
              <!-- End Navbar Toggle -->

              <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                <!-- Navbar Nav -->
                <ul id="navbarSettings" class="js-sticky-block js-scrollspy navbar-nav navbar-nav-lg nav-tabs card card-navbar-nav" data-hs-sticky-block-options='{
                       "parentSelector": "#navbarVerticalNavMenu",
                       "breakpoint": "lg",
                       "startPoint": "#navbarVerticalNavMenu",
                       "endPoint": "#stickyBlockEndPoint",
                       "stickyOffsetTop": 20
                     }'>
                  <li class="nav-item">
                    <a class="nav-link active" href="#content">
                      - Đóng tiền marketing
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#emailSection">
                      - Quản lý ví tiền
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#passwordSection">
                      - Nạp tiền
                    </a>
                  </li>
                
                  @if(config('wallet.transfers_enabled', false))
                  <li class="nav-item">
                    <a class="nav-link" href="#deleteAccountSection">
                      - Chuyển tiền
                    </a>
                  </li>
                  @endif
                </ul>
                <!-- End Navbar Nav -->
              </div>
            </div>
            <!-- End Navbar -->
          </div>

          <div class="col-lg-9">
            <!-- Card -->
            <div class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h2 class="card-title h4">Đóng tiền marketing</h2>
              </div>

              <!-- Body -->
              <div class="card-body">
                <img class="w-100" src="account/img/qr/dongtienmkt.png">
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div id="emailSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h3 class="card-title h4">Quản lý ví tiền</h3>
              </div>

              <!-- Body -->
              <div class="card-body">
                <img class="w-100" src="account/img/qr/vitien.png">
                  
                <!-- End Form -->
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div id="passwordSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h4 class="card-title">Nạp tiền (dành cho quản lý)</h4>
              </div>

              <!-- Body -->
              <div class="card-body">
                <img class="w-100" src="account/img/qr/naptien.png">
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            


            <!-- Card -->
            @if(config('wallet.transfers_enabled', false))
            <div id="deleteAccountSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h4 class="card-title">Chuyển tiền (dành cho quản lý)</h4>
              </div>

              <!-- Body -->
              <div class="card-body">
                <img class="w-100" src="account/img/qr/chuyentien.png">
              </div>
              <!-- End Body -->
            </div>
            @endif
            <!-- End Card -->

            <!-- Sticky Block End Point -->
            <div id="stickyBlockEndPoint"></div>
          </div>
        </div>
        <!-- End Row -->
  
</div>
@endsection


@section('js')

<script>
      $(document).on('ready', function () {
        // INITIALIZATION OF STICKY BLOCKS
        // =======================================================
        $('.js-sticky-block').each(function () {
          var stickyBlock = new HSStickyBlock($(this), {
            targetSelector: $('#header').hasClass('navbar-fixed') ? '#header' : null
          }).init();
        });
        // INITIALIZATION OF SCROLL NAV
        // =======================================================
        var scrollspy = new HSScrollspy($('body'), {
          // !SETTING "resolve" PARAMETER AND RETURNING "resolve('completed')" IS REQUIRED
          beforeScroll: function(resolve) {
            if (window.innerWidth < 992) {
              $('#navbarVerticalNavMenu').collapse('hide').on('hidden.bs.collapse', function () {
                return resolve('completed');
              });
            } else {
              return resolve('completed');
            }
          }
        }).init();
      });
    </script>

@endsection
