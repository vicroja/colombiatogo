<?php
/**
 * Template: Resort & Naturaleza
 * Diseño para cabañas, glamping, ecohoteles y alojamientos rurales.
 */
$isPreview      = $isPreview ?? false;
$primaryColor   = esc($website['primary_color'] ?? '#1D9E75');
$heroImage      = !empty($media)
    ? base_url(collect($media)->firstWhere('is_main', 1)['file_path']
        ?? $media[0]['file_path'])
    : '';
$agentRef       = $isPreview ? '' : (isset($_GET['ref']) ? esc($_GET['ref']) : '');
$currencySymbol = $tenant['currency_symbol'] ?? '$';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($tenant['name']) ?> — Reservas Oficiales</title>
    <meta name="description" content="<?= esc($website['about_text'] ? substr(strip_tags($website['about_text']), 0, 155) : 'Reserva directamente en ' . $tenant['name']) ?>">
    <meta property="og:title"       content="<?= esc($website['hero_title'] ?: $tenant['name']) ?>">
    <meta property="og:description" content="<?= esc($website['hero_subtitle'] ?? '') ?>">
    <?php if ($heroImage): ?>
        <meta property="og:image" content="<?= $heroImage ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root{--primary:<?= $primaryColor ?>;--primary-dark:color-mix(in srgb,<?= $primaryColor ?> 80%,#000)}
        *{box-sizing:border-box}
        body{font-family:'DM Sans',system-ui,sans-serif;color:#1a1a1a;background:#fff}

        /* Nav */
        .site-nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,.95);
            backdrop-filter:blur(8px);border-bottom:1px solid rgba(0,0,0,.07);
            padding:.75rem 0}
        .site-nav .brand{font-family:'Playfair Display',serif;font-size:1.2rem;
            color:#0f172a;text-decoration:none;display:flex;
            align-items:center;gap:.6rem}
        .site-nav .brand img{height:36px;border-radius:6px;object-fit:contain}
        .nav-cta{background:var(--primary);color:#fff!important;padding:.45rem 1.1rem;
            border-radius:8px;font-weight:600;font-size:.85rem;text-decoration:none;
            transition:all .2s}
        .nav-cta:hover{background:var(--primary-dark);transform:translateY(-1px)}

        /* Hero */
        .hero{min-height:88vh;display:flex;align-items:center;justify-content:center;
            text-align:center;position:relative;overflow:hidden;
            background:#0f172a}
        .hero-bg{position:absolute;inset:0;background-size:cover;background-position:center;
            transition:opacity .5s}
        .hero-overlay{position:absolute;inset:0;
            background:linear-gradient(to bottom,
            rgba(0,0,0,.35) 0%,rgba(0,0,0,.55) 100%)}
        .hero-content{position:relative;z-index:1;padding:2rem;max-width:700px}
        .hero-eyebrow{display:inline-block;background:rgba(255,255,255,.15);
            color:#fff;font-size:.72rem;font-weight:600;letter-spacing:.1em;
            text-transform:uppercase;padding:.3rem .9rem;border-radius:99px;
            border:1px solid rgba(255,255,255,.25);margin-bottom:1.25rem}
        .hero-title{font-family:'Playfair Display',serif;font-size:clamp(2rem,6vw,3.8rem);
            font-weight:700;color:#fff;line-height:1.1;margin-bottom:1rem}
        .hero-subtitle{font-size:1.05rem;color:rgba(255,255,255,.85);
            font-weight:300;margin-bottom:2rem;line-height:1.6}
        .hero-btn{display:inline-block;background:var(--primary);color:#fff;
            padding:.9rem 2.25rem;border-radius:10px;font-weight:600;
            font-size:1rem;text-decoration:none;transition:all .2s;
            box-shadow:0 4px 16px rgba(0,0,0,.3)}
        .hero-btn:hover{background:var(--primary-dark);color:#fff;
            transform:translateY(-2px)}

        /* Stats strip */
        .stats-strip{background:var(--primary);color:#fff;padding:.85rem 0}
        .stat-item{text-align:center;padding:.25rem}
        .stat-n{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700}
        .stat-l{font-size:.7rem;opacity:.8;text-transform:uppercase;letter-spacing:.05em}

        /* Sections */
        .section{padding:5rem 0}
        .section-alt{background:#f8faf8}
        .section-label{font-size:.72rem;font-weight:700;letter-spacing:.12em;
            text-transform:uppercase;color:var(--primary);margin-bottom:.6rem}
        .section-title{font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3vw,2.5rem);
            font-weight:700;color:#0f172a;line-height:1.2;margin-bottom:.75rem}
        .section-sub{font-size:1rem;color:#4b5563;font-weight:300;line-height:1.75}

        /* Unit cards */
        .unit-card{background:#fff;border-radius:16px;overflow:hidden;
            border:1px solid #e5e7eb;transition:transform .2s,box-shadow .2s}
        .unit-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1)}
        .unit-photo{height:220px;background:#f1f5f9;overflow:hidden;position:relative}
        .unit-photo img{width:100%;height:100%;object-fit:cover;transition:transform .4s}
        .unit-card:hover .unit-photo img{transform:scale(1.05)}
        .unit-photo-placeholder{width:100%;height:100%;display:flex;align-items:center;
            justify-content:center;font-size:3rem;color:#cbd5e1;
            background:linear-gradient(135deg,#f8fafc,#f1f5f9)}
        .unit-body{padding:1.25rem}
        .unit-name{font-weight:700;font-size:1rem;color:#0f172a;margin-bottom:.35rem}
        .unit-meta{font-size:.8rem;color:#6b7280;display:flex;gap:1rem;margin-bottom:.75rem}
        .unit-price{font-size:1.1rem;font-weight:700;color:var(--primary)}
        .unit-price span{font-size:.75rem;font-weight:400;color:#9ca3af}
        .btn-book{background:var(--primary);color:#fff;border:none;border-radius:8px;
            padding:.55rem 1.25rem;font-size:.875rem;font-weight:600;
            cursor:pointer;transition:all .2s;width:100%;margin-top:.75rem}
        .btn-book:hover{background:var(--primary-dark)}

        /* Gallery */
        .gallery-grid{columns:3;gap:.75rem}
        @media(max-width:768px){.gallery-grid{columns:2}}
        @media(max-width:480px){.gallery-grid{columns:1}}
        .gallery-item{break-inside:avoid;margin-bottom:.75rem;border-radius:10px;
            overflow:hidden;cursor:pointer;position:relative}
        .gallery-item img{width:100%;display:block;transition:transform .3s}
        .gallery-item:hover img{transform:scale(1.03)}

        /* About */
        .about-wrap{display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center}
        @media(max-width:768px){.about-wrap{grid-template-columns:1fr}}
        .about-img{border-radius:20px;overflow:hidden;aspect-ratio:4/3}
        .about-img img{width:100%;height:100%;object-fit:cover}

        /* Policies */
        .policy-item{display:flex;align-items:flex-start;gap:.75rem;
            padding:.75rem 0;border-bottom:1px solid #f3f4f6}
        .policy-item:last-child{border-bottom:none}
        .policy-icon{width:32px;height:32px;border-radius:8px;
            background:color-mix(in srgb,var(--primary) 10%,#fff);
            display:flex;align-items:center;justify-content:center;
            color:var(--primary);flex-shrink:0;font-size:.9rem}
        .policy-text{font-size:.875rem;color:#374151;line-height:1.5}

        /* Modal de reserva */
        .modal-header-custom{background:var(--primary);color:#fff;border-radius:16px 16px 0 0}
        .modal-content{border-radius:16px;border:none;
            box-shadow:0 24px 64px rgba(0,0,0,.2)}
        .step-indicator{display:flex;gap:.5rem;margin-bottom:1.25rem}
        .step-dot{width:8px;height:8px;border-radius:50%;background:#e5e7eb;flex:1;height:3px}
        .step-dot.active{background:var(--primary)}

        /* WhatsApp FAB */
        .wa-fab{position:fixed;bottom:1.5rem;right:1.5rem;z-index:200;
            background:#25D366;color:#fff;width:56px;height:56px;
            border-radius:50%;display:flex;align-items:center;justify-content:center;
            font-size:1.5rem;text-decoration:none;box-shadow:0 4px 16px rgba(37,211,102,.4);
            transition:transform .2s,box-shadow .2s}
        .wa-fab:hover{transform:scale(1.1);color:#fff;
            box-shadow:0 6px 24px rgba(37,211,102,.5)}

        /* Footer */
        .site-footer{background:#0f172a;color:#94a3b8;padding:3rem 0 1.5rem}
        .footer-brand{font-family:'Playfair Display',serif;font-size:1.2rem;
            color:#fff;margin-bottom:.35rem}
        .footer-link{color:#94a3b8;text-decoration:none;font-size:.82rem;transition:color .2s}
        .footer-link:hover{color:#fff}

        /* Preview banner */
        .preview-banner{background:#6366f1;color:#fff;text-align:center;
            padding:.5rem;font-size:.78rem;font-weight:600;
            position:sticky;top:0;z-index:1000}
    </style>
</head>
<body>

<?php if ($isPreview): ?>
    <div class="preview-banner">
        <i class="bi bi-eye me-1"></i>
        Vista previa — los formularios están desactivados
    </div>
<?php endif; ?>

<!-- ── Navbar ─────────────────────────────────────────────────────────────── -->
<nav class="site-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="#" class="brand">
            <?php if (!empty($tenant['logo_path'])): ?>
                <img src="<?= base_url($tenant['logo_path']) ?>"
                     alt="<?= esc($tenant['name']) ?>">
            <?php endif; ?>
            <?= esc($tenant['name']) ?>
        </a>
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($website['instagram_url'])): ?>
                <a href="<?= esc($website['instagram_url']) ?>"
                   target="_blank" class="text-secondary fs-5">
                    <i class="bi bi-instagram"></i>
                </a>
            <?php endif; ?>
            <a href="#habitaciones" class="nav-cta">
                Reservar ahora
            </a>
        </div>
    </div>
</nav>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section class="hero">
    <?php if ($heroImage): ?>
        <div class="hero-bg" style="background-image:url('<?= $heroImage ?>')"></div>
    <?php endif; ?>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-eyebrow">
            <?= esc($tenant['city'] ?? '') ?>
        </span>
        <h1 class="hero-title">
            <?= esc($website['hero_title'] ?: 'Bienvenido a ' . $tenant['name']) ?>
        </h1>
        <p class="hero-subtitle">
            <?= esc($website['hero_subtitle'] ?? '') ?>
        </p>
        <a href="#habitaciones" class="hero-btn">
            Ver disponibilidad
        </a>
    </div>
</section>

<!-- ── Stats strip ───────────────────────────────────────────────────────── -->
<div class="stats-strip">
    <div class="container">
        <div class="row g-2 justify-content-center">
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-n"><?= count($units) ?>+</div>
                <div class="stat-l">Unidades disponibles</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-n">
                    <?= $tenant['checkin_time'] ? substr($tenant['checkin_time'], 0, 5) : '3pm' ?>
                </div>
                <div class="stat-l">Check-in</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-n">
                    <?= $tenant['checkout_time'] ? substr($tenant['checkout_time'], 0, 5) : '12pm' ?>
                </div>
                <div class="stat-l">Check-out</div>
            </div>
            <div class="col-6 col-md-3 stat-item">
                <div class="stat-n">0%</div>
                <div class="stat-l">Comisión reserva directa</div>
            </div>
        </div>
    </div>
</div>

<!-- ── About ─────────────────────────────────────────────────────────────── -->
<?php if (!empty($website['about_text'])): ?>
    <section class="section" id="nosotros">
        <div class="container">
            <div class="about-wrap">
                <?php if (count($media) >= 2): ?>
                    <div class="about-img">
                        <img src="<?= base_url($media[1]['file_path']) ?>"
                             alt="<?= esc($tenant['name']) ?>">
                    </div>
                <?php endif; ?>
                <div <?= count($media) < 2 ? 'style="max-width:600px;margin:0 auto"' : '' ?>>
                    <div class="section-label">Nuestra historia</div>
                    <h2 class="section-title">
                        Bienvenido a <?= esc($tenant['name']) ?>
                    </h2>
                    <p class="section-sub">
                        <?= nl2br(esc($website['about_text'])) ?>
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Habitaciones ──────────────────────────────────────────────────────── -->
<section class="section section-alt" id="habitaciones">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-label">Alojamiento</div>
            <h2 class="section-title">Elige tu espacio</h2>
            <p class="section-sub" style="max-width:480px;margin:0 auto">
                Todas nuestras unidades incluyen acceso a instalaciones comunes
                y atención personalizada.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($units as $unit): ?>
                <div class="col-md-4">
                    <div class="unit-card">
                        <div class="unit-photo">
                            <?php if (!empty($unit['main_photo'])): ?>
                                <img src="<?= base_url($unit['main_photo']) ?>"
                                     alt="<?= esc($unit['name']) ?>">
                            <?php else: ?>
                                <div class="unit-photo-placeholder">
                                    <i class="bi bi-house-door"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="unit-body">
                            <div class="unit-name">
                                <?= esc($unit['name']) ?>
                            </div>
                            <div class="unit-meta">
                                <span>
                                    <i class="bi bi-people me-1"></i>
                                    <?= $unit['max_occupancy'] ?? 4 ?> personas
                                </span>
                                <?php if (!empty($unit['bathrooms'])): ?>
                                    <span>
                                        <i class="bi bi-droplet me-1"></i>
                                        <?= $unit['bathrooms'] ?> baño(s)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($unit['description'])): ?>
                                <p style="font-size:.82rem;color:#6b7280;
                                          margin-bottom:.75rem;line-height:1.5">
                                    <?= esc(substr($unit['description'], 0, 100)) ?>
                                    <?= strlen($unit['description']) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between
                                        align-items-center">
                                <?php if (!empty($unit['price_per_night'])): ?>
                                    <div class="unit-price">
                                        <?= $currencySymbol ?>
                                        <?= number_format($unit['price_per_night'], 0, ',', '.') ?>
                                        <span>/ noche</span>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size:.8rem;color:#94a3b8">
                                        Consultar precio
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$isPreview): ?>
                                <button class="btn-book"
                                        data-bs-toggle="modal"
                                        data-bs-target="#bookModal<?= $unit['id'] ?>">
                                    Seleccionar fechas
                                </button>
                            <?php else: ?>
                                <button class="btn-book"
                                        onclick="return false"
                                        style="opacity:.6;cursor:not-allowed">
                                    Seleccionar fechas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!$isPreview): ?>
                    <!-- Modal de reserva -->
                    <div class="modal fade"
                         id="bookModal<?= $unit['id'] ?>"
                         tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form action="<?= base_url('/book/'.$tenant['slug'].'/confirm') ?>"
                                      method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="unit_id"
                                           value="<?= $unit['id'] ?>">
                                    <input type="hidden" name="agent_ref"
                                           value="<?= $agentRef ?>">

                                    <div class="modal-header modal-header-custom">
                                        <h5 class="modal-title mb-0">
                                            Reservar <?= esc($unit['name']) ?>
                                        </h5>
                                        <button type="button"
                                                class="btn-close btn-close-white"
                                                data-bs-dismiss="modal">
                                        </button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="step-indicator">
                                            <div class="step-dot active"></div>
                                            <div class="step-dot active"></div>
                                            <div class="step-dot"></div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Check-in
                                                </label>
                                                <input type="date"
                                                       name="check_in_date"
                                                       class="form-control"
                                                       required
                                                       min="<?= date('Y-m-d') ?>">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Check-out
                                                </label>
                                                <input type="date"
                                                       name="check_out_date"
                                                       class="form-control"
                                                       required
                                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Nombre completo
                                                </label>
                                                <input type="text"
                                                       name="full_name"
                                                       class="form-control"
                                                       required>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Documento
                                                </label>
                                                <input type="text"
                                                       name="document"
                                                       class="form-control"
                                                       required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Adultos
                                                </label>
                                                <input type="number"
                                                       name="adults"
                                                       class="form-control"
                                                       value="2" min="1"
                                                       max="<?= $unit['max_occupancy'] ?? 4 ?>"
                                                       required>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Teléfono
                                                </label>
                                                <input type="text"
                                                       name="phone"
                                                       class="form-control"
                                                       required>
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label small fw-bold">
                                                    Email
                                                </label>
                                                <input type="email"
                                                       name="email"
                                                       class="form-control"
                                                       required>
                                            </div>
                                        </div>

                                        <?php if (!empty($website['policies_text'])): ?>
                                            <div class="alert alert-light
                                                    border small text-muted">
                                                <strong>Políticas:</strong><br>
                                                <?= nl2br(esc($website['policies_text'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="modal-footer bg-light">
                                        <button type="button"
                                                class="btn btn-outline-secondary"
                                                data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit"
                                                class="btn btn-primary fw-bold px-4"
                                                style="background:var(--primary);
                                                   border:none">
                                            Solicitar Reserva
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Galería ────────────────────────────────────────────────────────────── -->
<?php if (count($media) > 1): ?>
    <section class="section" id="galeria">
        <div class="container">
            <div class="text-center mb-4">
                <div class="section-label">Galería</div>
                <h2 class="section-title">Conoce nuestro espacio</h2>
            </div>
            <div class="gallery-grid">
                <?php foreach ($media as $m): ?>
                    <?php if ($m['file_type'] === 'image'): ?>
                        <div class="gallery-item">
                            <img src="<?= base_url($m['file_path']) ?>"
                                 alt="<?= esc($tenant['name']) ?>"
                                 loading="lazy">
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Políticas ─────────────────────────────────────────────────────────── -->
<?php if (!empty($website['policies_text'])): ?>
    <section class="section section-alt" id="politicas">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="text-center mb-4">
                        <div class="section-label">Información</div>
                        <h2 class="section-title">Políticas de estadía</h2>
                    </div>
                    <?php
                    $policies = array_filter(
                        explode("\n", $website['policies_text'])
                    );
                    $icons = ['bi-clock','bi-x-circle','bi-exclamation-circle',
                        'bi-volume-down','bi-people','bi-shield-check'];
                    foreach ($policies as $i => $policy):
                        $policy = trim(ltrim($policy, '•-*'));
                        if (empty($policy)) continue;
                        ?>
                        <div class="policy-item">
                            <div class="policy-icon">
                                <i class="bi <?= $icons[$i % count($icons)] ?>"></i>
                            </div>
                            <div class="policy-text"><?= esc($policy) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="footer-brand"><?= esc($tenant['name']) ?></div>
                <p style="font-size:.82rem;line-height:1.6">
                    <?= esc($tenant['address'] ?? '') ?>
                    <?php if ($tenant['city']): ?>
                        <br><?= esc($tenant['city']) ?>,
                        <?= esc($tenant['country'] ?? '') ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4">
                <div style="font-size:.72rem;font-weight:700;
                            text-transform:uppercase;letter-spacing:.08em;
                            color:#64748b;margin-bottom:.75rem">
                    Contacto
                </div>
                <?php if (!empty($tenant['phone'])): ?>
                    <p style="font-size:.85rem;margin-bottom:.35rem">
                        <i class="bi bi-telephone me-2"></i>
                        <?= esc($tenant['phone']) ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($tenant['email'])): ?>
                    <p style="font-size:.85rem;margin-bottom:.35rem">
                        <i class="bi bi-envelope me-2"></i>
                        <?= esc($tenant['email']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div style="font-size:.72rem;font-weight:700;
                            text-transform:uppercase;letter-spacing:.08em;
                            color:#64748b;margin-bottom:.75rem">
                    Síguenos
                </div>
                <div class="d-flex gap-3">
                    <?php if (!empty($website['instagram_url'])): ?>
                        <a href="<?= esc($website['instagram_url']) ?>"
                           target="_blank" class="footer-link"
                           style="font-size:1.3rem">
                            <i class="bi bi-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($website['facebook_url'])): ?>
                        <a href="<?= esc($website['facebook_url']) ?>"
                           target="_blank" class="footer-link"
                           style="font-size:1.3rem">
                            <i class="bi bi-facebook"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($website['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= esc($website['whatsapp_number']) ?>"
                           target="_blank" class="footer-link"
                           style="font-size:1.3rem;color:#25D366">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="border-top pt-3"
             style="border-color:#1e293b!important">
            <p style="font-size:.72rem;color:#475569;margin:0;text-align:center">
                &copy; <?= date('Y') ?> <?= esc($tenant['name']) ?> &middot;
                Reservas directas sin comisiones &middot;
                <span style="color:#334155">
                    Powered by GuestHandle
                </span>
            </p>
        </div>
    </div>
</footer>

<!-- ── WhatsApp FAB ───────────────────────────────────────────────────────── -->
<?php if (!empty($website['whatsapp_number']) && !$isPreview): ?>
    <a href="https://wa.me/<?= esc($website['whatsapp_number']) ?>?text=Hola%2C+me+interesa+reservar"
       target="_blank" class="wa-fab" title="Reservar por WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>