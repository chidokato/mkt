@extends('account.layout.index')

@section('title') Công Ty Cổ Phần Bất Động Sản Indochine @endsection

@section('css')
<link href="admin_asset/select2/css/select2.min.css" rel="stylesheet">
@endsection

@section('body') data-offset="80" data-hs-scrollspy-options='{"target": "#navbarSettings"}' @endsection

@section('content')

<div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
          <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-no-gutter">
                  <li class="breadcrumb-item"><a class="breadcrumb-link" href="javascript:;">Account</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
              </nav>

              <h1 class="page-header-title">Settings</h1>
            </div>

            <div class="col-sm-auto">
              <a class="btn btn-primary" href="profile.php">
                <i class="tio-user mr-1"></i> My profile
              </a>
            </div>
          </div>
          <!-- End Row -->
        </div>
        <!-- End Page Header -->

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
                      <i class="tio-user-outlined nav-icon"></i> Basic information
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#emailSection">
                      <i class="tio-online nav-icon"></i> Email
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" href="#passwordSection">
                      <i class="tio-lock-outlined nav-icon"></i> Password
                    </a>
                  </li>
                
                  <li class="nav-item">
                    <a class="nav-link" href="#deleteAccountSection">
                      <i class="tio-delete-outlined nav-icon"></i> Delete account
                    </a>
                  </li>
                </ul>
                <!-- End Navbar Nav -->
              </div>
            </div>
            <!-- End Navbar -->
          </div>

          <div class="col-lg-9">
            <!-- Card -->
            <div class="card mb-3 mb-lg-5">
              <!-- Profile Cover -->
              <div class="profile-cover">
                <div class="profile-cover-img-wrapper">
                  <img id="profileCoverImg" class="profile-cover-img" src="account/img\1920x400\img2.jpg" alt="Image Description">

                  <!-- Custom File Cover -->
                  <!-- <div class="profile-cover-content profile-cover-btn">
                    <div class="custom-file-btn">
                      <input type="file" class="js-file-attach custom-file-btn-input" id="profileCoverUplaoder" data-hs-file-attach-options='{
                                "textTarget": "#profileCoverImg",
                                "mode": "image",
                                "targetAttr": "src",
                                "allowTypes": [".png", ".jpeg", ".jpg"]
                             }'>
                      <label class="custom-file-btn-label btn btn-sm btn-white" for="profileCoverUplaoder">
                        <i class="tio-add-photo mr-sm-1"></i>
                        <span class="d-none d-sm-inline-block">Update your header</span>
                      </label>
                    </div>
                  </div> -->
                  <!-- End Custom File Cover -->
                </div>
              </div>
              <!-- End Profile Cover -->

              <!-- Avatar -->
              <label class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar" for="avatarUploader">
                <img id="avatarImg" class="avatar-img" src="{{ Auth::user()->avatar }}" alt="Image Description">

                <!-- <input type="file" class="js-file-attach avatar-uploader-input" id="avatarUploader" data-hs-file-attach-options='{
                          "textTarget": "#avatarImg",
                          "mode": "image",
                          "targetAttr": "src",
                          "allowTypes": [".png", ".jpeg", ".jpg"]
                       }'>

                <span class="avatar-uploader-trigger">
                  <i class="tio-edit avatar-uploader-icon shadow-soft"></i>
                </span> -->
              </label>
              <!-- End Avatar -->

              <!-- Body -->
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-5">
                    <!-- <span class="d-block font-size-sm mb-2">Who can see your profile photo? <i class="tio-help-outlined" data-toggle="tooltip" data-placement="top" title="Your visibility setting only applies to your profile photo. Your header image is always visible to anyone."></i></span> -->

                    <!-- Select -->
                    <!-- <div class="select2-custom">
                      <select class="js-select2-custom custom-select" size="1" style="opacity: 0;" data-hs-select2-options='{
                                "minimumResultsForSearch": "Infinity"
                              }'>
                        <option value="privacy1" data-option-template='<span class="media"><i class="tio-earth-east tio-lg text-body mr-2" style="margin-top: .125rem;"></i><span class="media-body"><span class="d-block">Anyone</span><small class="select2-custom-hide">Visible to anyone who can view your content. Accessible by installed apps.</small></span></span>'>Anyone</option>
                        <option value="privacy2" data-option-template='<span class="media"><i class="tio-lock-outlined tio-lg text-body mr-2" style="margin-top: .125rem;"></i><span class="media-body"><span class="d-block">Only you</span><small class="select2-custom-hide">Only visible to you.</small></span></span>'>Only you</option>
                      </select>
                     </div> -->
                    <!-- End Select -->
                   </div>
                </div>
                <!-- End Row -->
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h2 class="card-title h4">Thông tin cá nhân</h2>
              </div>

              <!-- Body -->
              <div class="card-body">
                <p class="">Nhóm / Sàn chỉ thay đổi được 1 lần. Vui lòng kiểm tra kỹ trước khi cập nhật !!!</p>
                <p class=""><i>Lỗi hoặc không thực hiện được xin liên hệ: 0977572947 (zalo)</i></p>
                <!-- Form -->
                <form action="{{ route('account.update') }}" method="POST">
                    @csrf
                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="firstNameLabel" class="col-sm-3 col-form-label input-label">Mã nhân viên</label>

                    <div class="col-sm-9">
                      <div class="input-group input-group-sm-down-break">
                        <input required type="text" name="employee_code" class="form-control" value="{{ $user->employee_code }}" placeholder="VD: IDC0116">
                      </div>
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="firstNameLabel" class="col-sm-3 col-form-label input-label">Họ và Tên <i class="tio-help-outlined text-body ml-1" data-toggle="tooltip" data-placement="top" title="Displayed on public forums, such as Front."></i></label>

                    <div class="col-sm-9">
                      <div class="input-group input-group-sm-down-break">
                        <input required type="text" name="yourname" class="form-control" value="{{ $user->yourname }}" placeholder="Họ & Tên">
                      </div>
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="phoneLabel" class="col-sm-3 col-form-label input-label">Số điện thoại</label>

                    <div class="col-sm-9">
                      <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="Số điện thoại">
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="organizationLabel" class="col-sm-3 col-form-label input-label">Nhóm / Sàn</label>

                    <div class="col-sm-9">
                      <select class="select2" {{ $user->department_id? 'disabled':'' }} class="form-control" name="department_id" required>
                        <option value="">---</option>
                        {!! $departmentOptions !!}
                    </select>
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="addressLine2Label" class="col-sm-3 col-form-label input-label"> Địa chỉ </label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="address" value="{{ $user->address }}" placeholder="Địa chỉ">
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                  </div>
                </form>
                <!-- End Form -->
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div id="emailSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h3 class="card-title h4">Email</h3>
              </div>

              <!-- Body -->
              <div class="card-body">
                <p>Email chính của bạn là <span class="font-weight-bold">{{ $user->email }}</span></p>

                <!-- Form -->
                <form id="changeEmailForm" action="{{ route('account.update-secondary-email') }}" method="POST">
                  @csrf
                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="secondaryEmailInput" class="col-sm-3 col-form-label input-label">Email phụ</label>

                    <div class="col-sm-9">
                      <input type="email" id="secondaryEmailInput" name="secondary_email" class="form-control" value="{{ $user->secondary_email }}" placeholder="Nhập email phụ (có thể dùng để đăng nhập)">
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group OTP (Hidden by default) -->
                  <div class="row form-group" id="otpGroup" style="display: none;">
                    <label for="otpInput" class="col-sm-3 col-form-label input-label">Mã OTP</label>

                    <div class="col-sm-9">
                      <input type="text" id="otpInput" name="otp" class="form-control" placeholder="Nhập mã OTP 6 số từ email">
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <div class="d-flex justify-content-end">
                    <button type="submit" id="btnSubmitEmail" class="btn btn-primary">Lưu Email Phụ</button>
                  </div>
                </form>
                <!-- End Form -->
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Card -->
            <div id="passwordSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h4 class="card-title">Change your password</h4>
              </div>

              <!-- Body -->
              <div class="card-body">
                <!-- Form -->
                <form id="changePasswordForm" action="{{ route('account.change-password') }}" method="POST">
                  @csrf

                  @if(Auth::user()->password)
                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="currentPasswordLabel" class="col-sm-3 col-form-label input-label">Mật khẩu hiện tại</label>

                    <div class="col-sm-9">
                      <input type="password" class="form-control" name="currentPassword" id="currentPasswordLabel" placeholder="Nhập mật khẩu hiện tại" aria-label="Nhập mật khẩu hiện tại">
                    </div>
                  </div>
                  <!-- End Form Group -->
                  @endif

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="newPassword" class="col-sm-3 col-form-label input-label">Mật khẩu mới</label>

                    <div class="col-sm-9">
                      <input type="password" class="js-pwstrength form-control" name="newPassword" id="newPassword" placeholder="Nhập mật khẩu mới" aria-label="Nhập mật khẩu mới" data-hs-pwstrength-options='{
                               "ui": {
                                 "container": "#changePasswordForm",
                                 "viewports": {
                                   "progress": "#passwordStrengthProgress",
                                   "verdict": "#passwordStrengthVerdict"
                                 }
                               }
                             }'>

                      <p id="passwordStrengthVerdict" class="form-text mb-2"></p>

                      <div id="passwordStrengthProgress"></div>
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <!-- Form Group -->
                  <div class="row form-group">
                    <label for="confirmNewPasswordLabel" class="col-sm-3 col-form-label input-label">Nhập lại mật khẩu mới</label>

                    <div class="col-sm-9">
                      <div class="mb-3">
                        <input type="password" class="form-control" name="confirmNewPassword" id="confirmNewPasswordLabel" placeholder="Xác nhận mật khẩu mới" aria-label="Xác nhận mật khẩu mới">
                      </div>

                      <h5>Yêu cầu mật khẩu:</h5>

                      <p class="font-size-sm mb-2">Đảm bảo mật khẩu đáp ứng các điều kiện sau:</p>

                      <ul class="font-size-sm">
                        <li>Ít nhất 6 ký tự</li>
                      </ul>
                    </div>
                  </div>
                  <!-- End Form Group -->

                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                  </div>
                </form>
                <!-- End Form -->
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            


            <!-- Card -->
            <div id="deleteAccountSection" class="card mb-3 mb-lg-5">
              <div class="card-header">
                <h4 class="card-title">Delete your account</h4>
              </div>

              <!-- Body -->
              <div class="card-body">
                <p class="card-text">When you delete your account, you lose access to Front account services, and we permanently delete your personal data. You can cancel the deletion for 14 days.</p>

                <div class="form-group">
                  <!-- Custom Checkbox -->
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="deleteAccountCheckbox">
                    <label class="custom-control-label" for="deleteAccountCheckbox">Confirm that I want to delete my account.</label>
                  </div>
                  <!-- End Custom Checkbox -->
                </div>

                <div class="d-flex justify-content-end">

                  <!-- <button type="submit" class="btn btn-danger">Delete</button> -->
                </div>
              </div>
              <!-- End Body -->
            </div>
            <!-- End Card -->

            <!-- Sticky Block End Point -->
            <div id="stickyBlockEndPoint"></div>
          </div>
        </div>
        <!-- End Row -->
      </div>
      <!-- End Content -->

