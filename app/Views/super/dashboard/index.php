<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">PMS - SuperAdmin Panel</span>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Hola, <?= esc($adminName) ?></span>
            <a href="<?= base_url('/super/logout') ?>" class="btn btn-sm btn-outline-light">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">¡Autenticación Aislada Exitosa!</h4>
        <p>Has ingresado al panel seguro del SuperAdmin. Esta área está completamente separada de los usuarios de los hoteles.</p>
    </div>
</div>

</body>
</html>