<?php
$border = $task['priority'] == 'alta' ? 'border-danger' : ($task['priority'] == 'media' ? 'border-warning' : 'border-info');
$badge = $task['priority'] == 'alta' ? 'bg-danger' : ($task['priority'] == 'media' ? 'bg-warning text-dark' : 'bg-info text-dark');
?>
<div class="card mb-2 <?= $border ?> shadow-sm">
    <div class="card-body p-2">
        <div class="d-flex justify-content-between align-items-start mb-1">
            <span class="badge <?= $badge ?> small"><?= strtoupper($task['priority']) ?></span>
            <?php if($task['blocks_unit']): ?>
                <span class="badge bg-dark small" title="Habitación Bloqueada"><i class="bi bi-lock-fill"></i></span>
            <?php endif; ?>
        </div>
        <h6 class="mb-1 fw-bold"><?= esc($task['title']) ?></h6>
        <p class="small text-muted mb-2 lh-sm"><?= esc($task['unit_name'] ?? 'Área General') ?></p>

        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
            <a href="<?= base_url('/maintenance/delete/'.$task['id']) ?>" class="text-danger small" onclick="return confirm('¿Borrar?');"><i class="bi bi-trash"></i></a>

            <form action="<?= base_url('/maintenance/update-status/'.$task['id']) ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <select name="status" class="form-select form-select-sm d-inline-block w-auto py-0" onchange="this.form.submit()">
                    <option value="pending" <?= $task['status']=='pending'?'selected':'' ?>>Pendiente</option>
                    <option value="in_progress" <?= $task['status']=='in_progress'?'selected':'' ?>>En Progreso</option>
                    <option value="completed" <?= $task['status']=='completed'?'selected':'' ?>>Completada</option>
                </select>
            </form>
        </div>
    </div>
</div>

