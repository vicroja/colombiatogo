<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PMS - <?= session('tenant_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url('/dashboard') ?>"><?= session('tenant_name') ?></a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/dashboard') ?>">Recepción</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/inventory') ?>">Inventario</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/reservations') ?>">Reservas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/settings') ?>">Configuración</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/products') ?>">Catálogo POS</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/reports') ?>"><i class="bi bi-bar-chart"></i> Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/promotions') ?>"><i class="bi bi-tags"></i> Promociones</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/agents') ?>"><i class="bi bi-briefcase"></i> Comisionistas</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="<?= base_url('/website') ?>"><i class="bi bi-globe"></i> Mi Página Web</a></li>
            </ul>
            <div class="d-flex text-white align-items-center">
                <span class="me-3"><?= session('user_name') ?></span>
                <a href="<?= base_url('/logout') ?>" class="btn btn-sm btn-light">Salir</a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>