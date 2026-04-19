<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GuestHandle — PMS inteligente con IA para hoteles boutique, cabañas y apartamentos turísticos. Gestiona reservaciones, WhatsApp automático y sitio web propio.">
    <meta name="keywords" content="PMS hotelero, software hotel Colombia, gestión reservaciones, WhatsApp hotel, inteligencia artificial hotel">
    <meta property="og:title" content="GuestHandle — PMS Inteligente para Hoteles">
    <meta property="og:description" content="Gestiona tu hotel con inteligencia artificial. Reservaciones, WhatsApp 24/7 y sitio web propio. Primer mes gratis.">
    <meta property="og:type" content="website">
    <title>GuestHandle — PMS Inteligente para Hoteles</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ── Reset y variables ───────────────────────────────────────────────────── */
        *{margin:0;padding:0;box-sizing:border-box}
        :root{
            --blue      : #185FA5;
            --blue-light: #E6F1FB;
            --blue-mid  : #378ADD;
            --blue-dark : #0C447C;
            --teal      : #0F6E56;
            --teal-light: #E1F5EE;
            --teal-mid  : #1D9E75;
            --amber-light:#FAEEDA;
            --amber-dark :#633806;
            --gray-50   : #F8F9FA;
            --gray-100  : #F1EFE8;
            --gray-200  : #D3D1C7;
            --gray-400  : #888780;
            --gray-600  : #5F5E5A;
            --gray-800  : #444441;
            --gray-900  : #2C2C2A;
            --white     : #FFFFFF;
            --serif     : 'Playfair Display', Georgia, serif;
            --sans      : 'DM Sans', 'Helvetica Neue', sans-serif;
            --r         : 10px;
            --r2        : 20px;
        }

        html { scroll-behavior: smooth }

        body {
            background  : var(--white);
            color       : var(--gray-900);
            font-family : var(--sans);
            font-size   : 16px;
            line-height : 1.6;
            overflow-x  : hidden;
        }

        a { text-decoration: none; color: inherit }

        /* ── Navegación ──────────────────────────────────────────────────────────── */
        nav {
            background      : rgba(255,255,255,0.96);
            border-bottom   : 1px solid var(--gray-100);
            padding         : 0 2rem;
            display         : flex;
            justify-content : space-between;
            align-items     : center;
            height          : 64px;
            position        : sticky;
            top             : 0;
            z-index         : 100;
            backdrop-filter : blur(8px);
        }

        .nav-logo {
            font-family : var(--serif);
            font-size   : 1.3rem;
            color       : var(--blue-dark);
            font-weight : 700;
            letter-spacing: -0.01em;
        }

        .nav-logo span {
            color      : var(--teal-mid);
            font-style : italic;
        }

        .nav-links {
            display     : flex;
            align-items : center;
            gap         : 1.75rem;
        }

        .nav-links a {
            font-size   : .875rem;
            color       : var(--gray-600);
            font-weight : 400;
            transition  : color .15s;
        }

        .nav-links a:hover { color: var(--blue) }

        .nav-actions {
            display     : flex;
            align-items : center;
            gap         : .75rem;
        }

        .btn-login {
            font-size   : .875rem;
            color       : var(--blue);
            font-weight : 500;
            padding     : .45rem 1rem;
            border      : 1.5px solid #B5D4F4;
            border-radius: 8px;
            transition  : all .15s;
            display     : inline-block;
        }

        .btn-login:hover { background: var(--blue-light) }

        .btn-cta {
            font-size     : .875rem;
            background    : var(--blue);
            color         : var(--white);
            font-weight   : 500;
            padding       : .5rem 1.1rem;
            border-radius : 8px;
            transition    : all .15s;
            border        : none;
            cursor        : pointer;
            display       : inline-block;
        }

        .btn-cta:hover { background: var(--blue-dark) }

        /* ── Hero ────────────────────────────────────────────────────────────────── */
        .hero {
            padding       : 5rem 2rem 4rem;
            text-align    : center;
            background    : var(--white);
            border-bottom : 1px solid var(--gray-100);
        }

        .hero-badge {
            display       : inline-flex;
            align-items   : center;
            gap           : .4rem;
            background    : var(--teal-light);
            color         : #085041;
            font-size     : .75rem;
            font-weight   : 600;
            letter-spacing: .04em;
            padding       : .35rem .9rem;
            border-radius : 99px;
            margin-bottom : 1.75rem;
            border        : 1px solid #9FE1CB;
        }

        .hero h1 {
            font-family    : var(--serif);
            font-size      : clamp(2rem, 5vw, 3.6rem);
            font-weight    : 700;
            color          : var(--gray-900);
            line-height    : 1.12;
            margin-bottom  : 1.25rem;
            letter-spacing : -0.02em;
            max-width      : 760px;
            margin-left    : auto;
            margin-right   : auto;
        }

        .hero h1 em {
            color      : var(--blue);
            font-style : italic;
        }

        .hero p {
            font-size     : 1.05rem;
            color         : var(--gray-600);
            max-width     : 520px;
            margin        : 0 auto 2.25rem;
            font-weight   : 300;
            line-height   : 1.75;
        }

        .hero-btns {
            display         : flex;
            gap             : .75rem;
            justify-content : center;
            flex-wrap       : wrap;
            margin-bottom   : 1.25rem;
        }

        .btn-hero-primary {
            background    : var(--blue);
            color         : var(--white);
            padding       : .8rem 1.75rem;
            border-radius : 10px;
            font-weight   : 600;
            font-size     : .95rem;
            border        : none;
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-block;
        }

        .btn-hero-primary:hover {
            background : var(--blue-dark);
            transform  : translateY(-1px);
        }

        .btn-hero-secondary {
            background    : var(--white);
            color         : var(--gray-800);
            padding       : .8rem 1.75rem;
            border-radius : 10px;
            font-weight   : 500;
            font-size     : .95rem;
            border        : 1.5px solid var(--gray-200);
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-block;
        }

        .btn-hero-secondary:hover {
            border-color : var(--blue-mid);
            color        : var(--blue);
        }

        .hero-sub {
            font-size : .78rem;
            color     : var(--gray-400);
        }

        .hero-sub strong { color: var(--teal-mid) }

        /* ── Stats bar ───────────────────────────────────────────────────────────── */
        .stats-bar {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(140px, 1fr));
            background            : var(--blue-dark);
            padding               : 1.5rem 2rem;
            gap                   : 1rem;
        }

        .stat-item { text-align: center; padding: .5rem }

        .stat-n {
            font-family : var(--serif);
            font-size   : 2rem;
            font-weight : 700;
            color       : var(--white);
            line-height : 1;
        }

        .stat-l {
            font-size      : .72rem;
            color          : #85B7EB;
            margin-top     : .25rem;
            letter-spacing : .06em;
            text-transform : uppercase;
        }

        /* ── Secciones genéricas ─────────────────────────────────────────────────── */
        section { padding: 5rem 2rem }

        .container { max-width: 1080px; margin: 0 auto }

        .eyebrow {
            font-size      : .72rem;
            font-weight    : 700;
            letter-spacing : .12em;
            text-transform : uppercase;
            color          : var(--teal-mid);
            margin-bottom  : .75rem;
        }

        .section-h {
            font-family    : var(--serif);
            font-size      : clamp(1.7rem, 3vw, 2.6rem);
            font-weight    : 700;
            color          : var(--gray-900);
            line-height    : 1.18;
            margin-bottom  : .75rem;
            letter-spacing : -0.02em;
        }

        .section-h em {
            color      : var(--blue);
            font-style : italic;
        }

        .section-p {
            font-size   : .975rem;
            color       : var(--gray-600);
            max-width   : 500px;
            font-weight : 300;
            line-height : 1.75;
        }

        /* ── Features ────────────────────────────────────────────────────────────── */
        .features-section { background: var(--gray-50) }

        .features-grid {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(290px, 1fr));
            gap                   : 1.25rem;
            margin-top            : 2.5rem;
        }

        .fcard {
            background    : var(--white);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 1.75rem;
            transition    : border-color .2s, transform .2s;
        }

        .fcard:hover {
            border-color : #B5D4F4;
            transform    : translateY(-2px);
        }

        .fcard-icon {
            width         : 42px;
            height        : 42px;
            border-radius : 10px;
            display       : flex;
            align-items   : center;
            justify-content: center;
            font-size     : 18px;
            margin-bottom : 1rem;
            flex-shrink   : 0;
        }

        .fcard-icon.blue  { background: var(--blue-light) }
        .fcard-icon.teal  { background: var(--teal-light) }
        .fcard-icon.amber { background: var(--amber-light) }

        .fcard h3 {
            font-size   : .975rem;
            font-weight : 600;
            color       : var(--gray-900);
            margin-bottom: .4rem;
        }

        .fcard p {
            font-size   : .84rem;
            color       : var(--gray-600);
            line-height : 1.65;
            font-weight : 300;
        }

        .ftag {
            display       : inline-block;
            margin-top    : .65rem;
            font-size     : .68rem;
            font-weight   : 600;
            background    : var(--blue-light);
            color         : var(--blue-dark);
            padding       : .2rem .6rem;
            border-radius : 4px;
            letter-spacing: .04em;
        }

        .ftag.teal  { background: var(--teal-light); color: #085041 }
        .ftag.amber { background: var(--amber-light); color: var(--amber-dark) }

        /* ── Cómo funciona ───────────────────────────────────────────────────────── */
        .how-section { background: var(--white) }

        .steps-row {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(200px, 1fr));
            gap                   : 2rem;
            margin-top            : 2.5rem;
        }

        .step {
            position      : relative;
            padding       : 1.5rem;
            border-radius : var(--r2);
            border        : 1px solid var(--gray-100);
            background    : var(--gray-50);
        }

        .step-n {
            font-family   : var(--serif);
            font-size     : 2.2rem;
            font-weight   : 700;
            color         : #B5D4F4;
            line-height   : 1;
            margin-bottom : .75rem;
        }

        .step h3 {
            font-size     : .925rem;
            font-weight   : 600;
            color         : var(--gray-900);
            margin-bottom : .4rem;
        }

        .step p {
            font-size   : .82rem;
            color       : var(--gray-600);
            font-weight : 300;
            line-height : 1.65;
        }

        /* ── Sección IA ──────────────────────────────────────────────────────────── */
        .ai-section {
            background : var(--blue-dark);
            color      : var(--white);
            padding    : 5rem 2rem;
        }

        .ai-grid {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 4rem;
            align-items           : center;
            max-width             : 1080px;
            margin                : 0 auto;
        }

        .ai-eyebrow {
            font-size      : .72rem;
            font-weight    : 700;
            letter-spacing : .12em;
            text-transform : uppercase;
            color          : #85B7EB;
            margin-bottom  : .75rem;
        }

        .ai-h {
            font-family    : var(--serif);
            font-size      : clamp(1.6rem, 3vw, 2.4rem);
            font-weight    : 700;
            color          : var(--white);
            line-height    : 1.18;
            margin-bottom  : .75rem;
            letter-spacing : -0.02em;
        }

        .ai-h em {
            color      : #9FE1CB;
            font-style : italic;
        }

        .ai-p {
            font-size   : .95rem;
            color       : #85B7EB;
            font-weight : 300;
            line-height : 1.75;
            margin-bottom: 1.75rem;
        }

        .ai-feat-list {
            display        : flex;
            flex-direction : column;
            gap            : .65rem;
        }

        .ai-feat {
            display     : flex;
            align-items : center;
            gap         : .65rem;
            font-size   : .875rem;
            color       : #E6F1FB;
            font-weight : 300;
        }

        .ai-feat-dot {
            width         : 6px;
            height        : 6px;
            background    : #9FE1CB;
            border-radius : 50%;
            flex-shrink   : 0;
        }

        /* Chat mockup */
        .chat-box {
            background    : var(--white);
            border-radius : var(--r2);
            overflow      : hidden;
        }

        .chat-header {
            background    : var(--blue-light);
            padding       : .75rem 1.25rem;
            display       : flex;
            align-items   : center;
            gap           : .65rem;
            border-bottom : 1px solid #B5D4F4;
        }

        .chat-avatar {
            width           : 32px;
            height          : 32px;
            border-radius   : 50%;
            background      : var(--teal-mid);
            display         : flex;
            align-items     : center;
            justify-content : center;
            font-size       : .7rem;
            font-weight     : 700;
            color           : var(--white);
            flex-shrink     : 0;
        }

        .chat-name {
            font-size   : .82rem;
            font-weight : 600;
            color       : var(--blue-dark);
        }

        .chat-status {
            font-size   : .7rem;
            color       : var(--teal-mid);
            display     : flex;
            align-items : center;
            gap         : .3rem;
        }

        .chat-dot {
            width         : 6px;
            height        : 6px;
            background    : var(--teal-mid);
            border-radius : 50%;
        }

        .chat-body {
            padding        : 1.25rem;
            display        : flex;
            flex-direction : column;
            gap            : .75rem;
        }

        .msg {
            max-width     : 82%;
            padding       : .6rem .9rem;
            border-radius : 12px;
            font-size     : .82rem;
            line-height   : 1.55;
        }

        .msg.guest {
            background    : var(--gray-100);
            color         : var(--gray-800);
            border-radius : 12px 12px 12px 3px;
            align-self    : flex-start;
        }

        .msg.hotel {
            background    : var(--teal-mid);
            color         : var(--white);
            border-radius : 12px 12px 3px 12px;
            align-self    : flex-end;
        }

        .msg-time {
            font-size  : .65rem;
            color      : var(--gray-400);
            margin-top : .2rem;
        }

        .msg-time.hotel-t {
            text-align : right;
            color      : rgba(255,255,255,.55);
        }

        .chat-footer {
            padding     : .65rem 1.25rem;
            border-top  : 1px solid var(--gray-100);
            font-size   : .7rem;
            color       : var(--teal-mid);
            display     : flex;
            align-items : center;
            gap         : .4rem;
        }

        /* ── Precios ─────────────────────────────────────────────────────────────── */
        .pricing-section { background: var(--gray-50) }

        .pricing-grid {
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(260px, 1fr));
            gap                   : 1.25rem;
            margin-top            : 2.5rem;
            max-width             : 820px;
            margin-left           : auto;
            margin-right          : auto;
        }

        .pcard {
            background    : var(--white);
            border        : 1px solid var(--gray-200);
            border-radius : var(--r2);
            padding       : 2rem;
            position      : relative;
        }

        .pcard.featured { border: 2px solid var(--blue-mid) }

        .p-badge {
            position      : absolute;
            top           : -13px;
            left          : 50%;
            transform     : translateX(-50%);
            background    : var(--blue);
            color         : var(--white);
            font-size     : .68rem;
            font-weight   : 700;
            padding       : .25rem .9rem;
            border-radius : 99px;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space   : nowrap;
        }

        .p-name {
            font-size      : .72rem;
            font-weight    : 700;
            letter-spacing : .1em;
            text-transform : uppercase;
            color          : var(--blue);
            margin-bottom  : .65rem;
        }

        .p-price {
            font-family : var(--serif);
            font-size   : 2.6rem;
            font-weight : 700;
            color       : var(--gray-900);
            line-height : 1;
        }

        .p-price sup {
            font-size      : 1rem;
            vertical-align : top;
            margin-top     : .5rem;
            font-family    : var(--sans);
            font-weight    : 400;
            color          : var(--gray-600);
        }

        .p-period {
            font-size     : .78rem;
            color         : var(--gray-400);
            margin        : .4rem 0 1.25rem;
        }

        .p-list {
            list-style    : none;
            margin-bottom : 1.75rem;
        }

        .p-list li {
            font-size     : .84rem;
            color         : var(--gray-800);
            padding       : .35rem 0;
            display       : flex;
            align-items   : center;
            gap           : .55rem;
            border-bottom : 1px solid var(--gray-100);
            font-weight   : 300;
        }

        .p-list li::before {
            content       : '';
            width         : 5px;
            height        : 5px;
            background    : var(--teal-mid);
            border-radius : 50%;
            flex-shrink   : 0;
        }

        .btn-plan {
            display       : block;
            text-align    : center;
            padding       : .75rem;
            border-radius : 10px;
            font-size     : .875rem;
            font-weight   : 600;
            cursor        : pointer;
            border        : none;
            transition    : all .2s;
            font-family   : var(--sans);
        }

        .btn-plan.primary { background: var(--blue); color: var(--white) }
        .btn-plan.primary:hover { background: var(--blue-dark) }
        .btn-plan.secondary { background: var(--white); color: var(--blue); border: 1.5px solid #B5D4F4 }
        .btn-plan.secondary:hover { background: var(--blue-light) }

        /* ── Registro ────────────────────────────────────────────────────────────── */
        .register-section { background: var(--white) }

        .register-grid {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 4rem;
            align-items           : start;
            max-width             : 1000px;
            margin                : 0 auto;
        }

        .rform {
            background    : var(--gray-50);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 2.25rem;
        }

        .rform-title {
            font-family   : var(--serif);
            font-size     : 1.35rem;
            font-weight   : 700;
            color         : var(--gray-900);
            margin-bottom : .2rem;
        }

        .rform-sub {
            font-size     : .82rem;
            color         : var(--gray-400);
            margin-bottom : 1.75rem;
            font-weight   : 300;
        }

        /* Form fields */
        .fg { margin-bottom: 1rem }

        .fg label {
            display        : block;
            font-size      : .72rem;
            font-weight    : 600;
            color          : var(--gray-600);
            letter-spacing : .06em;
            text-transform : uppercase;
            margin-bottom  : .35rem;
        }

        .fg input,
        .fg select {
            width          : 100%;
            background     : var(--white);
            border         : 1.5px solid var(--gray-200);
            border-radius  : 8px;
            color          : var(--gray-900);
            padding        : .65rem .9rem;
            font-family    : var(--sans);
            font-size      : .875rem;
            font-weight    : 300;
            outline        : none;
            transition     : border-color .2s;
            -webkit-appearance: none;
            appearance     : none;
        }

        .fg input::placeholder { color: var(--gray-400) }
        .fg input:focus,
        .fg select:focus { border-color: var(--blue-mid) }

        .fg-row {
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : .75rem;
        }

        .fcheck {
            display     : flex;
            align-items : flex-start;
            gap         : .6rem;
            margin      : 1rem 0;
        }

        .fcheck input[type="checkbox"] {
            width     : 15px;
            height    : 15px;
            min-width : 15px;
            margin-top: .2rem;
            accent-color: var(--blue);
            cursor    : pointer;
        }

        .fcheck label {
            font-size   : .78rem;
            color       : var(--gray-600);
            cursor      : pointer;
            font-weight : 300;
            text-transform: none;
            letter-spacing: 0;
        }

        .fcheck a { color: var(--blue) }

        .btn-reg {
            width         : 100%;
            background    : var(--blue);
            color         : var(--white);
            border        : none;
            padding       : .9rem;
            border-radius : 10px;
            font-size     : .95rem;
            font-weight   : 700;
            cursor        : pointer;
            font-family   : var(--sans);
            transition    : all .2s;
            letter-spacing: .01em;
        }

        .btn-reg:hover {
            background : var(--blue-dark);
            transform  : translateY(-1px);
        }

        .btn-reg:disabled {
            opacity   : .6;
            cursor    : not-allowed;
            transform : none;
        }

        .form-note {
            text-align  : center;
            font-size   : .72rem;
            color       : var(--gray-400);
            margin-top  : .75rem;
        }

        .login-link {
            text-align  : center;
            margin-top  : 1rem;
            font-size   : .82rem;
            color       : var(--gray-600);
        }

        .login-link a { color: var(--blue); font-weight: 500 }

        /* Pantalla de éxito */
        .success-wrap {
            display    : none;
            text-align : center;
            padding    : 2.5rem 1rem;
        }

        .success-wrap .sicon {
            width           : 56px;
            height          : 56px;
            background      : var(--teal-light);
            border-radius   : 50%;
            display         : flex;
            align-items     : center;
            justify-content : center;
            margin          : 0 auto 1rem;
            color           : var(--teal-mid);
            font-size       : 1.4rem;
        }

        .success-wrap h3 {
            font-family   : var(--serif);
            font-size     : 1.3rem;
            color         : var(--gray-900);
            margin-bottom : .5rem;
        }

        .success-wrap p {
            font-size     : .85rem;
            color         : var(--gray-600);
            margin-bottom : 1.5rem;
            font-weight   : 300;
        }

        .btn-goto {
            display       : inline-block;
            background    : var(--blue);
            color         : var(--white);
            padding       : .75rem 1.75rem;
            border-radius : 10px;
            font-weight   : 600;
            font-size     : .9rem;
            transition    : background .2s;
        }

        .btn-goto:hover { background: var(--blue-dark) }

        /* ── Footer ──────────────────────────────────────────────────────────────── */
        footer {
            background : var(--gray-900);
            color      : var(--gray-400);
            padding    : 2.5rem 2rem;
            text-align : center;
        }

        .footer-logo {
            font-family   : var(--serif);
            font-size     : 1.2rem;
            color         : var(--white);
            margin-bottom : .5rem;
            display       : block;
        }

        .footer-logo span {
            color      : #9FE1CB;
            font-style : italic;
        }

        /* ── Animaciones de entrada ──────────────────────────────────────────────── */
        .reveal {
            opacity    : 0;
            transform  : translateY(18px);
            transition : opacity .55s ease, transform .55s ease;
        }

        .reveal.visible {
            opacity   : 1;
            transform : none;
        }

        /* ── Alertas de error ────────────────────────────────────────────────────── */
        .form-error {
            background    : #FCEBEB;
            color         : #A32D2D;
            border        : 1px solid #F09595;
            border-radius : 8px;
            padding       : .65rem .9rem;
            font-size     : .82rem;
            margin-bottom : 1rem;
            display       : none;
        }

        /* ── Responsive ──────────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .ai-grid,
            .register-grid,
            .fg-row { grid-template-columns: 1fr }

            .nav-links { display: none }

            .hero { padding: 4rem 1.25rem 3rem }

            section { padding: 3.5rem 1.25rem }

            .register-grid { gap: 2.5rem }
        }

        @media (max-width: 480px) {
            .hero-btns { flex-direction: column; align-items: center }
            .btn-hero-primary,
            .btn-hero-secondary { width: 100%; max-width: 280px; text-align: center }
        }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════════════════════════
     NAVEGACIÓN
════════════════════════════════════════════════════════════ -->
<nav>
    <div class="nav-logo">Guest<span>Handle</span></div>

    <div class="nav-links">
        <a href="#features">Funcionalidades</a>
        <a href="#como-funciona">Cómo funciona</a>
        <a href="#precios">Precios</a>
    </div>

    <div class="nav-actions">
        <a href="/login" class="btn-login">Iniciar sesión</a>
        <a href="#registro" class="btn-cta">Empezar gratis</a>
    </div>
</nav>

<!-- ════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<div class="hero">
    <div class="hero-badge">&#10022; Primer mes gratis &middot; Sin tarjeta de crédito</div>

    <h1>El PMS que gestiona tu hotel<br>con <em>inteligencia artificial</em></h1>

    <p>Reservaciones, tarifas, WhatsApp automático y sitio web propio. Todo desde un panel. Configuración en minutos.</p>

    <div class="hero-btns">
        <a href="#registro" class="btn-hero-primary">Crear cuenta gratis</a>
        <a href="#como-funciona" class="btn-hero-secondary">Ver cómo funciona</a>
    </div>

    <p class="hero-sub">
        <strong>30 días gratis</strong> con todas las funcionalidades &middot; Sin contratos &middot; Cancela cuando quieras
    </p>
</div>

<!-- ════════════════════════════════════════════════════════════
     STATS BAR
════════════════════════════════════════════════════════════ -->
<div class="stats-bar">
    <div class="stat-item reveal">
        <div class="stat-n">&#8734;</div>
        <div class="stat-l">Reservaciones incluidas</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n">24/7</div>
        <div class="stat-l">Asistente IA activo</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n">&lt;5'</div>
        <div class="stat-l">Para configurar</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n">0%</div>
        <div class="stat-l">Comisión por reserva directa</div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     FUNCIONALIDADES
════════════════════════════════════════════════════════════ -->
<section id="features" class="features-section">
    <div class="container">
        <div class="reveal">
            <div class="eyebrow">Funcionalidades</div>
            <h2 class="section-h">Todo lo que necesitas,<br><em>sin complicaciones</em></h2>
            <p class="section-p">Diseñado para hoteles boutique, cabañas, glamping y apartamentos turísticos que quieren operar como grandes cadenas.</p>
        </div>

        <div class="features-grid">
            <div class="fcard reveal">
                <div class="fcard-icon blue">&#128197;</div>
                <h3>Calendario de reservaciones</h3>
                <p>Vista Gantt con todas tus unidades. Bloqueos rápidos, disponibilidad en tiempo real y gestión del estado de cada habitación.</p>
                <span class="ftag">Core</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon teal">&#129302;</div>
                <h3>Asistente IA por WhatsApp</h3>
                <p>Responde consultas 24/7, cotiza precios y gestiona reservas automáticamente. Aprende tu tono con conversaciones reales.</p>
                <span class="ftag teal">Con IA</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon amber">&#128176;</div>
                <h3>Motor de tarifas dinámicas</h3>
                <p>Planes tarifarios, temporadas, precios por persona adicional y descuentos. Todo configurable desde un panel visual.</p>
                <span class="ftag amber">Revenue</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon blue">&#129534;</div>
                <h3>Folio y punto de venta</h3>
                <p>Agrega consumos y servicios a cada reservación. Cierre de caja con pagos parciales o totales al momento del checkout.</p>
                <span class="ftag">Operativo</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon teal">&#127760;</div>
                <h3>Sitio web con motor de reservas</h3>
                <p>Página pública para recibir reservas directas sin comisiones. Personalizable con tu logo, fotos y textos generados por IA.</p>
                <span class="ftag teal">Marketing</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon amber">&#128202;</div>
                <h3>Reportes y KPIs</h3>
                <p>Ocupación, ingresos, canales de origen y comisiones. Exporta reportes y toma decisiones con datos reales de tu operación.</p>
                <span class="ftag amber">Analítica</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon blue">&#128722;</div>
                <h3>Compras y proveedores</h3>
                <p>Registra órdenes de compra, controla pagos a proveedores y lleva el historial de gastos operativos de tu propiedad.</p>
                <span class="ftag">Administración</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon teal">&#129309;</div>
                <h3>Agentes y comisiones</h3>
                <p>Gestiona agencias y vendedores externos con tracking codes. Las comisiones se calculan automáticamente por cada reserva.</p>
                <span class="ftag teal">Ventas</span>
            </div>

            <div class="fcard reveal">
                <div class="fcard-icon amber">&#128295;</div>
                <h3>Mantenimiento</h3>
                <p>Tareas de mantenimiento por unidad. Bloqueo automático de habitaciones hasta que estén disponibles para nuevos huéspedes.</p>
                <span class="ftag amber">Operativo</span>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     CÓMO FUNCIONA
════════════════════════════════════════════════════════════ -->
<section id="como-funciona" class="how-section">
    <div class="container">
        <div class="reveal" style="text-align:center">
            <div class="eyebrow" style="text-align:center">Cómo funciona</div>
            <h2 class="section-h" style="text-align:center">De cero a operativo <em>en un día</em></h2>
        </div>

        <div class="steps-row">
            <div class="step reveal">
                <div class="step-n">01</div>
                <h3>Crea tu cuenta</h3>
                <p>Registro en 2 minutos. Solo el nombre de tu hotel, email y contraseña. Sin tarjeta de crédito requerida.</p>
            </div>
            <div class="step reveal">
                <div class="step-n">02</div>
                <h3>Wizard de configuración</h3>
                <p>Un asistente guiado te ayuda a configurar habitaciones, tarifas, productos y el asistente IA paso a paso.</p>
            </div>
            <div class="step reveal">
                <div class="step-n">03</div>
                <h3>Conecta WhatsApp</h3>
                <p>Vincula tu número oficial de WhatsApp Business en 3 clics. La IA empieza a atender huéspedes de inmediato.</p>
            </div>
            <div class="step reveal">
                <div class="step-n">04</div>
                <h3>Opera y crece</h3>
                <p>Recibe reservas directas, gestiona tu equipo y consulta reportes desde cualquier dispositivo.</p>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     SECCIÓN IA
════════════════════════════════════════════════════════════ -->
<section class="ai-section">
    <div class="ai-grid">
        <div class="reveal">
            <div class="ai-eyebrow">Inteligencia Artificial</div>
            <h2 class="ai-h">Un asistente que<br><em>suena como tú</em></h2>
            <p class="ai-p">La IA aprende el tono de tu hotel. Pega conversaciones reales de WhatsApp y el sistema replica exactamente tu forma de atender a los huéspedes.</p>
            <div class="ai-feat-list">
                <div class="ai-feat"><div class="ai-feat-dot"></div>Responde consultas de disponibilidad y precios automáticamente</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Escala a un humano cuando la situación lo requiere</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Soporta español, inglés y más idiomas</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Activo 24/7 sin costo adicional por mensaje</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Se configura en minutos desde el wizard de onboarding</div>
            </div>
        </div>

        <div class="reveal">
            <div class="chat-box">
                <div class="chat-header">
                    <div class="chat-avatar">GH</div>
                    <div>
                        <div class="chat-name">Hotel Boutique Casa Verde</div>
                        <div class="chat-status">
                            <div class="chat-dot"></div>
                            Asistente IA activo
                        </div>
                    </div>
                </div>
                <div class="chat-body">
                    <div>
                        <div class="msg guest">Buenas! tienen disponible para el 20 de diciembre? somos 3 adultos</div>
                        <div class="msg-time">10:24 am</div>
                    </div>
                    <div>
                        <div class="msg hotel">Hola! Claro que sí, tenemos disponible la Cabaña Vista Verde para esas fechas. Para 3 adultos el precio es $320.000 por noche. ¿Te cuento qué incluye?</div>
                        <div class="msg-time hotel-t">10:24 am &middot; Respondido automáticamente</div>
                    </div>
                    <div>
                        <div class="msg guest">sí porfavor y cómo reservo?</div>
                        <div class="msg-time">10:25 am</div>
                    </div>
                    <div>
                        <div class="msg hotel">Incluye desayuno casero, wifi y piscina. Para reservar necesito tu nombre y un 50% de anticipo. ¿Cuántas noches planeas quedarte?</div>
                        <div class="msg-time hotel-t">10:25 am &middot; Respondido automáticamente</div>
                    </div>
                </div>
                <div class="chat-footer">
                    <div class="chat-dot"></div>
                    Respondido en 3 segundos &middot; Powered by GuestHandle IA
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     PRECIOS
════════════════════════════════════════════════════════════ -->
<section id="precios" class="pricing-section">
    <div class="container">
        <div class="reveal" style="text-align:center">
            <div class="eyebrow" style="text-align:center">Planes</div>
            <h2 class="section-h" style="text-align:center">Simple y <em>transparente</em></h2>
            <p class="section-p" style="margin:0 auto .5rem;text-align:center">
                Primer mes gratis en cualquier plan. Sin comisiones por reserva. Sin contratos.
            </p>
        </div>

        <div class="pricing-grid">
            <div class="pcard reveal">
                <div class="p-name">Esencial</div>
                <div class="p-price"><sup>$</sup>99.000</div>
                <div class="p-period">COP / mes &middot; hasta 10 unidades</div>
                <ul class="p-list">
                    <li>Reservaciones ilimitadas</li>
                    <li>Calendario Gantt</li>
                    <li>Motor de tarifas</li>
                    <li>Folio y punto de venta</li>
                    <li>Reportes básicos</li>
                    <li>Hasta 3 usuarios</li>
                </ul>
                <a href="#registro" class="btn-plan secondary">Empezar gratis</a>
            </div>

            <div class="pcard featured reveal">
                <div class="p-badge">Más popular</div>
                <div class="p-name">Profesional</div>
                <div class="p-price"><sup>$</sup>189.000</div>
                <div class="p-period">COP / mes &middot; unidades ilimitadas</div>
                <ul class="p-list">
                    <li>Todo lo de Esencial</li>
                    <li>Asistente IA WhatsApp 24/7</li>
                    <li>Sitio web + motor de reservas</li>
                    <li>Compras y proveedores</li>
                    <li>Agentes y comisiones</li>
                    <li>Usuarios ilimitados</li>
                    <li>Reportes avanzados</li>
                    <li>Soporte prioritario</li>
                </ul>
                <a href="#registro" class="btn-plan primary">Empezar 30 días gratis</a>
            </div>
        </div>

        <p class="reveal" style="text-align:center;font-size:.78rem;color:var(--gray-400);margin-top:1.5rem">
            Sin tarjeta de crédito &middot; Cancela en cualquier momento &middot; Datos seguros
        </p>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     REGISTRO
════════════════════════════════════════════════════════════ -->
<section id="registro" class="register-section">
    <div class="container">
        <div class="register-grid">

            <!-- Columna izquierda: info -->
            <div class="reveal">
                <div class="eyebrow">Registro gratuito</div>
                <h2 class="section-h">Empieza hoy,<br><em>gratis</em></h2>
                <p class="section-p" style="margin-bottom:2rem">
                    En menos de 2 minutos tu hotel estará en GuestHandle. Al registrarte, un asistente guiado te ayuda a configurar todo paso a paso.
                </p>

                <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2rem">
                    <div style="display:flex;align-items:center;gap:.85rem">
                        <div style="width:34px;height:34px;background:var(--blue-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue-dark);font-size:.8rem;font-weight:700;flex-shrink:0">1</div>
                        <span style="font-size:.875rem;color:var(--gray-600);font-weight:300">Completa el formulario — menos de 2 minutos</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:.85rem">
                        <div style="width:34px;height:34px;background:var(--blue-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue-dark);font-size:.8rem;font-weight:700;flex-shrink:0">2</div>
                        <span style="font-size:.875rem;color:var(--gray-600);font-weight:300">El wizard te guía para configurar tu hotel</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:.85rem">
                        <div style="width:34px;height:34px;background:var(--blue-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blue-dark);font-size:.8rem;font-weight:700;flex-shrink:0">3</div>
                        <span style="font-size:.875rem;color:var(--gray-600);font-weight:300">30 días gratis con todas las funcionalidades</span>
                    </div>
                </div>

                <!-- Bloque login -->
                <div style="padding:1.25rem;background:var(--gray-50);border-radius:var(--r2);border:1px solid var(--gray-100)">
                    <p style="font-size:.78rem;color:var(--gray-600);font-weight:300;margin-bottom:.4rem">¿Ya tienes cuenta?</p>
                    <a href="/login" style="display:inline-flex;align-items:center;gap:.5rem;font-size:.875rem;color:var(--blue);font-weight:600">
                        Iniciar sesión en tu panel <span>&#8594;</span>
                    </a>
                </div>
            </div>

            <!-- Columna derecha: formulario -->
            <div class="reveal">
                <div class="rform">

                    <!-- Formulario principal -->
                    <div id="formContent">
                        <h3 class="rform-title">Crear cuenta gratis</h3>
                        <p class="rform-sub">Primer mes gratis &middot; Sin tarjeta de crédito</p>

                        <!-- Mensaje de error -->
                        <div class="form-error" id="formError"></div>

                        <form id="registerForm" novalidate>
                            <?= csrf_field() ?>

                            <div class="fg">
                                <label for="hotel_name">Nombre del hotel *</label>
                                <input type="text" id="hotel_name" name="hotel_name"
                                       placeholder="Hotel Boutique Casa Grande"
                                       required maxlength="120">
                            </div>

                            <div class="fg-row">
                                <div class="fg">
                                    <label for="reg_name">Tu nombre *</label>
                                    <input type="text" id="reg_name" name="name"
                                           placeholder="Ana García"
                                           required maxlength="120">
                                </div>
                                <div class="fg">
                                    <label for="reg_phone">WhatsApp *</label>
                                    <input type="tel" id="reg_phone" name="phone"
                                           placeholder="+57 300 000 0000"
                                           required maxlength="30">
                                </div>
                            </div>

                            <div class="fg">
                                <label for="reg_email">Email *</label>
                                <input type="email" id="reg_email" name="email"
                                       placeholder="ana@mihotel.com"
                                       required maxlength="150">
                            </div>

                            <div class="fg-row">
                                <div class="fg">
                                    <label for="reg_city">Ciudad *</label>
                                    <input type="text" id="reg_city" name="city"
                                           placeholder="Medellín"
                                           required maxlength="100">
                                </div>
                                <div class="fg">
                                    <label for="reg_country">País</label>
                                    <select id="reg_country" name="country">
                                        <option value="Colombia">Colombia</option>
                                        <option value="México">México</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Chile">Chile</option>
                                        <option value="Perú">Perú</option>
                                        <option value="Ecuador">Ecuador</option>
                                        <option value="Costa Rica">Costa Rica</option>
                                        <option value="Panamá">Panamá</option>
                                        <option value="España">España</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="fg-row">
                                <div class="fg">
                                    <label for="reg_password">Contraseña *</label>
                                    <input type="password" id="reg_password" name="password"
                                           placeholder="Mínimo 8 caracteres"
                                           required minlength="8">
                                </div>
                                <div class="fg">
                                    <label for="reg_password_confirm">Confirmar *</label>
                                    <input type="password" id="reg_password_confirm"
                                           name="password_confirm"
                                           placeholder="Repetir contraseña"
                                           required>
                                </div>
                            </div>

                            <div class="fcheck">
                                <input type="checkbox" id="terms" required>
                                <label for="terms">
                                    Acepto los <a href="/terminos" target="_blank">términos de servicio</a>
                                    y la <a href="/privacidad" target="_blank">política de privacidad</a>
                                </label>
                            </div>

                            <button type="submit" class="btn-reg" id="btnReg">
                                Crear cuenta y empezar gratis &#8594;
                            </button>

                            <p class="form-note">
                                Al registrarte, el wizard de configuración te espera
                            </p>
                        </form>

                        <div class="login-link">
                            ¿Ya tienes cuenta? <a href="/login">Inicia sesión aquí</a>
                        </div>
                    </div>

                    <!-- Pantalla de éxito -->
                    <div class="success-wrap" id="successWrap">
                        <div class="sicon">&#10003;</div>
                        <h3>¡Bienvenido a GuestHandle!</h3>
                        <p>Tu cuenta está lista. En un momento te redirigimos al asistente de configuración.</p>
                        <a href="/onboarding" class="btn-goto">Ir al wizard &#8594;</a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════════ -->
<footer>
    <span class="footer-logo">Guest<span>Handle</span></span>
    <p style="font-size:.82rem">
        PMS inteligente para hoteles boutique, cabañas y apartamentos turísticos
    </p>
    <p style="font-size:.72rem;margin-top:.4rem">
        &copy; <?= date('Y') ?> GuestHandle &middot; Todos los derechos reservados &middot;
        <a href="/login" style="color:#85B7EB">Iniciar sesión</a>
    </p>
</footer>

<!-- ════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════ -->
<script>
    (function () {

        /* ── Reveal on scroll ─────────────────────────────────────────────── */
        const revEls = document.querySelectorAll('.reveal');
        const obs    = new IntersectionObserver(function (entries) {
            entries.forEach(function (e, i) {
                if (e.isIntersecting) {
                    setTimeout(function () {
                        e.target.classList.add('visible');
                    }, i * 70);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.08 });

        revEls.forEach(function (el) { obs.observe(el); });

        /* ── Registro ─────────────────────────────────────────────────────── */
        var form = document.getElementById('registerForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            handleRegister();
        });

        function showError(msg) {
            var el      = document.getElementById('formError');
            el.textContent = msg;
            el.style.display = 'block';
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideError() {
            var el = document.getElementById('formError');
            el.style.display = 'none';
        }

        function handleRegister() {
            hideError();

            var hotel   = form.hotel_name.value.trim();
            var name    = form.name.value.trim();
            var email   = form.email.value.trim();
            var phone   = form.phone.value.trim();
            var city    = form.city.value.trim();
            var pwd     = form.password.value;
            var pwdC    = form.password_confirm.value;
            var terms   = document.getElementById('terms').checked;
            var btn     = document.getElementById('btnReg');

            /* Validaciones en cliente */
            if (!hotel)                    return showError('El nombre del hotel es requerido.');
            if (!name)                     return showError('Tu nombre es requerido.');
            if (!email)                    return showError('El email es requerido.');
            if (!phone)                    return showError('El teléfono / WhatsApp es requerido.');
            if (!city)                     return showError('La ciudad es requerida.');
            if (pwd.length < 8)            return showError('La contraseña debe tener al menos 8 caracteres.');
            if (pwd !== pwdC)              return showError('Las contraseñas no coinciden.');
            if (!terms)                    return showError('Debes aceptar los términos de servicio.');

            /* Enviar al servidor */
            btn.disabled    = true;
            btn.textContent = 'Creando tu cuenta...';

            var data = new FormData(form);

            fetch('/register', {
                method  : 'POST',
                body    : data,
                headers : { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        /* Mostrar pantalla de éxito */
                        document.getElementById('formContent').style.display = 'none';
                        document.getElementById('successWrap').style.display  = 'block';

                        /* Redirigir al wizard tras 1.8 segundos */
                        if (res.redirect) {
                            setTimeout(function () {
                                window.location.href = res.redirect;
                            }, 1800);
                        }
                    } else {
                        showError(res.message || 'Error al crear la cuenta. Intenta de nuevo.');
                        btn.disabled    = false;
                        btn.textContent = 'Crear cuenta y empezar gratis \u2192';
                    }
                })
                .catch(function () {
                    showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
                    btn.disabled    = false;
                    btn.textContent = 'Crear cuenta y empezar gratis \u2192';
                });
        }

    })();
</script>

</body>
</html>