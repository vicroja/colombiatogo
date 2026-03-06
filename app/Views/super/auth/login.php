<?= $this->extend('super/layouts/auth') ?>

<?= $this->section('title') ?>Iniciar Sesión<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card auth-card">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Panel SuperAdmin</h3>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('/super/login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="admin@pms.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="******">
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">Ingresar</button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>