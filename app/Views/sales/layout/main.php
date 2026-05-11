<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Sales') ?> - MAVILUSA</title>

    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f8f9fa}</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="/sales/dashboard">MAVILUSA Sales</a>
    <div class="d-flex gap-3 text-white align-items-center">
        <a href="/sales/dashboard" class="text-white text-decoration-none">Dashboard</a>
        <a href="/sales/leads" class="text-white text-decoration-none">Pipeline</a>
        <span class="text-light">|</span>
        <span><?= esc(session('sales_user_name')) ?> (<?= esc(session('sales_user_role')) ?>)</span>
        <a href="/sales/logout" class="btn btn-sm btn-outline-light">Salir</a>
    </div>
</nav>

<div class="container-fluid p-4">
    <?php if (session('success')): ?>
        <div class="alert alert-success"><?= esc(session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('error')): ?>
        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
