<!DOCTYPE html>
<html lang="en">
<head>
<!-- Required Meta Tags Always Come First -->
<base href="{{asset('')}}">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Title -->
<title>Dashboard | Front - Admin &amp; Dashboard Template</title>

<!-- Favicon -->
<link rel="shortcut icon" href="favicon.ico">

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

<!-- CSS Implementing Plugins -->
<link rel="stylesheet" href="account/css/vendor.min.css">
<link rel="stylesheet" href="account/vendor/icon-set/style.css">

<!-- CSS Front Template -->
<link rel="stylesheet" href="account/css/theme.min.css?v=1.0">

<link rel="stylesheet" href="account/css/custom2.css">
<link rel="stylesheet" href="admin_asset/fontawesome-free/css/all.min.css">

@yield('css')

</head>

<body class="footer-offset has-navbar-vertical-aside navbar-vertical-aside-show-xl @yield('body')">

@include('account.layout.header')

@include('account.layout.menu')

<main id="content" role="main" class="main pointer-event bg-light">
@if($sumPrice > 0)
<div class="row-alert">
	<div>
		<p>Nợ tiền quảng cáo: <span class="price">{{ number_format((float)$sumPrice) }}</span></p>
		<p>Vui lòng thanh toán sớm để được đăng ký marketing kỳ tiếp theo</p>
	</div>
	<div>
		<a href="actualcosts?report_id=&handler_ids%5B%5D={{Auth::id()}}&approved=&paid=&settled=0&post_id=&department_id=&channel_id=&outstanding=1">
			<button type="button" class="btn">Xem chi tiết</button>
		</a>
	</div>
</div>
@endif

@yield('content')

@include('account.layout.footer')
</main>

@include('account.layout.popup')

<!-- JS Implementing Plugins -->
<script src="account/js/vendor.min.js"></script>
<script src="account/vendor/chart.js/dist/Chart.min.js"></script>
<script src="account/vendor/chart.js.extensions/chartjs-extensions.js"></script>
<script src="account/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>

<!-- JS Front -->
<script src="account/js/theme.min.js"></script>
<script src="account/js/custom.js"></script>

<!-- JS Plugins Init. -->
@yield('js')

@include('admin.alert')

<script>
document.addEventListener('click', function (event) {
    const link = event.target.closest('a');
    if (!link) return;

    const href = (link.getAttribute('href') || '').trim();
    if (!href) return;

    const isWithdrawLinkByClass = link.classList.contains('js-withdraw-guard');
    const isWithdrawLinkByPath = /(^|\/)account\/wallet\/withdraw(\?|$)/i.test(href);
    if (!isWithdrawLinkByClass && !isWithdrawLinkByPath) return;

    event.preventDefault();

    const goToWithdrawPage = function () {
        window.location.href = href;
    };

    if (typeof Swal === 'undefined') {
        if (window.confirm('Phòng kế toán chỉ duyệt những lệnh rút của các nhân sự nghỉ việc. Bạn có chắc là đã nghỉ việc?')) {
            goToWithdrawPage();
        }
        return;
    }

    Swal.fire({
        title: 'Xác nhận trước khi rút tiền',
        text: 'Phòng kế toán chỉ duyệt những lệnh rút của các nhân sự nghỉ việc',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Có. tôi đã nghỉ việc',
        cancelButtonText: 'Chưa !',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: true
    }).then(function (result) {
        if (result.isConfirmed) {
            goToWithdrawPage();
        }
    });
});
</script>

</body>
</html>
