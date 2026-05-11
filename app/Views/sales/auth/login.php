<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso vendedores - MAVILUSA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:420px; margin-top:80px;">
    <div class="card shadow">
        <div class="card-body p-4">
            <h4 class="text-center mb-4">Acceso vendedores</h4>
            <?php if (session('error')): ?>
                <div class="alert alert-danger"><?= esc(session('error')) ?></div>
            <?php endif; ?>
            <form method="post" action="/sales/login">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
