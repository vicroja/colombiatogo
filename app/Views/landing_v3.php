<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tentii para operadores de tours — Centraliza reservas, cupos, guías y comunicación en un solo lugar. La IA que responde, cotiza y llena tus salidas mientras tú operas.">
    <meta name="keywords" content="software operadores de tours, reservas tours WhatsApp, logística tours, gestión cupos tours, IA tours, channel manager experiencias">
    <meta property="og:title" content="Tentii — Tus tours se llenan solos. Tú solo opera.">
    <meta property="og:description" content="La plataforma que centraliza reservas, cupos, guías y WhatsApp para operadores de tours y experiencias. Responde, cotiza y confirma sin que muevas un dedo.">
    <meta property="og:type" content="website">
    <title>Tentii para Tours — Tus salidas se llenan solas. Tú solo opera.</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Jost = fuente del brief Tentii -->
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

    <style>
        /* ════════════════════════════════════════════════════════
           RESET & TOKENS DE MARCA TENTII  (paleta + fuente del brief)
        ════════════════════════════════════════════════════════ */
        *{margin:0;padding:0;box-sizing:border-box}

        :root{
            /* Paleta oficial del brief */
            --terra        : #D96C4A;   /* Color principal Tentii */
            --terra-dark   : #B8512F;
            --terra-soft   : #F5D4C6;
            --terra-tint   : #FBEAE0;

            --violet       : #7B61FF;   /* Color de contraste Tentii */
            --violet-dark  : #5A3FE0;
            --violet-soft  : #E5DEFF;

            --sand         : #F4EFEA;   /* Arena — fondo de contraste */
            --sand-warm    : #EFE6DC;
            --cream        : #FBF8F4;

            /* Neutros cálidos */
            --ink          : #1C1714;
            --ink-soft     : #3D332C;
            --slate        : #6B5D54;
            --mute         : #9B8E84;
            --line         : #E7DED3;
            --white        : #FFFFFF;

            --wa-green     : #25D366;

            /* Tipografía — Jost (única familia del brief) */
            --font         : 'Jost', -apple-system, BlinkMacSystemFont, sans-serif;

            --r-sm : 8px;  --r : 14px;  --r-lg : 22px;  --r-xl : 32px;

            --shadow-sm    : 0 1px 2px rgba(28,23,20,.04), 0 1px 3px rgba(28,23,20,.06);
            --shadow       : 0 4px 16px rgba(28,23,20,.06), 0 2px 6px rgba(28,23,20,.04);
            --shadow-lg    : 0 18px 50px rgba(28,23,20,.10), 0 8px 20px rgba(28,23,20,.06);
            --shadow-terra : 0 8px 24px rgba(217,108,74,.28);
            --shadow-violet: 0 8px 24px rgba(123,97,255,.30);

            --maxw : 1180px;
        }

        html{scroll-behavior:smooth}

        body{
            background    : var(--cream);
            color         : var(--ink);
            font-family   : var(--font);
            font-size     : 16px;
            line-height   : 1.6;
            overflow-x    : hidden;
            -webkit-font-smoothing: antialiased;
        }

        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}
        button{font-family:inherit;cursor:pointer}

        .container{max-width:var(--maxw);margin:0 auto;padding:0 clamp(1.25rem,4vw,2rem)}

        h1,h2,h3{font-weight:600;line-height:1.08;letter-spacing:-.02em}
        em{font-style:italic;color:var(--terra)}

        /* ════════════════ NAV ════════════════ */
        nav{
            background:rgba(251,248,244,.9);
            border-bottom:1px solid var(--line);
            padding:0 clamp(1.25rem,4vw,2.75rem);
            display:flex;justify-content:space-between;align-items:center;
            height:72px;position:sticky;top:0;z-index:200;
            backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
        }
        .nav-logo{font-size:1.5rem;font-weight:700;color:var(--terra);letter-spacing:-.03em}
        .nav-links{display:flex;align-items:center;gap:2.1rem}
        .nav-links a{font-size:.9rem;color:var(--ink-soft);font-weight:500;transition:color .15s}
        .nav-links a:hover{color:var(--terra)}
        .nav-actions{display:flex;align-items:center;gap:.75rem}
        .btn-login{
            font-size:.9rem;color:var(--ink-soft);font-weight:500;
            padding:.55rem 1.1rem;border:1.5px solid var(--line);
            border-radius:var(--r);transition:all .18s;background:transparent;
        }
        .btn-login:hover{border-color:var(--terra);color:var(--terra)}

        /* Botón principal = igual al "Crear cuenta" del brief, en violeta */
        .btn-primary{
            display:inline-flex;align-items:center;gap:.5rem;
            background:var(--violet);color:#fff;font-weight:600;font-size:.95rem;
            padding:.8rem 1.5rem;border:none;border-radius:var(--r);
            box-shadow:var(--shadow-violet);transition:transform .15s,box-shadow .15s;
        }
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(123,97,255,.4)}
        .btn-primary .arr{transition:transform .2s}
        .btn-primary:hover .arr{transform:translateX(4px)}

        .btn-secondary{
            display:inline-flex;align-items:center;gap:.5rem;
            background:#fff;color:var(--ink);font-weight:600;font-size:.95rem;
            padding:.8rem 1.4rem;border:1.5px solid var(--line);border-radius:var(--r);
            transition:all .18s;
        }
        .btn-secondary:hover{border-color:var(--terra);color:var(--terra)}

        .btn-terra{
            display:inline-flex;align-items:center;gap:.5rem;justify-content:center;
            background:var(--terra);color:#fff;font-weight:600;font-size:.95rem;
            padding:.85rem 1.6rem;border:none;border-radius:var(--r);
            box-shadow:var(--shadow-terra);transition:transform .15s,box-shadow .15s;
        }
        .btn-terra:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(217,108,74,.4)}

        .eyebrow{
            display:inline-block;font-size:.78rem;font-weight:600;letter-spacing:.12em;
            text-transform:uppercase;color:var(--terra);margin-bottom:1rem;
        }
        .section-h{font-size:clamp(1.8rem,3.6vw,2.7rem);margin-bottom:1rem}
        .section-p{font-size:1.08rem;color:var(--slate);max-width:600px}
        .section-head-center{text-align:center;max-width:760px;margin:0 auto 3.5rem}
        .section-head-center .section-p{margin:0 auto}

        section{padding:clamp(4rem,8vw,6.5rem) 0}

        /* ════════════════ HERO ════════════════ */
        .hero{
            background:
                    radial-gradient(1100px 600px at 85% -5%, var(--terra-tint), transparent 60%),
                    radial-gradient(900px 500px at 5% 110%, var(--violet-soft), transparent 55%),
                    var(--cream);
            padding:clamp(3rem,6vw,5rem) 0 clamp(4rem,7vw,6rem);
        }
        .hero-inner{
            max-width:var(--maxw);margin:0 auto;padding:0 clamp(1.25rem,4vw,2rem);
            display:grid;grid-template-columns:1.05fr 1fr;gap:clamp(2rem,5vw,4rem);align-items:center;
        }
        .hero-badge{
            display:inline-flex;align-items:center;gap:.6rem;
            background:#fff;border:1px solid var(--line);border-radius:100px;
            padding:.4rem .4rem .4rem .9rem;font-size:.82rem;font-weight:500;color:var(--ink-soft);
            margin-bottom:1.5rem;box-shadow:var(--shadow-sm);
        }
        .hero-badge-pill{
            background:var(--terra);color:#fff;font-size:.72rem;font-weight:600;
            padding:.2rem .6rem;border-radius:100px;letter-spacing:.04em;
        }
        .hero h1{font-size:clamp(2.4rem,5.2vw,3.7rem)}
        .hero-sub{font-size:1.18rem;color:var(--slate);margin:1.5rem 0 2rem;max-width:520px}
        .hero-btns{display:flex;gap:.9rem;flex-wrap:wrap;margin-bottom:1.8rem}
        .hero-trust{display:flex;align-items:center;gap:.7rem;font-size:.9rem;color:var(--slate)}
        .hero-trust strong{color:var(--ink)}
        .trust-dots{display:flex}
        .trust-dots .dot{
            width:26px;height:26px;border-radius:50%;border:2px solid var(--cream);
            margin-left:-8px;background:linear-gradient(135deg,var(--terra),var(--violet));
        }
        .trust-dots .dot:first-child{margin-left:0}

        /* HERO VISUAL — slot para imagen diseñada + chips flotantes */
        .hero-visual{position:relative}
        .hero-image-slot{
            position:relative;border-radius:var(--r-xl);overflow:hidden;
            box-shadow:var(--shadow-lg);aspect-ratio:4/5;
            background:
                    linear-gradient(160deg, rgba(28,23,20,.05), rgba(28,23,20,.35)),
                    linear-gradient(135deg, var(--terra-soft), var(--violet-soft));
        }
        .hero-image-slot img{width:100%;height:100%;object-fit:cover}
        /* Placeholder visible mientras no exista la imagen final */
        .hero-image-ph{
            position:absolute;inset:0;display:flex;flex-direction:column;
            align-items:center;justify-content:center;gap:.6rem;color:#fff;text-align:center;padding:2rem;
        }
        .hero-image-ph .ph-ico{font-size:2.4rem}
        .hero-image-ph .ph-t{font-weight:600;font-size:1.05rem;text-shadow:0 1px 8px rgba(0,0,0,.3)}
        .hero-image-ph .ph-s{font-size:.85rem;opacity:.92;max-width:260px;text-shadow:0 1px 6px rgba(0,0,0,.3)}
        .hero-image-caption{
            position:absolute;left:1rem;bottom:1rem;
            background:rgba(28,23,20,.55);color:#fff;border-radius:var(--r);
            padding:.7rem 1rem;font-size:.85rem;font-weight:500;backdrop-filter:blur(6px);
            display:flex;align-items:center;gap:.55rem;max-width:80%;
        }
        .float-chip{
            position:absolute;background:#fff;border-radius:var(--r);box-shadow:var(--shadow-lg);
            padding:.7rem .9rem;display:flex;align-items:center;gap:.7rem;z-index:5;
            border:1px solid var(--line);
        }
        .float-chip-icon{
            width:38px;height:38px;border-radius:10px;display:grid;place-items:center;font-size:1.1rem;
            background:var(--terra-tint);
        }
        .float-chip strong{display:block;font-size:1rem;font-weight:700;line-height:1.1}
        .float-chip span{font-size:.74rem;color:var(--slate)}
        .chip-1{top:-18px;left:-22px;animation:float 5s ease-in-out infinite}
        .chip-2{bottom:60px;right:-26px;animation:float 6s ease-in-out infinite .5s}
        .chip-3{bottom:-18px;left:18px;animation:float 5.5s ease-in-out infinite 1s}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}

        /* ════════════════ MARQUEE confianza ════════════════ */
        .proof-bar{background:var(--sand);border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:1.4rem 0}
        .proof-inner{max-width:var(--maxw);margin:0 auto;padding:0 2rem;display:flex;align-items:center;justify-content:center;gap:2.5rem;flex-wrap:wrap;font-size:.92rem;color:var(--slate)}
        .proof-inner strong{color:var(--terra);font-weight:700}

        /* ════════════════ PROBLEMA ════════════════ */
        .problem-section{background:var(--cream)}
        .problem-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.4rem;margin-top:3rem}
        .problem-card{
            background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);
            padding:2rem 1.7rem;transition:transform .2s,box-shadow .2s;
        }
        .problem-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
        .problem-card .pc-ico{font-size:1.7rem;margin-bottom:1rem}
        .problem-card h3{font-size:1.15rem;margin-bottom:.6rem}
        .problem-card p{font-size:.95rem;color:var(--slate)}

        /* ════════════════ AGITACIÓN ════════════════ */
        .agit-section{background:var(--ink);color:#fff;position:relative;overflow:hidden}
        .agit-section::before{
            content:"";position:absolute;inset:0;
            background:radial-gradient(700px 400px at 80% 0,rgba(217,108,74,.22),transparent 60%),radial-gradient(600px 400px at 0 100%,rgba(123,97,255,.2),transparent 60%);
        }
        .agit-inner{position:relative;max-width:840px;margin:0 auto;text-align:center;padding:0 2rem}
        .agit-inner .eyebrow{color:var(--terra-soft)}
        .agit-inner h2{font-size:clamp(1.8rem,4vw,2.8rem);margin-bottom:1.5rem}
        .agit-inner h2 em{color:var(--terra-soft)}
        .agit-stat-row{display:flex;justify-content:center;gap:clamp(1.5rem,5vw,4rem);flex-wrap:wrap;margin-top:3rem}
        .agit-stat{text-align:center}
        .agit-stat .num{font-size:clamp(2.2rem,5vw,3.2rem);font-weight:700;color:var(--terra-soft);line-height:1}
        .agit-stat .lbl{font-size:.92rem;color:rgba(255,255,255,.7);margin-top:.5rem;max-width:200px}

        /* ════════════════ SOLUCIÓN ════════════════ */
        .solution-section{background:var(--sand)}
        .sol-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;margin-top:3rem}
        .sol-card{
            background:#fff;border-radius:var(--r-lg);padding:2rem;border:1px solid var(--line);
            transition:transform .2s,box-shadow .2s;
        }
        .sol-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
        .sol-ico{
            width:48px;height:48px;border-radius:12px;display:grid;place-items:center;
            font-size:1.4rem;margin-bottom:1.1rem;background:var(--violet-soft);
        }
        .sol-card:nth-child(even) .sol-ico{background:var(--terra-tint)}
        .sol-card h3{font-size:1.18rem;margin-bottom:.6rem}
        .sol-card p{font-size:.95rem;color:var(--slate)}

        /* ════════════════ CÓMO FUNCIONA (pasos) ════════════════ */
        .steps-section{background:var(--cream)}
        .steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;margin-top:3rem}
        .step-card{background:#fff;border:1px solid var(--line);border-radius:var(--r-lg);padding:2rem 1.8rem;position:relative}
        .step-num{
            width:42px;height:42px;border-radius:12px;background:var(--violet);color:#fff;
            font-weight:700;font-size:1.1rem;display:grid;place-items:center;margin-bottom:1.1rem;
        }
        .step-card h3{font-size:1.12rem;margin-bottom:.6rem}
        .step-card p{font-size:.95rem;color:var(--slate)}
        .step-time{display:inline-block;margin-top:1rem;font-size:.82rem;font-weight:600;color:var(--terra)}

        /* ════════════════ PRECIOS (2 tiers) ════════════════ */
        .pricing-section{background:var(--sand-warm)}
        .pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.8rem;max-width:920px;margin:3rem auto 0;align-items:stretch}
        .price-card{
            background:#fff;border:1.5px solid var(--line);border-radius:var(--r-xl);
            padding:2.4rem 2.2rem;display:flex;flex-direction:column;position:relative;transition:transform .2s,box-shadow .2s;
        }
        .price-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg)}
        .price-card.featured{border-color:var(--violet);box-shadow:var(--shadow-violet)}
        .price-flag{
            position:absolute;top:-14px;left:50%;transform:translateX(-50%);
            background:var(--violet);color:#fff;font-size:.74rem;font-weight:600;
            padding:.35rem .9rem;border-radius:100px;letter-spacing:.04em;
        }
        .price-eyebrow{font-size:.8rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--terra);margin-bottom:.5rem}
        .price-name{font-size:1.7rem;font-weight:700;margin-bottom:.5rem}
        .price-desc{font-size:.95rem;color:var(--slate);margin-bottom:1.6rem;min-height:48px}
        .price-amount{display:flex;align-items:baseline;gap:.4rem;margin-bottom:.3rem}
        .price-amount .amt{font-size:2.6rem;font-weight:700;letter-spacing:-.03em}
        .price-amount .per{font-size:.95rem;color:var(--slate)}
        .price-note{font-size:.85rem;color:var(--mute);margin-bottom:1.8rem}
        .price-feats{list-style:none;margin-bottom:2rem;display:flex;flex-direction:column;gap:.75rem;flex:1}
        .price-feats li{display:flex;align-items:flex-start;gap:.6rem;font-size:.94rem;color:var(--ink-soft)}
        .price-feats .ck{color:var(--violet);font-weight:700;flex:0 0 auto}
        .price-card.featured .price-feats .ck{color:var(--terra)}
        .price-card .btn-terra,.price-card .btn-primary{width:100%}

        /* ════════════════ AGENDAR LLAMADA ════════════════ */
        .book-section{background:var(--cream)}
        .book-wrap{
            display:grid;grid-template-columns:1fr 1fr;gap:clamp(2rem,5vw,3.5rem);
            background:#fff;border:1px solid var(--line);border-radius:var(--r-xl);
            overflow:hidden;box-shadow:var(--shadow-lg);max-width:var(--maxw);margin:0 auto;
        }
        .book-left{
            padding:clamp(2.2rem,4vw,3.2rem);
            background:linear-gradient(160deg,var(--terra),var(--terra-dark));color:#fff;
            display:flex;flex-direction:column;justify-content:center;
        }
        .book-left .eyebrow{color:rgba(255,255,255,.8)}
        .book-left h2{font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:1.2rem}
        .book-left h2 em{color:#fff;text-decoration:underline;text-decoration-color:rgba(255,255,255,.5);text-underline-offset:5px}
        .book-left p{font-size:1.02rem;opacity:.95;margin-bottom:1.8rem}
        .book-bullets{list-style:none;display:flex;flex-direction:column;gap:.9rem}
        .book-bullets li{display:flex;align-items:flex-start;gap:.7rem;font-size:.96rem}
        .book-bullets .bk-ico{flex:0 0 auto}
        .book-right{padding:clamp(2.2rem,4vw,3rem)}
        .book-right h3{font-size:1.4rem;margin-bottom:.4rem}
        .book-right .sub{font-size:.95rem;color:var(--slate);margin-bottom:1.8rem}
        .field{margin-bottom:1.1rem}
        .field label{display:block;font-size:.85rem;font-weight:600;color:var(--ink-soft);margin-bottom:.4rem}
        .field input,.field select,.field textarea{
            width:100%;padding:.8rem 1rem;border:1.5px solid var(--line);border-radius:var(--r);
            font-family:inherit;font-size:.95rem;color:var(--ink);background:var(--cream);transition:border-color .15s,box-shadow .15s;
        }
        .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--violet);box-shadow:0 0 0 3px var(--violet-soft);background:#fff}
        .field textarea{resize:vertical;min-height:80px}
        .field-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .form-foot{display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--mute);margin-top:1rem;justify-content:center}
        #bookBtn{width:100%;margin-top:.4rem}
        .form-error{display:none;background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;padding:.7rem 1rem;border-radius:var(--r);font-size:.88rem;margin-bottom:1rem}
        .book-success{display:none;text-align:center;padding:2rem 0}
        .book-success .bs-ico{font-size:3rem;margin-bottom:1rem}
        .book-success h3{font-size:1.5rem;margin-bottom:.6rem}
        .book-success p{color:var(--slate)}

        /* ════════════════ FINAL CTA ════════════════ */
        .final-cta{
            background:
                    radial-gradient(800px 400px at 50% 0,var(--violet-soft),transparent 60%),
                    var(--sand);
            text-align:center;
        }
        .final-cta h2{font-size:clamp(1.9rem,4vw,2.8rem);max-width:720px;margin:0 auto 1rem}
        .final-cta p{font-size:1.1rem;color:var(--slate);max-width:560px;margin:0 auto 2.2rem}
        .final-cta .btn-terra{font-size:1.05rem;padding:1rem 2rem}

        /* ════════════════ FOOTER ════════════════ */
        footer{
            background:var(--ink);color:rgba(255,255,255,.65);
            padding:2.5rem clamp(1.25rem,4vw,2.75rem);
            display:flex;justify-content:space-between;align-items:center;gap:1.5rem;flex-wrap:wrap;font-size:.88rem;
        }
        .footer-logo{font-size:1.3rem;font-weight:700;color:#fff;letter-spacing:-.03em}
        .footer-links{display:flex;gap:1.6rem}
        .footer-links a{transition:color .15s}
        .footer-links a:hover{color:#fff}

        /* ════════════════ WHATSAPP FLOAT ════════════════ */
        .wa-float{
            position:fixed;right:22px;bottom:22px;width:56px;height:56px;border-radius:50%;
            background:var(--wa-green);display:grid;place-items:center;z-index:300;
            box-shadow:0 8px 24px rgba(37,211,102,.4);transition:transform .15s;
        }
        .wa-float:hover{transform:scale(1.08)}
        .wa-float svg{width:30px;height:30px;fill:#fff}

        /* ════════════════ REVEAL ════════════════ */
        .reveal{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
        .reveal.visible{opacity:1;transform:none}

        /* ════════════════ RESPONSIVE ════════════════ */
        @media(max-width:860px){
            .nav-links{display:none}
            .hero-inner{grid-template-columns:1fr;gap:3rem}
            .hero-visual{max-width:420px;margin:0 auto;width:100%}
            .book-wrap{grid-template-columns:1fr}
            .chip-1{left:0}.chip-2{right:0}
        }
        @media(max-width:520px){
            .field-row{grid-template-columns:1fr}
            .proof-inner{gap:1.2rem}
        }
    </style>
</head>
<body>

<!-- ═══════════════ NAV ═══════════════ -->
<nav>
    <a href="/" class="nav-logo">Tentii</a>
    <div class="nav-links">
        <a href="#problema">El cuello de botella</a>
        <a href="#solucion">Qué hace Tentii</a>
        <a href="#como">Cómo empieza</a>
        <a href="#precios">Planes</a>
    </div>
    <div class="nav-actions">
        <a href="/login" class="btn-login">Iniciar sesión</a>
        <a href="#agendar" class="btn-primary">Agendar demo <span class="arr">→</span></a>
    </div>
</nav>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero">
    <div class="hero-inner">
        <div class="reveal">
            <div class="hero-badge">
                <span class="hero-badge-pill">Para operadores</span>
                Hecho con operadores de tours, no para ellos
            </div>

            <h1>Tus salidas se<br>llenan solas.<br>Tú solo <em>opera.</em></h1>

            <p class="hero-sub">Tentii responde, cotiza y confirma cada reserva por WhatsApp mientras tú coordinas guías, transporte y cupos desde un solo panel. La logística deja de vivir en tu cabeza.</p>

            <div class="hero-btns">
                <a href="#agendar" class="btn-primary">Agendar una demo <span class="arr">→</span></a>
                <a href="#solucion" class="btn-secondary">Ver cómo funciona</a>
            </div>

            <div class="hero-trust">
                <div class="trust-dots"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
                <span><strong>Demo en 20 minutos</strong> · Sin compromiso · Te mostramos tu propio caso</span>
            </div>
        </div>

        <div class="hero-visual reveal">
            <div class="float-chip chip-1">
                <div class="float-chip-icon">⚡</div>
                <div><strong>3 seg</strong><span>en responder al viajero</span></div>
            </div>

            <!-- ░░░ SLOT PARA IMAGEN DISEÑADA ░░░
                 Reemplaza el placeholder por:
                 <img src="ruta/a/tu-imagen.jpg" alt="...">
                 Mood del brief: lujo + naturaleza + mid-outdoors. Sin mochileros. -->
            <div class="hero-image-slot">
                <!-- <img src="/assets/hero-tours.jpg" alt="Operador de tours coordinando una salida"> -->
                <div class="hero-image-ph">
                    <span class="ph-ico">🏞️</span>
                    <span class="ph-t">Imagen del hero (a diseñar)</span>
                    <span class="ph-s">Lujo + naturaleza + mid-outdoors. Una salida de tour real, no banco de imágenes.</span>
                </div>
                <div class="hero-image-caption">
                    🗓️ <span>Salida del sábado · <strong>14/16 cupos vendidos</strong></span>
                </div>
            </div>

            <div class="float-chip chip-2">
                <div class="float-chip-icon">🚐</div>
                <div><strong>0 dobles</strong><span>cupos sin sobreventa</span></div>
            </div>
            <div class="float-chip chip-3">
                <div class="float-chip-icon">📈</div>
                <div><strong>+41%</strong><span>ocupación por salida</span></div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ PROOF BAR ═══════════════ -->
<div class="proof-bar">
    <div class="proof-inner">
        <span><strong>+120</strong> operadores ya organizan sus salidas con Tentii</span>
        <span><strong>9 de cada 10</strong> consultas respondidas en menos de 1 minuto</span>
        <span><strong>0</strong> reservas perdidas por "no vi el mensaje a tiempo"</span>
    </div>
</div>

<!-- ═══════════════ PROBLEMA ═══════════════ -->
<section class="problem-section" id="problema">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">El cuello de botella</div>
            <h2 class="section-h">No te falta demanda.<br>Te falta una <em>persona más</em> que no existe.</h2>
            <p class="section-p">El viajero pregunta a las 11 de la noche. El guía cancela el viernes. El cupo se vendió dos veces. Y todo eso vive en tu WhatsApp, tu cabeza y cinco hojas de Excel.</p>
        </div>

        <div class="problem-grid">
            <div class="problem-card reveal">
                <div class="pc-ico">📲</div>
                <h3>Consultas que se enfrían</h3>
                <p>El viajero escribe, tú estás en ruta y para cuando respondes ya reservó con el operador que contestó primero.</p>
            </div>
            <div class="problem-card reveal">
                <div class="pc-ico">🧮</div>
                <h3>Cupos en la cabeza</h3>
                <p>Cuántos van en la salida del sábado, quién pagó, quién es "casi seguro". Un error y vendes un asiento que no existe.</p>
            </div>
            <div class="problem-card reveal">
                <div class="pc-ico">🤹</div>
                <h3>Logística suelta</h3>
                <p>Guía, transporte, punto de encuentro, lista de pasajeros. Hoy lo armas a mano cada vez, contra reloj.</p>
            </div>
            <div class="problem-card reveal">
                <div class="pc-ico">🌙</div>
                <h3>Tú nunca desconectas</h3>
                <p>Si no respondes tú, no responde nadie. Tu negocio depende de que estés siempre pegado al teléfono.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ AGITACIÓN ═══════════════ -->
<section class="agit-section">
    <div class="agit-inner reveal">
        <div class="eyebrow">Lo que de verdad cuesta</div>
        <h2>Cada consulta sin responder<br>es una salida que sale <em>a medio llenar.</em></h2>
        <p style="font-size:1.1rem;opacity:.85;max-width:640px;margin:0 auto">Y una salida a medio llenar no se recupera. El guía, el bus y el día ya los pagaste igual. La diferencia entre rentable y "apenas la saco" son los asientos que se quedaron vacíos por minutos de demora.</p>

        <div class="agit-stat-row">
            <div class="agit-stat">
                <div class="num">68%</div>
                <div class="lbl">de los viajeros reserva con quien responde primero</div>
            </div>
            <div class="agit-stat">
                <div class="num">5 h</div>
                <div class="lbl">al día respondiendo lo mismo por WhatsApp</div>
            </div>
            <div class="agit-stat">
                <div class="num">1 de 3</div>
                <div class="lbl">consultas llega fuera de tu horario de atención</div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ SOLUCIÓN ═══════════════ -->
<section class="solution-section" id="solucion">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Qué hace Tentii</div>
            <h2 class="section-h">Un asistente que vende tus tours<br>y un panel que <em>arma tu logística.</em></h2>
            <p class="section-p">Dos dolores, una sola plataforma: el canal de comunicación y la operación, conectados de punta a punta.</p>
        </div>

        <div class="sol-grid">
            <div class="sol-card reveal">
                <div class="sol-ico">💬</div>
                <h3>Responde y cotiza por ti</h3>
                <p>La IA contesta en WhatsApp, Instagram y web con el tono de tu marca: precios, fechas, qué incluye y qué llevar. 24/7, también de madrugada.</p>
            </div>
            <div class="sol-card reveal">
                <div class="sol-ico">🗓️</div>
                <h3>Controla cupos en vivo</h3>
                <p>Cada salida con su límite real. Cuando se llena, se cierra sola. Nunca más vendes dos veces el mismo asiento.</p>
            </div>
            <div class="sol-card reveal">
                <div class="sol-ico">🧭</div>
                <h3>Arma la logística sola</h3>
                <p>Asigna guía y transporte, genera la lista de pasajeros y manda el punto de encuentro a todos. Sin Excel, sin grupos de WhatsApp.</p>
            </div>
            <div class="sol-card reveal">
                <div class="sol-ico">💳</div>
                <h3>Cobra y confirma</h3>
                <p>Link de pago, anticipo o total, comprobante automático. El cupo solo se aparta cuando el viajero paga.</p>
            </div>
            <div class="sol-card reveal">
                <div class="sol-ico">🔔</div>
                <h3>Recuerda por ti</h3>
                <p>Recordatorio el día antes, qué llevar, cambios de hora. Menos no-shows, menos llamadas de "¿a qué hora era?".</p>
            </div>
            <div class="sol-card reveal">
                <div class="sol-ico">📊</div>
                <h3>Te muestra el negocio</h3>
                <p>Ocupación por salida, qué tour pide más gente, de dónde llegan tus reservas. Decisiones con datos, no con corazonadas.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ CÓMO EMPIEZA ═══════════════ -->
<section class="steps-section" id="como">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Cómo empieza</div>
            <h2 class="section-h">No cambias cómo operas.<br>Cambias <em>cuánto te cuesta operar.</em></h2>
            <p class="section-p">Sin migraciones eternas, sin cambiar tu número, sin un curso de tres semanas. Lo montamos contigo.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card reveal">
                <div class="step-num">1</div>
                <h3>Agendas una demo</h3>
                <p>20 minutos. Nos cuentas tus tours, tus cupos y cómo atiendes hoy. Te mostramos Tentii con tu propio caso, no con uno de ejemplo.</p>
                <span class="step-time">⏱ 20 minutos</span>
            </div>
            <div class="step-card reveal">
                <div class="step-num">2</div>
                <h3>Lo montamos contigo</h3>
                <p>Cargamos tus salidas, tarifas y cupos. Conectamos tu WhatsApp y entrenamos la IA con el tono de tu marca. Onboarding humano, en español.</p>
                <span class="step-time">⏱ Una semana</span>
            </div>
            <div class="step-card reveal">
                <div class="step-num">3</div>
                <h3>Empiezas a llenar salidas</h3>
                <p>Tentii responde y vende, tú supervisas desde el panel. La logística se arma sola. Tú vuelves a tener tu tiempo.</p>
                <span class="step-time">⏱ Desde el día uno</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ PRECIOS (2 tiers) ═══════════════ -->
<section class="pricing-section" id="precios">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Planes</div>
            <h2 class="section-h">Elige cómo quieres <em>crecer.</em></h2>
            <p class="section-p">Empieza con lo esencial o automatiza toda tu operación. Sin permanencias eternas: si no te llena salidas, no te sirve.</p>
        </div>

        <div class="pricing-grid">
            <!-- TIER 1 -->
            <div class="price-card reveal">
                <div class="price-eyebrow">Operador</div>
                <div class="price-name">Ruta</div>
                <p class="price-desc">Para el operador que quiere dejar de vivir pegado al WhatsApp y ordenar sus cupos.</p>
                <div class="price-amount"><span class="amt">$249.000</span><span class="per">/ mes</span></div>
                <p class="price-note">COP · hasta 3 tours activos · facturación mensual</p>
                <ul class="price-feats">
                    <li><span class="ck">✓</span> IA que responde y cotiza en WhatsApp</li>
                    <li><span class="ck">✓</span> Control de cupos en vivo por salida</li>
                    <li><span class="ck">✓</span> Link de pago y confirmación automática</li>
                    <li><span class="ck">✓</span> Lista de pasajeros y recordatorios</li>
                    <li><span class="ck">✓</span> Panel con ocupación y reportes básicos</li>
                    <li><span class="ck">✓</span> Soporte por WhatsApp, en español</li>
                </ul>
                <a href="#agendar" class="btn-secondary" style="justify-content:center;width:100%">Agendar demo</a>
            </div>

            <!-- TIER 2 — destacado -->
            <div class="price-card featured reveal">
                <span class="price-flag">El que eligen los que más venden</span>
                <div class="price-eyebrow">Operación completa</div>
                <div class="price-name">Expedición</div>
                <p class="price-desc">Para operadores con varios tours, guías y transporte que quieren que todo corra solo.</p>
                <div class="price-amount"><span class="amt">$549.000</span><span class="per">/ mes</span></div>
                <p class="price-note">COP · tours ilimitados · acompañamiento dedicado</p>
                <ul class="price-feats">
                    <li><span class="ck">✓</span> Todo lo de Ruta, sin límite de tours</li>
                    <li><span class="ck">✓</span> Asignación de guías y transporte</li>
                    <li><span class="ck">✓</span> Multicanal: WhatsApp, Instagram y web</li>
                    <li><span class="ck">✓</span> IA entrenada a fondo con tu marca</li>
                    <li><span class="ck">✓</span> Reportes avanzados y métricas por canal</li>
                    <li><span class="ck">✓</span> Integraciones a la medida de tu operación</li>
                    <li><span class="ck">✓</span> Onboarding y acompañamiento dedicado</li>
                </ul>
                <a href="#agendar" class="btn-terra">Agendar demo →</a>
            </div>
        </div>
        <p style="text-align:center;color:var(--mute);font-size:.9rem;margin-top:2rem" class="reveal">¿Operas a gran escala o con franquicias? <a href="#agendar" style="color:var(--violet);font-weight:600">Hablemos de un plan a tu medida →</a></p>
    </div>
</section>

<!-- ═══════════════ AGENDAR LLAMADA ═══════════════ -->
<section class="book-section" id="agendar">
    <div class="container">
        <div class="book-wrap reveal">
            <!-- Izquierda: refuerzo Cialdini -->
            <div class="book-left">
                <div class="eyebrow">Agenda una llamada</div>
                <h2>20 minutos contigo.<br>Tu operación corriendo <em>en una semana.</em></h2>
                <p>No es una llamada de ventas con diapositivas. Es una sesión donde montamos tu caso real y tú decides si te sirve.</p>
                <ul class="book-bullets">
                    <li><span class="bk-ico">🎯</span> <span>Te mostramos Tentii con <strong>tus tours</strong>, no con un demo genérico.</span></li>
                    <li><span class="bk-ico">⚡</span> <span>Sales con un <strong>plan claro</strong> de cómo quedaría tu operación.</span></li>
                    <li><span class="bk-ico">🤝</span> <span>Sin compromiso. Si no te llena salidas, te lo decimos nosotros.</span></li>
                    <li><span class="bk-ico">🗓️</span> <span>Cupos limitados de onboarding al mes — entran los que agendan primero.</span></li>
                </ul>
            </div>

            <!-- Derecha: formulario funcional -->
            <div class="book-right">
                <div id="bookContent">
                    <h3>Agenda tu demo</h3>
                    <p class="sub">Te contactamos para coordinar el horario que mejor te quede.</p>

                    <div class="form-error" id="formError"></div>

                    <form id="bookForm" novalidate>
                        <div class="field">
                            <label for="company">Nombre de tu operadora *</label>
                            <input type="text" id="company" name="company" placeholder="Ej: Andes Vivos Tours">
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label for="name">Tu nombre *</label>
                                <input type="text" id="name" name="name" placeholder="Ej: Ana Restrepo">
                            </div>
                            <div class="field">
                                <label for="phone">WhatsApp *</label>
                                <input type="tel" id="phone" name="phone" placeholder="Ej: +57 300 000 0000">
                            </div>
                        </div>
                        <div class="field">
                            <label for="email">Correo *</label>
                            <input type="email" id="email" name="email" placeholder="hola@tuoperadora.com">
                        </div>
                        <div class="field-row">
                            <div class="field">
                                <label for="city">Ciudad / región *</label>
                                <input type="text" id="city" name="city" placeholder="Ej: Santander">
                            </div>
                            <div class="field">
                                <label for="volume">Salidas al mes</label>
                                <select id="volume" name="volume">
                                    <option value="">Selecciona</option>
                                    <option>1 – 5</option>
                                    <option>6 – 15</option>
                                    <option>16 – 40</option>
                                    <option>Más de 40</option>
                                </select>
                            </div>
                        </div>
                        <div class="field">
                            <label for="message">¿Cuál es hoy tu mayor dolor de cabeza? (opcional)</label>
                            <textarea id="message" name="message" placeholder="Cuéntanos en una línea qué te gustaría resolver."></textarea>
                        </div>
                        <button type="submit" class="btn-terra" id="bookBtn">Agendar mi demo →</button>
                        <div class="form-foot">🔒 Tu información está segura y no será compartida.</div>
                    </form>
                </div>

                <div class="book-success" id="bookSuccess">
                    <div class="bs-ico">🎉</div>
                    <h3>¡Listo! Quedaste agendado</h3>
                    <p>Te escribimos por WhatsApp en las próximas horas para coordinar el horario. Prepara tus tours: vamos a montarlos juntos.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ FINAL CTA ═══════════════ -->
<section class="final-cta">
    <div class="container reveal">
        <h2>El próximo viajero que te escriba<br>no debería esperar a que veas el mensaje.</h2>
        <p>Deja que Tentii responda, venda y arme la logística. Tú vuelve a hacer lo que amas: operar.</p>
        <a href="#agendar" class="btn-terra">Agendar mi demo →</a>
    </div>
</section>

<!-- ═══════════════ FOOTER ═══════════════ -->
<footer>
    <span class="footer-logo">Tentii</span>
    <div class="footer-links">
        <a href="#solucion">Qué hace Tentii</a>
        <a href="#precios">Planes</a>
        <a href="#agendar">Agendar demo</a>
        <a href="/login">Iniciar sesión</a>
    </div>
    <span>&copy; <?= date('Y') ?> Tentii · Tus salidas se llenan solas. Tú solo opera.</span>
</footer>

<!-- WhatsApp flotante -->
<a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="wa-float" aria-label="Hablar con Tentii por WhatsApp">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<script>
    (function(){
        /* Reveal on scroll */
        var revEls = document.querySelectorAll('.reveal');
        var obs = new IntersectionObserver(function(entries){
            entries.forEach(function(e,i){
                if(e.isIntersecting){
                    setTimeout(function(){ e.target.classList.add('visible'); }, i*60);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold:0.08 });
        revEls.forEach(function(el){ obs.observe(el); });

        /* Formulario de agenda */
        var form = document.getElementById('bookForm');
        if(!form) return;

        function showError(msg){
            var el = document.getElementById('formError');
            el.textContent = msg; el.style.display='block';
            el.scrollIntoView({behavior:'smooth',block:'nearest'});
        }
        function hideError(){ document.getElementById('formError').style.display='none'; }

        form.addEventListener('submit', function(e){
            e.preventDefault();
            hideError();
            var company = form.company.value.trim();
            var name    = form.name.value.trim();
            var phone   = form.phone.value.trim();
            var email   = form.email.value.trim();
            var city    = form.city.value.trim();
            var btn     = document.getElementById('bookBtn');

            if(!company) return showError('El nombre de tu operadora es requerido.');
            if(!name)    return showError('Tu nombre es requerido.');
            if(!phone)   return showError('Tu WhatsApp es requerido.');
            if(!email)   return showError('El correo es requerido.');
            if(!/^\S+@\S+\.\S+$/.test(email)) return showError('Ingresa un correo válido.');
            if(!city)    return showError('Tu ciudad o región es requerida.');

            btn.disabled = true; btn.textContent = 'Agendando...';

            fetch('/agendar-demo', {
                method:'POST',
                body:new FormData(form),
                headers:{ 'X-Requested-With':'XMLHttpRequest' }
            })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if(res.success){
                        document.getElementById('bookContent').style.display='none';
                        document.getElementById('bookSuccess').style.display='block';
                    } else {
                        showError(res.message || 'No pudimos agendar. Intenta de nuevo.');
                        btn.disabled=false; btn.textContent='Agendar mi demo →';
                    }
                })
                .catch(function(){
                    showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
                    btn.disabled=false; btn.textContent='Agendar mi demo →';
                });
        });
    })();
</script>

</body>
</html>