<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tentii — La plataforma que convierte cada conversación en una reserva. WhatsApp, web y redes integrados con IA, CRM y reservas para hoteles boutique, lodges y operadores turísticos.">
    <meta name="keywords" content="reservas hoteles WhatsApp, CRM turismo, IA conversacional hoteles, plataforma reservas boutique, channel manager turismo">
    <meta property="og:title" content="Tentii — Tu experiencia se reserva desde el primer mensaje">
    <meta property="og:description" content="La plataforma conversacional para hoteles boutique, lodges y operadores. Convierte cada WhatsApp, DM y email en una reserva confirmada.">
    <meta property="og:type" content="website">
    <title>Tentii — Tu experiencia se reserva desde el primer mensaje</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ════════════════════════════════════════════════════════
           RESET & TOKENS DE MARCA TENTII
        ════════════════════════════════════════════════════════ */
        *{margin:0;padding:0;box-sizing:border-box}

        :root{
            /* Paleta cálida — del brief */
            --terra        : #D96C4A;     /* Primario Tentii */
            --terra-dark   : #B8512F;
            --terra-soft   : #F5D4C6;
            --terra-tint   : #FBEAE0;

            /* Contraste tech — del brief */
            --violet       : #7B61FF;     /* CTA / digital */
            --violet-dark  : #5A3FE0;
            --violet-soft  : #E5DEFF;

            /* Fondos cálidos */
            --sand         : #F4EFEA;     /* Arena del brief */
            --sand-warm    : #EFE6DC;
            --cream        : #FBF7F2;

            /* Neutros */
            --ink          : #1A1410;     /* Tinta cálida, no negro frío */
            --ink-soft     : #3D332C;
            --slate        : #6B5D54;
            --mute         : #9B8E84;
            --line         : #E7DED3;
            --white        : #FFFFFF;

            /* WhatsApp (solo para el mockup auténtico) */
            --wa-green     : #25D366;
            --wa-bubble    : #D9FDD3;
            --wa-header    : #075E54;

            /* Tipografía */
            --serif        : 'Fraunces', Georgia, serif;
            --sans         : 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            /* Radios */
            --r-sm         : 8px;
            --r            : 14px;
            --r-lg         : 22px;
            --r-xl         : 32px;

            /* Sombras cálidas */
            --shadow-sm    : 0 1px 2px rgba(26,20,16,.04), 0 1px 3px rgba(26,20,16,.06);
            --shadow       : 0 4px 16px rgba(26,20,16,.06), 0 2px 6px rgba(26,20,16,.04);
            --shadow-lg    : 0 18px 50px rgba(26,20,16,.10), 0 8px 20px rgba(26,20,16,.06);
            --shadow-terra : 0 8px 24px rgba(217,108,74,.25);
            --shadow-violet: 0 8px 24px rgba(123,97,255,.30);
        }

        html{scroll-behavior:smooth}

        body{
            background    : var(--cream);
            color         : var(--ink);
            font-family   : var(--sans);
            font-size     : 16px;
            line-height   : 1.65;
            overflow-x    : hidden;
            -webkit-font-smoothing: antialiased;
        }

        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}
        button{font-family:inherit}

        /* ════════════════════════════════════════════════════════
           NAVEGACIÓN
        ════════════════════════════════════════════════════════ */
        nav{
            background      : rgba(251,247,242,.92);
            border-bottom   : 1px solid var(--line);
            padding         : 0 clamp(1.25rem,4vw,2.75rem);
            display         : flex;
            justify-content : space-between;
            align-items     : center;
            height          : 72px;
            position        : sticky;
            top             : 0;
            z-index         : 200;
            backdrop-filter : blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .nav-logo{
            height : 34px;
            width  : auto;
            display: block;
        }

        .nav-links{
            display     : flex;
            align-items : center;
            gap         : 2.25rem;
        }

        .nav-links a{
            font-size   : .88rem;
            color       : var(--ink-soft);
            font-weight : 500;
            transition  : color .15s;
        }

        .nav-links a:hover{color:var(--terra)}

        .nav-actions{
            display     : flex;
            align-items : center;
            gap         : .75rem;
        }

        .btn-login{
            font-size    : .88rem;
            color        : var(--ink-soft);
            font-weight  : 500;
            padding      : .55rem 1.1rem;
            border       : 1.5px solid var(--line);
            border-radius: var(--r);
            transition   : all .18s;
            background   : transparent;
        }

        .btn-login:hover{
            border-color: var(--terra);
            color       : var(--terra);
        }

        .btn-nav-cta{
            font-size     : .88rem;
            background    : var(--ink);
            color         : var(--white);
            font-weight   : 600;
            padding       : .6rem 1.25rem;
            border-radius : var(--r);
            transition    : all .18s;
            border        : none;
            cursor        : pointer;
            display       : inline-block;
        }

        .btn-nav-cta:hover{
            background : var(--terra);
            transform  : translateY(-1px);
        }

        /* ════════════════════════════════════════════════════════
           HERO — StoryBrand: héroe (hotelero) + promesa de una línea
        ════════════════════════════════════════════════════════ */
        .hero{
            padding    : clamp(3.5rem,7vw,6rem) clamp(1.25rem,4vw,2.75rem) clamp(3rem,6vw,5rem);
            background : var(--cream);
            position   : relative;
            overflow   : hidden;
        }

        .hero::before{
            content   : '';
            position  : absolute;
            top       : -10%;
            right     : -15%;
            width     : 700px;
            height    : 700px;
            background: radial-gradient(circle, var(--terra-tint) 0%, transparent 65%);
            pointer-events: none;
            z-index   : 0;
        }

        .hero::after{
            content   : '';
            position  : absolute;
            bottom    : -20%;
            left      : -10%;
            width     : 500px;
            height    : 500px;
            background: radial-gradient(circle, var(--violet-soft) 0%, transparent 70%);
            pointer-events: none;
            z-index   : 0;
            opacity   : .55;
        }

        .hero-inner{
            max-width : 1180px;
            margin    : 0 auto;
            position  : relative;
            z-index   : 1;
            display   : grid;
            grid-template-columns: 1.05fr 1fr;
            gap       : clamp(2rem,5vw,4.5rem);
            align-items: center;
        }

        .hero-badge{
            display       : inline-flex;
            align-items   : center;
            gap           : .55rem;
            background    : var(--white);
            color         : var(--terra-dark);
            font-size     : .76rem;
            font-weight   : 600;
            letter-spacing: .02em;
            padding       : .4rem 1rem .4rem .5rem;
            border-radius : 99px;
            margin-bottom : 1.5rem;
            border        : 1px solid var(--line);
            box-shadow    : var(--shadow-sm);
        }

        .hero-badge-pill{
            background    : var(--terra);
            color         : var(--white);
            font-size     : .68rem;
            font-weight   : 700;
            padding       : .15rem .55rem;
            border-radius : 99px;
            letter-spacing: .04em;
        }

        .hero h1{
            font-family    : var(--serif);
            font-size      : clamp(2.3rem, 5.2vw, 4.1rem);
            font-weight    : 600;
            color          : var(--ink);
            line-height    : 1.02;
            margin-bottom  : 1.5rem;
            letter-spacing : -0.025em;
        }

        .hero h1 em{
            font-style : italic;
            color      : var(--terra);
            font-weight: 500;
        }

        .hero-sub{
            font-size     : 1.12rem;
            color         : var(--slate);
            max-width     : 540px;
            margin        : 0 0 2.25rem;
            font-weight   : 400;
            line-height   : 1.6;
        }

        .hero-btns{
            display    : flex;
            gap        : .85rem;
            flex-wrap  : wrap;
            margin-bottom: 1.5rem;
        }

        .btn-primary{
            background    : var(--terra);
            color         : var(--white);
            padding       : 1rem 1.85rem;
            border-radius : var(--r);
            font-weight   : 600;
            font-size     : .95rem;
            border        : none;
            cursor        : pointer;
            transition    : all .22s;
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
            box-shadow    : var(--shadow-terra);
        }

        .btn-primary:hover{
            background : var(--terra-dark);
            transform  : translateY(-2px);
            box-shadow : 0 12px 28px rgba(217,108,74,.35);
        }

        .btn-primary .arr{transition:transform .2s}
        .btn-primary:hover .arr{transform:translateX(3px)}

        .btn-secondary{
            background    : transparent;
            color         : var(--ink);
            padding       : 1rem 1.5rem;
            border-radius : var(--r);
            font-weight   : 600;
            font-size     : .95rem;
            border        : 1.5px solid var(--ink);
            cursor        : pointer;
            transition    : all .22s;
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
        }

        .btn-secondary:hover{
            background  : var(--ink);
            color       : var(--white);
        }

        .hero-trust{
            display    : flex;
            align-items: center;
            gap        : .6rem;
            font-size  : .82rem;
            color      : var(--slate);
        }

        .hero-trust strong{color:var(--ink);font-weight:600}

        .trust-dots{
            display     : flex;
            align-items : center;
            gap         : .25rem;
        }

        .trust-dots .dot{
            width: 6px; height: 6px; border-radius: 50%; background: var(--terra);
        }

        /* Visual del hero — chat + chip flotantes */
        .hero-visual{
            position : relative;
            min-height: 520px;
            display  : flex;
            align-items: center;
            justify-content: center;
        }

        .hero-chat-card{
            background    : var(--white);
            border-radius : var(--r-xl);
            box-shadow    : var(--shadow-lg);
            border        : 1px solid var(--line);
            width         : 100%;
            max-width     : 420px;
            overflow      : hidden;
            position      : relative;
            z-index       : 2;
            transform     : rotate(-1.2deg);
        }

        /* chip orgánicos flotantes */
        .float-chip{
            position      : absolute;
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : .7rem 1rem;
            box-shadow    : var(--shadow);
            display       : flex;
            align-items   : center;
            gap           : .6rem;
            font-size     : .82rem;
            color         : var(--ink);
            z-index       : 3;
        }

        .float-chip-icon{
            width          : 30px;
            height         : 30px;
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .95rem;
            flex-shrink    : 0;
        }

        .float-chip strong{
            display    : block;
            font-weight: 700;
            color      : var(--terra);
            font-size  : .9rem;
            line-height: 1.1;
        }

        .float-chip span{
            font-size: .7rem;
            color    : var(--slate);
        }

        .chip-1{
            top: -1rem; left: -1.5rem;
            background : var(--terra);
            color      : var(--white);
            border-color: var(--terra);
            transform  : rotate(-5deg);
        }
        .chip-1 strong{color:var(--white)}
        .chip-1 span{color:rgba(255,255,255,.85)}
        .chip-1 .float-chip-icon{background:rgba(255,255,255,.2)}

        .chip-2{
            bottom: 1rem; right: -1.5rem;
            transform  : rotate(3deg);
        }
        .chip-2 .float-chip-icon{background:var(--violet-soft);color:var(--violet-dark)}

        .chip-3{
            top: 35%; right: -2rem;
            transform  : rotate(-2deg);
        }
        .chip-3 .float-chip-icon{background:var(--terra-tint);color:var(--terra-dark)}

        /* ════════════════════════════════════════════════════════
           STATS — autoridad sutil
        ════════════════════════════════════════════════════════ */
        .stats-band{
            background : var(--sand);
            border-top : 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            padding    : 2rem clamp(1.25rem,4vw,2.75rem);
        }

        .stats-inner{
            max-width: 1180px;
            margin   : 0 auto;
            display  : grid;
            grid-template-columns: repeat(4, 1fr);
            gap      : 1.5rem;
        }

        .stat{
            text-align: left;
            padding-left: 1.5rem;
            border-left: 2px solid var(--terra);
        }

        .stat-n{
            font-family    : var(--serif);
            font-size      : clamp(1.8rem, 3vw, 2.4rem);
            font-weight    : 600;
            color          : var(--ink);
            line-height    : 1;
            margin-bottom  : .35rem;
            letter-spacing : -0.02em;
        }

        .stat-n .pct{color:var(--terra);font-weight:500}

        .stat-l{
            font-size   : .78rem;
            color       : var(--slate);
            line-height : 1.4;
        }

        /* ════════════════════════════════════════════════════════
           COMMONS
        ════════════════════════════════════════════════════════ */
        section{padding:clamp(4rem,8vw,7rem) clamp(1.25rem,4vw,2.75rem)}

        .container{max-width:1180px;margin:0 auto}

        .container-narrow{max-width:780px;margin:0 auto}

        .eyebrow{
            display       : inline-block;
            font-size      : .72rem;
            font-weight    : 700;
            letter-spacing : .16em;
            text-transform : uppercase;
            color          : var(--terra);
            margin-bottom  : 1rem;
        }

        .eyebrow.violet{color:var(--violet-dark)}
        .eyebrow.light{color:var(--terra-soft)}

        .section-h{
            font-family    : var(--serif);
            font-size      : clamp(1.9rem, 3.6vw, 2.85rem);
            font-weight    : 600;
            color          : var(--ink);
            line-height    : 1.12;
            letter-spacing : -0.025em;
            margin-bottom  : 1rem;
        }

        .section-h em{font-style:italic;color:var(--terra)}

        .section-p{
            font-size   : 1.02rem;
            color       : var(--slate);
            max-width   : 580px;
            line-height : 1.7;
        }

        .section-head-center{text-align:center;margin:0 auto 3.5rem}
        .section-head-center .section-p{margin:0 auto}

        /* ════════════════════════════════════════════════════════
           SECCIÓN PROBLEMA — PAS (Problema)
        ════════════════════════════════════════════════════════ */
        .problem-section{
            background : var(--sand);
            position   : relative;
        }

        .problem-grid{
            display   : grid;
            grid-template-columns: 1fr 1fr;
            gap       : clamp(2rem,5vw,4rem);
            align-items: center;
        }

        .problem-quote{
            font-family : var(--serif);
            font-size   : clamp(1.5rem, 2.8vw, 2.15rem);
            line-height : 1.3;
            color       : var(--ink);
            font-weight : 500;
            font-style  : italic;
            padding-left: 1.5rem;
            border-left : 3px solid var(--terra);
            margin-bottom: 1.75rem;
        }

        .problem-quote .strike{
            color: var(--mute);
            text-decoration: line-through;
            text-decoration-color: var(--terra);
            font-style: normal;
        }

        .problem-body p{
            font-size: 1.02rem;
            color    : var(--slate);
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .problem-body p strong{color:var(--ink);font-weight:600}

        /* Lista de "leaks" — agitación */
        .leak-list{
            display       : flex;
            flex-direction: column;
            gap           : .85rem;
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 1.75rem;
            box-shadow    : var(--shadow);
        }

        .leak-list-title{
            font-family : var(--serif);
            font-size   : 1.1rem;
            font-weight : 600;
            color       : var(--ink);
            margin-bottom: .5rem;
            display     : flex;
            align-items : center;
            gap         : .6rem;
        }

        .leak-list-title .dot-red{
            width: 8px; height: 8px;
            background: var(--terra);
            border-radius: 50%;
            animation: pulse-red 1.8s infinite;
        }

        @keyframes pulse-red{
            0%,100%{opacity:1; transform:scale(1)}
            50%{opacity:.4; transform:scale(1.3)}
        }

        .leak-row{
            display    : flex;
            align-items: flex-start;
            gap        : .85rem;
            padding    : .65rem 0;
            border-top : 1px solid var(--line);
            font-size  : .9rem;
            color      : var(--ink-soft);
            line-height: 1.5;
        }

        .leak-row:first-of-type{border-top:none}

        .leak-icon{
            font-size : 1rem;
            flex-shrink: 0;
            margin-top: .1rem;
            opacity   : .85;
        }

        .leak-row .time{
            color    : var(--mute);
            font-size: .78rem;
            display  : block;
            margin-top: .2rem;
        }

        /* ════════════════════════════════════════════════════════
           AGITACIÓN — el costo invisible
        ════════════════════════════════════════════════════════ */
        .agit-section{
            background: var(--cream);
        }

        .agit-grid{
            display              : grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap                  : 1.25rem;
            margin-top           : 3rem;
        }

        .agit-card{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 1.75rem 1.5rem;
            transition    : all .25s;
            position      : relative;
            overflow      : hidden;
        }

        .agit-card::before{
            content   : '';
            position  : absolute;
            top       : 0; left: 0;
            width     : 100%; height: 3px;
            background: var(--terra);
            transform : scaleX(0);
            transform-origin: left;
            transition: transform .3s;
        }

        .agit-card:hover{
            transform   : translateY(-3px);
            box-shadow  : var(--shadow);
            border-color: transparent;
        }

        .agit-card:hover::before{transform:scaleX(1)}

        .agit-icon{
            width         : 44px;
            height        : 44px;
            background    : var(--terra-tint);
            border-radius : var(--r-sm);
            display       : flex;
            align-items   : center;
            justify-content: center;
            font-size     : 1.25rem;
            margin-bottom : 1rem;
        }

        .agit-card h3{
            font-family   : var(--serif);
            font-size     : 1.05rem;
            font-weight   : 600;
            color         : var(--ink);
            margin-bottom : .5rem;
            line-height   : 1.3;
        }

        .agit-card p{
            font-size  : .87rem;
            color      : var(--slate);
            line-height: 1.6;
        }

        .agit-card .cost{
            display      : inline-block;
            margin-top   : 1rem;
            font-size    : .72rem;
            font-weight  : 600;
            color        : var(--terra-dark);
            background   : var(--terra-tint);
            padding      : .25rem .65rem;
            border-radius: 99px;
        }

        /* ════════════════════════════════════════════════════════
           SOLUCIÓN — el "guía" StoryBrand
        ════════════════════════════════════════════════════════ */
        .solution-section{
            background : linear-gradient(180deg, var(--cream) 0%, var(--sand) 100%);
        }

        .solution-intro{
            text-align: center;
            max-width : 720px;
            margin    : 0 auto 3.5rem;
        }

        .solution-pull{
            font-family : var(--serif);
            font-size   : clamp(1.5rem, 2.8vw, 2.2rem);
            line-height : 1.3;
            color       : var(--ink);
            font-weight : 500;
            margin-top  : 1.25rem;
        }

        .solution-pull em{color:var(--terra);font-style:italic}

        /* Trio de pilares */
        .pillars{
            display              : grid;
            grid-template-columns: repeat(3, 1fr);
            gap                  : 1.5rem;
        }

        .pillar{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 2rem 1.75rem;
            text-align    : left;
        }

        .pillar-num{
            font-family : var(--serif);
            font-size   : 2.2rem;
            font-weight : 600;
            color       : var(--terra);
            line-height : 1;
            font-style  : italic;
            margin-bottom: .75rem;
        }

        .pillar h3{
            font-family  : var(--serif);
            font-size    : 1.25rem;
            font-weight  : 600;
            color        : var(--ink);
            margin-bottom: .65rem;
        }

        .pillar p{
            font-size  : .92rem;
            color      : var(--slate);
            line-height: 1.65;
            margin-bottom: 1.25rem;
        }

        .pillar-bullets{
            list-style    : none;
            display       : flex;
            flex-direction: column;
            gap           : .55rem;
        }

        .pillar-bullets li{
            font-size  : .85rem;
            color      : var(--ink-soft);
            display    : flex;
            align-items: flex-start;
            gap        : .55rem;
            line-height: 1.5;
        }

        .pillar-bullets li::before{
            content: '';
            width: 14px; height: 14px;
            background: var(--terra-tint);
            border: 1.5px solid var(--terra);
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: .15rem;
            position: relative;
        }

        /* ════════════════════════════════════════════════════════
           DEMO — chat protagonista
        ════════════════════════════════════════════════════════ */
        .demo-section{
            background : var(--ink);
            color      : var(--white);
            position   : relative;
            overflow   : hidden;
        }

        .demo-section::before{
            content   : '';
            position  : absolute;
            top       : 10%; right: -10%;
            width     : 500px; height: 500px;
            background: radial-gradient(circle, rgba(217,108,74,.18) 0%, transparent 65%);
            pointer-events: none;
        }

        .demo-section::after{
            content   : '';
            position  : absolute;
            bottom    : 5%; left: -10%;
            width     : 400px; height: 400px;
            background: radial-gradient(circle, rgba(123,97,255,.18) 0%, transparent 65%);
            pointer-events: none;
        }

        .demo-section .container{position:relative;z-index:1}

        .demo-section .eyebrow{color:var(--terra-soft)}
        .demo-section .section-h{color:var(--white)}
        .demo-section .section-h em{color:var(--terra-soft)}
        .demo-section .section-p{color:rgba(255,255,255,.7)}

        .demo-grid{
            display              : grid;
            grid-template-columns: 1.05fr 1fr;
            gap                  : clamp(2rem,5vw,4.5rem);
            align-items          : center;
            margin-top           : 3rem;
        }

        .demo-features{
            display       : flex;
            flex-direction: column;
            gap           : 1rem;
        }

        .demo-feat{
            display    : flex;
            align-items: flex-start;
            gap        : 1rem;
            padding    : 1.1rem 1.25rem;
            background : rgba(255,255,255,.04);
            border     : 1px solid rgba(255,255,255,.08);
            border-radius: var(--r);
            transition : all .2s;
        }

        .demo-feat:hover{
            background  : rgba(255,255,255,.07);
            border-color: rgba(217,108,74,.4);
        }

        .demo-feat-icon{
            width          : 40px;
            height         : 40px;
            border-radius  : var(--r-sm);
            background     : var(--terra);
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : 1.1rem;
            flex-shrink    : 0;
        }

        .demo-feat h4{
            font-family  : var(--serif);
            font-size    : 1rem;
            font-weight  : 600;
            color        : var(--white);
            margin-bottom: .25rem;
        }

        .demo-feat p{
            font-size  : .85rem;
            color      : rgba(255,255,255,.6);
            line-height: 1.55;
        }

        /* Chat mockup auténtico WhatsApp */
        .chat-wrap{
            display: flex;
            justify-content: center;
        }

        .chat-phone{
            background    : var(--white);
            border-radius : var(--r-lg);
            box-shadow    : 0 30px 60px rgba(0,0,0,.4);
            overflow      : hidden;
            width         : 100%;
            max-width     : 400px;
        }

        .chat-ph-header{
            background    : var(--wa-header);
            padding       : 1rem 1.25rem;
            display       : flex;
            align-items   : center;
            gap           : .85rem;
        }

        .chat-avatar{
            width          : 42px;
            height         : 42px;
            background     : var(--terra);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .82rem;
            font-weight    : 700;
            color          : var(--white);
            flex-shrink    : 0;
            font-family    : var(--serif);
        }

        .chat-name{
            font-size   : .95rem;
            font-weight : 600;
            color       : var(--white);
        }

        .chat-status{
            font-size   : .72rem;
            color       : #9FE1CB;
            display     : flex;
            align-items : center;
            gap         : .35rem;
        }

        .status-dot{
            width: 6px; height: 6px;
            background    : var(--wa-green);
            border-radius : 50%;
            animation     : pulse-green 2s infinite;
        }

        @keyframes pulse-green{
            0%,100%{opacity:1}
            50%{opacity:.4}
        }

        .chat-body{
            background    : #ECE5DD;
            padding       : 1rem;
            display       : flex;
            flex-direction: column;
            gap           : .55rem;
            height        : 460px;
            overflow-y    : auto;
            scroll-behavior: smooth;
        }

        .chat-body::-webkit-scrollbar{width:4px}
        .chat-body::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:4px}

        .msg-wrap{display:flex;flex-direction:column;animation:msgIn .4s ease}
        .msg-wrap.right{align-items:flex-end}
        .msg-wrap.left{align-items:flex-start}

        @keyframes msgIn{
            from{opacity:0;transform:translateY(8px)}
            to{opacity:1;transform:translateY(0)}
        }

        .msg-bubble{
            max-width     : 85%;
            padding       : .55rem .85rem;
            border-radius : 10px;
            font-size     : .82rem;
            line-height   : 1.5;
            box-shadow    : 0 1px 0 rgba(0,0,0,.05);
            word-wrap     : break-word;
        }

        .msg-bubble.in{
            background    : var(--white);
            color         : #111;
            border-radius : 0 10px 10px 10px;
        }

        .msg-bubble.out{
            background    : var(--wa-bubble);
            color         : #111;
            border-radius : 10px 10px 0 10px;
        }

        .msg-bubble strong{font-weight:600}

        .msg-meta{
            font-size  : .65rem;
            color      : #888;
            margin-top : .2rem;
            display    : flex;
            align-items: center;
            gap        : .25rem;
        }

        .msg-meta.out-t{justify-content:flex-end;color:#667781}

        .ticks{color:#53BDEB;font-size:.78rem}

        .chat-typing{
            display     : flex;
            align-items : center;
            gap         : .3rem;
            padding     : .55rem .85rem;
            background  : var(--white);
            border-radius: 0 10px 10px 10px;
            width       : 56px;
            align-self  : flex-start;
        }

        .typing-dot{
            width      : 6px;
            height     : 6px;
            background : #94A3B8;
            border-radius:50%;
            animation  : typing .9s infinite;
        }

        .typing-dot:nth-child(2){animation-delay:.2s}
        .typing-dot:nth-child(3){animation-delay:.4s}

        @keyframes typing{
            0%,100%{transform:translateY(0);opacity:.4}
            50%{transform:translateY(-3px);opacity:1}
        }

        .chat-tentii-badge{
            background    : var(--terra);
            color         : var(--white);
            font-size     : .72rem;
            font-weight   : 600;
            text-align    : center;
            padding       : .55rem 1rem;
            display       : flex;
            align-items   : center;
            justify-content: center;
            gap           : .4rem;
        }

        /* ════════════════════════════════════════════════════════
           PRODUCTOS — Tentii Studio vs Tentii Start
        ════════════════════════════════════════════════════════ */
        .products-section{
            background: var(--cream);
        }

        .products-grid{
            display              : grid;
            grid-template-columns: 1fr 1fr;
            gap                  : 1.5rem;
            margin-top           : 3.5rem;
        }

        .product-card{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-xl);
            padding       : 2.5rem 2.25rem;
            position      : relative;
            display       : flex;
            flex-direction: column;
        }

        .product-card.featured{
            background : linear-gradient(180deg, var(--ink) 0%, #0f0a08 100%);
            color      : var(--white);
            border     : none;
            box-shadow : var(--shadow-lg);
        }

        .product-card.featured .product-name,
        .product-card.featured h3{color:var(--white)}

        .product-card.featured .product-desc{color:rgba(255,255,255,.7)}

        .product-card.featured .product-includes li{color:rgba(255,255,255,.85)}

        .product-card.featured .product-includes li::before{background:var(--terra)}

        .product-tag{
            display       : inline-block;
            font-size     : .68rem;
            font-weight   : 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding       : .3rem .75rem;
            border-radius : 99px;
            margin-bottom : 1.25rem;
            align-self    : flex-start;
        }

        .product-tag.start{background:var(--terra-tint);color:var(--terra-dark)}
        .product-tag.studio{background:var(--violet);color:var(--white)}

        .product-name{
            font-family   : var(--serif);
            font-size     : 1.8rem;
            font-weight   : 600;
            color         : var(--ink);
            margin-bottom : .35rem;
            letter-spacing: -0.02em;
        }

        .product-name em{font-style:italic;color:var(--terra)}

        .product-tagline{
            font-size : .92rem;
            color     : var(--mute);
            margin-bottom: 1.5rem;
        }

        .product-card.featured .product-tagline{color:rgba(255,255,255,.5)}

        .product-desc{
            font-size  : .95rem;
            color      : var(--slate);
            line-height: 1.65;
            margin-bottom: 1.75rem;
        }

        .product-for{
            background    : var(--sand);
            border-radius : var(--r);
            padding       : .85rem 1.1rem;
            font-size     : .82rem;
            color         : var(--ink-soft);
            margin-bottom : 1.5rem;
            line-height   : 1.55;
        }

        .product-card.featured .product-for{
            background: rgba(255,255,255,.08);
            color    : rgba(255,255,255,.85);
        }

        .product-for strong{color:var(--terra-dark);font-weight:600}
        .product-card.featured .product-for strong{color:var(--terra-soft)}

        .product-includes{
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .65rem;
            margin-bottom: 2rem;
            flex: 1;
        }

        .product-includes li{
            font-size  : .9rem;
            color      : var(--ink-soft);
            display    : flex;
            align-items: flex-start;
            gap        : .65rem;
            line-height: 1.5;
        }

        .product-includes li::before{
            content: '';
            width: 16px; height: 16px;
            background: var(--terra);
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E") center/contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z'/%3E%3C/svg%3E") center/contain no-repeat;
            flex-shrink: 0;
            margin-top: .15rem;
        }

        .product-cta{
            background    : var(--terra);
            color         : var(--white);
            padding       : .95rem 1.5rem;
            border-radius : var(--r);
            font-weight   : 600;
            font-size     : .92rem;
            border        : none;
            cursor        : pointer;
            transition    : all .2s;
            display       : flex;
            align-items   : center;
            justify-content: center;
            gap           : .5rem;
            text-align    : center;
        }

        .product-cta:hover{
            background: var(--terra-dark);
            transform: translateY(-1px);
        }

        .product-card.featured .product-cta{
            background: var(--violet);
        }

        .product-card.featured .product-cta:hover{
            background: var(--violet-dark);
        }

        .product-scarcity{
            font-size : .76rem;
            color     : var(--mute);
            margin-top: .85rem;
            text-align: center;
        }

        .product-card.featured .product-scarcity{color:rgba(255,255,255,.55)}

        .product-scarcity strong{color:var(--terra)}

        /* ════════════════════════════════════════════════════════
           PLAN — los 3 pasos StoryBrand
        ════════════════════════════════════════════════════════ */
        .plan-section{
            background: var(--sand);
            position  : relative;
        }

        .plan-steps{
            display              : grid;
            grid-template-columns: repeat(3, 1fr);
            gap                  : 1.5rem;
            margin-top           : 3.5rem;
            position             : relative;
        }

        .plan-step{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 2rem 1.75rem;
            position      : relative;
        }

        .plan-step-head{
            display    : flex;
            align-items: center;
            gap        : .85rem;
            margin-bottom: 1rem;
        }

        .plan-step-num{
            width         : 38px;
            height        : 38px;
            border-radius : 50%;
            background    : var(--terra);
            color         : var(--white);
            font-family   : var(--serif);
            font-size     : 1.05rem;
            font-weight   : 700;
            display       : flex;
            align-items   : center;
            justify-content: center;
            flex-shrink   : 0;
        }

        .plan-step h3{
            font-family : var(--serif);
            font-size   : 1.2rem;
            font-weight : 600;
            color       : var(--ink);
            line-height : 1.25;
        }

        .plan-step p{
            font-size  : .92rem;
            color      : var(--slate);
            line-height: 1.65;
            margin-bottom: 1.25rem;
        }

        .plan-step-time{
            display      : inline-flex;
            align-items  : center;
            gap          : .4rem;
            font-size    : .75rem;
            font-weight  : 600;
            color        : var(--terra-dark);
            background   : var(--terra-tint);
            padding      : .35rem .85rem;
            border-radius: 99px;
        }

        .plan-promise{
            margin-top    : 3rem;
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 2rem;
            display       : flex;
            flex-wrap     : wrap;
            justify-content: center;
            gap           : 1.5rem 2.5rem;
        }

        .promise-item{
            display    : flex;
            align-items: center;
            gap        : .65rem;
            font-size  : .88rem;
            color      : var(--ink-soft);
        }

        .promise-icon{
            font-size: 1.1rem;
            color    : var(--terra);
        }

        /* ════════════════════════════════════════════════════════
           PRUEBA SOCIAL — Cialdini
        ════════════════════════════════════════════════════════ */
        .social-section{
            background: var(--cream);
        }

        .testimonials-grid{
            display              : grid;
            grid-template-columns: repeat(auto-fit, minmax(300px,1fr));
            gap                  : 1.5rem;
            margin-top           : 3.5rem;
        }

        .tcard{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-lg);
            padding       : 2rem 1.75rem;
            display       : flex;
            flex-direction: column;
            gap           : 1.5rem;
            position      : relative;
        }

        .tcard-quote-mark{
            font-family : var(--serif);
            font-size   : 3.5rem;
            font-weight : 700;
            color       : var(--terra-tint);
            line-height : .5;
            position    : absolute;
            top         : 1.5rem;
            right       : 1.5rem;
        }

        .tcard-metrics{
            display: flex;
            gap    : .65rem;
        }

        .metric-pill{
            flex          : 1;
            text-align    : center;
            background    : var(--sand);
            border-radius : var(--r-sm);
            padding       : .75rem .5rem;
        }

        .metric-pill .mn{
            font-family : var(--serif);
            font-size   : 1.4rem;
            font-weight : 600;
            color       : var(--ink);
            display     : block;
            line-height : 1;
        }

        .metric-pill .mn .pct{color:var(--terra)}

        .metric-pill .ml{
            font-size : .68rem;
            color     : var(--slate);
            margin-top: .35rem;
            display   : block;
        }

        .tcard-quote{
            font-size  : .95rem;
            color      : var(--ink-soft);
            line-height: 1.65;
            font-style : italic;
            font-family: var(--serif);
            font-weight: 400;
        }

        .tcard-author{
            display    : flex;
            align-items: center;
            gap        : .85rem;
            padding-top: 1.25rem;
            border-top : 1px solid var(--line);
        }

        .author-av{
            width: 42px; height: 42px;
            border-radius: 50%;
            background: var(--terra-tint);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
            font-weight: 700;
            color: var(--terra-dark);
            font-family: var(--serif);
            flex-shrink: 0;
        }

        .author-name{
            font-size  : .9rem;
            font-weight: 600;
            color      : var(--ink);
        }

        .author-role{
            font-size: .78rem;
            color    : var(--slate);
        }

        /* ════════════════════════════════════════════════════════
           INTEGRACIONES
        ════════════════════════════════════════════════════════ */
        .integrations-section{
            background: var(--sand);
            text-align: center;
        }

        .int-grid{
            display        : flex;
            flex-wrap      : wrap;
            gap            : .75rem;
            margin-top     : 2.5rem;
            justify-content: center;
        }

        .int-pill{
            display     : flex;
            align-items : center;
            gap         : .55rem;
            background  : var(--white);
            border      : 1px solid var(--line);
            border-radius: 99px;
            padding     : .65rem 1.15rem;
            font-size   : .86rem;
            font-weight : 500;
            color       : var(--ink-soft);
            transition  : all .2s;
        }

        .int-pill:hover{
            border-color: var(--terra);
            transform   : translateY(-2px);
        }

        .int-dot{
            width: 8px; height: 8px;
            border-radius: 50%;
        }

        /* ════════════════════════════════════════════════════════
           REGISTRO
        ════════════════════════════════════════════════════════ */
        .register-section{
            background : var(--cream);
            position   : relative;
            overflow   : hidden;
        }

        .register-section::before{
            content   : '';
            position  : absolute;
            top       : -10%; right: -10%;
            width     : 500px; height: 500px;
            background: radial-gradient(circle, var(--terra-tint) 0%, transparent 65%);
            pointer-events: none;
        }

        .register-grid{
            display              : grid;
            grid-template-columns: 1fr 1.1fr;
            gap                  : clamp(2rem,5vw,4.5rem);
            align-items          : start;
            max-width            : 1080px;
            margin               : 0 auto;
            position             : relative;
            z-index              : 1;
        }

        .reg-left-step{
            display     : flex;
            align-items : flex-start;
            gap         : .95rem;
            margin-bottom: 1rem;
        }

        .reg-step-circle{
            width          : 36px;
            height         : 36px;
            background     : var(--terra);
            color          : var(--white);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-family    : var(--serif);
            font-size      : .9rem;
            font-weight    : 700;
            flex-shrink    : 0;
        }

        .reg-step-text{
            font-size  : .95rem;
            color      : var(--ink-soft);
            padding-top: .5rem;
            line-height: 1.5;
        }

        .reg-step-text strong{color:var(--ink);font-weight:600}

        .login-box{
            padding       : 1.25rem 1.5rem;
            background    : var(--white);
            border-radius : var(--r);
            border        : 1px solid var(--line);
            margin-top    : 1.5rem;
        }

        .login-box p{
            font-size: .82rem;
            color   : var(--slate);
            margin-bottom: .4rem;
        }

        .login-box a{
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .92rem;
            color: var(--terra);
            font-weight: 600;
        }

        .login-box a:hover{color:var(--terra-dark)}

        /* Formulario */
        .rform{
            background    : var(--white);
            border        : 1px solid var(--line);
            border-radius : var(--r-xl);
            padding       : 2.5rem;
            box-shadow    : var(--shadow-lg);
        }

        .rform-title{
            font-family   : var(--serif);
            font-size     : 1.6rem;
            font-weight   : 600;
            color         : var(--ink);
            margin-bottom : .35rem;
            letter-spacing: -0.02em;
        }

        .rform-sub{
            font-size     : .85rem;
            color         : var(--slate);
            margin-bottom : 1.75rem;
            display       : flex;
            align-items   : center;
            gap           : .55rem;
            flex-wrap     : wrap;
        }

        .rform-sub-pill{
            background    : var(--terra-tint);
            color         : var(--terra-dark);
            font-size     : .7rem;
            font-weight   : 700;
            padding       : .25rem .65rem;
            border-radius : 99px;
            letter-spacing: .03em;
        }

        .fg{margin-bottom:1.1rem}

        .fg label{
            display       : block;
            font-size     : .72rem;
            font-weight   : 700;
            color         : var(--ink-soft);
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom : .45rem;
        }

        .fg input,
        .fg select{
            width          : 100%;
            background     : var(--cream);
            border         : 1.5px solid var(--line);
            border-radius  : var(--r);
            color          : var(--ink);
            padding        : .8rem 1rem;
            font-family    : var(--sans);
            font-size      : .92rem;
            outline        : none;
            transition     : all .2s;
            -webkit-appearance: none;
            appearance     : none;
        }

        .fg input::placeholder{color:var(--mute)}
        .fg input:focus,
        .fg select:focus{
            border-color: var(--terra);
            background  : var(--white);
            box-shadow  : 0 0 0 3px rgba(217,108,74,.1);
        }

        .fg-row{
            display              : grid;
            grid-template-columns: 1fr 1fr;
            gap                  : .85rem;
        }

        .fcheck{
            display    : flex;
            align-items: flex-start;
            gap        : .65rem;
            margin     : 1.25rem 0;
        }

        .fcheck input[type="checkbox"]{
            width      : 16px;
            height     : 16px;
            min-width  : 16px;
            margin-top : .2rem;
            accent-color: var(--terra);
            cursor     : pointer;
        }

        .fcheck label{
            font-size     : .82rem;
            color         : var(--slate);
            cursor        : pointer;
            text-transform: none;
            letter-spacing: 0;
            line-height   : 1.55;
            font-weight   : 400;
        }

        .fcheck a{color:var(--terra);font-weight:500;text-decoration:underline}

        .btn-reg{
            width         : 100%;
            background    : var(--terra);
            color         : var(--white);
            border        : none;
            padding       : 1.05rem;
            border-radius : var(--r);
            font-size     : 1rem;
            font-weight   : 600;
            cursor        : pointer;
            font-family   : var(--sans);
            transition    : all .22s;
            display       : flex;
            align-items   : center;
            justify-content: center;
            gap           : .5rem;
            box-shadow    : var(--shadow-terra);
        }

        .btn-reg:hover{
            background: var(--terra-dark);
            transform : translateY(-2px);
            box-shadow: 0 12px 28px rgba(217,108,74,.35);
        }

        .btn-reg:disabled{
            opacity   : .6;
            cursor    : not-allowed;
            transform : none;
        }

        .form-note{
            text-align: center;
            font-size : .76rem;
            color     : var(--mute);
            margin-top: .85rem;
        }

        .login-link{
            text-align: center;
            margin-top: 1.25rem;
            font-size : .88rem;
            color     : var(--slate);
        }

        .login-link a{color:var(--terra);font-weight:600}

        .form-error{
            background    : #FEF2F2;
            color         : #991B1B;
            border        : 1px solid #FECACA;
            border-radius : var(--r);
            padding       : .75rem 1rem;
            font-size     : .85rem;
            margin-bottom : 1rem;
            display       : none;
        }

        .success-wrap{
            display   : none;
            text-align: center;
            padding   : 2.5rem 1rem;
        }

        .success-icon{
            width          : 64px;
            height         : 64px;
            background     : var(--terra-tint);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            margin         : 0 auto 1.25rem;
            color          : var(--terra-dark);
            font-size      : 1.75rem;
            font-family    : var(--serif);
            font-weight    : 700;
        }

        .success-wrap h3{
            font-family  : var(--serif);
            font-size    : 1.5rem;
            color        : var(--ink);
            margin-bottom: .65rem;
        }

        .success-wrap p{
            font-size  : .9rem;
            color      : var(--slate);
            margin-bottom: 1.75rem;
            line-height: 1.6;
        }

        .btn-goto{
            display      : inline-flex;
            align-items  : center;
            gap          : .5rem;
            background   : var(--terra);
            color        : var(--white);
            padding      : .85rem 1.85rem;
            border-radius: var(--r);
            font-weight  : 600;
            font-size    : .92rem;
            transition   : all .2s;
            box-shadow   : var(--shadow-terra);
        }

        .btn-goto:hover{
            background: var(--terra-dark);
            transform : translateY(-1px);
        }

        /* ════════════════════════════════════════════════════════
           CTA FINAL
        ════════════════════════════════════════════════════════ */
        .final-cta{
            background : linear-gradient(135deg, var(--ink) 0%, #2a1a12 100%);
            padding    : clamp(4rem,8vw,6.5rem) clamp(1.25rem,4vw,2.75rem);
            text-align : center;
            color      : var(--white);
            position   : relative;
            overflow   : hidden;
        }

        .final-cta::before{
            content   : '';
            position  : absolute;
            top       : -50%; left: 50%;
            transform : translateX(-50%);
            width     : 800px; height: 800px;
            background: radial-gradient(circle, rgba(217,108,74,.18) 0%, transparent 60%);
            pointer-events: none;
        }

        .final-cta .inner{
            position: relative;
            z-index : 1;
            max-width: 760px;
            margin: 0 auto;
        }

        .final-cta h2{
            font-family   : var(--serif);
            font-size     : clamp(2rem, 4.2vw, 3.2rem);
            font-weight   : 600;
            color         : var(--white);
            margin-bottom : 1.25rem;
            letter-spacing: -0.025em;
            line-height   : 1.1;
        }

        .final-cta h2 em{font-style:italic;color:var(--terra-soft)}

        .final-cta > .inner > p{
            font-size    : 1.08rem;
            color        : rgba(255,255,255,.7);
            margin-bottom: 2.5rem;
            max-width    : 540px;
            margin-left  : auto;
            margin-right : auto;
            line-height  : 1.6;
        }

        .final-btns{
            display       : flex;
            justify-content: center;
            flex-wrap     : wrap;
            gap           : .85rem;
            margin-bottom : 1.75rem;
        }

        .btn-final-primary{
            background    : var(--terra);
            color         : var(--white);
            padding       : 1.1rem 2.2rem;
            border-radius : var(--r);
            font-weight   : 600;
            font-size     : 1rem;
            display       : inline-flex;
            align-items   : center;
            gap           : .55rem;
            transition    : all .22s;
            box-shadow    : var(--shadow-terra);
        }

        .btn-final-primary:hover{
            background: var(--terra-dark);
            transform : translateY(-2px);
        }

        .btn-final-ghost{
            display      : inline-flex;
            align-items  : center;
            gap          : .55rem;
            background   : rgba(255,255,255,.08);
            color        : var(--white);
            padding      : 1.1rem 1.85rem;
            border-radius: var(--r);
            font-size    : 1rem;
            font-weight  : 600;
            border       : 1.5px solid rgba(255,255,255,.18);
            transition   : all .22s;
        }

        .btn-final-ghost:hover{
            background  : rgba(255,255,255,.14);
            border-color: var(--terra-soft);
        }

        .final-note{
            font-size : .82rem;
            color     : rgba(255,255,255,.5);
        }

        /* ════════════════════════════════════════════════════════
           FOOTER
        ════════════════════════════════════════════════════════ */
        footer{
            background : var(--ink);
            padding    : 2.5rem clamp(1.25rem,4vw,2.75rem);
            display    : flex;
            flex-wrap  : wrap;
            justify-content: space-between;
            align-items: center;
            gap        : 1.25rem;
            border-top : 1px solid rgba(255,255,255,.08);
        }

        .footer-logo-img{
            height: 28px;
            opacity: .9;
            filter: brightness(0) invert(1);
        }

        .footer-links{
            display: flex;
            gap    : 1.75rem;
        }

        .footer-links a{
            font-size : .85rem;
            color     : rgba(255,255,255,.5);
            transition: color .15s;
        }

        .footer-links a:hover{color:var(--terra-soft)}

        .footer-right{
            font-size: .8rem;
            color    : rgba(255,255,255,.35);
        }

        /* ════════════════════════════════════════════════════════
           WHATSAPP FLOTANTE
        ════════════════════════════════════════════════════════ */
        .wa-float{
            position      : fixed;
            bottom        : 1.75rem;
            right         : 1.75rem;
            z-index       : 999;
            width         : 56px;
            height        : 56px;
            background    : var(--wa-green);
            border-radius : 50%;
            display       : flex;
            align-items   : center;
            justify-content: center;
            box-shadow    : 0 8px 24px rgba(37,211,102,.40);
            transition    : transform .2s;
        }

        .wa-float:hover{transform:scale(1.08)}

        .wa-float svg{width:28px;height:28px;fill:#fff}

        /* ════════════════════════════════════════════════════════
           REVEAL
        ════════════════════════════════════════════════════════ */
        .reveal{
            opacity   : 0;
            transform : translateY(24px);
            transition: opacity .65s ease, transform .65s ease;
        }

        .reveal.visible{
            opacity   : 1;
            transform : none;
        }

        /* ════════════════════════════════════════════════════════
           RESPONSIVE
        ════════════════════════════════════════════════════════ */
        @media(max-width: 960px){
            .hero-inner,
            .demo-grid,
            .products-grid,
            .register-grid,
            .problem-grid{grid-template-columns:1fr}

            .hero-visual{min-height:auto;margin-top:2rem}

            .pillars,
            .plan-steps{grid-template-columns:1fr}

            .stats-inner{grid-template-columns:repeat(2,1fr);gap:1.25rem 1.5rem}

            .nav-links{display:none}
        }

        @media(max-width: 640px){
            .hero h1{font-size:2.1rem}
            .stats-inner{grid-template-columns:repeat(2,1fr)}
            .stat{padding-left:1rem}
            .fg-row{grid-template-columns:1fr}
            footer{flex-direction:column;text-align:center}
            .hero-btns{flex-direction:column;align-items:stretch}
            .btn-primary, .btn-secondary{justify-content:center}
            .chip-1, .chip-3{display:none}
            .chip-2{right:.5rem}
            .rform{padding:1.75rem 1.5rem}
            .product-card{padding:2rem 1.5rem}
            .plan-step{padding:1.75rem 1.5rem}
        }

        @media(prefers-reduced-motion: reduce){
            *,*::before,*::after{
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
            .reveal{opacity:1;transform:none}
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════
     NAVEGACIÓN
═══════════════════════════════════════════════════════════ -->
<nav>
    <a href="#" aria-label="Tentii — Inicio">
        <img src="https://tentii.com/assets/tentii.png" alt="Tentii" class="nav-logo" width="117" height="34">
    </a>

    <div class="nav-links">
        <a href="#cambio">El cambio</a>
        <a href="#productos">Productos</a>
        <a href="#plan">Cómo empieza</a>
        <a href="#registro">Empezar</a>
    </div>

    <div class="nav-actions">
        <a href="/login" class="btn-login">Iniciar sesión</a>
        <a href="#registro" class="btn-nav-cta">Empezar gratis</a>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     HERO — StoryBrand one-liner + héroe en su mundo
═══════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-inner">
        <div class="reveal">
            <div class="hero-badge">
                <span class="hero-badge-pill">Nuevo</span>
                Hecho por hoteleros, no por ingenieros
            </div>

            <h1>Tu experiencia<br>se reserva desde<br>el <em>primer mensaje.</em></h1>

            <p class="hero-sub">Tentii convierte cada WhatsApp, DM y consulta en una reserva confirmada — sin que pierdas el toque humano que tus huéspedes ya aman.</p>

            <div class="hero-btns">
                <a href="#registro" class="btn-primary">
                    Empezar gratis
                    <span class="arr">→</span>
                </a>
                <a href="#productos" class="btn-secondary">
                    Ver cómo funciona
                </a>
            </div>

            <div class="hero-trust">
                <div class="trust-dots">
                    <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                </div>
                <span><strong>30 días gratis</strong> · Sin tarjeta · Cancelas cuando quieras</span>
            </div>
        </div>

        <div class="hero-visual reveal">
            <div class="float-chip chip-1">
                <div class="float-chip-icon">⚡</div>
                <div>
                    <strong>3 seg</strong>
                    <span>tiempo de respuesta</span>
                </div>
            </div>

            <div class="hero-chat-card">
                <div class="chat-ph-header">
                    <div class="chat-avatar">CV</div>
                    <div>
                        <div class="chat-name">Lodge Cielo Verde</div>
                        <div class="chat-status"><span class="status-dot"></span> Atendiendo ahora</div>
                    </div>
                </div>
                <div style="background:#ECE5DD;padding:1rem;display:flex;flex-direction:column;gap:.55rem;min-height:230px">
                    <div class="msg-wrap right">
                        <div class="msg-bubble in">Hola, vi su Instagram. Tienen cabaña para 4 el puente de junio?</div>
                        <div class="msg-meta">8:42 pm</div>
                    </div>
                    <div class="msg-wrap left">
                        <div class="msg-bubble out">¡Hola Camila! 🌿 Sí, tenemos la <strong>Cabaña Roble</strong> disponible esas fechas. Incluye desayuno y caminata al mirador. ¿Te paso fotos y la cotización?</div>
                        <div class="msg-meta out-t">8:42 pm <span class="ticks">✓✓</span></div>
                    </div>
                    <div class="msg-wrap right">
                        <div class="msg-bubble in">Siiii por favor 🙌</div>
                        <div class="msg-meta">8:43 pm</div>
                    </div>
                </div>
                <div class="chat-tentii-badge">
                    ✨ Conversación atendida con Tentii
                </div>
            </div>

            <div class="float-chip chip-2">
                <div class="float-chip-icon">🌙</div>
                <div>
                    <strong>24/7</strong>
                    <span>también de madrugada</span>
                </div>
            </div>

            <div class="float-chip chip-3">
                <div class="float-chip-icon">💬</div>
                <div>
                    <strong>+38%</strong>
                    <span>reservas directas</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     STATS BAND — autoridad sutil
═══════════════════════════════════════════════════════════ -->
<div class="stats-band">
    <div class="stats-inner">
        <div class="stat reveal">
            <div class="stat-n">+38<span class="pct">%</span></div>
            <div class="stat-l">reservas directas promedio</div>
        </div>
        <div class="stat reveal">
            <div class="stat-n">&lt;5<span class="pct">s</span></div>
            <div class="stat-l">tiempo de respuesta</div>
        </div>
        <div class="stat reveal">
            <div class="stat-n">96<span class="pct">%</span></div>
            <div class="stat-l">consultas con respuesta</div>
        </div>
        <div class="stat reveal">
            <div class="stat-n">+3<span class="pct">h</span></div>
            <div class="stat-l">ahorradas por día / equipo</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     PROBLEMA — StoryBrand: el dolor del héroe
═══════════════════════════════════════════════════════════ -->
<section class="problem-section" id="cambio">
    <div class="container">
        <div class="problem-grid">
            <div class="reveal">
                <div class="eyebrow">El problema real</div>
                <div class="problem-quote">
                    "El huésped no <span class="strike">reserva</span>… <em>pregunta primero.</em>"
                </div>
                <div class="problem-body">
                    <p>El 70% de las reservas turísticas <strong>empiezan como una conversación</strong> — un WhatsApp, un DM, un correo, un formulario.</p>
                    <p>Pero esas conversaciones viven en <strong>cinco lugares distintos</strong>: el celular del dueño, el de la recepcionista, una hoja de Excel, la bandeja de Instagram y un grupo de WhatsApp del equipo.</p>
                    <p>Cuando no respondes en 10 minutos, el huésped ya está hablando con tu competencia. <strong>Y eso pasa todos los días.</strong></p>
                </div>
            </div>

            <div class="reveal">
                <div class="leak-list">
                    <div class="leak-list-title">
                        <span class="dot-red"></span>
                        Conversaciones perdidas hoy
                    </div>
                    <div class="leak-row">
                        <div class="leak-icon">🌙</div>
                        <div>
                            "Tienen disponibilidad para el fin de semana?"
                            <span class="time">WhatsApp — 2:17 am · sin responder</span>
                        </div>
                    </div>
                    <div class="leak-row">
                        <div class="leak-icon">📩</div>
                        <div>
                            "Cuánto el plan de 3 noches para 2?"
                            <span class="time">Instagram DM — ayer · respondido 16 hrs después</span>
                        </div>
                    </div>
                    <div class="leak-row">
                        <div class="leak-icon">📞</div>
                        <div>
                            "Mandé un correo y nadie me contestó…"
                            <span class="time">Email — hace 3 días · sin abrir</span>
                        </div>
                    </div>
                    <div class="leak-row">
                        <div class="leak-icon">💸</div>
                        <div>
                            "Lo reservé por Booking, era más fácil."
                            <span class="time">Cliente recuperado por OTA · –18% comisión</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     AGITACIÓN — el costo invisible
═══════════════════════════════════════════════════════════ -->
<section class="agit-section">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Lo que nadie te dice</div>
            <h2 class="section-h">No estás perdiendo reservas.<br>Estás perdiendo <em>conversaciones.</em></h2>
            <p class="section-p">Cada consulta sin respuesta es un huésped que confió en ti primero. Y se fue.</p>
        </div>

        <div class="agit-grid">
            <div class="agit-card reveal">
                <div class="agit-icon">🌙</div>
                <h3>La reserva de la madrugada</h3>
                <p>Tu mejor huésped pregunta a las 2am desde un Uber. A las 9am, ya reservó en otra parte.</p>
                <span class="cost">Pérdida: 1 reserva / semana</span>
            </div>
            <div class="agit-card reveal">
                <div class="agit-icon">📋</div>
                <h3>Las preguntas que se repiten</h3>
                <p>"¿Cuánto cuesta?", "¿incluye desayuno?", "¿aceptan mascotas?". Tres horas diarias respondiendo lo mismo.</p>
                <span class="cost">Costo: 90 hrs / mes</span>
            </div>
            <div class="agit-card reveal">
                <div class="agit-icon">💬</div>
                <h3>El WhatsApp con 47 sin leer</h3>
                <p>De esos 47, doce eran consultas de reserva reales. Ya buscaron a otro. Ya no vuelven.</p>
                <span class="cost">Pérdida invisible</span>
            </div>
            <div class="agit-card reveal">
                <div class="agit-icon">📉</div>
                <h3>El seguimiento que nunca pasa</h3>
                <p>"Voy a pensarlo y te aviso". Nunca avisaron. Tú tampoco volviste a escribir. Lead frío para siempre.</p>
                <span class="cost">68% de leads tibios se enfrían</span>
            </div>
            <div class="agit-card reveal">
                <div class="agit-icon">🔄</div>
                <h3>El cliente que ya vino</h3>
                <p>Volvió a escribir el año siguiente. Nadie recordó su nombre, ni que prefiere habitación con vista.</p>
                <span class="cost">Fidelización al 22% del potencial</span>
            </div>
            <div class="agit-card reveal">
                <div class="agit-icon">💸</div>
                <h3>La comisión que sangra</h3>
                <p>Le pagas 18% a Booking porque no tienes tiempo de atender la venta directa que tocó tu puerta.</p>
                <span class="cost">~$2.000.000 / mes en comisiones</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     SOLUCIÓN — el "guía" StoryBrand: 3 pilares
═══════════════════════════════════════════════════════════ -->
<section class="solution-section">
    <div class="container">
        <div class="solution-intro reveal">
            <div class="eyebrow">La forma Tentii</div>
            <h2 class="section-h">No tienes que cambiar tu esencia.<br>Solo necesitas <em>una mejor forma de responder.</em></h2>
            <p class="solution-pull">Tres mundos que viven separados —<br>conversación, gestión y operación—<br><em>integrados en un solo flujo.</em></p>
        </div>

        <div class="pillars">
            <div class="pillar reveal">
                <div class="pillar-num">i.</div>
                <h3>Conversación</h3>
                <p>Donde realmente empieza la intención de compra turística — y donde tu equipo se queda sin manos.</p>
                <ul class="pillar-bullets">
                    <li>WhatsApp, Instagram, email y web en una sola bandeja</li>
                    <li>Asistente IA que responde con tu tono, no con un bot genérico</li>
                    <li>Traspaso a humano en un clic, con todo el contexto</li>
                </ul>
            </div>

            <div class="pillar reveal">
                <div class="pillar-num">ii.</div>
                <h3>Gestión comercial</h3>
                <p>Donde se ordenan los leads, las cotizaciones y el seguimiento — sin notas pegadas en la pantalla.</p>
                <ul class="pillar-bullets">
                    <li>CRM turístico: historial, preferencias y oportunidades</li>
                    <li>Cotizaciones que se vuelven reservas con un botón</li>
                    <li>Seguimiento automático de leads tibios y recuperación</li>
                </ul>
            </div>

            <div class="pillar reveal">
                <div class="pillar-num">iii.</div>
                <h3>Operación turística</h3>
                <p>Donde se conectan disponibilidad, experiencia y reserva — y donde Excel deja de ser tu sistema.</p>
                <ul class="pillar-bullets">
                    <li>Calendario de disponibilidad y cupos por experiencia</li>
                    <li>Channel manager con canales directos e indirectos</li>
                    <li>Recordatorios pre-check-in, post-estancia y reviews</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     DEMO — chat IA en vivo
═══════════════════════════════════════════════════════════ -->
<section class="demo-section">
    <div class="container">
        <div class="section-head-center reveal" style="text-align:left;max-width:620px;margin-left:0">
            <div class="eyebrow">Asistente conversacional</div>
            <h2 class="section-h">Responde como tú,<br><em>aunque tú estés durmiendo.</em></h2>
            <p class="section-p">Tu asistente Tentii cotiza, agenda y confirma — conectado en tiempo real a tu disponibilidad. Y aprende tu forma de hablar.</p>
        </div>

        <div class="demo-grid">
            <div class="reveal">
                <div class="demo-features">
                    <div class="demo-feat">
                        <div class="demo-feat-icon">💬</div>
                        <div>
                            <h4>Tu voz, no la de un robot</h4>
                            <p>El asistente se entrena con tu marca, tus paquetes y tu manera de hablar. Suena como tú.</p>
                        </div>
                    </div>
                    <div class="demo-feat">
                        <div class="demo-feat-icon">⚡</div>
                        <div>
                            <h4>Respuesta en menos de 5 segundos</h4>
                            <p>24/7, en español, inglés, portugués. Sin pausa, sin domingos cerrados.</p>
                        </div>
                    </div>
                    <div class="demo-feat">
                        <div class="demo-feat-icon">🎯</div>
                        <div>
                            <h4>Sabe cuándo pasarte la conversación</h4>
                            <p>Detecta huésped VIP, queja sensible o cotización compleja — y la pasa a un humano con todo el contexto.</p>
                        </div>
                    </div>
                    <div class="demo-feat">
                        <div class="demo-feat-icon">🔁</div>
                        <div>
                            <h4>Seguimiento que sí pasa</h4>
                            <p>El lead que no cerró hoy recibe seguimiento mañana — sin que nadie tenga que acordarse.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reveal chat-wrap">
                <div class="chat-phone">
                    <div class="chat-ph-header">
                        <div class="chat-avatar">CV</div>
                        <div>
                            <div class="chat-name">Lodge Cielo Verde</div>
                            <div class="chat-status">
                                <span class="status-dot"></span>
                                Asistente Tentii activo
                            </div>
                        </div>
                    </div>
                    <div class="chat-body" id="chatBody">
                        <!-- mensajes se inyectan vía JS -->
                    </div>
                    <div class="chat-tentii-badge">
                        ✨ Atendido por Tentii · Conversación real
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     DOS LÍNEAS DE PRODUCTO — diferenciación clara del brief
═══════════════════════════════════════════════════════════ -->
<section class="products-section" id="productos">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow violet">Dos formas de empezar</div>
            <h2 class="section-h">Una para arrancar mañana.<br>Otra para <em>levantar tu marca al siguiente nivel.</em></h2>
            <p class="section-p">Elige según el momento de tu negocio. Las dos te llevan al mismo lugar: que cada conversación se vuelva reserva.</p>
        </div>

        <div class="products-grid">
            <div class="product-card reveal">
                <span class="product-tag start">Autoatendido</span>
                <h3 class="product-name">Tentii <em>Start</em></h3>
                <p class="product-tagline">Para empezar hoy, sin asesores</p>

                <p class="product-desc">El producto que activas tú mismo en una tarde. Personalización guiada por wizard, con plantillas pensadas para hostales, cabañas y operadores pequeños.</p>

                <div class="product-for">
                    <strong>Para ti, si:</strong> tienes equipo pequeño, vendes principalmente por WhatsApp e Instagram, y alguien en tu equipo se anima a cacharrear configuraciones por su cuenta.
                </div>

                <ul class="product-includes">
                    <li>Asistente IA en WhatsApp con plantillas turísticas</li>
                    <li>Inbox unificado: WhatsApp + Instagram + email</li>
                    <li>CRM básico con historial de huéspedes</li>
                    <li>Cotizaciones y seguimiento automático</li>
                    <li>Calendario de disponibilidad</li>
                    <li>Onboarding guiado por wizard (10 min)</li>
                    <li>Soporte por chat</li>
                </ul>

                <a href="#registro" class="product-cta">
                    Empezar gratis ahora →
                </a>
                <p class="product-scarcity">30 días gratis · Sin tarjeta de crédito</p>
            </div>

            <div class="product-card featured reveal">
                <span class="product-tag studio">Consultivo · alta personalización</span>
                <h3 class="product-name">Tentii <em>Studio</em></h3>
                <p class="product-tagline">Para marcas que cuidan cada palabra</p>

                <p class="product-desc">Acompañamiento consultivo de marca + onboarding asistido para que la IA hable exactamente como tú hablas. Diseñado para hoteles boutique, lodges premium y operadores de experiencias.</p>

                <div class="product-for">
                    <strong>Para ti, si:</strong> tu marca es tu activo principal, no puedes permitirte que la IA suene genérica, y quieres replicar tu estilo de atención a escala sin perder el alma.
                </div>

                <ul class="product-includes">
                    <li>Todo lo de Tentii Start, con límites elevados</li>
                    <li>Sesión de análisis de marca y tono de voz</li>
                    <li>Entrenamiento personalizado del asistente IA</li>
                    <li>Diseño de flujos conversacionales a medida</li>
                    <li>Channel manager con canales premium</li>
                    <li>Account manager dedicado</li>
                    <li>Onboarding asistido (2 semanas)</li>
                    <li>Soporte prioritario por WhatsApp</li>
                </ul>

                <a href="#registro" class="product-cta">
                    Agendar consultoría →
                </a>
                <p class="product-scarcity"><strong>Cupos limitados</strong> · Solo 8 onboardings al mes</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     PLAN — los 3 pasos (StoryBrand requiere un plan claro)
═══════════════════════════════════════════════════════════ -->
<section class="plan-section" id="plan">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Cómo empieza</div>
            <h2 class="section-h">En menos de 10 minutos.<br><em>De verdad, no es cliché.</em></h2>
            <p class="section-p">Sin migraciones dolorosas. Sin desarrollo. Sin cambiar tu número de WhatsApp.</p>
        </div>

        <div class="plan-steps">
            <div class="plan-step reveal">
                <div class="plan-step-head">
                    <div class="plan-step-num">1</div>
                    <h3>Cuéntanos de tu negocio</h3>
                </div>
                <p>Nombre, tipos de alojamiento o experiencias, tarifas base. Como llenar un perfil — pero el wizard te guía cada paso.</p>
                <span class="plan-step-time">⏱ 2 minutos</span>
            </div>

            <div class="plan-step reveal">
                <div class="plan-step-head">
                    <div class="plan-step-num">2</div>
                    <h3>Conecta tus canales</h3>
                </div>
                <p>Tu WhatsApp Business, Instagram y email. Escaneas un QR o autorizas con un clic. No cambias de número, no cambias de bandeja.</p>
                <span class="plan-step-time">⏱ 5 minutos</span>
            </div>

            <div class="plan-step reveal">
                <div class="plan-step-head">
                    <div class="plan-step-num">3</div>
                    <h3>Tu asistente empieza a vender</h3>
                </div>
                <p>La IA aprende de tu marca y empieza a responder, cotizar y agendar. Tú supervisas desde el dashboard. Eso es todo.</p>
                <span class="plan-step-time">⏱ Inmediato</span>
            </div>
        </div>

        <div class="plan-promise reveal">
            <div class="promise-item"><span class="promise-icon">🛡️</span> 30 días de garantía o te devolvemos cada peso</div>
            <div class="promise-item"><span class="promise-icon">🤝</span> Onboarding humano incluido</div>
            <div class="promise-item"><span class="promise-icon">💬</span> Soporte en español, por WhatsApp</div>
            <div class="promise-item"><span class="promise-icon">🔄</span> Migración asistida de tus datos</div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     PRUEBA SOCIAL — Cialdini
═══════════════════════════════════════════════════════════ -->
<section class="social-section">
    <div class="container">
        <div class="section-head-center reveal">
            <div class="eyebrow">Resultados reales</div>
            <h2 class="section-h">Negocios turísticos que <em>ya duermen tranquilos</em></h2>
        </div>

        <div class="testimonials-grid">
            <div class="tcard reveal">
                <div class="tcard-quote-mark">"</div>
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn">+41<span class="pct">%</span></span>
                        <span class="ml">reservas directas</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">3<span class="pct">h</span></span>
                        <span class="ml">ahorradas / día</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">–22<span class="pct">%</span></span>
                        <span class="ml">comisiones OTA</span>
                    </div>
                </div>
                <p class="tcard-quote">Antes respondíamos 80 WhatsApps al día a pulso. Hoy el asistente atiende el 94% y nuestras reservas directas subieron 41% en un trimestre. Lo más bonito: los huéspedes nos siguen diciendo que les contestamos "como amigos".</p>
                <div class="tcard-author">
                    <div class="author-av">MG</div>
                    <div>
                        <div class="author-name">María González</div>
                        <div class="author-role">Gerente · Hotel Boutique Casa Verde</div>
                    </div>
                </div>
            </div>

            <div class="tcard reveal">
                <div class="tcard-quote-mark">"</div>
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn">+28<span class="pct">%</span></span>
                        <span class="ml">ingresos / mes</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">98<span class="pct">%</span></span>
                        <span class="ml">satisfacción</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">&lt;3<span class="pct">s</span></span>
                        <span class="ml">respuesta</span>
                    </div>
                </div>
                <p class="tcard-quote">Configuramos Tentii Studio en dos semanas. El equipo entró a entender nuestra marca, no a vendernos un software. Cuando la IA empezó a responder, los huéspedes ni siquiera notaron el cambio. Eso para mí lo dice todo.</p>
                <div class="tcard-author">
                    <div class="author-av" style="background:var(--violet-soft);color:var(--violet-dark)">CR</div>
                    <div>
                        <div class="author-name">Carlos Restrepo</div>
                        <div class="author-role">Director · Apart Hotel El Poblado</div>
                    </div>
                </div>
            </div>

            <div class="tcard reveal">
                <div class="tcard-quote-mark">"</div>
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn">89<span class="pct">%</span></span>
                        <span class="ml">menos trabajo manual</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">+35<span class="pct">%</span></span>
                        <span class="ml">conversión</span>
                    </div>
                </div>
                <p class="tcard-quote">Tenemos cabañas en zona rural sin recepción nocturna. Tentii cambió el modelo: el huésped pregunta a la hora que sea, la IA atiende, la gente reserva, y nosotros dormimos. La primera noche que activamos el sistema cerramos tres reservas mientras descansábamos.</p>
                <div class="tcard-author">
                    <div class="author-av" style="background:#FEF3C7;color:#92400E">LS</div>
                    <div>
                        <div class="author-name">Laura Soto</div>
                        <div class="author-role">Propietaria · Cabañas La Montaña</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     INTEGRACIONES — autoridad
═══════════════════════════════════════════════════════════ -->
<section class="integrations-section">
    <div class="container">
        <div class="reveal">
            <div class="eyebrow">Se lleva bien con lo que ya usas</div>
            <h2 class="section-h">Conectado con las herramientas<br>que ya están en tu día a día</h2>
            <p class="section-p" style="margin:1rem auto 0">¿No ves la tuya? Conectamos integraciones nuevas en 72 horas.</p>
        </div>

        <div class="int-grid reveal">
            <div class="int-pill"><span class="int-dot" style="background:#003580"></span> Booking.com</div>
            <div class="int-pill"><span class="int-dot" style="background:#E4002B"></span> Expedia</div>
            <div class="int-pill"><span class="int-dot" style="background:#FF5A5F"></span> Airbnb</div>
            <div class="int-pill"><span class="int-dot" style="background:#E6002D"></span> Despegar</div>
            <div class="int-pill"><span class="int-dot" style="background:#FF6F00"></span> GetYourGuide</div>
            <div class="int-pill"><span class="int-dot" style="background:#00AF87"></span> TripAdvisor</div>
            <div class="int-pill"><span class="int-dot" style="background:#25D366"></span> WhatsApp Business</div>
            <div class="int-pill"><span class="int-dot" style="background:#E4405F"></span> Instagram</div>
            <div class="int-pill"><span class="int-dot" style="background:#635BFF"></span> Stripe</div>
            <div class="int-pill"><span class="int-dot" style="background:#009EE3"></span> Mercado Pago</div>
            <div class="int-pill"><span class="int-dot" style="background:#4285F4"></span> Google Calendar</div>
            <div class="int-pill"><span class="int-dot" style="background:#0078D4"></span> Outlook / Gmail</div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     REGISTRO — FUNCIONALIDAD INTACTA
═══════════════════════════════════════════════════════════ -->
<section id="registro" class="register-section">
    <div class="container">
        <div class="register-grid">

            <!-- Columna izquierda — refuerzo Cialdini -->
            <div class="reveal">
                <div class="eyebrow">Empezá hoy mismo</div>
                <h2 class="section-h">El próximo huésped<br>que te escriba,<br><em>ya no se va a otra parte.</em></h2>
                <p class="section-p" style="margin-bottom:2.25rem">Crea tu cuenta gratis. El wizard te lleva paso a paso para que en menos de 10 minutos tu asistente Tentii ya esté atendiendo en WhatsApp.</p>

                <div style="margin-bottom:1rem">
                    <div class="reg-left-step">
                        <div class="reg-step-circle">1</div>
                        <div class="reg-step-text"><strong>Llenas el formulario</strong> — menos de 2 minutos</div>
                    </div>
                    <div class="reg-left-step">
                        <div class="reg-step-circle">2</div>
                        <div class="reg-step-text"><strong>El wizard te guía</strong> para configurar tu negocio turístico</div>
                    </div>
                    <div class="reg-left-step">
                        <div class="reg-step-circle">3</div>
                        <div class="reg-step-text"><strong>30 días gratis</strong> con todas las funcionalidades</div>
                    </div>
                </div>

                <div class="login-box">
                    <p>¿Ya tienes cuenta Tentii?</p>
                    <a href="/login">Iniciar sesión en tu panel →</a>
                </div>
            </div>

            <!-- Columna derecha — formulario funcional -->
            <div class="reveal">
                <div class="rform">
                    <div id="formContent">
                        <h3 class="rform-title">Crear cuenta gratis</h3>
                        <p class="rform-sub">
                            <span class="rform-sub-pill">30 días gratis</span>
                            Sin tarjeta de crédito
                        </p>

                        <div class="form-error" id="formError"></div>

                        <form id="registerForm" novalidate>
                            <?= csrf_field() ?>

                            <div class="fg">
                                <label for="hotel_name">Nombre del negocio *</label>
                                <input type="text" id="hotel_name" name="hotel_name"
                                       placeholder="Lodge Cielo Verde"
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
                                       placeholder="ana@tunegocio.com"
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
                                Crear cuenta y empezar gratis →
                            </button>

                            <p class="form-note">Al registrarte, el wizard de configuración te espera</p>
                        </form>

                        <div class="login-link">
                            ¿Ya tienes cuenta? <a href="/login">Inicia sesión aquí</a>
                        </div>
                    </div>

                    <!-- Éxito -->
                    <div class="success-wrap" id="successWrap">
                        <div class="success-icon">✓</div>
                        <h3>¡Bienvenido a Tentii!</h3>
                        <p>Tu cuenta está lista. En un momento te llevamos al asistente de configuración.</p>
                        <a href="/onboarding" class="btn-goto">Ir al wizard →</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     CTA FINAL — pregunta StoryBrand sobre las consecuencias
═══════════════════════════════════════════════════════════ -->
<section class="final-cta">
    <div class="inner">
        <h2>¿Cuántas conversaciones<br>perdiste <em>esta semana?</em></h2>
        <p>Cada día que pasa sin Tentii, hay un huésped que te escribió primero y terminó reservando en otro lugar. Hoy puede ser el último.</p>
        <div class="final-btns">
            <a href="#registro" class="btn-final-primary">
                Activar mi cuenta gratis
                <span>→</span>
            </a>
            <a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="btn-final-ghost">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Hablar con un asesor
            </a>
        </div>
        <p class="final-note">30 días de garantía · Sin tarjeta de crédito · Cancela cuando quieras</p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ -->
<footer>
    <img src="https://tentii.com/assets/tentii.png" alt="Tentii" class="footer-logo-img" width="96" height="28">

    <div class="footer-links">
        <a href="/terminos">Términos</a>
        <a href="/privacidad">Privacidad</a>
        <a href="/login">Iniciar sesión</a>
    </div>

    <div class="footer-right">
        &copy; <?= date('Y') ?> Tentii · Tu experiencia se reserva desde el primer mensaje
    </div>
</footer>

<!-- WhatsApp flotante -->
<a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="wa-float" title="Hablar con Tentii por WhatsApp" aria-label="Hablar con Tentii por WhatsApp">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script>
    (function(){

        /* ─── Reveal on scroll ─── */
        var revEls = document.querySelectorAll('.reveal');
        var obs    = new IntersectionObserver(function(entries){
            entries.forEach(function(e, i){
                if(e.isIntersecting){
                    setTimeout(function(){ e.target.classList.add('visible'); }, i * 60);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.08 });

        revEls.forEach(function(el){ obs.observe(el); });

        /* ─── Simulador de chat con 20+ mensajes antes de loopear ─── */
        var chatBody = document.getElementById('chatBody');
        if(chatBody){

            // 22 turnos de conversación = 22 mensajes, loop después de cerrar la conversación
            var conversation = [
                { side:'in',  text:'Hola! Vi su lodge en Instagram, está hermoso 🌿', time:'8:42 pm' },
                { side:'out', text:'¡Hola Camila! Qué bueno que escribís 🙌 Sí, somos un lodge familiar a 2 horas de Bogotá. ¿Para qué fechas estás mirando?', time:'8:42 pm', tick:true },
                { side:'in',  text:'Para el puente de junio, somos 2 adultos y un bebé', time:'8:43 pm' },
                { side:'out', text:'Perfecto. Para esas fechas tengo dos opciones lindas:<br><br>🌲 <strong>Cabaña Roble</strong> — $480.000/noche<br>🌄 <strong>Suite Mirador</strong> — $620.000/noche<br><br>Las dos incluyen desayuno y caminata guiada. ¿Te paso fotos?', time:'8:43 pm', tick:true },
                { side:'in',  text:'Siiii por favor 📸', time:'8:44 pm' },
                { side:'out', text:'Te las envío ahora 👇 La Cabaña Roble es perfecta para familias con peques, tiene su propio jardincito.', time:'8:44 pm', tick:true },
                { side:'in',  text:'Qué amor 🥺 ¿Aceptan bebés sin recargo?', time:'8:45 pm' },
                { side:'out', text:'Sin recargo, totalmente bienvenidos 👶 Y si necesitan cuna, la tenemos sin costo. Solo me avisas el día de la llegada.', time:'8:45 pm', tick:true },
                { side:'in',  text:'Genial! ¿Y hay para hacer con el bebé por ahí?', time:'8:46 pm' },
                { side:'out', text:'Tenemos sendero corto (40 min ida y vuelta) apto para coche todoterreno, observación de aves al amanecer, y los dueños llevan al peque a saludar a las gallinas 🐔. Súper tranquilo.', time:'8:46 pm', tick:true },
                { side:'in',  text:'Me encanta. ¿Cuánto sería 3 noches en la Cabaña Roble?', time:'8:47 pm' },
                { side:'out', text:'3 noches × $480.000 = <strong>$1.440.000 total</strong>, todo incluido (desayunos, actividades guiadas, cuna). ¿Te bloqueo las fechas?', time:'8:47 pm', tick:true },
                { side:'in',  text:'Sí! Cómo hago el pago?', time:'8:48 pm' },
                { side:'out', text:'Te mando link de pago seguro 🔒. Puedes pagar 50% para confirmar y 50% al llegar, o el total ahora. ¿Cómo prefieres?', time:'8:48 pm', tick:true },
                { side:'in',  text:'Pago el 50% ahora', time:'8:49 pm' },
                { side:'out', text:'Listo, te envío el link: 👉 <span style="color:#1E88E5;text-decoration:underline">tentii.pay/cv-4821</span><br><br>Una vez confirmado, te llega al correo el comprobante y las instrucciones de llegada.', time:'8:49 pm', tick:true },
                { side:'in',  text:'Listo, ya pagué ✅', time:'8:51 pm' },
                { side:'out', text:'¡Confirmadísimo! 🎉 Reserva <strong>#CV-4821</strong> para 14, 15 y 16 de junio. Cabaña Roble. Te mando el correo con todo en un minuto.', time:'8:51 pm', tick:true },
                { side:'in',  text:'Mil graciasss, no esperaba que fuera tan fácil 😍', time:'8:52 pm' },
                { side:'out', text:'Para eso estamos 💛 Una semana antes te escribo con la ruta y los horarios. Cualquier cosa, aquí estamos. ¡Nos vemos en junio!', time:'8:52 pm', tick:true },
                { side:'in',  text:'Genial!! Hasta junio 👋', time:'8:53 pm' },
                { side:'out', text:'¡Hasta junio, Camila! 🌿✨', time:'8:53 pm', tick:true }
            ];

            var idx = 0;
            var typingEl = null;

            function appendMsg(msg){
                var wrap = document.createElement('div');
                wrap.className = 'msg-wrap ' + (msg.side === 'in' ? 'right' : 'left');

                var bubble = document.createElement('div');
                bubble.className = 'msg-bubble ' + (msg.side === 'in' ? 'in' : 'out');
                bubble.innerHTML = msg.text;
                wrap.appendChild(bubble);

                var meta = document.createElement('div');
                meta.className = 'msg-meta' + (msg.side === 'out' ? ' out-t' : '');
                meta.innerHTML = msg.time + (msg.tick ? ' <span class="ticks">✓✓</span>' : '');
                wrap.appendChild(meta);

                chatBody.appendChild(wrap);
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            function showTyping(){
                typingEl = document.createElement('div');
                typingEl.className = 'chat-typing';
                typingEl.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
                chatBody.appendChild(typingEl);
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            function hideTyping(){
                if(typingEl && typingEl.parentNode){
                    typingEl.parentNode.removeChild(typingEl);
                    typingEl = null;
                }
            }

            function resetChat(){
                chatBody.innerHTML = '';
                idx = 0;
            }

            function step(){
                // Si ya pasamos todos los mensajes, esperamos y reiniciamos
                if(idx >= conversation.length){
                    setTimeout(function(){
                        resetChat();
                        setTimeout(step, 600);
                    }, 6000);
                    return;
                }

                var msg = conversation[idx];

                // Si es del asistente, mostramos "escribiendo..."
                if(msg.side === 'out'){
                    showTyping();
                    setTimeout(function(){
                        hideTyping();
                        appendMsg(msg);
                        idx++;
                        setTimeout(step, 1300 + Math.random() * 700);
                    }, 1100 + Math.random() * 600);
                } else {
                    // Mensaje entrante del huésped: aparece directo
                    appendMsg(msg);
                    idx++;
                    setTimeout(step, 1300 + Math.random() * 700);
                }
            }

            // Inicia la conversación cuando el chat sea visible
            var chatObs = new IntersectionObserver(function(entries){
                entries.forEach(function(e){
                    if(e.isIntersecting){
                        setTimeout(step, 800);
                        chatObs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.3 });

            chatObs.observe(chatBody);
        }

        /* ─── Registro — FUNCIONALIDAD ORIGINAL INTACTA ─── */
        var form = document.getElementById('registerForm');
        if(!form) return;

        form.addEventListener('submit', function(e){
            e.preventDefault();
            handleRegister();
        });

        function showError(msg){
            var el = document.getElementById('formError');
            el.textContent = msg;
            el.style.display = 'block';
            el.scrollIntoView({ behavior:'smooth', block:'nearest' });
        }

        function hideError(){
            document.getElementById('formError').style.display = 'none';
        }

        function handleRegister(){
            hideError();
            var hotel  = form.hotel_name.value.trim();
            var name   = form.name.value.trim();
            var email  = form.email.value.trim();
            var phone  = form.phone.value.trim();
            var city   = form.city.value.trim();
            var pwd    = form.password.value;
            var pwdC   = form.password_confirm.value;
            var terms  = document.getElementById('terms').checked;
            var btn    = document.getElementById('btnReg');

            if(!hotel)          return showError('El nombre del negocio es requerido.');
            if(!name)           return showError('Tu nombre es requerido.');
            if(!email)          return showError('El email es requerido.');
            if(!phone)          return showError('El teléfono / WhatsApp es requerido.');
            if(!city)           return showError('La ciudad es requerida.');
            if(pwd.length < 8)  return showError('La contraseña debe tener al menos 8 caracteres.');
            if(pwd !== pwdC)    return showError('Las contraseñas no coinciden.');
            if(!terms)          return showError('Debes aceptar los términos de servicio.');

            btn.disabled    = true;
            btn.textContent = 'Creando tu cuenta...';

            fetch('/register', {
                method  : 'POST',
                body    : new FormData(form),
                headers : { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    if(res.success){
                        document.getElementById('formContent').style.display = 'none';
                        document.getElementById('successWrap').style.display  = 'block';
                        if(res.redirect){
                            setTimeout(function(){ window.location.href = res.redirect; }, 1800);
                        }
                    } else {
                        showError(res.message || 'Error al crear la cuenta. Intenta de nuevo.');
                        btn.disabled    = false;
                        btn.textContent = 'Crear cuenta y empezar gratis \u2192';
                    }
                })
                .catch(function(){
                    showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
                    btn.disabled    = false;
                    btn.textContent = 'Crear cuenta y empezar gratis \u2192';
                });
        }

    })();
</script>

</body>
</html>