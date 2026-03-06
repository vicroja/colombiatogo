<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - SuperAdmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('/super/dashboard') ?>">PMS Maestro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/super/dashboard') ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/super/tenants') ?>">Propiedades</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('/super/billing') ?>">Facturación</a>
                </li>

            </ul>
            <div class="d-flex align-items-center text-white">
                <span class="me-3"><?= session('superadmin_name') ?></span>
                <a href="<?= base_url('/super/logout') ?>" class="btn btn-sm btn-outline-danger">Salir</a>
            </div>
        </div>
    </div>
</nav>

<div class="container">
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>