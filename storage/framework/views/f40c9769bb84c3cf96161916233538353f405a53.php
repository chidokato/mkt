<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="<?php echo $__env->yieldContent('robots', 'noindex, nofollow'); ?>">
    <meta name="description" content="<?php echo $__env->yieldContent('description', 'Hệ thống nội bộ MKT - Indochine Real Estate'); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <base href="<?php echo e(asset('')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Indochine MKT'); ?></title>
    <link rel="shortcut icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('account/css/vendor.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('account/css/theme.min.css?v=1.0')); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <?php echo $__env->yieldContent('css'); ?>
</head>
<body class="bg-light">
    <main id="content" role="main" class="main">
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <script src="<?php echo e(asset('account/js/vendor.min.js')); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('js'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\www\mkt\resources\views/layout/index.blade.php ENDPATH**/ ?>