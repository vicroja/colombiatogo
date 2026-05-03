<!DOCTYPE html>
<html lang="es">
<head>
    <?= csrf_meta() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Oportunidad — Tentii</title>
    <meta name="description" content="Descubre en 2 minutos cuánto dinero está dejando sobre la mesa tu hotel. Diagnóstico gratuito con IA.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">

    <style>
        /* ════════════════════════════════════════════════════════
           VARIABLES & RESET
        ════════════════════════════════════════════════════════ */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --copper:       #C3623A;
            --copper-light: #F7EAE3;
            --copper-dark:  #9E4A27;
            --copper-glow:  rgba(195, 98, 58, 0.25);
            --navy:         #1B2438;
            --navy-mid:     #2D3A52;
            --navy-light:   #3D4F6E;
            --navy-glass:   rgba(27, 36, 56, 0.85);
            --white:        #FFFFFF;
            --gray-50:      #F8F9FB;
            --gray-100:     #F0F2F6;
            --gray-200:     #E2E7EF;
            --gray-400:     #94A3B8;
            --gray-500:     #64748B;
            --gray-700:     #334155;
            --green:        #22C55E;
            --yellow:       #F59E0B;
            --red:          #EF4444;
            --serif:  'DM Serif Display', serif;
            --sans:   'DM Sans', sans-serif;
            --ease:   cubic-bezier(0.4, 0, 0.2, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--sans);
            background: var(--navy);
            color: var(--white);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.65;
        }

        a { text-decoration: none; color: inherit; }
        img { display: block; max-width: 100%; }

        /* ── Animated background ── */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: drift 20s ease-in-out infinite alternate;
        }
        .bg-orb.o1 { width: 600px; height: 600px; background: var(--copper); top: -150px; right: -100px; animation-duration: 23s; }
        .bg-orb.o2 { width: 500px; height: 500px; background: #4A6FA5; bottom: -100px; left: -150px; animation-duration: 18s; animation-delay: -8s; }
        .bg-orb.o3 { width: 350px; height: 350px; background: var(--copper-dark); top: 40%; left: 30%; animation-duration: 28s; animation-delay: -4s; }

        @keyframes drift {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.08); }
        }

        /* ── Grid noise overlay ── */
        .bg-canvas::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.4;
        }

        /* ════════════════════════════════════════════════════════
           LAYOUT
        ════════════════════════════════════════════════════════ */
        .page-wrap {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            padding: 1.25rem clamp(1.25rem, 5vw, 3rem);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-logo img {
            height: 32px;
            width: auto;
        }

        .topbar-back {
            font-size: .8rem;
            color: var(--gray-400);
            display: flex;
            align-items: center;
            gap: .4rem;
            transition: color .2s;
        }
        .topbar-back:hover { color: var(--white); }
        .topbar-back svg { width: 14px; height: 14px; fill: currentColor; }

        .main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem clamp(1.25rem, 5vw, 3rem) 4rem;
        }

        /* ════════════════════════════════════════════════════════
           CARD PRINCIPAL
        ════════════════════════════════════════════════════════ */
        .card {
            width: 100%;
            max-width: 680px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            padding: clamp(2rem, 5vw, 3.5rem);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                    0 0 0 1px rgba(255,255,255,0.06) inset,
                    0 32px 80px rgba(0,0,0,0.35),
                    0 0 60px var(--copper-glow);
        }

        /* ════════════════════════════════════════════════════════
           HEADER DEL FORMULARIO
        ════════════════════════════════════════════════════════ */
        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(195,98,58,.15);
            border: 1px solid rgba(195,98,58,.3);
            color: #E8A882;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .35rem 1rem;
            border-radius: 99px;
            margin-bottom: 1.25rem;
        }
        .form-eyebrow-dot {
            width: 6px; height: 6px;
            background: var(--copper);
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(0.75); }
        }

        .form-title {
            font-family: var(--serif);
            font-size: clamp(1.75rem, 4vw, 2.6rem);
            font-weight: 400;
            line-height: 1.1;
            margin-bottom: .75rem;
            letter-spacing: -.01em;
        }

        .form-title em {
            font-style: italic;
            color: var(--copper);
            background: linear-gradient(135deg, #E8834E, var(--copper));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-subtitle {
            font-size: .925rem;
            color: var(--gray-400);
            max-width: 420px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ════════════════════════════════════════════════════════
           PROGRESS BAR
        ════════════════════════════════════════════════════════ */
        .progress-wrap {
            margin-bottom: 2.25rem;
        }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: .6rem;
        }

        .progress-label {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .05em;
            color: var(--gray-500);
            transition: color .3s;
        }
        .progress-label.active { color: var(--copper); }
        .progress-label.done { color: var(--gray-400); }

        .progress-track {
            height: 3px;
            background: rgba(255,255,255,.08);
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--copper-dark), var(--copper), #E8834E);
            border-radius: 99px;
            transition: width .5s var(--ease);
            box-shadow: 0 0 12px var(--copper-glow);
        }

        /* ════════════════════════════════════════════════════════
           STEPS
        ════════════════════════════════════════════════════════ */
        .step {
            display: none;
            animation: step-in .4s var(--ease) both;
        }
        .step.active {
            display: block;
        }
        @keyframes step-in {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .step-title-sm {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--copper);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .step-title-sm::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.08);
        }

        /* ════════════════════════════════════════════════════════
           FORM FIELDS
        ════════════════════════════════════════════════════════ */
        .field-group {
            margin-bottom: 1.25rem;
        }

        .field-label {
            display: block;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--gray-400);
            margin-bottom: .5rem;
        }

        .field-input,
        .field-select {
            width: 100%;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 12px;
            color: var(--white);
            padding: .8rem 1.1rem;
            font-family: var(--sans);
            font-size: .9rem;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
            -webkit-appearance: none;
            appearance: none;
        }

        .field-input::placeholder { color: rgba(255,255,255,.25); }
        .field-input:focus,
        .field-select:focus {
            border-color: var(--copper);
            background: rgba(195,98,58,.06);
            box-shadow: 0 0 0 3px rgba(195,98,58,.12);
        }

        .field-select option {
            background: var(--navy-mid);
            color: var(--white);
        }

        .field-select-wrap {
            position: relative;
        }
        .field-select-wrap::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 0; height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid var(--gray-400);
            pointer-events: none;
        }

        /* Pill options (radio-style) */
        .pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .pill-option {
            position: relative;
        }

        .pill-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0; height: 0;
        }

        .pill-option label {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            background: rgba(255,255,255,.05);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 99px;
            font-size: .82rem;
            font-weight: 500;
            color: var(--gray-400);
            cursor: pointer;
            transition: all .2s var(--ease);
            user-select: none;
        }

        .pill-option input:checked + label {
            background: rgba(195,98,58,.18);
            border-color: var(--copper);
            color: #E8A882;
            box-shadow: 0 0 0 3px rgba(195,98,58,.1);
        }

        .pill-option label:hover {
            border-color: rgba(195,98,58,.5);
            color: var(--white);
        }

        /* URL field con prefijo */
        .url-wrap {
            display: flex;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
        }
        .url-wrap:focus-within {
            border-color: var(--copper);
            box-shadow: 0 0 0 3px rgba(195,98,58,.12);
        }
        .url-prefix {
            padding: .8rem 1rem;
            font-size: .82rem;
            color: var(--gray-500);
            background: rgba(255,255,255,.03);
            border-right: 1px solid rgba(255,255,255,.08);
            white-space: nowrap;
            line-height: 1.4;
            display: flex;
            align-items: center;
        }
        .url-input {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--white);
            padding: .8rem 1rem;
            font-family: var(--sans);
            font-size: .9rem;
            outline: none;
        }
        .url-input::placeholder { color: rgba(255,255,255,.25); }

        /* Campo de precio con símbolo */
        .price-wrap {
            position: relative;
        }
        .price-symbol {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: .9rem;
            font-weight: 600;
            pointer-events: none;
        }
        .price-input {
            padding-left: 2.5rem !important;
        }

        /* PMS follow-up (condicional) */
        .pms-followup {
            max-height: 0;
            overflow: hidden;
            transition: max-height .35s var(--ease), opacity .3s;
            opacity: 0;
        }
        .pms-followup.visible {
            max-height: 120px;
            opacity: 1;
        }

        /* ════════════════════════════════════════════════════════
           STEP 2 — URL + info
        ════════════════════════════════════════════════════════ */
        .url-info-box {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-top: 1rem;
            display: flex;
            gap: .85rem;
            align-items: flex-start;
        }
        .url-info-icon {
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: .1rem;
        }
        .url-info-text {
            font-size: .78rem;
            color: var(--gray-500);
            line-height: 1.6;
        }
        .url-info-text strong { color: var(--gray-400); font-weight: 500; }

        /* ════════════════════════════════════════════════════════
           BUTTONS
        ════════════════════════════════════════════════════════ */
        .btn-row {
            display: flex;
            gap: .75rem;
            margin-top: 2rem;
            align-items: center;
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, var(--copper-dark), var(--copper));
            color: var(--white);
            border: none;
            padding: .9rem 1.5rem;
            border-radius: 12px;
            font-family: var(--sans);
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s var(--ease);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(195,98,58,.3);
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background .2s;
        }
        .btn-primary:hover::before { background: rgba(255,255,255,.08); }
        .btn-primary:active { transform: scale(0.98); }
        .btn-primary:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }
        .btn-primary svg { width: 16px; height: 16px; fill: currentColor; }

        .btn-back {
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.1);
            color: var(--gray-400);
            padding: .9rem 1.25rem;
            border-radius: 12px;
            font-family: var(--sans);
            font-size: .9rem;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: .4rem;
        }
        .btn-back:hover { border-color: rgba(255,255,255,.2); color: var(--white); }
        .btn-back svg { width: 14px; height: 14px; fill: currentColor; }

        /* ════════════════════════════════════════════════════════
           LOADING STATE
        ════════════════════════════════════════════════════════ */
        #loadingState { display: none; text-align: center; padding: 1rem 0; }
        #loadingState.active { display: block; }

        .loading-animation {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.75rem;
        }

        .loading-ring {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid transparent;
            animation: spin 2s linear infinite;
        }
        .loading-ring.r1 {
            border-top-color: var(--copper);
            animation-duration: 1.2s;
        }
        .loading-ring.r2 {
            inset: 8px;
            border-right-color: rgba(195,98,58,.5);
            animation-duration: 1.8s;
            animation-direction: reverse;
        }
        .loading-ring.r3 {
            inset: 16px;
            border-bottom-color: rgba(195,98,58,.25);
            animation-duration: 2.4s;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-icon {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .loading-title {
            font-family: var(--serif);
            font-size: 1.35rem;
            margin-bottom: .5rem;
        }

        .loading-steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            margin-top: 1rem;
            text-align: left;
            max-width: 280px;
            margin-left: auto;
            margin-right: auto;
        }

        .loading-step {
            font-size: .8rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: .6rem;
            transition: color .3s;
        }
        .loading-step.active { color: var(--white); }
        .loading-step.done { color: var(--green); }

        .lstep-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .6rem;
            transition: background .3s;
        }
        .loading-step.active .lstep-icon { background: var(--copper); animation: pulse-dot 1s ease-in-out infinite; }
        .loading-step.done .lstep-icon { background: var(--green); }

        /* ════════════════════════════════════════════════════════
           RESULTADO
        ════════════════════════════════════════════════════════ */
        #resultadoState { display: none; }
        #resultadoState.active { display: block; animation: step-in .5s var(--ease) both; }

        /* Score gauge */
        .score-section {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .score-gauge-wrap {
            position: relative;
            width: 180px;
            height: 100px;
            margin: 0 auto 1.25rem;
        }

        .score-svg {
            width: 180px;
            height: 100px;
            overflow: visible;
        }

        .gauge-track {
            fill: none;
            stroke: rgba(255,255,255,.08);
            stroke-width: 12;
            stroke-linecap: round;
        }

        .gauge-fill {
            fill: none;
            stroke-width: 12;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.5s var(--ease);
        }

        .score-number {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .score-value {
            font-family: var(--serif);
            font-size: 3rem;
            line-height: 1;
            display: block;
            transition: color .5s;
        }

        .score-max {
            font-size: .72rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .score-nivel {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .3rem .9rem;
            border-radius: 99px;
            margin-bottom: .75rem;
        }

        .score-hotel {
            font-family: var(--serif);
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: .4rem;
        }

        .score-url {
            font-size: .75rem;
            color: var(--gray-500);
            margin-bottom: 1rem;
        }

        .score-resumen {
            font-size: .875rem;
            color: var(--gray-400);
            line-height: 1.75;
            max-width: 460px;
            margin: 0 auto;
        }

        /* Oportunidad box */
        .oportunidad-box {
            background: rgba(195,98,58,.1);
            border: 1px solid rgba(195,98,58,.25);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: left;
        }

        .oport-icon { font-size: 1.75rem; flex-shrink: 0; }

        .oport-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--copper);
            margin-bottom: .2rem;
        }

        .oport-value {
            font-family: var(--serif);
            font-size: 1.6rem;
            color: var(--white);
            line-height: 1.1;
        }

        .oport-sub {
            font-size: .72rem;
            color: var(--gray-500);
            margin-top: .15rem;
        }

        /* Dimensiones */
        .dimensiones-section {
            margin-bottom: 2rem;
        }

        .section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--gray-500);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,.07);
        }

        .dim-row {
            display: flex;
            flex-direction: column;
            gap: .85rem;
        }

        .dim-item {
            display: grid;
            grid-template-columns: 130px 1fr 36px;
            align-items: center;
            gap: 1rem;
        }

        .dim-name {
            font-size: .78rem;
            font-weight: 500;
            color: var(--gray-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dim-bar-track {
            height: 6px;
            background: rgba(255,255,255,.07);
            border-radius: 99px;
            overflow: hidden;
        }

        .dim-bar-fill {
            height: 100%;
            border-radius: 99px;
            width: 0%;
            transition: width 1.2s var(--ease);
        }

        .dim-score {
            font-size: .8rem;
            font-weight: 700;
            text-align: right;
        }

        .dim-hallazgo {
            grid-column: 2 / 4;
            margin-top: -.4rem;
            font-size: .75rem;
            color: var(--gray-500);
            line-height: 1.5;
            padding-left: .1rem;
        }

        /* Hallazgos & Quick Wins */
        .findings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .findings-box {
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 1.25rem;
        }

        .findings-box-header {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .85rem;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .findings-box-header .fh-icon {
            font-size: .9rem;
        }

        .findings-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .findings-list li {
            font-size: .78rem;
            color: var(--gray-400);
            line-height: 1.5;
            padding-left: 1rem;
            position: relative;
        }

        .findings-list li::before {
            content: '';
            position: absolute;
            left: 0;
            top: .5em;
            width: 5px;
            height: 5px;
            border-radius: 50%;
        }

        .findings-criticos .findings-list li::before { background: var(--red); }
        .findings-wins .findings-list li::before { background: var(--green); }

        /* Valor Tentii */
        .valor-box {
            background: linear-gradient(135deg, rgba(195,98,58,.12), rgba(195,98,58,.05));
            border: 1px solid rgba(195,98,58,.2);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .valor-box::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--copper-dark), var(--copper), transparent);
        }

        .valor-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--copper);
            margin-bottom: .6rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .valor-text {
            font-size: .875rem;
            color: var(--gray-300, #CBD5E1);
            line-height: 1.75;
        }

        /* CTA final */
        .result-cta {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .result-cta p {
            font-size: .82rem;
            color: var(--gray-500);
            margin-bottom: 1.25rem;
        }

        .btn-cta-big {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            background: linear-gradient(135deg, var(--copper-dark), var(--copper));
            color: var(--white);
            padding: 1rem 2rem;
            border-radius: 14px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: var(--sans);
            transition: all .2s;
            box-shadow: 0 8px 30px rgba(195,98,58,.35);
            text-decoration: none;
        }
        .btn-cta-big:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(195,98,58,.45); }
        .btn-cta-big svg { width: 18px; height: 18px; fill: currentColor; }

        .btn-restart {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin-top: 1rem;
            background: transparent;
            border: 1.5px solid rgba(255,255,255,.1);
            color: var(--gray-400);
            padding: .65rem 1.25rem;
            border-radius: 10px;
            font-size: .8rem;
            cursor: pointer;
            font-family: var(--sans);
            transition: all .2s;
        }
        .btn-restart:hover { border-color: rgba(255,255,255,.2); color: var(--white); }

        /* ════════════════════════════════════════════════════════
           ERROR STATE
        ════════════════════════════════════════════════════════ */
        .error-banner {
            background: rgba(239, 68, 68, .1);
            border: 1px solid rgba(239, 68, 68, .25);
            border-radius: 12px;
            padding: .85rem 1.1rem;
            font-size: .83rem;
            color: #FCA5A5;
            margin-top: 1.25rem;
            display: none;
            line-height: 1.5;
        }
        .error-banner.visible { display: block; }

        /* ════════════════════════════════════════════════════════
           METADATA footer del resultado
        ════════════════════════════════════════════════════════ */
        .result-meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            font-size: .7rem;
            color: var(--gray-600, #475569);
        }
        .result-meta span {
            display: flex;
            align-items: center;
            gap: .3rem;
        }

        /* ════════════════════════════════════════════════════════
           RESPONSIVE
        ════════════════════════════════════════════════════════ */
        @media (max-width: 600px) {
            .card { padding: 1.75rem 1.25rem; }
            .findings-grid { grid-template-columns: 1fr; }
            .dim-item { grid-template-columns: 100px 1fr 30px; gap: .6rem; }
            .pill-group { gap: .35rem; }
            .pill-option label { font-size: .76rem; padding: .45rem .85rem; }
        }
    </style>
</head>
<body>

<!-- Fondo animado -->
<div class="bg-canvas">
    <div class="bg-orb o1"></div>
    <div class="bg-orb o2"></div>
    <div class="bg-orb o3"></div>
</div>

<div class="page-wrap">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-logo">
            <picture>
                <source media="(min-width:600px)" srcset="https://tentii.com/public/tentii-lg_2200w.png" width="160">
                <img src="https://tentii.com/public/tentii_mini_594w.png" width="100" alt="Tentii">
            </picture>
        </div>
        <a href="/" class="topbar-back">
            <svg viewBox="0 0 24 24"><path d="M19 12H5m7-7-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
            Volver al inicio
        </a>
    </div>

    <!-- Main -->
    <div class="main">
        <div class="card">

            <!-- ══ FORMULARIO ══ -->
            <div id="formState">
                <div class="form-header">
                    <div class="form-eyebrow">
                        <div class="form-eyebrow-dot"></div>
                        Diagnóstico gratuito · Powered by IA
                    </div>
                    <h1 class="form-title">¿Cuánto dinero está dejando<br>sobre la mesa <em>tu hotel</em>?</h1>
                    <p class="form-subtitle">Responde 7 preguntas rápidas e ingresa tu sitio web. La IA analiza tu operación y te da un diagnóstico personalizado en segundos.</p>
                </div>

                <!-- Progress -->
                <div class="progress-wrap">
                    <div class="progress-labels">
                        <span class="progress-label active" id="plabel-1">Tu operación</span>
                        <span class="progress-label" id="plabel-2">Tu sitio web</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar" id="progressBar" style="width: 50%"></div>
                    </div>
                </div>

                <!-- Error banner -->
                <div class="error-banner" id="errorBanner"></div>

                <!-- ── STEP 1 ── -->
                <div class="step active" id="step1">
                    <div class="step-title-sm">Paso 1 de 2 — Tu operación actual</div>

                    <div class="field-group">
                        <label class="field-label">Nombre de tu hotel o alojamiento *</label>
                        <input type="text" class="field-input" id="nombre_hotel"
                               placeholder="Ej: Hotel Boutique Casa Verde" maxlength="100">
                    </div>

                    <div class="field-group">
                        <label class="field-label">¿Usan WhatsApp Business? *</label>
                        <div class="pill-group">
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_business" id="wb_si" value="Sí">
                                <label for="wb_si">✅ Sí</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_business" id="wb_no" value="No">
                                <label for="wb_no">❌ No</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_business" id="wb_personal" value="Usamos WhatsApp personal">
                                <label for="wb_personal">📱 Solo personal</label>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">¿Tienen respuestas automáticas configuradas? *</label>
                        <div class="pill-group">
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_automatico" id="wa_si" value="Sí, respuestas automáticas">
                                <label for="wa_si">🤖 Sí</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_automatico" id="wa_no" value="No, todo manual">
                                <label for="wa_no">✋ Todo manual</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="whatsapp_automatico" id="wa_parcial" value="Solo mensaje de bienvenida">
                                <label for="wa_parcial">🔤 Solo bienvenida</label>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">¿Quién atiende el WhatsApp? *</label>
                        <div class="pill-group">
                            <div class="pill-option">
                                <input type="radio" name="quien_atiende" id="qa_dueno" value="El dueño o gerente">
                                <label for="qa_dueno">👤 El dueño</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="quien_atiende" id="qa_recepcion" value="Recepcionistas">
                                <label for="qa_recepcion">🏨 Recepción</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="quien_atiende" id="qa_equipo" value="Equipo de ventas dedicado">
                                <label for="qa_equipo">📞 Ventas</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="quien_atiende" id="qa_nadie" value="Nadie en horario nocturno">
                                <label for="qa_nadie">🌙 Nadie en la noche</label>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Volumen de mensajes WhatsApp por día *</label>
                        <div class="pill-group">
                            <div class="pill-option">
                                <input type="radio" name="volumen_whatsapp" id="vol_poco" value="Menos de 10 mensajes/día">
                                <label for="vol_poco">Menos de 10</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="volumen_whatsapp" id="vol_medio" value="Entre 10 y 30 mensajes/día">
                                <label for="vol_medio">10 – 30</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="volumen_whatsapp" id="vol_alto" value="Entre 30 y 80 mensajes/día">
                                <label for="vol_alto">30 – 80</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="volumen_whatsapp" id="vol_muy_alto" value="Más de 80 mensajes/día">
                                <label for="vol_muy_alto">+80</label>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Precio promedio por reserva (COP) *</label>
                        <div class="price-wrap">
                            <span class="price-symbol">$</span>
                            <input type="number" class="field-input price-input" id="precio_reserva"
                                   placeholder="350.000" min="0" step="1000">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">¿Usan algún PMS actualmente? *</label>
                        <div class="pill-group">
                            <div class="pill-option">
                                <input type="radio" name="tiene_pms" id="pms_si" value="si">
                                <label for="pms_si">✅ Sí tenemos</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="tiene_pms" id="pms_no" value="no">
                                <label for="pms_no">❌ No usamos</label>
                            </div>
                            <div class="pill-option">
                                <input type="radio" name="tiene_pms" id="pms_excel" value="excel">
                                <label for="pms_excel">📊 Excel / hojas</label>
                            </div>
                        </div>

                        <div class="pms-followup" id="pmsFollowup">
                            <div style="height: .75rem"></div>
                            <input type="text" class="field-input" id="cual_pms"
                                   placeholder="¿Cuál? Ej: Cloudbeds, Siesa, Pxsol, otro..." maxlength="80">
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn-primary" onclick="goToStep2()">
                            Continuar
                            <svg viewBox="0 0 24 24"><path d="M5 12h14m-7-7 7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                        </button>
                    </div>
                </div>

                <!-- ── STEP 2 ── -->
                <div class="step" id="step2">
                    <div class="step-title-sm">Paso 2 de 2 — Tu sitio web</div>

                    <div class="field-group">
                        <label class="field-label">Sitio web de tu hotel *</label>
                        <div class="url-wrap">
                            <span class="url-prefix">https://</span>
                            <input type="text" class="url-input" id="website"
                                   placeholder="mihotel.com">
                        </div>
                    </div>

                    <div class="url-info-box">
                        <div class="url-info-icon">🔍</div>
                        <div class="url-info-text">
                            <strong>La IA va a leer tu sitio web</strong> — analiza la homepage y hasta 8 páginas internas para encontrar hallazgos concretos: si tienes motor de reservas, qué servicios ofreces, cómo se ve tu presencia digital y más.
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn-back" onclick="goToStep1()">
                            <svg viewBox="0 0 24 24"><path d="M19 12H5m7-7-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                            Atrás
                        </button>
                        <button class="btn-primary" id="btnAnalizar" onclick="analizar()">
                            <svg viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                            Generar diagnóstico
                        </button>
                    </div>
                </div>

            </div><!-- /#formState -->

            <!-- ══ LOADING ══ -->
            <div id="loadingState">
                <div class="loading-animation">
                    <div class="loading-ring r1"></div>
                    <div class="loading-ring r2"></div>
                    <div class="loading-ring r3"></div>
                    <div class="loading-icon">🏨</div>
                </div>
                <div class="loading-title">Analizando tu hotel…</div>
                <p style="font-size:.82rem;color:var(--gray-500);margin-top:.4rem">Esto toma entre 10 y 20 segundos</p>
                <ul class="loading-steps">
                    <li class="loading-step" id="lstep1">
                        <div class="lstep-icon">→</div>
                        Leyendo tu sitio web
                    </li>
                    <li class="loading-step" id="lstep2">
                        <div class="lstep-icon">→</div>
                        Analizando páginas internas
                    </li>
                    <li class="loading-step" id="lstep3">
                        <div class="lstep-icon">→</div>
                        Procesando respuestas
                    </li>
                    <li class="loading-step" id="lstep4">
                        <div class="lstep-icon">→</div>
                        Generando diagnóstico con IA
                    </li>
                    <li class="loading-step" id="lstep5">
                        <div class="lstep-icon">→</div>
                        Calculando oportunidad
                    </li>
                </ul>
            </div>

            <!-- ══ RESULTADO ══ -->
            <div id="resultadoState">

                <!-- Score -->
                <div class="score-section">
                    <div class="score-gauge-wrap">
                        <svg class="score-svg" viewBox="0 0 180 100">
                            <path class="gauge-track"
                                  d="M 20 90 A 70 70 0 0 1 160 90"
                                  stroke-dasharray="220" stroke-dashoffset="0"/>
                            <path class="gauge-fill" id="gaugeFill"
                                  d="M 20 90 A 70 70 0 0 1 160 90"
                                  stroke-dasharray="220" stroke-dashoffset="220"/>
                        </svg>
                        <div class="score-number">
                            <span class="score-value" id="scoreValue">0</span>
                            <span class="score-max">/100</span>
                        </div>
                    </div>

                    <div class="score-nivel" id="scoreNivel"></div>
                    <div class="score-hotel" id="scoreHotel"></div>
                    <div class="score-url" id="scoreUrl"></div>
                    <p class="score-resumen" id="scoreResumen"></p>

                    <div class="oportunidad-box">
                        <div class="oport-icon">💰</div>
                        <div>
                            <div class="oport-label">Oportunidad mensual estimada</div>
                            <div class="oport-value" id="oportunidadValue"></div>
                            <div class="oport-sub">En reservas recuperables con automatización</div>
                        </div>
                    </div>
                </div>

                <!-- Dimensiones -->
                <div class="dimensiones-section">
                    <div class="section-label">Diagnóstico por dimensión</div>
                    <div class="dim-row" id="dimensionesRow"></div>
                </div>

                <!-- Hallazgos + Quick Wins -->
                <div class="findings-grid">
                    <div class="findings-box findings-criticos">
                        <div class="findings-box-header" style="color:#FCA5A5">
                            <span class="fh-icon">⚠️</span>
                            Hallazgos críticos
                        </div>
                        <ul class="findings-list" id="hallazgosList"></ul>
                    </div>
                    <div class="findings-box findings-wins">
                        <div class="findings-box-header" style="color:#86EFAC">
                            <span class="fh-icon">⚡</span>
                            Quick wins con Tentii
                        </div>
                        <ul class="findings-list" id="quickWinsList"></ul>
                    </div>
                </div>

                <!-- Valor Tentii -->
                <div class="valor-box">
                    <div class="valor-label">
                        <span>🏨</span> Lo que Tentii haría por este hotel
                    </div>
                    <p class="valor-text" id="valorTentii"></p>
                </div>

                <!-- CTA -->
                <div class="result-cta">
                    <p>Tu diagnóstico está listo. El siguiente paso es una demo en vivo de 20 minutos — sin compromiso.</p>
                    <a href="https://wa.me/573000000000" target="_blank" class="btn-cta-big">
                        <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Agendar demo gratis por WhatsApp
                    </a>
                    <br>
                    <button class="btn-restart" onclick="restart()">
                        ↺ Analizar otro hotel
                    </button>
                </div>

                <!-- Meta -->
                <div class="result-meta">
                    <span>🔍 <span id="metaPages"></span></span>
                    <span>🕐 <span id="metaDate"></span></span>
                    <span>✨ Análisis con Gemini AI</span>
                </div>

            </div><!-- /#resultadoState -->

        </div><!-- /.card -->
    </div><!-- /.main -->

</div><!-- /.page-wrap -->

<script>
    // ════════════════════════════════════════════════════════
    // NAVEGACIÓN DE PASOS
    // ════════════════════════════════════════════════════════
    function goToStep2() {
        const errors = validateStep1();
        if (errors.length) {
            showError(errors[0]);
            return;
        }
        hideError();
        document.getElementById('step1').classList.remove('active');
        document.getElementById('step2').classList.add('active');
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('plabel-1').className = 'progress-label done';
        document.getElementById('plabel-2').className = 'progress-label active';
    }

    function goToStep1() {
        hideError();
        document.getElementById('step2').classList.remove('active');
        document.getElementById('step1').classList.add('active');
        document.getElementById('progressBar').style.width = '50%';
        document.getElementById('plabel-1').className = 'progress-label active';
        document.getElementById('plabel-2').className = 'progress-label';
    }

    function restart() {
        document.getElementById('resultadoState').classList.remove('active');
        document.getElementById('resultadoState').style.display = 'none';
        document.getElementById('formState').style.display = 'block';
        document.getElementById('step2').classList.remove('active');
        document.getElementById('step1').classList.add('active');
        document.getElementById('progressBar').style.width = '50%';
        document.getElementById('plabel-1').className = 'progress-label active';
        document.getElementById('plabel-2').className = 'progress-label';
        // Reset form
        document.getElementById('nombre_hotel').value = '';
        document.getElementById('precio_reserva').value = '';
        document.getElementById('website').value = '';
        document.getElementById('cual_pms').value = '';
        document.querySelectorAll('input[type=radio]').forEach(r => r.checked = false);
        document.getElementById('pmsFollowup').classList.remove('visible');
        hideError();
    }

    // PMS follow-up
    document.querySelectorAll('input[name="tiene_pms"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const fu = document.getElementById('pmsFollowup');
            if (this.value === 'si') {
                fu.classList.add('visible');
            } else {
                fu.classList.remove('visible');
                document.getElementById('cual_pms').value = '';
            }
        });
    });

    // ════════════════════════════════════════════════════════
    // VALIDACIÓN
    // ════════════════════════════════════════════════════════
    function validateStep1() {
        const errs = [];
        if (!document.getElementById('nombre_hotel').value.trim())
            errs.push('Ingresa el nombre de tu hotel.');
        if (!document.querySelector('input[name="whatsapp_business"]:checked'))
            errs.push('Indica si usan WhatsApp Business.');
        if (!document.querySelector('input[name="whatsapp_automatico"]:checked'))
            errs.push('Indica si tienen respuestas automáticas.');
        if (!document.querySelector('input[name="quien_atiende"]:checked'))
            errs.push('¿Quién atiende el WhatsApp?');
        if (!document.querySelector('input[name="volumen_whatsapp"]:checked'))
            errs.push('Indica el volumen de mensajes diarios.');
        const precio = document.getElementById('precio_reserva').value;
        if (!precio || isNaN(precio) || Number(precio) <= 0)
            errs.push('Ingresa el precio promedio por reserva.');
        if (!document.querySelector('input[name="tiene_pms"]:checked'))
            errs.push('Indica si usan algún PMS.');
        return errs;
    }

    function validateStep2() {
        const website = document.getElementById('website').value.trim();
        if (!website) return ['Ingresa la URL de tu sitio web.'];
        // Validación básica de dominio
        const domainRegex = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/.*)?$/;
        if (!domainRegex.test(website)) return ['La URL no parece válida. Ej: mihotel.com'];
        return [];
    }

    // ════════════════════════════════════════════════════════
    // LOADING STEPS ANIMATION
    // ════════════════════════════════════════════════════════
    const loadingTimers = [];

    function runLoadingSteps() {
        const steps = ['lstep1','lstep2','lstep3','lstep4','lstep5'];
        steps.forEach(id => {
            const el = document.getElementById(id);
            el.className = 'loading-step';
            el.querySelector('.lstep-icon').textContent = '→';
        });

        const delays = [200, 2500, 5000, 8000, 13000];
        steps.forEach((id, i) => {
            const t = setTimeout(() => {
                // Mark previous as done
                if (i > 0) {
                    const prev = document.getElementById(steps[i-1]);
                    prev.className = 'loading-step done';
                    prev.querySelector('.lstep-icon').textContent = '✓';
                }
                const el = document.getElementById(id);
                el.className = 'loading-step active';
            }, delays[i]);
            loadingTimers.push(t);
        });
    }

    function clearLoadingTimers() {
        loadingTimers.forEach(clearTimeout);
        loadingTimers.length = 0;
    }

    // ════════════════════════════════════════════════════════
    // ANALIZAR — fetch al backend
    // ════════════════════════════════════════════════════════
    async function analizar() {
        const errors = validateStep2();
        if (errors.length) {
            showError(errors[0]);
            return;
        }
        hideError();

        // Collect form data
        const formData = new FormData();
        formData.append('nombre_hotel', document.getElementById('nombre_hotel').value.trim());
        formData.append('whatsapp_business', document.querySelector('input[name="whatsapp_business"]:checked').value);
        formData.append('whatsapp_automatico', document.querySelector('input[name="whatsapp_automatico"]:checked').value);
        formData.append('quien_atiende', document.querySelector('input[name="quien_atiende"]:checked').value);
        formData.append('volumen_whatsapp', document.querySelector('input[name="volumen_whatsapp"]:checked').value);
        formData.append('precio_reserva', document.getElementById('precio_reserva').value);
        formData.append('tiene_pms', document.querySelector('input[name="tiene_pms"]:checked').value);
        formData.append('cual_pms', document.getElementById('cual_pms').value.trim());

        let website = document.getElementById('website').value.trim();
        if (!website.startsWith('http')) website = 'https://' + website;
        formData.append('website', website);

        // Fetch CSRF token from meta (CI4 standard)
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            formData.append(csrfMeta.getAttribute('name'), csrfMeta.getAttribute('content'));
        }

        // UI → loading
        document.getElementById('formState').style.display = 'none';
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('loadingState').classList.add('active');
        runLoadingSteps();

        try {
            const res = await fetch('/calculadora/analizar', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const json = await res.json();

            clearLoadingTimers();
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('loadingState').classList.remove('active');

            if (!json.success) {
                document.getElementById('formState').style.display = 'block';
                document.getElementById('step2').classList.add('active');
                document.getElementById('step1').classList.remove('active');
                showError(json.error || 'Error al generar el diagnóstico. Intenta de nuevo.');
                return;
            }

            renderResultado(json.diagnostico);

        } catch (e) {
            clearLoadingTimers();
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('formState').style.display = 'block';
            document.getElementById('step2').classList.add('active');
            document.getElementById('step1').classList.remove('active');
            showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
        }
    }

    // ════════════════════════════════════════════════════════
    // RENDER DEL RESULTADO
    // ════════════════════════════════════════════════════════
    function renderResultado(d) {
        const score = d.score_global || 0;
        const nivel = d.nivel || 'Básico';
        const meta  = d.meta || {};

        // Show container
        document.getElementById('resultadoState').style.display = 'block';
        document.getElementById('resultadoState').classList.add('active');

        // ── Score gauge ──
        const nivelColors = {
            'Crítico':    { color: '#EF4444', bg: 'rgba(239,68,68,.15)',  border: 'rgba(239,68,68,.3)'  },
            'Básico':     { color: '#F59E0B', bg: 'rgba(245,158,11,.15)', border: 'rgba(245,158,11,.3)' },
            'Intermedio': { color: '#3B82F6', bg: 'rgba(59,130,246,.15)', border: 'rgba(59,130,246,.3)' },
            'Avanzado':   { color: '#22C55E', bg: 'rgba(34,197,94,.15)',  border: 'rgba(34,197,94,.3)'  },
        };
        const nc = nivelColors[nivel] || nivelColors['Básico'];

        // Gauge — circumference of the arc is ~220
        const pct = score / 100;
        const dashoffset = 220 - (220 * pct);
        const gaugeFill = document.getElementById('gaugeFill');
        gaugeFill.style.stroke = nc.color;

        // Animate score
        setTimeout(() => {
            gaugeFill.style.strokeDashoffset = dashoffset;
            animateNumber(document.getElementById('scoreValue'), 0, score, 1400);
        }, 100);

        document.getElementById('scoreValue').style.color = nc.color;

        // Nivel badge
        const nivelEl = document.getElementById('scoreNivel');
        nivelEl.textContent = nivel;
        nivelEl.style.background = nc.bg;
        nivelEl.style.border = `1px solid ${nc.border}`;
        nivelEl.style.color = nc.color;

        document.getElementById('scoreHotel').textContent = meta.hotel || '';
        document.getElementById('scoreUrl').textContent = meta.website || '';
        document.getElementById('scoreResumen').textContent = d.resumen_ejecutivo || '';

        // Oportunidad
        const oport = d.oportunidad_mensual_estimada || 0;
        document.getElementById('oportunidadValue').textContent = '$ ' + oport.toLocaleString('es-CO') + ' COP';

        // ── Dimensiones ──
        const dimContainer = document.getElementById('dimensionesRow');
        dimContainer.innerHTML = '';
        const dims = d.dimensiones || {};
        const dimOrder = ['atencion_cliente','automatizacion','revenue_management','presencia_digital','gestion_operativa'];

        dimOrder.forEach((key, idx) => {
            const dim = dims[key];
            if (!dim) return;
            const s = dim.score || 0;
            const color = s >= 70 ? '#22C55E' : s >= 40 ? '#F59E0B' : '#EF4444';

            const item = document.createElement('div');
            item.className = 'dim-item';
            item.innerHTML = `
            <div class="dim-name">${dim.label || key}</div>
            <div class="dim-bar-track">
                <div class="dim-bar-fill" id="dim-bar-${idx}" style="background:${color}"></div>
            </div>
            <div class="dim-score" style="color:${color}">${s}</div>
            <div class="dim-hallazgo">${dim.hallazgo || ''}</div>
        `;
            dimContainer.appendChild(item);

            // Animate bar
            setTimeout(() => {
                document.getElementById(`dim-bar-${idx}`).style.width = s + '%';
            }, 300 + idx * 120);
        });

        // ── Hallazgos críticos ──
        const hallazgosList = document.getElementById('hallazgosList');
        hallazgosList.innerHTML = '';
        (d.hallazgos_criticos || []).forEach(h => {
            const li = document.createElement('li');
            li.textContent = h;
            hallazgosList.appendChild(li);
        });

        // ── Quick wins ──
        const qwList = document.getElementById('quickWinsList');
        qwList.innerHTML = '';
        (d.quick_wins || []).forEach(w => {
            const li = document.createElement('li');
            li.textContent = w;
            qwList.appendChild(li);
        });

        // ── Valor Tentii ──
        document.getElementById('valorTentii').textContent = d.valor_tentii || '';

        // ── Meta ──
        document.getElementById('metaPages').textContent =
            (meta.pages_crawled || 0) + ' páginas analizadas';

        const ts = meta.generated_at ? new Date(meta.generated_at) : new Date();
        document.getElementById('metaDate').textContent =
            ts.toLocaleDateString('es-CO', { day:'numeric', month:'long', year:'numeric' });

        // Scroll to top of card
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ════════════════════════════════════════════════════════
    // UTILS
    // ════════════════════════════════════════════════════════
    function animateNumber(el, from, to, duration) {
        const start = performance.now();
        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(from + (to - from) * eased);
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    function showError(msg) {
        const el = document.getElementById('errorBanner');
        el.textContent = '⚠️ ' + msg;
        el.classList.add('visible');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideError() {
        document.getElementById('errorBanner').classList.remove('visible');
    }
</script>

</body>
</html>