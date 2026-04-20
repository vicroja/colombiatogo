<?php
/**
 * Template: Boutique Urbano
 *
 * Estética editorial — tipografía grande, mucho espacio en blanco,
 * fotos cuadradas, minimalista y elegante. Ideal para hoteles de ciudad,
 * apartamentos de diseño y propiedades premium.
 */
$isPreview      = $isPreview ?? false;
$primaryColor   = esc($website['primary_color'] ?? '#1a1a1a');
$agentRef       = $isPreview ? '' : (isset($_GET['ref']) ? esc($_GET['ref']) : '');
$currencySymbol = $tenant['currency_symbol'] ?? '$';

// Foto de portada — primera marcada como principal
$coverPhoto = null;
foreach ($media as $m) {
    if ($m['is_main']) { $coverPhoto = $m; break; }
}
if (!$coverPhoto && !empty($media)) $coverPhoto = $media[0];

// Fotos de galería (excluyendo la portada)
$galleryPhotos = array_filter($media, fn($m) =>
    $m['file_type'] === 'image' && $m['id'] !== ($coverPhoto['id'] ?? null)
);
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
    <?php if ($coverPhoto): ?>
        <meta property="og:image" content="<?= base_url($coverPhoto['file_path']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* ── Variables ─────────────────────────────────────────────────────────── */
        :root {
            --primary     : <?= $primaryColor ?>;
            --serif       : 'Cormorant Garamond', Georgia, serif;
            --sans        : 'DM Sans', system-ui, sans-serif;
            --cream       : #faf9f7;
            --ink         : #1a1a1a;
            --muted       : #6b6b6b;
            --border      : #e8e5e0;
        }

        /* ── Reset ─────────────────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
        html { scroll-behavior: smooth }
        body {
            font-family  : var(--sans);
            color        : var(--ink);
            background   : #fff;
            font-size    : 16px;
            line-height  : 1.6;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; display: block }
        a   { text-decoration: none; color: inherit }

        /* ── Preview banner ────────────────────────────────────────────────────── */
        .preview-banner {
            background  : #6366f1;
            color       : #fff;
            text-align  : center;
            padding     : .45rem;
            font-size   : .75rem;
            font-weight : 600;
            font-family : var(--sans);
        }

        /* ── Nav ───────────────────────────────────────────────────────────────── */
        .b-nav {
            position      : sticky;
            top           : 0;
            z-index       : 100;
            background    : rgba(255,255,255,.96);
            border-bottom : 1px solid var(--border);
            backdrop-filter: blur(8px);
        }
        .b-nav-inner {
            max-width   : 1200px;
            margin      : 0 auto;
            padding     : 0 2rem;
            height      : 60px;
            display     : flex;
            align-items : center;
            justify-content: space-between;
        }
        .b-brand {
            font-family    : var(--serif);
            font-size      : 1.25rem;
            font-weight    : 400;
            color          : var(--ink);
            letter-spacing : 0.02em;
            display        : flex;
            align-items    : center;
            gap            : .65rem;
        }
        .b-brand img {
            height        : 32px;
            object-fit    : contain;
            border-radius : 4px;
        }
        .b-nav-links {
            display     : flex;
            align-items : center;
            gap         : 2rem;
        }
        .b-nav-link {
            font-size      : .78rem;
            font-weight    : 500;
            color          : var(--muted);
            letter-spacing : .06em;
            text-transform : uppercase;
            transition     : color .15s;
        }
        .b-nav-link:hover { color: var(--ink) }
        .b-nav-cta {
            background    : var(--primary);
            color         : #fff !important;
            padding       : .45rem 1.1rem;
            border-radius : 6px;
            font-size     : .78rem;
            font-weight   : 500;
            letter-spacing: .04em;
            text-transform: uppercase;
            transition    : opacity .15s;
        }
        .b-nav-cta:hover { opacity: .85 }

        /* ── Split Hero ─────────────────────────────────────────────────────────── */
        .b-hero {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            min-height            : calc(100vh - 60px);
        }
        .b-hero-text {
            display         : flex;
            flex-direction  : column;
            justify-content : center;
            padding         : 5rem 4rem 5rem 6rem;
            background      : var(--cream);
            position        : relative;
        }
        .b-hero-eyebrow {
            font-size      : .7rem;
            font-weight    : 500;
            letter-spacing : .14em;
            text-transform : uppercase;
            color          : var(--primary);
            margin-bottom  : 1.25rem;
            display        : flex;
            align-items    : center;
            gap            : .6rem;
        }
        .b-hero-eyebrow::before {
            content    : '';
            width      : 24px;
            height     : 1px;
            background : var(--primary);
            display    : block;
        }
        .b-hero-title {
            font-family    : var(--serif);
            font-size      : clamp(2.4rem, 4vw, 4rem);
            font-weight    : 300;
            line-height    : 1.08;
            color          : var(--ink);
            margin-bottom  : 1.5rem;
            letter-spacing : -0.01em;
        }
        .b-hero-title em {
            font-style : italic;
            font-weight: 300;
            color      : var(--primary);
        }
        .b-hero-sub {
            font-size     : 1rem;
            color         : var(--muted);
            font-weight   : 300;
            line-height   : 1.75;
            max-width     : 400px;
            margin-bottom : 2.5rem;
        }
        .b-hero-btn {
            display       : inline-flex;
            align-items   : center;
            gap           : .75rem;
            background    : var(--primary);
            color         : #fff;
            padding       : .85rem 2rem;
            border-radius : 6px;
            font-size     : .85rem;
            font-weight   : 500;
            letter-spacing: .04em;
            transition    : opacity .2s, transform .2s;
            align-self    : flex-start;
        }
        .b-hero-btn:hover {
            color     : #fff;
            opacity   : .88;
            transform : translateY(-1px);
        }
        .b-hero-btn i { font-size: 1rem }
        .b-hero-meta {
            position     : absolute;
            bottom       : 2rem;
            left         : 6rem;
            display      : flex;
            gap          : 2.5rem;
        }
        .b-meta-item .mn {
            font-family : var(--serif);
            font-size   : 1.6rem;
            font-weight : 300;
            color       : var(--ink);
            line-height : 1;
        }
        .b-meta-item .ml {
            font-size      : .65rem;
            letter-spacing : .1em;
            text-transform : uppercase;
            color          : var(--muted);
            margin-top     : .2rem;
        }
        .b-hero-photo {
            position   : relative;
            overflow   : hidden;
            background : #e8e5e0;
        }
        .b-hero-photo img {
            width      : 100%;
            height     : 100%;
            object-fit : cover;
            transition : transform 8s ease;
        }
        .b-hero-photo:hover img { transform: scale(1.04) }
        .b-hero-photo-placeholder {
            width           : 100%;
            height          : 100%;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : 4rem;
            color           : #c5bfb8;
            background      : linear-gradient(135deg, #f5f3f0, #ece9e4);
        }

        /* ── Container ─────────────────────────────────────────────────────────── */
        .b-container {
            max-width : 1200px;
            margin    : 0 auto;
            padding   : 0 2rem;
        }

        /* ── Section base ───────────────────────────────────────────────────────── */
        .b-section { padding: 6rem 0 }
        .b-section-alt { background: var(--cream) }
        .b-label {
            font-size      : .68rem;
            font-weight    : 500;
            letter-spacing : .16em;
            text-transform : uppercase;
            color          : var(--primary);
            margin-bottom  : 1rem;
            display        : flex;
            align-items    : center;
            gap            : .5rem;
        }
        .b-label::before {
            content    : '';
            width      : 20px;
            height     : 1px;
            background : var(--primary);
        }
        .b-title {
            font-family    : var(--serif);
            font-size      : clamp(1.8rem, 3vw, 3rem);
            font-weight    : 300;
            color          : var(--ink);
            line-height    : 1.15;
            letter-spacing : -0.01em;
        }
        .b-title em { font-style: italic; color: var(--primary) }

        /* ── About ──────────────────────────────────────────────────────────────── */
        .b-about {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 6rem;
            align-items           : center;
        }
        .b-about-text {
            font-size   : 1.05rem;
            color       : #4a4a4a;
            line-height : 1.85;
            font-weight : 300;
        }
        .b-about-photo {
            position      : relative;
            border-radius : 4px;
            overflow      : hidden;
            aspect-ratio  : 3/4;
        }
        .b-about-photo img {
            width      : 100%;
            height     : 100%;
            object-fit : cover;
        }
        .b-about-photo-accent {
            position      : absolute;
            bottom        : -1.5rem;
            right         : -1.5rem;
            width         : 120px;
            height        : 120px;
            border-radius : 4px;
            background    : var(--primary);
            opacity       : .15;
            z-index       : -1;
        }

        /* ── Units — layout editorial ────────────────────────────────────────────── */
        .b-units-header {
            display         : flex;
            justify-content : space-between;
            align-items     : flex-end;
            margin-bottom   : 3.5rem;
            padding-bottom  : 1.5rem;
            border-bottom   : 1px solid var(--border);
        }
        .b-unit-row {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 4rem;
            align-items           : center;
            padding               : 3rem 0;
            border-bottom         : 1px solid var(--border);
        }
        .b-unit-row:nth-child(even) .b-unit-image { order: 1 }
        .b-unit-row:nth-child(even) .b-unit-info  { order: 0 }
        .b-unit-image {
            position      : relative;
            border-radius : 4px;
            overflow      : hidden;
            aspect-ratio  : 16/10;
            background    : var(--cream);
        }
        .b-unit-image img {
            width      : 100%;
            height     : 100%;
            object-fit : cover;
            transition : transform .5s ease;
        }
        .b-unit-row:hover .b-unit-image img { transform: scale(1.03) }
        .b-unit-image-placeholder {
            width           : 100%;
            height          : 100%;
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : 3rem;
            color           : #c5bfb8;
        }
        .b-unit-num {
            font-family    : var(--serif);
            font-size      : 4rem;
            font-weight    : 300;
            color          : var(--border);
            line-height    : 1;
            margin-bottom  : .5rem;
        }
        .b-unit-name {
            font-family    : var(--serif);
            font-size      : 1.75rem;
            font-weight    : 400;
            color          : var(--ink);
            margin-bottom  : .75rem;
            letter-spacing : -0.01em;
        }
        .b-unit-desc {
            font-size     : .9rem;
            color         : var(--muted);
            line-height   : 1.7;
            font-weight   : 300;
            margin-bottom : 1.25rem;
        }
        .b-unit-specs {
            display       : flex;
            gap           : 1.5rem;
            margin-bottom : 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom : 1px solid var(--border);
        }
        .b-unit-spec {
            display        : flex;
            flex-direction : column;
            gap            : .2rem;
        }
        .b-unit-spec .sv {
            font-family : var(--serif);
            font-size   : 1.2rem;
            font-weight : 400;
            color       : var(--ink);
        }
        .b-unit-spec .sl {
            font-size      : .65rem;
            letter-spacing : .08em;
            text-transform : uppercase;
            color          : var(--muted);
        }
        .b-unit-price-row {
            display     : flex;
            align-items : center;
            justify-content: space-between;
            gap         : 1rem;
        }
        .b-price {
            font-family : var(--serif);
            font-size   : 1.5rem;
            font-weight : 400;
            color       : var(--ink);
        }
        .b-price-sub {
            font-size   : .75rem;
            color       : var(--muted);
            font-weight : 300;
            margin-top  : .1rem;
        }
        .b-book-btn {
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
            background    : var(--ink);
            color         : #fff;
            border        : none;
            padding       : .7rem 1.5rem;
            border-radius : 5px;
            font-size     : .82rem;
            font-weight   : 500;
            letter-spacing: .04em;
            cursor        : pointer;
            transition    : background .2s, transform .15s;
            white-space   : nowrap;
        }
        .b-book-btn:hover {
            background : var(--primary);
            transform  : translateY(-1px);
        }

        /* ── Gallery — masonry editorial ────────────────────────────────────────── */
        .b-gallery {
            display               : grid;
            grid-template-columns : repeat(12, 1fr);
            grid-auto-rows        : 120px;
            gap                   : .75rem;
        }
        .b-gallery-item {
            overflow      : hidden;
            border-radius : 4px;
            cursor        : pointer;
            position      : relative;
        }
        .b-gallery-item img {
            width      : 100%;
            height     : 100%;
            object-fit : cover;
            transition : transform .4s ease;
        }
        .b-gallery-item:hover img { transform: scale(1.06) }
        /* Layout específico por posición */
        .b-gallery-item:nth-child(1){ grid-column: span 5; grid-row: span 3 }
        .b-gallery-item:nth-child(2){ grid-column: span 4; grid-row: span 2 }
        .b-gallery-item:nth-child(3){ grid-column: span 3; grid-row: span 2 }
        .b-gallery-item:nth-child(4){ grid-column: span 3; grid-row: span 2 }
        .b-gallery-item:nth-child(5){ grid-column: span 4; grid-row: span 2 }
        .b-gallery-item:nth-child(6){ grid-column: span 5; grid-row: span 2 }
        .b-gallery-item:nth-child(n+7){ grid-column: span 4; grid-row: span 2 }

        /* ── Policies ───────────────────────────────────────────────────────────── */
        .b-policies-grid {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(240px, 1fr));
            gap                   : 2rem;
            margin-top            : 3rem;
        }
        .b-policy-card {
            padding       : 1.75rem;
            border        : 1px solid var(--border);
            border-radius : 8px;
            background    : #fff;
            transition    : border-color .2s;
        }
        .b-policy-card:hover { border-color: var(--primary) }
        .b-policy-icon {
            width           : 36px;
            height          : 36px;
            border-radius   : 6px;
            background      : color-mix(in srgb, var(--primary) 10%, #fff);
            display         : flex;
            align-items     : center;
            justify-content : center;
            color           : var(--primary);
            font-size       : .9rem;
            margin-bottom   : .85rem;
        }
        .b-policy-title {
            font-size   : .8rem;
            font-weight : 600;
            color       : var(--ink);
            margin-bottom: .3rem;
            letter-spacing: .02em;
        }
        .b-policy-text {
            font-size   : .82rem;
            color       : var(--muted);
            line-height : 1.6;
            font-weight : 300;
        }

        /* ── Contact strip ──────────────────────────────────────────────────────── */
        .b-contact {
            background    : var(--ink);
            color         : #fff;
            padding       : 5rem 0;
            text-align    : center;
        }
        .b-contact-title {
            font-family    : var(--serif);
            font-size      : clamp(2rem, 4vw, 3.5rem);
            font-weight    : 300;
            margin-bottom  : .75rem;
            letter-spacing : -0.01em;
        }
        .b-contact-title em { font-style: italic; color: color-mix(in srgb, var(--primary) 90%, #fff) }
        .b-contact-sub {
            font-size     : 1rem;
            color         : #94a3b8;
            font-weight   : 300;
            margin-bottom : 2.5rem;
        }
        .b-contact-btn {
            display       : inline-flex;
            align-items   : center;
            gap           : .65rem;
            background    : var(--primary);
            color         : #fff;
            padding       : 1rem 2.5rem;
            border-radius : 6px;
            font-size     : .9rem;
            font-weight   : 500;
            letter-spacing: .04em;
            transition    : opacity .2s, transform .2s;
        }
        .b-contact-btn:hover { color: #fff; opacity: .88; transform: translateY(-2px) }
        .b-contact-links {
            display         : flex;
            gap             : 1.5rem;
            justify-content : center;
            margin-top      : 2rem;
        }
        .b-contact-link {
            color      : #64748b;
            font-size  : 1.25rem;
            transition : color .2s;
        }
        .b-contact-link:hover { color: #fff }

        /* ── Footer ─────────────────────────────────────────────────────────────── */
        .b-footer {
            background     : #111;
            color          : #475569;
            padding        : 1.5rem 0;
            text-align     : center;
            font-size      : .72rem;
            letter-spacing : .04em;
        }
        .b-footer a { color: #475569; transition: color .2s }
        .b-footer a:hover { color: #94a3b8 }

        /* ── Modal ──────────────────────────────────────────────────────────────── */
        .b-modal .modal-content {
            border-radius : 12px;
            border        : none;
            box-shadow    : 0 32px 80px rgba(0,0,0,.25);
            overflow      : hidden;
        }
        .b-modal-header {
            background : var(--primary);
            color      : #fff;
            padding    : 1.25rem 1.5rem;
            display    : flex;
            align-items: center;
            justify-content: space-between;
        }
        .b-modal-title {
            font-family : var(--serif);
            font-size   : 1.2rem;
            font-weight : 400;
        }
        .b-modal-body { padding: 1.75rem }
        .b-form-label {
            font-size      : .7rem;
            font-weight    : 600;
            letter-spacing : .08em;
            text-transform : uppercase;
            color          : var(--muted);
            display        : block;
            margin-bottom  : .35rem;
        }
        .b-form-control {
            width         : 100%;
            border        : 1px solid var(--border);
            border-radius : 6px;
            padding       : .6rem .85rem;
            font-size     : .9rem;
            font-family   : var(--sans);
            color         : var(--ink);
            outline       : none;
            transition    : border-color .2s;
            background    : #fff;
        }
        .b-form-control:focus { border-color: var(--primary) }
        .b-submit-btn {
            background    : var(--primary);
            color         : #fff;
            border        : none;
            padding       : .85rem 2rem;
            border-radius : 6px;
            font-size     : .88rem;
            font-weight   : 500;
            cursor        : pointer;
            transition    : opacity .2s;
            letter-spacing: .03em;
        }
        .b-submit-btn:hover { opacity: .88 }

        /* ── WhatsApp FAB ───────────────────────────────────────────────────────── */
        .b-wa-fab {
            position      : fixed;
            bottom        : 1.5rem;
            right         : 1.5rem;
            z-index       : 200;
            background    : #25D366;
            color         : #fff;
            width         : 52px;
            height        : 52px;
            border-radius : 50%;
            display       : flex;
            align-items   : center;
            justify-content: center;
            font-size     : 1.4rem;
            box-shadow    : 0 4px 16px rgba(37,211,102,.4);
            transition    : transform .2s;
        }
        .b-wa-fab:hover { transform: scale(1.1); color: #fff }

        /* ── Lightbox simple ────────────────────────────────────────────────────── */
        .b-lightbox {
            position        : fixed;
            inset           : 0;
            background      : rgba(0,0,0,.93);
            z-index         : 9999;
            display         : none;
            align-items     : center;
            justify-content : center;
            padding         : 2rem;
        }
        .b-lightbox.open { display: flex }
        .b-lightbox img {
            max-width  : 92vw;
            max-height : 88vh;
            object-fit : contain;
            border-radius: 4px;
        }
        .b-lightbox-close {
            position  : absolute;
            top       : 1.25rem;
            right     : 1.5rem;
            color     : #fff;
            font-size : 1.75rem;
            cursor    : pointer;
            background: none;
            border    : none;
            line-height: 1;
        }

        /* ── Responsive ─────────────────────────────────────────────────────────── */
        @media (max-width: 900px) {
            .b-hero              { grid-template-columns: 1fr; min-height: auto }
            .b-hero-text         { padding: 4rem 2rem 3rem; order: 1 }
            .b-hero-photo        { height: 55vw; order: 0 }
            .b-hero-meta         { left: 2rem; bottom: 1.5rem }
            .b-about             { grid-template-columns: 1fr; gap: 2.5rem }
            .b-about-photo       { aspect-ratio: 16/9; order: -1 }
            .b-unit-row          { grid-template-columns: 1fr; gap: 1.5rem }
            .b-unit-row:nth-child(even) .b-unit-image,
            .b-unit-row:nth-child(even) .b-unit-info { order: unset }
            .b-gallery           { grid-template-columns: repeat(2,1fr);
                grid-auto-rows: 160px }
            .b-gallery-item      { grid-column: span 1 !important;
                grid-row: span 1 !important }
            .b-nav-links         { display: none }
            .b-units-header      { flex-direction: column; align-items: flex-start;
                gap: 1rem }
        }
    </style>
</head>
<body>

<?php if ($isPreview): ?>
    <div class="preview-banner">
        <i class="bi bi-eye me-1"></i> Vista previa — los formularios están desactivados
    </div>
<?php endif; ?>

<!-- ── Navbar ─────────────────────────────────────────────────────────────── -->
<nav class="b-nav">
    <div class="b-nav-inner">
        <a href="#" class="b-brand">
            <?php if (!empty($tenant['logo_path'])): ?>
                <img src="<?= base_url($tenant['logo_path']) ?>"
                     alt="<?= esc($tenant['name']) ?>">
            <?php endif; ?>
            <?= esc($tenant['name']) ?>
        </a>
        <div class="b-nav-links">
            <?php if (!empty($website['about_text'])): ?>
                <a href="#nosotros" class="b-nav-link">Nosotros</a>
            <?php endif; ?>
            <a href="#alojamiento" class="b-nav-link">Alojamiento</a>
            <?php if (!empty($galleryPhotos)): ?>
                <a href="#galeria" class="b-nav-link">Galería</a>
            <?php endif; ?>
            <a href="#alojamiento" class="b-nav-cta">Reservar</a>
        </div>
    </div>
</nav>

<!-- ── Hero split ─────────────────────────────────────────────────────────── -->
<div class="b-hero">
    <!-- Texto izquierda -->
    <div class="b-hero-text">
        <div class="b-hero-eyebrow">
            <?= esc($tenant['city'] ?? 'Alojamiento boutique') ?>
        </div>
        <h1 class="b-hero-title">
            <?php
            $heroTitle = $website['hero_title'] ?: $tenant['name'];
            // Italizar la última palabra para efecto editorial
            $words = explode(' ', $heroTitle);
            if (count($words) > 1) {
                $last   = array_pop($words);
                echo esc(implode(' ', $words)) . ' <em>' . esc($last) . '</em>';
            } else {
                echo '<em>' . esc($heroTitle) . '</em>';
            }
            ?>
        </h1>
        <?php if (!empty($website['hero_subtitle'])): ?>
            <p class="b-hero-sub">
                <?= esc($website['hero_subtitle']) ?>
            </p>
        <?php endif; ?>
        <a href="#alojamiento" class="b-hero-btn">
            Ver disponibilidad
            <i class="bi bi-arrow-right"></i>
        </a>

        <!-- Métricas -->
        <div class="b-hero-meta">
            <div class="b-meta-item">
                <div class="mn"><?= count($units) ?></div>
                <div class="ml">Unidades</div>
            </div>
            <div class="b-meta-item">
                <div class="mn">
                    <?= $tenant['checkin_time']
                        ? substr($tenant['checkin_time'], 0, 5)
                        : '3pm' ?>
                </div>
                <div class="ml">Check-in</div>
            </div>
            <div class="b-meta-item">
                <div class="mn">0%</div>
                <div class="ml">Comisión</div>
            </div>
        </div>
    </div>

    <!-- Foto derecha -->
    <div class="b-hero-photo">
        <?php if ($coverPhoto): ?>
            <img src="<?= base_url($coverPhoto['file_path']) ?>"
                 alt="<?= esc($tenant['name']) ?>">
        <?php else: ?>
            <div class="b-hero-photo-placeholder">
                <i class="bi bi-building"></i>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── About ──────────────────────────────────────────────────────────────── -->
<?php if (!empty($website['about_text'])): ?>
    <section class="b-section" id="nosotros">
        <div class="b-container">
            <div class="b-about">
                <div>
                    <div class="b-label">Nuestra historia</div>
                    <h2 class="b-title" style="margin-bottom:1.75rem">
                        <?= esc($tenant['name']) ?>,<br>
                        <em>un lugar para recordar</em>
                    </h2>
                    <p class="b-about-text">
                        <?= nl2br(esc($website['about_text'])) ?>
                    </p>
                </div>
                <?php if (count($media) >= 2): ?>
                    <div class="b-about-photo">
                        <img src="<?= base_url($media[1]['file_path']) ?>"
                             alt="<?= esc($tenant['name']) ?>">
                        <div class="b-about-photo-accent"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Alojamiento ────────────────────────────────────────────────────────── -->
<section class="b-section b-section-alt" id="alojamiento">
    <div class="b-container">
        <div class="b-units-header">
            <div>
                <div class="b-label">Alojamiento</div>
                <h2 class="b-title">
                    Elige tu <em>espacio ideal</em>
                </h2>
            </div>
            <p style="font-size:.85rem;color:var(--muted);max-width:280px;
                      font-weight:300;text-align:right;line-height:1.6">
                Todas las unidades incluyen acceso completo a las
                instalaciones y atención personalizada.
            </p>
        </div>

        <?php foreach ($units as $i => $unit): ?>
            <div class="b-unit-row">
                <!-- Imagen -->
                <div class="b-unit-image">
                    <?php if (!empty($unit['main_photo'])): ?>
                        <img src="<?= base_url($unit['main_photo']) ?>"
                             alt="<?= esc($unit['name']) ?>">
                    <?php else: ?>
                        <div class="b-unit-image-placeholder">
                            <i class="bi bi-house-door"
                               style="color:#c5bfb8;font-size:3rem"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="b-unit-info">
                    <div class="b-unit-num">0<?= $i + 1 ?></div>
                    <div class="b-unit-name"><?= esc($unit['name']) ?></div>

                    <?php if (!empty($unit['description'])): ?>
                        <p class="b-unit-desc">
                            <?= esc(substr($unit['description'], 0, 160)) ?>
                            <?= strlen($unit['description']) > 160 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>

                    <div class="b-unit-specs">
                        <div class="b-unit-spec">
                        <span class="sv">
                            <?= $unit['max_occupancy'] ?? 4 ?>
                        </span>
                            <span class="sl">Personas</span>
                        </div>
                        <?php if (!empty($unit['bathrooms'])): ?>
                            <div class="b-unit-spec">
                                <span class="sv"><?= $unit['bathrooms'] ?></span>
                                <span class="sl">Baños</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($unit['beds_info'])): ?>
                            <div class="b-unit-spec">
                        <span class="sv" style="font-size:.9rem">
                            <?= esc($unit['beds_info']) ?>
                        </span>
                                <span class="sl">Camas</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="b-unit-price-row">
                        <div>
                            <?php if (!empty($unit['price_per_night'])): ?>
                                <div class="b-price">
                                    <?= $currencySymbol ?>
                                    <?= number_format($unit['price_per_night'], 0, ',', '.') ?>
                                </div>
                                <div class="b-price-sub">por noche · impuestos incluidos</div>
                            <?php else: ?>
                                <div class="b-price-sub">Consultar precio</div>
                            <?php endif; ?>
                        </div>

                        <?php if (!$isPreview): ?>
                            <button class="b-book-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#bModal<?= $unit['id'] ?>">
                                Reservar
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        <?php else: ?>
                            <button class="b-book-btn"
                                    style="opacity:.5;cursor:not-allowed"
                                    onclick="return false">
                                Reservar
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!$isPreview): ?>
                <!-- Modal de reserva -->
                <div class="modal fade b-modal"
                     id="bModal<?= $unit['id'] ?>"
                     tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <form action="<?= base_url('/book/'.$tenant['slug'].'/confirm') ?>"
                                  method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="unit_id"
                                       value="<?= $unit['id'] ?>">
                                <input type="hidden" name="agent_ref"
                                       value="<?= $agentRef ?>">

                                <div class="b-modal-header">
                                    <div class="b-modal-title">
                                        <?= esc($unit['name']) ?>
                                    </div>
                                    <button type="button"
                                            class="btn-close btn-close-white"
                                            data-bs-dismiss="modal">
                                    </button>
                                </div>

                                <div class="b-modal-body">
                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="b-form-label">Check-in</label>
                                            <input type="date"
                                                   name="check_in_date"
                                                   class="b-form-control"
                                                   required
                                                   min="<?= date('Y-m-d') ?>">
                                        </div>
                                        <div class="col-6">
                                            <label class="b-form-label">Check-out</label>
                                            <input type="date"
                                                   name="check_out_date"
                                                   class="b-form-control"
                                                   required
                                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="b-form-label">
                                                Nombre completo
                                            </label>
                                            <input type="text" name="full_name"
                                                   class="b-form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="b-form-label">
                                                Documento
                                            </label>
                                            <input type="text" name="document"
                                                   class="b-form-control" required>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-4">
                                            <label class="b-form-label">Adultos</label>
                                            <input type="number" name="adults"
                                                   class="b-form-control"
                                                   value="2" min="1"
                                                   max="<?= $unit['max_occupancy'] ?? 4 ?>"
                                                   required>
                                        </div>
                                        <div class="col-4">
                                            <label class="b-form-label">Teléfono</label>
                                            <input type="text" name="phone"
                                                   class="b-form-control" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="b-form-label">Email</label>
                                            <input type="email" name="email"
                                                   class="b-form-control" required>
                                        </div>
                                    </div>
                                    <?php if (!empty($website['policies_text'])): ?>
                                        <div style="background:var(--cream);
                                            border-radius:8px;padding:1rem;
                                            font-size:.8rem;color:var(--muted);
                                            line-height:1.6">
                                            <strong style="color:var(--ink)">
                                                Políticas:
                                            </strong><br>
                                            <?= nl2br(esc($website['policies_text'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div style="padding:1rem 1.75rem;
                                    border-top:1px solid var(--border);
                                    display:flex;justify-content:flex-end;
                                    gap:.75rem;background:var(--cream)">
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-bs-dismiss="modal">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="b-submit-btn">
                                        Solicitar reserva
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>
</section>

<!-- ── Galería editorial ──────────────────────────────────────────────────── -->
<?php if (count($galleryPhotos) >= 2): ?>
    <section class="b-section" id="galeria">
        <div class="b-container">
            <div style="display:flex;justify-content:space-between;
                    align-items:flex-end;margin-bottom:2.5rem">
                <div>
                    <div class="b-label">Galería</div>
                    <h2 class="b-title">
                        Nuestro <em>espacio</em>
                    </h2>
                </div>
                <span style="font-size:.78rem;color:var(--muted)">
                <?= count($galleryPhotos) ?> fotografías
            </span>
            </div>
            <div class="b-gallery">
                <?php foreach (array_slice(array_values($galleryPhotos), 0, 9) as $m): ?>
                    <div class="b-gallery-item"
                         onclick="openLightbox('<?= base_url($m['file_path']) ?>')">
                        <img src="<?= base_url($m['file_path']) ?>"
                             alt="<?= esc($tenant['name']) ?>"
                             loading="lazy">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Políticas ─────────────────────────────────────────────────────────── -->
<?php if (!empty($website['policies_text'])): ?>
    <section class="b-section b-section-alt" id="politicas">
        <div class="b-container">
            <div style="max-width:700px">
                <div class="b-label">Información</div>
                <h2 class="b-title" style="margin-bottom:3rem">
                    Políticas de <em>estadía</em>
                </h2>
            </div>
            <?php
            $policyIcons = [
                'bi-clock-history',
                'bi-calendar-x',
                'bi-exclamation-circle',
                'bi-volume-down',
                'bi-paw',
                'bi-wind',
                'bi-shield-check',
            ];
            $policies = array_values(array_filter(
                array_map('trim', explode("\n", $website['policies_text']))
            ));
            ?>
            <div class="b-policies-grid">
                <?php foreach ($policies as $i => $policy):
                    $policy = ltrim($policy, '•-* ');
                    if (empty($policy)) continue;
                    // Separar título de cuerpo si hay ":"
                    $parts = explode(':', $policy, 2);
                    $pTitle = count($parts) > 1 ? trim($parts[0]) : null;
                    $pText  = count($parts) > 1 ? trim($parts[1]) : trim($parts[0]);
                    ?>
                    <div class="b-policy-card">
                        <div class="b-policy-icon">
                            <i class="bi <?= $policyIcons[$i % count($policyIcons)] ?>"></i>
                        </div>
                        <?php if ($pTitle): ?>
                            <div class="b-policy-title"><?= esc($pTitle) ?></div>
                        <?php endif; ?>
                        <div class="b-policy-text"><?= esc($pText) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ── Contact strip ──────────────────────────────────────────────────────── -->
<div class="b-contact">
    <div class="b-container">
        <div class="b-contact-title">
            ¿Listo para <em>reservar</em>?
        </div>
        <p class="b-contact-sub">
            Reserva directo y obtén el mejor precio garantizado.
            Sin intermediarios, sin comisiones.
        </p>
        <a href="#alojamiento" class="b-contact-btn">
            <i class="bi bi-calendar-check"></i>
            Ver disponibilidad
        </a>
        <div class="b-contact-links">
            <?php if (!empty($website['whatsapp_number'])): ?>
                <a href="https://wa.me/<?= esc($website['whatsapp_number']) ?>"
                   target="_blank" class="b-contact-link"
                   title="WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($website['instagram_url'])): ?>
                <a href="<?= esc($website['instagram_url']) ?>"
                   target="_blank" class="b-contact-link"
                   title="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($website['facebook_url'])): ?>
                <a href="<?= esc($website['facebook_url']) ?>"
                   target="_blank" class="b-contact-link"
                   title="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($tenant['email'])): ?>
                <a href="mailto:<?= esc($tenant['email']) ?>"
                   class="b-contact-link" title="Email">
                    <i class="bi bi-envelope"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="b-footer">
    <div class="b-container">
        <p>
            &copy; <?= date('Y') ?> <?= esc($tenant['name']) ?>
            &nbsp;&middot;&nbsp;
            <?= esc($tenant['city'] ?? '') ?>
            <?= !empty($tenant['country']) ? ', ' . esc($tenant['country']) : '' ?>
            &nbsp;&middot;&nbsp;
            Powered by <a href="#">GuestHandle</a>
        </p>
    </div>
</footer>

<!-- ── WhatsApp FAB ───────────────────────────────────────────────────────── -->
<?php if (!empty($website['whatsapp_number']) && !$isPreview): ?>
    <a href="https://wa.me/<?= esc($website['whatsapp_number']) ?>?text=Hola%2C+quiero+consultar+disponibilidad"
       target="_blank" class="b-wa-fab" title="Reservar por WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
<?php endif; ?>

<!-- ── Lightbox ───────────────────────────────────────────────────────────── -->
<div class="b-lightbox" id="bLightbox" onclick="closeLightbox()">
    <button class="b-lightbox-close"
            onclick="closeLightbox()">
        &times;
    </button>
    <img id="bLightboxImg" src="" alt="">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openLightbox(src) {
        document.getElementById('bLightboxImg').src = src;
        document.getElementById('bLightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('bLightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
    });
</script>
</body>
</html>