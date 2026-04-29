<?php
/**
 * onboarding/steps/step_profile.php
 * Paso 2: Define si el tenant maneja alojamiento, tours o ambos.
 */
$hasAccommodation = $has_accommodation ?? true;
$hasTours         = $has_tours         ?? false;
?>

<div class="wizard-card">
    <div class="card-eyebrow">Paso 2</div>
    <h5>¿Qué tipo de negocio tienes?</h5>
    <p class="card-hint">
        Selecciona uno o ambos. Esto personaliza el sistema y el wizard
        para mostrarte solo lo que necesitas.
    </p>

    <form action="/onboarding/step/2" method="POST" id="formStep2">
        <?= csrf_field() ?>

        <div class="row g-3 mb-4">

            <!-- Opción: Alojamiento -->
            <div class="col-md-6">
                <label class="d-block h-100" style="cursor:pointer">
                    <input type="checkbox" name="has_accommodation" value="1"
                           id="chk_accommodation"
                        <?= $hasAccommodation ? 'checked' : '' ?>
                           class="d-none profile-check">
                    <div class="profile-card h-100 p-4 rounded-3 border-2 text-center
                                <?= $hasAccommodation ? 'border border-primary bg-primary bg-opacity-10' : 'border' ?>"
                         id="card_accommodation">
                        <i class="bi bi-building fs-1 mb-3 d-block"
                           style="color: <?= $hasAccommodation ? '#6366f1' : '#94a3b8' ?>"></i>
                        <h6 class="fw-bold mb-1">Alojamiento</h6>
                        <p class="text-muted small mb-0">
                            Hotel, hostal, cabaña, glamping o cualquier tipo de hospedaje.
                            Gestiona habitaciones, tarifas y reservas por noche.
                        </p>
                        <div class="mt-3">
                            <span class="badge <?= $hasAccommodation ? 'bg-primary' : 'bg-light text-muted border' ?>">
                                <?= $hasAccommodation ? '✓ Seleccionado' : 'Seleccionar' ?>
                            </span>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Opción: Tours -->
            <div class="col-md-6">
                <label class="d-block h-100" style="cursor:pointer">
                    <input type="checkbox" name="has_tours" value="1"
                           id="chk_tours"
                        <?= $hasTours ? 'checked' : '' ?>
                           class="d-none profile-check">
                    <div class="profile-card h-100 p-4 rounded-3 border-2 text-center
                                <?= $hasTours ? 'border border-primary bg-primary bg-opacity-10' : 'border' ?>"
                         id="card_tours">
                        <i class="bi bi-compass fs-1 mb-3 d-block"
                           style="color: <?= $hasTours ? '#6366f1' : '#94a3b8' ?>"></i>
                        <h6 class="fw-bold mb-1">Tours y Actividades</h6>
                        <p class="text-muted small mb-0">
                            Operador turístico, agencia de aventura o experiencias.
                            Gestiona tours, salidas y grupos de pasajeros.
                        </p>
                        <div class="mt-3">
                            <span class="badge <?= $hasTours ? 'bg-primary' : 'bg-light text-muted border' ?>">
                                <?= $hasTours ? '✓ Seleccionado' : 'Seleccionar' ?>
                            </span>
                        </div>
                    </div>
                </label>
            </div>

        </div>

        <!-- Aviso si ninguno está seleccionado -->
        <div id="profileWarning" class="alert alert-warning d-none">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Debes seleccionar al menos un tipo de negocio para continuar.
        </div>

        <!-- Preview de pasos que se activarán -->
        <div class="p-3 rounded-3 mb-4" style="background:#f8fafc; border:1px solid #e2e8f0">
            <p class="small fw-semibold text-muted mb-2">
                <i class="bi bi-list-check me-1"></i> Pasos que se activarán:
            </p>
            <div id="stepsPreview" class="d-flex flex-wrap gap-2">
                <!-- Se actualiza por JS -->
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn-wiz-primary" id="btnSubmit2">
                Confirmar y continuar
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </div>

    </form>
</div>

<script>
    const STEPS_ACCOMMODATION = ['Fotos', 'Primera Habitación', 'Plan Tarifario'];
    const STEPS_TOURS         = ['Fotos', 'Primer Tour', 'Primera Salida'];
    const STEPS_COMMON        = ['Asistente IA', 'Producto/Servicio', 'WhatsApp', 'Vista Previa'];

    function updateUI() {
        const hasAcc   = document.getElementById('chk_accommodation').checked;
        const hasTours = document.getElementById('chk_tours').checked;

        // Actualizar estilos de tarjetas
        toggleCard('card_accommodation', hasAcc);
        toggleCard('card_tours',         hasTours);

        // Actualizar badges dentro de las tarjetas
        updateBadge('card_accommodation', hasAcc);
        updateBadge('card_tours',         hasTours);

        // Preview de pasos
        let activeSteps = [];
        if (hasAcc)   activeSteps = [...activeSteps, ...STEPS_ACCOMMODATION];
        if (hasTours) activeSteps = [...activeSteps, ...STEPS_TOURS];
        activeSteps = [...new Set(activeSteps), ...STEPS_COMMON]; // deduplicar "Fotos"

        const preview = document.getElementById('stepsPreview');
        preview.innerHTML = activeSteps.map(s =>
            `<span class="badge bg-light text-dark border">${s}</span>`
        ).join('');

        // Mostrar/ocultar warning
        document.getElementById('profileWarning').classList.toggle('d-none', hasAcc || hasTours);
    }

    function toggleCard(cardId, active) {
        const card = document.getElementById(cardId);
        card.classList.toggle('border-primary',          active);
        card.classList.toggle('bg-primary',              active);
        card.classList.toggle('bg-opacity-10',           active);
        card.querySelector('i').style.color = active ? '#6366f1' : '#94a3b8';
    }

    function updateBadge(cardId, active) {
        const badge = document.getElementById(cardId).querySelector('.badge');
        badge.className = active ? 'badge bg-primary' : 'badge bg-light text-muted border';
        badge.textContent = active ? '✓ Seleccionado' : 'Seleccionar';
    }

    // Validar antes de submit
    document.getElementById('formStep2').addEventListener('submit', function(e) {
        const hasAcc   = document.getElementById('chk_accommodation').checked;
        const hasTours = document.getElementById('chk_tours').checked;

        if (!hasAcc && !hasTours) {
            e.preventDefault();
            document.getElementById('profileWarning').classList.remove('d-none');
            return;
        }

        const btn = document.getElementById('btnSubmit2');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    });

    // Listeners en los checkboxes ocultos
    document.querySelectorAll('.profile-check').forEach(chk => {
        chk.addEventListener('change', updateUI);
    });

    // Ejecutar al cargar para reflejar estado inicial
    document.addEventListener('DOMContentLoaded', updateUI);
</script>