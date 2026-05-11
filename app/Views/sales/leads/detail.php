<?= $this->extend('sales/layout/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-7">
        <h4><?= esc($lead['property_name']) ?>
            <?php if ($lead['is_cold']): ?>
                <span class="badge bg-warning text-dark">Frío</span>
            <?php endif; ?>
        </h4>
        <p class="text-muted">
            <?= esc($lead['contact_name']) ?>
            <?php if (!empty($lead['contact_position'])): ?>
                — <?= esc($lead['contact_position']) ?>
            <?php endif; ?>
        </p>

        <div class="card mb-3">
            <div class="card-body">
                <h6>Información de contacto</h6>
                <p class="mb-1"><strong>Email:</strong> <?= esc($lead['contact_email'] ?: '—') ?></p>
                <p class="mb-1"><strong>Teléfono:</strong> <?= esc($lead['contact_phone'] ?: '—') ?></p>

                <h6 class="mt-3">Hotel</h6>
                <p class="mb-1"><strong>Tipo:</strong> <?= esc($lead['property_type']) ?></p>
                <p class="mb-1"><strong>Habitaciones:</strong> <?= $lead['rooms_count'] ?: '—' ?></p>
                <p class="mb-1"><strong>Ciudad:</strong> <?= esc($lead['property_city'] ?: '—') ?>, <?= esc($lead['property_country'] ?: '') ?></p>
                <p class="mb-1"><strong>PMS actual:</strong> <?= esc($lead['current_pms'] ?: 'No tiene') ?></p>
                <?php if (!empty($lead['property_website'])): ?>
                    <p class="mb-1"><strong>Web:</strong> <a href="<?= esc($lead['property_website']) ?>" target="_blank"><?= esc($lead['property_website']) ?></a></p>
                <?php endif; ?>

                <h6 class="mt-3">Oportunidad</h6>
                <p class="mb-1"><strong>Valor estimado:</strong> $<?= number_format($lead['estimated_value'] ?? 0, 0, ',', '.') ?></p>
                <p class="mb-1"><strong>Cierre esperado:</strong> <?= $lead['expected_close_date'] ?: '—' ?></p>
            </div>
        </div>

        <!-- Acción rápida -->
        <div class="card mb-3">
            <div class="card-body">
                <h6>Registrar actividad</h6>
                <select id="actType" class="form-select form-select-sm mb-2">
                    <option value="note">Nota</option>
                    <option value="call">Llamada</option>
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="meeting">Reunión</option>
                    <option value="demo">Demo</option>
                </select>
                <textarea id="actBody" class="form-control form-control-sm mb-2" rows="2" placeholder="Detalles..."></textarea>
                <button class="btn btn-sm btn-primary" onclick="addActivity()">Guardar</button>
            </div>
        </div>

        <!-- Próxima acción -->
        <div class="card mb-3">
            <div class="card-body">
                <h6>Próxima acción</h6>
                <?php if (!empty($lead['next_action_at'])): ?>
                    <p class="<?= strtotime($lead['next_action_at']) < time() ? 'text-danger' : '' ?>">
                        <strong><?= date('d/m/Y H:i', strtotime($lead['next_action_at'])) ?></strong>:
                        <?= esc($lead['next_action_note']) ?>
                    </p>
                <?php endif; ?>
                <input type="datetime-local" id="naAt" class="form-control form-control-sm mb-2">
                <input type="text" id="naNote" class="form-control form-control-sm mb-2" placeholder="Ej: llamar para confirmar demo">
                <button class="btn btn-sm btn-outline-primary" onclick="setNextAction()">Programar</button>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <h5>Timeline</h5>
        <div class="timeline">
            <?php foreach ($timeline as $a): ?>
                <div class="timeline-item">
                    <div class="d-flex justify-content-between">
                        <strong><?= esc($a['type']) ?></strong>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['occurred_at'])) ?></small>
                    </div>
                    <?php if (!empty($a['subject'])): ?>
                        <div class="text-muted small"><?= esc($a['subject']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($a['body'])): ?>
                        <p class="mb-0 mt-1"><?= nl2br(esc($a['body'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($a['user_name'])): ?>
                        <small class="text-muted">Por <?= esc($a['user_name']) ?></small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.timeline-item {
    padding: 10px; border-left: 2px solid #0d6efd; margin-left: 8px;
    margin-bottom: 8px; background: #f8f9fa; border-radius: 0 4px 4px 0;
}
</style>

<script>
const LEAD_ID = <?= $lead['id'] ?>;

async function addActivity() {
    const fd = new FormData();
    fd.append('lead_id', LEAD_ID);
    fd.append('type', document.getElementById('actType').value);
    fd.append('body', document.getElementById('actBody').value);
    const r = await fetch('/sales/leads/addActivity',{method:'POST',body:fd});
    const d = await r.json();
    if (d.ok) location.reload();
}

async function setNextAction() {
    const fd = new FormData();
    fd.append('lead_id', LEAD_ID);
    fd.append('next_action_at', document.getElementById('naAt').value);
    fd.append('next_action_note', document.getElementById('naNote').value);
    const r = await fetch('/sales/leads/setNextAction',{method:'POST',body:fd});
    const d = await r.json();
    if (d.ok) location.reload();
}
</script>
<?= $this->endSection() ?>
