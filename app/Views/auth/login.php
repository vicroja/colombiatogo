<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #e9ecef; }
        .login-container { max-width: 400px; margin-top: 8vh; }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="card shadow">
        <div class="card-body p-5">
            <h4 class="text-center mb-4">Ingreso de Personal</h4>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger text-center">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/login') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" required placeholder="tu@correo.com">
                </div>
                <div class="mb-4">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 py-2">Ingresar a Recepción</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>