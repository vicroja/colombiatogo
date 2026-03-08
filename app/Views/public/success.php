<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva Recibida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="text-center p-5 bg-white shadow rounded-4 border-top border-success border-5" style="max-width: 500px;">
    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
    <h2 class="fw-bold mt-4 mb-3">¡Solicitud Recibida!</h2>
    <p class="text-muted fs-5">Tu reserva ha sido enviada con éxito a <strong><?= esc($tenant['name']) ?></strong>.</p>
    <p class="small text-muted mb-4">Actualmente está en estado <strong>Pendiente de Confirmación</strong>.</p>

    <?php if($website['whatsapp_number']): ?>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $website['whatsapp_number']) ?>" target="_blank" class="btn btn-success btn-lg fw-bold w-100 mb-3 shadow-sm">
            <i class="bi bi-whatsapp"></i> Confirmar vía WhatsApp
        </a>
    <?php endif; ?>

    <a href="<?= base_url('/book/'.$tenant['slug']) ?>" class="btn btn-outline-secondary w-100">Volver al inicio</a>
</div>

</body>
</html>