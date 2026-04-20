<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PMS - <?= session('tenant_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-pastel.css') ?>">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url('/dashboard') ?>"><?= session('tenant_name') ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/dashboard') ?>">Recepción</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/inventory') ?>">Inventario</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/maintenance') ?>">Mantenimiento</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-bold text-white" href="#" id="navbarRates" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-currency-dollar"></i> Tarifas
                    </a>
                    <ul class="dropdown-menu shadow" aria-labelledby="navbarRates">
                        <li><a class="dropdown-item" href="<?= base_url('/rate-plans') ?>"><i class="bi bi-tags"></i> Planes Tarifarios</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('/rate-plans/matrix') ?>"><i class="bi bi-grid-3x3"></i> Matriz de Precios</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('/seasonal-rates') ?>"><i class="bi bi-calendar-event"></i> Temporadas Altas</a></li>
                    </ul>
                </li>

                <li class="nav-item"><a class="nav-link" href="<?= base_url('/reservations') ?>">Reservas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/products') ?>">Catálogo POS</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/reports') ?>"><i class="bi bi-bar-chart"></i> Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/promotions') ?>"><i class="bi bi-percent"></i> Promociones</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/agents') ?>"><i class="bi bi-briefcase"></i> Comisionistas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/commissions') ?>"><i class="bi bi-cash-coin"></i> Liquidar</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="<?= base_url('/website') ?>"><i class="bi bi-globe"></i> Mi Web</a></li>
                <li class="nav-item"><a class="nav-link ms-2" href="<?= base_url('/settings') ?>"><i class="bi bi-gear"></i></a></li>
            </ul>

            <div class="d-flex text-white align-items-center">
                <span class="me-3 small text-white-50"><?= session('user_name') ?></span>
                <a href="<?= base_url('/logout') ?>" class="btn btn-sm btn-outline-light border-0"><i class="bi bi-box-arrow-right"></i> Salir</a>
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