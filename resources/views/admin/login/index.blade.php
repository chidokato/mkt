<!DOCTYPE html>
<html>
<head>
    <title></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" >
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.css" rel="stylesheet"/>
    <style type="text/css">
        .gradient-custom-2 {
        /* fallback for old browsers */
        background: #fccb90;

        /* Chrome 10-25, Safari 5.1-6 */
        background: -webkit-linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);

        /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        background: linear-gradient(to right, #ee7724, #d8363a, #dd3675, #b44593);
        }

        @media (min-width: 768px) {
        .gradient-form {
        height: 100vh !important;
        }
        }
        @media (min-width: 769px) {
        .gradient-custom-2 {
        border-top-right-radius: .3rem;
        border-bottom-right-radius: .3rem;
        }
        }
    </style>
</head>
<body>

<section class="h-100 gradient-form" style="background-color: #eee;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-xl-10">
        <div class="card rounded-3 text-black">
          <div class="row g-0">
            <div class="col-lg-6">
              <div class="card-body p-md-5 mx-md-4">

                <div class="text-center">
                  <h4 class="mt-1 mb-5 pb-1">Đăng nhập vào trang quản trị</h4>
                </div>
                @include('admin.alert')
                
                <form action="{{ url('admin') }}" method="POST">
                  @csrf
                  <p>Vui lòng đăng nhập bằng tài khoản của bạn</p>

                  <div class="form-outline mb-4">
                    <input type="email" name="email" id="email" class="form-control" placeholder="Địa chỉ email" required />
                    <label class="form-label" for="email">Tên đăng nhập</label>
                  </div>

                  <div class="form-outline mb-4">
                    <input type="password" name="password" id="password" class="form-control" required />
                    <label class="form-label" for="password">Mật khẩu</label>
                  </div>

                  <div class="form-check mb-4">
                      <input class="form-check-input" type="checkbox" name="remember" id="remember" />
                      <label class="form-check-label" for="remember"> Ghi nhớ đăng nhập </label>
                  </div>

                  <div class="text-center pt-1 mb-5 pb-1">
                    <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Đăng nhập</button>
                    <!-- <a class="text-muted" href="#!">Quên mật khẩu?</a> -->
                  </div>

                </form>

              </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
              <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                <a href="{{ route('google.redirect') }}">
                    <button class="btn btn-light btn-lg d-flex align-items-center shadow-sm border rounded-pill px-4 py-2">
                      <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google Logo" class="me-2" width="24" height="24">
                      <span class="">Sign in with Google</span>
                    </button>
                    </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" ></script>
<!-- MDB -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.js"
></script>
</body>
</html>