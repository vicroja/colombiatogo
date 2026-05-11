<?= $this->extend('sales/layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Pipeline de ventas</h4>
        <small class="text-muted">
            <?= count($leads) ?> oportunidades abiertas
            <?php if ($isManager): ?> — vista de gerente <?php endif; ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <form method="get" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar..." value="<?= esc($_GET['q'] ?? '') ?>">
            <?php if ($isManager && !empty($sellers)): ?>
            <select name="seller" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos los vendedores</option>
                <?php foreach ($sellers as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (($_GET['seller'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                        <?= esc($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
        </form>
        <a href="/sales/leads/create" class="btn btn-sm btn-primary">+ Nuevo lead</a>
    </div>
</div>

<div class="kanban-board">
    <?php foreach ($stages as $stage): ?>
        <?php
            $stageLeads = array_filter($leads, fn($l) => $l['stage_id'] == $stage['id']);
            $stageValue = array_sum(array_column($stageLeads, 'estimated_value'));
        ?>
        <div class="kanban-column" data-stage-id="<?= $stage['id'] ?>">
            <div class="kanban-header" style="border-top: 3px solid <?= esc($stage['color']) ?>;">
                <strong><?= esc($stage['name']) ?></strong>
                <span class="badge bg-light text-dark"><?= count($stageLeads) ?></span>
                <?php if ($stageValue): ?>
                    <small class="d-block text-muted">$<?= number_format($stageValue, 0, ',', '.') ?></small>
                <?php endif; ?>
            </div>
            <div class="kanban-cards" id="stage-<?= $stage['id'] ?>">
                <?php foreach ($stageLeads as $lead): ?>
                    <div class="kanban-card <?= $lead['is_cold'] ? 'is-cold' : '' ?>" data-lead-id="<?= $lead['id'] ?>">
                        <a href="/sales/leads/detail/<?= $lead['id'] ?>" class="text-decoration-none text-dark">
                            <div class="fw-bold"><?= esc($lead['property_name']) ?></div>
                            <small class="text-muted d-block"><?= esc($lead['contact_name']) ?></small>
                            <?php if (!empty($lead['rooms_count'])): ?>
                                <small class="text-muted"><?= $lead['rooms_count'] ?> hab.</small>
                            <?php endif; ?>
                            <?php if (!empty($lead['estimated_value'])): ?>
                                <div class="mt-1"><strong>$<?= number_format($lead['estimated_value'], 0, ',', '.') ?></strong></div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mt-1">
                                <small><?= esc($lead['seller_name'] ?? 'Sin asignar') ?></small>
                                <?php if (!empty($lead['next_action_at'])): ?>
                                    <small class="<?= strtotime($lead['next_action_at']) < time() ? 'text-danger' : 'text-muted' ?>">
                                        <?= date('d/m H:i', strtotime($lead['next_action_at'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <?php if ($lead['is_cold']): ?>
                                <span class="badge bg-warning text-dark mt-1">Frío</span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.kanban-board {
    display: flex; gap: 12px; overflow-x: auto; padding: 10px 0;
    min-height: calc(100vh - 220px);
}
.kanban-column {
    flex: 0 0 280px; background: #f4f5f7; border-radius: 6px; padding: 8px;
    display: flex; flex-direction: column;
}
.kanban-header { padding: 8px; background: white; border-radius: 4px; margin-bottom: 8px; }
.kanban-cards { flex: 1; min-height: 100px; }
.kanban-card {
    background: white; padding: 10px; margin-bottom: 8px; border-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,.08); cursor: grab; transition: box-shadow .15s;
}
.kanban-card:hover { box-shadow: 0 4px 8px rgba(0,0,0,.12); }
.kanban-card.is-cold { border-left: 3px solid #ffc107; }
.sortable-ghost { opacity: 0.4; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.querySelectorAll('.kanban-cards').forEach(col => {
    new Sortable(col, {
        group: 'leads',
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: async (evt) => {
            const leadId  = evt.item.dataset.leadId;
            const stageId = evt.to.parentElement.dataset.stageId;

            const fd = new FormData();
            fd.append('lead_id', leadId);
            fd.append('stage_id', stageId);

            const res = await fetch('/sales/leads/move', {method:'POST', body:fd});
            const data = await res.json();

            if (!data.ok) {
                alert(data.msg || 'Error al mover el lead');
                location.reload();
                return;
            }
            if (data.needs_won_modal) {
                openWonModal(leadId);
            } else if (data.needs_lost_modal) {
                openLostModal(leadId, data.reasons);
            }
        }
    });
});

function openLostModal(leadId, reasons) {
    const reason = prompt("Razón de pérdida:\n\n" +
        reasons.map((r,i)=>`${i+1}. ${r.name}`).join('\n') +
        "\n\nIngresa el número:");
    if (!reason) { location.reload(); return; }
    const reasonId = reasons[parseInt(reason)-1]?.id;
    const notes = prompt("Notas (opcional):") || '';

    const fd = new FormData();
    fd.append('lead_id', leadId);
    fd.append('loss_reason_id', reasonId);
    fd.append('loss_notes', notes);
    fetch('/sales/leads/markLost',{method:'POST',body:fd}).then(()=>location.reload());
}

function openWonModal(leadId) {
    const planId = prompt("ID del plan de suscripción a asignar:");
    if (!planId) { location.reload(); return; }
    const pass = prompt("Password inicial para el admin del hotel (vacío = aleatorio):") || '';

    const fd = new FormData();
    fd.append('lead_id', leadId);
    fd.append('plan_id', planId);
    fd.append('admin_password', pass);
    fetch('/sales/leads/markWon',{method:'POST',body:fd})
        .then(r=>r.json())
        .then(d=>{
            if (d.ok) alert('Cliente creado. Tenant ID: '+d.tenant_id);
            else alert('Error: '+d.msg);
            location.reload();
        });
}
</script>
<?= $this->endSection() ?>
