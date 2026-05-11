<?= $this->extend('super/layout/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Reportes de ventas</h4>
            <small class="text-muted">Métricas globales del equipo comercial</small>
        </div>
    </div>

    <!-- Resumen forecast -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Forecast del mes</small>
                    <h3 class="text-success mb-0">$<?= number_format($forecast, 0, ',', '.') ?></h3>
                    <small class="text-muted">Oportunidades abiertas × probabilidad de cierre</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Total leads en pipeline</small>
                    <h3 class="text-primary mb-0">
                        <?php
                        $totalOpen = 0;
                        foreach ($funnel as $f) {
                            // Sumamos todos excepto Ganado y Perdido
                            if ($f['order_position'] < 7) $totalOpen += $f['total'];
                        }
                        echo $totalOpen;
                        ?>
                    </h3>
                    <small class="text-muted">Oportunidades activas hoy</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body">
                    <small class="text-muted text-uppercase">Vendedores activos</small>
                    <h3 class="text-info mb-0"><?= count($ranking) ?></h3>
                    <small class="text-muted">Con actividad en últimos 90 días</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Embudo -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Embudo de conversión</strong>
            <small class="text-muted">— Distribución de leads por etapa</small>
        </div>
        <div class="card-body">
            <?php
            $maxFunnel = 0;
            foreach ($funnel as $f) { if ($f['total'] > $maxFunnel) $maxFunnel = $f['total']; }
            if ($maxFunnel == 0) $maxFunnel = 1;
            ?>
            <table class="table table-sm mb-0">
                <thead>
                <tr>
                    <th>Etapa</th>
                    <th class="text-center" style="width:10%">Cantidad</th>
                    <th style="width:50%">Distribución</th>
                    <th class="text-end" style="width:20%">Valor estimado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($funnel as $f): ?>
                    <?php $pct = ($f['total'] / $maxFunnel) * 100; ?>
                    <tr>
                        <td>
                            <span class="d-inline-block" style="width:10px; height:10px; border-radius:50%; background:<?= esc($f['color']) ?>"></span>
                            <strong><?= esc($f['name']) ?></strong>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-dark"><?= $f['total'] ?></span></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?= $pct ?>%; background-color: <?= esc($f['color']) ?>;">
                                </div>
                            </div>
                        </td>
                        <td class="text-end">$<?= number_format($f['value_sum'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ranking de vendedores -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <strong>Ranking de vendedores</strong>
            <small class="text-muted">— Últimos 90 días</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                <tr>
                    <th style="width:5%">#</th>
                    <th>Vendedor</th>
                    <th class="text-center">Leads totales</th>
                    <th class="text-center">Ganados</th>
                    <th class="text-center">Perdidos</th>
                    <th class="text-center">Tasa cierre</th>
                    <th class="text-end">Ingresos generados</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($ranking)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">Sin datos aún</td></tr>
                <?php else: ?>
                    <?php foreach ($ranking as $i => $r): ?>
                        <?php
                        $closeRate = ($r['total_leads'] > 0)
                            ? round(($r['won'] / $r['total_leads']) * 100, 1)
                            : 0;
                        ?>
                        <tr>
                            <td>
                                <?php if ($i === 0 && $r['won'] > 0): ?>
                                    <span title="Top del mes">🏆</span>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= esc($r['name']) ?></strong></td>
                            <td class="text-center"><?= $r['total_leads'] ?></td>
                            <td class="text-center text-success"><strong><?= $r['won'] ?></strong></td>
                            <td class="text-center text-muted"><?= $r['lost'] ?></td>
                            <td class="text-center">
                                <span class="badge <?= $closeRate >= 30 ? 'bg-success' : ($closeRate >= 15 ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                    <?= $closeRate ?>%
                                </span>
                            </td>
                            <td class="text-end"><strong>$<?= number_format($r['revenue'], 0, ',', '.') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <!-- Razones de pérdida -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <strong>Razones de pérdida</strong>
                    <small class="text-muted">— Últimos 90 días</small>
                </div>
                <div class="card-body">
                    <?php
                    $totalLost = 0;
                    foreach ($lossReasons as $r) { $totalLost += $r['total']; }
                    if ($totalLost == 0) $totalLost = 1;
                    ?>
                    <?php if (empty($lossReasons) || array_sum(array_column($lossReasons, 'total')) === 0): ?>
                        <p class="text-muted mb-0">Sin pérdidas registradas en el período.</p>
                    <?php else: ?>
                        <table class="table table-sm mb-0">
                            <?php foreach ($lossReasons as $r): ?>
                                <?php if ($r['total'] == 0) continue; ?>
                                <?php $pct = round(($r['total'] / $totalLost) * 100, 1); ?>
                                <tr>
                                    <td><?= esc($r['name']) ?></td>
                                    <td class="text-end" style="width:25%">
                                        <strong><?= $r['total'] ?></strong>
                                        <small class="text-muted">(<?= $pct ?>%)</small>
                                    </td>
                                    <td style="width:30%">
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-danger" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <small class="text-muted d-block mt-2">
                            💡 Las razones más frecuentes te indican qué arreglar del pitch o del producto.
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Inbound vs Outbound -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <strong>Inbound vs Outbound</strong>
                    <small class="text-muted">— Conversión por origen</small>
                </div>
                <div class="card-body">
                    <?php if (empty($sourceComparison)): ?>
                        <p class="text-muted mb-0">Sin datos suficientes.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Tipo</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Ganados</th>
                                <th class="text-center">Tasa</th>
                                <th class="text-end">Ingresos</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sourceComparison as $s): ?>
                                <?php
                                $rate = ($s['total'] > 0) ? round(($s['won'] / $s['total']) * 100, 1) : 0;
                                $labels = [
                                    'inbound'  => '🌐 Inbound',
                                    'outbound' => '📞 Outbound',
                                    'referral' => '🤝 Referido',
                                    'event'    => '🎪 Evento',
                                ];
                                ?>
                                <tr>
                                    <td><?= $labels[$s['type']] ?? esc($s['type']) ?></td>
                                    <td class="text-center"><?= $s['total'] ?></td>
                                    <td class="text-center"><strong class="text-success"><?= $s['won'] ?></strong></td>
                                    <td class="text-center"><?= $rate ?>%</td>
                                    <td class="text-end">$<?= number_format($s['revenue'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <small class="text-muted d-block mt-2">
                            💡 Compara dónde invertir más: ¿inversión en ads o más prospectores?
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>