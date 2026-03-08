<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($tenant['name']) ?> | Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-color: <?= esc($website['primary_color']) ?>; }
        .text-primary { color: var(--primary-color) !important; }
        .btn-primary { background-color: var(--primary-color) !important; border: none; }
        body { background-color: #fff; font-family: 'Georgia', serif; } /* Letra elegante */
    </style>
</head>
<body>
<?php
// Atrapamos el código de la URL si existe (ej. ?ref=PROMO10)
$agentRef = isset($_GET['ref']) ? esc($_GET['ref']) : '';
?>
<div class="container py-5 text-center">
    <h1 class="display-3 text-primary mb-3"><?= esc($tenant['name']) ?></h1>
    <p class="lead text-muted mb-5"><?= esc($website['hero_subtitle']) ?></p>

    <h3 class="mb-4 border-bottom pb-2 d-inline-block">Nuestras Habitaciones</h3>
    <div class="row justify-content-center mt-4">
        <?php foreach($units as $unit): ?>
            <div class="col-md-4 mb-4">
                <div class="card border border-light shadow-sm">
                    <div class="card-body py-5">
                        <h4 class="text-primary"><?= esc($unit['name']) ?></h4>
                        <p class="text-muted">Max: <?= $unit['max_occupancy'] ?? 2 ?> personas</p>
                        <button class="btn btn-outline-dark mt-3">Reservar</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>