@endsection


@section('js')

<!-- select2 multiple JavaScript -->
<script src="admin_asset/select2/js/select2.min.js"></script>
<script src="admin_asset/select2/js/select2-searchInputPlaceholder.js"></script>
<script type="text/javascript">
    // $(document).ready(function() { $('.select2').select2({ placeholder: '...'}); });
    $(document).ready(function() { $('.select2').select2({ searchInputPlaceholder: '...' }); });
</script>

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

    <script>
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: response.message,
                        position: 'center',
                        confirmButtonText: 'OK',
                        backdrop: true
                    });
                    form[0].reset();
                } else {
                    showCenterError(response.message);
                }
            },
            error: function(xhr) {
                showCenterError('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    });

    $('#changeEmailForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var btn = $('#btnSubmitEmail');
        var originalBtnText = btn.text();
        btn.prop('disabled', true).text('Đang xử lý...');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(response) {
                btn.prop('disabled', false).text(originalBtnText);
                if (response.status) {
                    if (response.step === 'otp') {
                        form.attr('action', '{{ route("account.verify-secondary-email-otp") }}');
                        $('#secondaryEmailInput').attr('readonly', true);
                        $('#otpGroup').show();
                        $('#btnSubmitEmail').text('Xác nhận OTP');
                        Swal.fire({
                            icon: 'info',
                            title: 'Nhập mã OTP',
                            text: response.message,
                            position: 'center',
                            confirmButtonText: 'OK',
                            backdrop: true
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: response.message,
                            position: 'center',
                            confirmButtonText: 'OK',
                            backdrop: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }
                } else {
                    showCenterError(response.message);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).text(originalBtnText);
                showCenterError('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        });
    });
    </script>

@endsection