<?= $this->extend('sales/layout/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Dashboard <?= $isManager ? '(Gerente)' : '' ?></h4>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-primary mb-0"><?= $metrics['open_leads'] ?? 0 ?></h2>
                <small>Leads abiertos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-success mb-0"><?= $metrics['won_this_month'] ?? 0 ?></h2>
                <small>Ganados este mes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-warning mb-0"><?= $metrics['cold_leads'] ?? 0 ?></h2>
                <small>Leads fríos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="text-danger mb-0"><?= $metrics['overdue_actions'] ?? 0 ?></h2>
                <small>Acciones vencidas</small>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="/sales/leads" class="btn btn-primary">Ver pipeline</a>
    <a href="/sales/leads/create" class="btn btn-outline-secondary">Nuevo lead</a>
</div>
<?= $this->endSection() ?>
