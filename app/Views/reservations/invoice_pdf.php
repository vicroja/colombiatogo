<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Reserva</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { border: none; padding: 0; }
        .title { font-size: 24px; font-weight: bold; color: #2E75B6; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #666; }
        .box { border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: #f9f9f9; }
        .details-table th, .details-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .details-table th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .total-row td { font-size: 15px; background-color: #f9f9f9; }
        .grand-total { font-size: 18px; color: #2E75B6; font-weight: bold; }
        .balance-due { font-size: 16px; color: #d9534f; font-weight: bold; }
        .balance-zero { font-size: 16px; color: #5cb85c; font-weight: bold; }
        .footer-note { text-align: center; font-size: 11px; color: #888; margin-top: 40px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
<?php
// Calculamos las noches automáticamente por si la reserva es antigua y no tiene el dato
$nights = $reservation['nights'] ?? 0;
if (empty($nights)) {
    $in = new \DateTime($reservation['check_in_date']);
    $out = new \DateTime($reservation['check_out_date']);
    $nights = $in->diff($out)->days;
    if ($nights == 0) $nights = 1; // Para evitar que diga 0 noches
}
?>
<table class="header-table">
    <tr>
        <td width="60%">
            <?php if(!empty($tenant_logo) && file_exists(FCPATH . $tenant_logo)): ?>
                <img src="<?= FCPATH . $tenant_logo ?>" style="max-height: 80px; margin-bottom: 10px;">
            <?php else: ?>
                <div class="title"><?= esc($tenant_name) ?></div>
            <?php endif; ?>
            <div class="subtitle">Liquidación de Estadía y Servicios</div>
        </td>
        <td width="40%" class="text-right">
            <h2 style="margin:0; color:#444;">COMPROBANTE</h2>
            <p style="margin:5px 0 0 0;"><strong>Reserva #:</strong> <?= str_pad($reservation['id'], 5, '0', STR_PAD_LEFT) ?></p>
            <p style="margin:0;"><strong>Fecha Emisión:</strong> <?= date('d/m/Y') ?></p>
        </td>
    </tr>
</table>

<table>
    <tr>
        <td width="48%" class="box" style="vertical-align: top;">
            <strong>Cobrar a:</strong><br>
            <?= esc($reservation['full_name']) ?><br>
            Documento: <?= esc($reservation['document']) ?>
        </td>
        <td width="4%"></td>
        <td width="48%" class="box" style="vertical-align: top;">
            <strong>Detalles de Alojamiento:</strong><br>
            Unidad: <?= esc($reservation['unit_name']) ?><br>
            Check-in: <?= date('d/m/Y', strtotime($reservation['check_in_date'])) ?><br>
            Check-out: <?= date('d/m/Y', strtotime($reservation['check_out_date'])) ?><br>
            Noches: <?= $nights ?>
        </td>
    </tr>
</table>

<table class="details-table">
    <thead>
    <tr>
        <th width="50%">Descripción del Cargo</th>
        <th width="15%" class="text-center">Cant.</th>
        <th width="15%" class="text-right">Precio Unit.</th>
        <th width="20%" class="text-right">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Tarifa de Alojamiento (<?= $nights ?> noches)</td>
        <td class="text-center">1</td>
        <td class="text-right"><?= $currency ?><?= number_format($reservation['total_price'], 2) ?></td>
        <td class="text-right fw-bold"><?= $currency ?><?= number_format($reservation['total_price'], 2) ?></td>
    </tr>

    <?php foreach($consumptions as $c): ?>
        <tr>
            <td>Consumo: <?= esc($c['description']) ?></td>
            <td class="text-center"><?= $c['quantity'] ?></td>
            <td class="text-right"><?= $currency ?><?= number_format($c['unit_price'], 2) ?></td>
            <td class="text-right"><?= $currency ?><?= number_format($c['subtotal'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>

    <tbody>
    <tr class="total-row">
        <td colspan="3" class="text-right fw-bold">GRAN TOTAL CARGOS:</td>
        <td class="text-right grand-total"><?= $currency ?><?= number_format($grandTotal, 2) ?></td>
    </tr>
    <tr>
        <td colspan="3" class="text-right">Total Pagado / Abonos:</td>
        <td class="text-right" style="color: green;">- <?= $currency ?><?= number_format($totalPaid, 2) ?></td>
    </tr>
    <tr>
        <td colspan="3" class="text-right fw-bold">SALDO PENDIENTE:</td>
        <td class="text-right <?= $balance > 0 ? 'balance-due' : 'balance-zero' ?>">
            <?= $currency ?><?= number_format($balance, 2) ?>
        </td>
    </tr>
    </tbody>
</table>

<?php if(!empty($payments)): ?>
    <h4 style="margin-bottom: 5px; color: #444;">Registro de Pagos Realizados</h4>
    <table class="details-table" style="width: 70%;">
        <thead>
        <tr>
            <th width="30%">Fecha</th>
            <th width="40%">Método / Referencia</th>
            <th width="30%" class="text-right">Monto</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($payments as $pay): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($pay['created_at'])) ?></td>
                <td>
                    <?= strtoupper($pay['payment_method']) ?>
                    <?= $pay['reference'] ? ' (Ref: '.esc($pay['reference']).')' : '' ?>
                </td>
                <td class="text-right fw-bold" style="color: green;"><?= $currency ?><?= number_format($pay['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="footer-note">
    <p>Gracias por su estadía en <strong><?= esc($tenant_name) ?></strong>.</p>
    <p>Este documento es un comprobante de liquidación de estadía y consumos.</p>
</div>

</body>
</html>