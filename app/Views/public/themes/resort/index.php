<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($tenant['name']) ?> | Reservas Oficiales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: <?= esc($website['primary_color']) ?>;
        }
        .text-primary { color: var(--primary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
        .btn-primary { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        .btn-primary:hover { filter: brightness(0.9); }
        .hero-section {
            background-color: #333;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?= !empty($media) ? base_url($media[0]['file_path']) : '' ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <?php if($tenant['logo_path']): ?>
                <img src="<?= base_url($tenant['logo_path']) ?>" alt="Logo" height="40" class="me-2">
            <?php endif; ?>
            <?= esc($tenant['name']) ?>
        </a>
        <div>
            <?php if($website['instagram_url']): ?>
                <a href="<?= esc($website['instagram_url']) ?>" target="_blank" class="text-dark fs-4 me-3"><i class="bi bi-instagram"></i></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<header class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3"><?= esc($website['hero_title']) ?: 'Bienvenido a ' . esc($tenant['name']) ?></h1>
        <p class="lead mb-4"><?= esc($website['hero_subtitle']) ?></p>
        <a href="#habitaciones" class="btn btn-primary btn-lg fw-bold px-5 rounded-pill shadow">Reservar Ahora</a>
    </div>
</header>

<div class="container my-5">

    <?php if($website['about_text']): ?>
        <div class="row justify-content-center mb-5 text-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-3">Nuestra Propiedad</h2>
                <p class="text-muted fs-5 lh-lg"><?= nl2br(esc($website['about_text'])) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <h2 id="habitaciones" class="fw-bold mb-4 border-bottom pb-2">Alojamiento y Reservas</h2>
    <div class="row g-4 mb-5">
        <?php foreach($units as $unit): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="bg-secondary text-white text-center py-5">
                        <i class="bi bi-house-door fs-1"></i>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-primary"><?= esc($unit['name']) ?></h5>

                        <p class="small text-muted mb-3"><i class="bi bi-people"></i> Capacidad: <?= $unit['max_occupancy'] ?? 4 ?> personas</p>

                        <button class="btn btn-outline-primary mt-auto fw-bold" data-bs-toggle="modal" data-bs-target="#bookModal<?= $unit['id'] ?>">
                            Seleccionar Fechas
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="bookModal<?= $unit['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content border-0 shadow">
                        <form action="<?= base_url('/book/'.$tenant['slug'].'/confirm') ?>" method="post">
                            <input type="hidden" name="unit_id" value="<?= $unit['id'] ?>">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Reservar <?= esc($unit['name']) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h6 class="fw-bold mb-3 border-bottom pb-2">1. Selecciona tus Fechas</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Check-in</label>
                                        <input type="date" name="check_in_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Check-out</label>
                                        <input type="date" name="check_out_date" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 border-bottom pb-2">2. Tus Datos Personales</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Nombre Completo</label>
                                        <input type="text" name="full_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Documento (ID / Pasaporte)</label>
                                        <input type="text" name="document" class="form-control" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small fw-bold">Adultos</label>

                                        <input type="number" name="adults" class="form-control" value="2" min="1" max="<?= $unit['max_occupancy'] ?? 4 ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small fw-bold">Teléfono / WhatsApp</label>
                                        <input type="text" name="phone" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small fw-bold">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                                <?php if($website['policies_text']): ?>
                                    <div class="alert alert-light border small text-muted mt-3">
                                        <strong>Políticas del Hotel:</strong><br><?= nl2br(esc($website['policies_text'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary fw-bold px-4">Solicitar Reserva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if(!empty($media)): ?>
        <h2 class="fw-bold mb-4 border-bottom pb-2">Galería</h2>
        <div class="row g-2 mb-5">
            <?php foreach($media as $m): ?>
                <div class="col-6 col-md-3">
                    <?php if($m['file_type'] == 'image'): ?>
                        <img src="<?= base_url($m['file_path']) ?>" class="img-fluid rounded shadow-sm w-100" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<footer class="bg-dark text-white py-4 text-center mt-auto">
    <div class="container">
        <h5 class="fw-bold"><?= esc($tenant['name']) ?></h5>
        <p class="small text-muted mb-0"><?= esc($tenant['address']) ?>, <?= esc($tenant['city']) ?></p>
        <p class="small text-muted mt-2">&copy; <?= date('Y') ?> Desarrollado con tecnología MAVILUSA PMS.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>