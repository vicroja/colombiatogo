<?php
/**
 * onboarding/steps/step1_identity.php
 *
 * Paso 1: Datos básicos del hotel.
 * Pre-pobla con los valores actuales del tenant si ya existen.
 */

// Valores actuales del tenant para pre-poblar el formulario
$t = $tenant ?? [];
?>

<!-- ── Encabezado del paso ───────────────────────────────────────────────── -->
<div class="wizard-card">
    <div class="card-eyebrow">Paso 1</div>
    <h5>Cuéntanos sobre tu hotel</h5>
    <p class="card-hint">
        Esta información aparecerá en tu sitio web, reservaciones y comunicaciones con huéspedes.
    </p>

    <!-- ── Formulario ───────────────────────────────────────────────────── -->
    <form action="/onboarding/step/1" method="POST" id="formStep1">
        <?= csrf_field() ?>

        <!-- Nombre del hotel -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="name">
                Nombre del hotel <span class="text-danger">*</span>
            </label>
            <input
                type="text"
                class="form-control form-control-lg"
                id="name"
                name="name"
                value="<?= esc($t['name'] ?? '') ?>"
                placeholder="Ej: Hotel Boutique Casa Grande"
                required
                maxlength="120"
            >
        </div>

        <!-- Email y teléfono -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="email">
                    Email de contacto
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="<?= esc($t['email'] ?? '') ?>"
                        placeholder="reservas@mihotel.com"
                        maxlength="120"
                    >
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="phone">
                    Teléfono / WhatsApp <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input
                        type="text"
                        class="form-control"
                        id="phone"
                        name="phone"
                        value="<?= esc($t['phone'] ?? '') ?>"
                        placeholder="+57 300 000 0000"
                        required
                        maxlength="30"
                    >
                </div>
            </div>
        </div>

        <!-- Dirección -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="address">Dirección</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input
                    type="text"
                    class="form-control"
                    id="address"
                    name="address"
                    value="<?= esc($t['address'] ?? '') ?>"
                    placeholder="Calle 10 # 5-23, Zona Rosa"
                    maxlength="255"
                >
            </div>
        </div>

        <!-- Ciudad y País -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="city">
                    Ciudad <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="city"
                    name="city"
                    value="<?= esc($t['city'] ?? '') ?>"
                    placeholder="Medellín"
                    required
                    maxlength="100"
                >
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="country">
                    País <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="country" name="country" required>
                    <option value="">Selecciona...</option>
                    <?php
                    $countries = [
                        'Colombia'   => 'Colombia',
                        'México'     => 'México',
                        'Argentina'  => 'Argentina',
                        'Chile'      => 'Chile',
                        'Perú'       => 'Perú',
                        'Ecuador'    => 'Ecuador',
                        'Venezuela'  => 'Venezuela',
                        'España'     => 'España',
                        'Costa Rica' => 'Costa Rica',
                        'Panamá'     => 'Panamá',
                        'Otro'       => 'Otro',
                    ];
                    foreach ($countries as $val => $label):
                        $selected = ($t['country'] ?? '') === $val ? 'selected' : '';
                        ?>
                        <option value="<?= esc($val) ?>" <?= $selected ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="my-4" style="border-color:#f1f5f9">

        <!-- Horarios de check-in / check-out -->
        <div class="mb-1">
            <p class="fw-semibold mb-1">
                <i class="bi bi-clock me-1 text-primary"></i>
                Horarios de entrada y salida
            </p>
            <p class="text-muted small mb-3">
                Se mostrarán en confirmaciones de reserva y en tu sitio web.
            </p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="checkin_time">
                    Check-in <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-box-arrow-in-right"></i></span>
                    <input
                        type="time"
                        class="form-control"
                        id="checkin_time"
                        name="checkin_time"
                        value="<?= esc($t['checkin_time'] ?? '15:00') ?>"
                        required
                    >
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="checkout_time">
                    Check-out <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-box-arrow-right"></i></span>
                    <input
                        type="time"
                        class="form-control"
                        id="checkout_time"
                        name="checkout_time"
                        value="<?= esc($t['checkout_time'] ?? '12:00') ?>"
                        required
                    >
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color:#f1f5f9">

        <!-- Moneda y Zona horaria -->
        <div class="mb-1">
            <p class="fw-semibold mb-1">
                <i class="bi bi-cash-coin me-1 text-primary"></i>
                Moneda y zona horaria
            </p>
            <p class="text-muted small mb-3">
                La moneda se usará en tarifas, facturas y reportes.
            </p>
        </div>

        <div class="row g-3 mb-4">
            <!-- Moneda -->
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="currency_code">
                    Código de moneda <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="currency_code" name="currency_code"
                        required onchange="syncCurrencySymbol(this.value)">
                    <?php
                    $currencies = [
                        'COP' => ['name' => 'Peso colombiano',   'symbol' => '$'],
                        'MXN' => ['name' => 'Peso mexicano',     'symbol' => '$'],
                        'ARS' => ['name' => 'Peso argentino',    'symbol' => '$'],
                        'CLP' => ['name' => 'Peso chileno',      'symbol' => '$'],
                        'PEN' => ['name' => 'Sol peruano',       'symbol' => 'S/'],
                        'USD' => ['name' => 'Dólar americano',   'symbol' => '$'],
                        'EUR' => ['name' => 'Euro',              'symbol' => '€'],
                        'CRC' => ['name' => 'Colón costarricense','symbol'=> '₡'],
                        'PAB' => ['name' => 'Balboa panameño',   'symbol' => 'B/.'],
                    ];
                    foreach ($currencies as $code => $info):
                        $selected = ($t['currency_code'] ?? 'COP') === $code ? 'selected' : '';
                        ?>
                        <option value="<?= $code ?>"
                                data-symbol="<?= esc($info['symbol']) ?>"
                            <?= $selected ?>>
                            <?= $code ?> — <?= esc($info['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Símbolo (se auto-rellena) -->
            <div class="col-md-2">
                <label class="form-label fw-semibold" for="currency_symbol">
                    Símbolo
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="currency_symbol"
                    name="currency_symbol"
                    value="<?= esc($t['currency_symbol'] ?? '$') ?>"
                    maxlength="5"
                    readonly
                >
            </div>

            <!-- Zona horaria -->
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="timezone">
                    Zona horaria <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="timezone" name="timezone" required>
                    <?php
                    $timezones = [
                        'America/Bogota'     => 'Colombia (UTC-5)',
                        'America/Mexico_City'=> 'México Centro (UTC-6)',
                        'America/Cancun'     => 'México Este (UTC-5)',
                        'America/Argentina/Buenos_Aires' => 'Argentina (UTC-3)',
                        'America/Santiago'   => 'Chile (UTC-3/-4)',
                        'America/Lima'       => 'Perú (UTC-5)',
                        'America/Guayaquil'  => 'Ecuador (UTC-5)',
                        'America/Caracas'    => 'Venezuela (UTC-4)',
                        'America/Costa_Rica' => 'Costa Rica (UTC-6)',
                        'America/Panama'     => 'Panamá (UTC-5)',
                        'Europe/Madrid'      => 'España (UTC+1/+2)',
                        'UTC'                => 'UTC',
                    ];
                    foreach ($timezones as $tz => $label):
                        $selected = ($t['timezone'] ?? 'America/Bogota') === $tz ? 'selected' : '';
                        ?>
                        <option value="<?= esc($tz) ?>" <?= $selected ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ── Navegación del paso ───────────────────────────────────────── -->
        <div class="d-flex justify-content-end pt-2">
            <button type="submit" class="btn-wiz-primary" id="btnSubmit1">
                Guardar y continuar
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

    </form>
</div>

<!-- ── Tip informativo ───────────────────────────────────────────────────── -->
<div class="d-flex align-items-start gap-3 p-3 rounded-3"
     style="background:#f0f4ff;border:1px solid #c7d2fe">
    <i class="bi bi-lightbulb-fill mt-1" style="color:#6366f1;font-size:1.1rem"></i>
    <div>
        <strong style="font-size:.85rem;color:#3730a3">¿Sabías que?</strong>
        <p class="mb-0 text-muted" style="font-size:.82rem">
            El nombre y ciudad del hotel se usan para personalizar automáticamente
            el asistente de IA en el paso 5. Entre más completes aquí, mejor será
            el resultado.
        </p>
    </div>
</div>

<script>
    /**
     * Sincroniza el símbolo de moneda al cambiar el select
     */
    function syncCurrencySymbol(code) {
        const select = document.getElementById('currency_code');
        const opt    = select.querySelector(`option[value="${code}"]`);
        if (opt) {
            document.getElementById('currency_symbol').value = opt.dataset.symbol ?? '$';
        }
    }

    /**
     * Validación básica antes de submit para UX inmediata
     */
    document.getElementById('formStep1').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit1');
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        // Re-habilitar si hay error de validación del servidor (recarga)
        window.addEventListener('pageshow', () => { btn.disabled = false; });
    });
</script>