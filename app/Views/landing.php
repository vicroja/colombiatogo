<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tentii — El PMS nacido en la era de la IA. Gestiona alojamiento + tours + WhatsApp en una sola plataforma. Sin comisiones por reserva. Desde USD 20/mes.">
    <meta name="keywords" content="PMS hoteles, software glamping, software cabañas, PMS tours, WhatsApp IA hotel, alternativa Asksuite, alternativa WeSpeak, channel manager Latam">
    <meta property="og:title" content="Tentii — PMS con IA para hoteles boutique, glamping, cabañas y tours">
    <meta property="og:description" content="No es un chatbot pegado a un PMS viejo. Es un PMS construido desde cero con IA. Reservas + tours + WhatsApp en uno. Sin comisiones.">
    <meta property="og:type" content="website">
    <title>Tentii — PMS con IA para alojamientos y tours</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ────────────────────────────────────────────────────────────────
           RESET & VARIABLES
        ──────────────────────────────────────────────────────────────── */
        *{margin:0;padding:0;box-sizing:border-box}

        :root{
            /* Tentii palette — earthy + modern */
            --terra        : #C2410C;   /* terracota — acento principal */
            --terra-dark   : #9A3412;
            --terra-light  : #FFF7ED;
            --terra-mid    : #FED7AA;

            --moss         : #166534;   /* verde profundo */
            --moss-dark    : #14532D;
            --moss-light   : #F0FDF4;
            --moss-mid     : #4ADE80;

            --sand         : #FAF7F2;   /* fondo cálido */
            --sand-dark    : #F4EFE7;
            --bone         : #FCFAF5;

            --ink          : #0C0A09;
            --ink-soft     : #1C1917;
            --slate-700    : #44403C;
            --slate-500    : #78716C;
            --slate-400    : #A8A29E;
            --slate-200    : #E7E5E4;
            --slate-100    : #F5F5F4;

            --wa           : #25D366;
            --wa-dark      : #128C7E;
            --wa-light     : #DCFCE7;

            --amber        : #F59E0B;
            --amber-light  : #FEF3C7;

            --display      : 'Fraunces', Georgia, serif;
            --sans         : 'Inter', system-ui, sans-serif;

            --r            : 8px;
            --r2           : 14px;
            --r3           : 22px;

            --shadow-sm    : 0 1px 3px rgba(12,10,9,.05), 0 1px 2px rgba(12,10,9,.04);
            --shadow-md    : 0 4px 18px rgba(12,10,9,.07), 0 2px 6px rgba(12,10,9,.04);
            --shadow-lg    : 0 20px 50px -12px rgba(12,10,9,.18), 0 8px 16px -8px rgba(12,10,9,.08);
        }

        html{scroll-behavior:smooth}

        body{
            background  : var(--bone);
            color       : var(--ink-soft);
            font-family : var(--sans);
            font-size   : 16px;
            line-height : 1.65;
            overflow-x  : hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}

        /* Subtle grain texture overlay */
        body::before{
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1000;
            opacity: .025;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* ────────────────────────────────────────────────────────────────
           NAV
        ──────────────────────────────────────────────────────────────── */
        nav{
            background      : rgba(252,250,245,0.85);
            border-bottom   : 1px solid var(--slate-200);
            padding         : 0 clamp(1.25rem,4vw,2.5rem);
            display         : flex;
            justify-content : space-between;
            align-items     : center;
            height          : 68px;
            position        : sticky;
            top             : 0;
            z-index         : 200;
            backdrop-filter : blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .nav-logo{
            font-family    : var(--display);
            font-size      : 1.45rem;
            font-weight    : 600;
            color          : var(--ink);
            letter-spacing : -0.025em;
            display        : flex;
            align-items    : baseline;
            gap            : .15rem;
            font-style     : italic;
        }

        .nav-logo-dot{
            width         : 7px;
            height        : 7px;
            background    : var(--terra);
            border-radius : 50%;
            display       : inline-block;
            transform     : translateY(-2px);
        }

        .nav-links{
            display     : flex;
            align-items : center;
            gap         : 2.25rem;
        }

        .nav-links a{
            font-size  : .85rem;
            color      : var(--slate-500);
            font-weight: 400;
            transition : color .15s;
        }

        .nav-links a:hover{color:var(--ink)}

        .nav-actions{
            display     : flex;
            align-items : center;
            gap         : .65rem;
        }

        .btn-login{
            font-size    : .85rem;
            color        : var(--ink);
            font-weight  : 500;
            padding      : .5rem 1rem;
            border-radius: var(--r);
            transition   : all .15s;
        }

        .btn-login:hover{background:var(--slate-100)}

        .btn-nav-cta{
            font-size     : .85rem;
            background    : var(--ink);
            color         : var(--bone);
            font-weight   : 500;
            padding       : .55rem 1.15rem;
            border-radius : var(--r);
            transition    : all .15s;
            border        : none;
            cursor        : pointer;
            display       : inline-block;
        }

        .btn-nav-cta:hover{background:var(--terra)}

        @media(max-width: 820px){
            .nav-links{display:none}
        }

        /* ────────────────────────────────────────────────────────────────
           HERO
        ──────────────────────────────────────────────────────────────── */
        .hero{
            padding    : clamp(4rem,8vw,7rem) clamp(1.25rem,4vw,2.5rem) clamp(3.5rem,7vw,6rem);
            background : var(--bone);
            position   : relative;
            overflow   : hidden;
        }

        .hero::before{
            content   : '';
            position  : absolute;
            top       : -200px;
            right     : -150px;
            width     : 600px;
            height    : 600px;
            background: radial-gradient(circle, rgba(194,65,12,.08) 0%, transparent 60%);
            pointer-events: none;
            z-index   : 0;
        }

        .hero::after{
            content   : '';
            position  : absolute;
            bottom    : -300px;
            left      : -200px;
            width     : 700px;
            height    : 700px;
            background: radial-gradient(circle, rgba(22,101,52,.06) 0%, transparent 60%);
            pointer-events: none;
            z-index   : 0;
        }

        .hero-inner{
            max-width: 1100px;
            margin   : 0 auto;
            position : relative;
            z-index  : 1;
            display  : grid;
            grid-template-columns: 1.1fr .9fr;
            gap      : 4rem;
            align-items: center;
        }

        @media(max-width: 900px){
            .hero-inner{grid-template-columns: 1fr; gap: 3rem; text-align:center}
        }

        .hero-badge{
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
            background    : var(--terra-light);
            color         : var(--terra-dark);
            font-size     : .72rem;
            font-weight   : 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding       : .4rem 1rem;
            border-radius : 99px;
            margin-bottom : 1.5rem;
            border        : 1px solid var(--terra-mid);
        }

        .hero-badge-dot{
            width         : 6px;
            height        : 6px;
            background    : var(--terra);
            border-radius : 50%;
            animation     : pulse 2.5s infinite;
        }

        .hero h1{
            font-family    : var(--display);
            font-size      : clamp(2.25rem, 5.5vw, 4.1rem);
            font-weight    : 500;
            color          : var(--ink);
            line-height    : 1.02;
            margin-bottom  : 1.5rem;
            letter-spacing : -0.035em;
        }

        .hero h1 em{
            font-style: italic;
            font-weight: 400;
            color: var(--terra);
        }

        .hero h1 .underline{
            position: relative;
            white-space: nowrap;
        }

        .hero h1 .underline::after{
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 4px;
            height: 6px;
            background: var(--moss-mid);
            opacity: .4;
            z-index: -1;
            border-radius: 4px;
        }

        .hero-sub{
            font-size     : 1.075rem;
            color         : var(--slate-500);
            max-width     : 540px;
            margin-bottom : 2rem;
            font-weight   : 400;
            line-height   : 1.7;
        }

        @media(max-width: 900px){
            .hero-sub{margin-left:auto;margin-right:auto}
        }

        .hero-btns{
            display         : flex;
            gap             : .75rem;
            flex-wrap       : wrap;
            margin-bottom   : 1.5rem;
        }

        @media(max-width: 900px){
            .hero-btns{justify-content:center}
        }

        .btn-primary{
            background    : var(--ink);
            color         : var(--bone);
            padding       : .9rem 1.85rem;
            border-radius : var(--r);
            font-weight   : 600;
            font-size     : .92rem;
            border        : none;
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
            font-family   : var(--sans);
        }

        .btn-primary:hover{
            background : var(--terra);
            transform  : translateY(-1px);
            box-shadow : var(--shadow-md);
        }

        .btn-primary .arrow{
            transition: transform .2s;
        }

        .btn-primary:hover .arrow{transform: translateX(3px)}

        .btn-secondary{
            background    : transparent;
            color         : var(--ink);
            padding       : .9rem 1.6rem;
            border-radius : var(--r);
            font-weight   : 500;
            font-size     : .92rem;
            border        : 1.5px solid var(--slate-200);
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
        }

        .btn-secondary:hover{border-color: var(--ink); background: var(--bone)}

        .hero-note{
            font-size : .8rem;
            color     : var(--slate-400);
            display   : flex;
            align-items: center;
            gap        : .9rem;
            flex-wrap  : wrap;
        }

        @media(max-width: 900px){
            .hero-note{justify-content:center}
        }

        .hero-note .sep{color: var(--slate-200)}
        .hero-note strong{color: var(--ink-soft); font-weight: 500}

        /* Hero visual — split mockup */
        .hero-visual{
            position: relative;
        }

        .hv-card{
            background    : white;
            border-radius : var(--r2);
            box-shadow    : var(--shadow-lg);
            border        : 1px solid var(--slate-200);
            overflow      : hidden;
            position      : relative;
        }

        .hv-card.dashboard{
            transform     : rotate(-1.5deg);
            position      : relative;
            z-index       : 2;
        }

        .hv-card.whatsapp{
            position      : absolute;
            bottom        : -40px;
            right         : -30px;
            width         : 60%;
            transform     : rotate(4deg);
            z-index       : 3;
        }

        @media(max-width: 900px){
            .hv-card.whatsapp{
                position: relative;
                width   : 80%;
                margin  : -50px auto 0;
                right   : auto;
                bottom  : auto;
            }
        }

        .hv-dash-header{
            background    : var(--ink);
            padding       : .8rem 1.1rem;
            display       : flex;
            align-items   : center;
            gap           : .6rem;
        }

        .hv-dash-dots{display:flex;gap:.3rem}
        .hv-dash-dots span{
            width:10px;height:10px;border-radius:50%;
            background: rgba(255,255,255,.2);
        }
        .hv-dash-dots span:first-child{background:#FF5F56}
        .hv-dash-dots span:nth-child(2){background:#FFBD2E}
        .hv-dash-dots span:nth-child(3){background:#27C93F}

        .hv-dash-title{
            color: rgba(255,255,255,.5);
            font-size: .7rem;
            margin-left: .4rem;
            font-family: var(--sans);
        }

        .hv-dash-body{
            padding: 1.25rem;
            background: var(--sand);
        }

        .hv-stats{
            display: grid;
            grid-template-columns: repeat(3,1fr);
            gap: .65rem;
            margin-bottom: 1.1rem;
        }

        .hv-stat{
            background: white;
            border-radius: var(--r);
            padding: .7rem .8rem;
            border: 1px solid var(--slate-200);
        }

        .hv-stat-l{
            font-size: .6rem;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: .15rem;
        }

        .hv-stat-n{
            font-family: var(--display);
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--ink);
        }

        .hv-stat-n .up{color: var(--moss); font-size: .7rem; margin-left: .25rem}

        .hv-cal{
            background: white;
            border-radius: var(--r);
            border: 1px solid var(--slate-200);
            padding: .75rem;
        }

        .hv-cal-h{
            font-size: .65rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .55rem;
            display: flex;
            justify-content: space-between;
        }

        .hv-cal-h span{color: var(--slate-500); font-weight: 400}

        .hv-cal-grid{
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
            gap: 2px;
        }

        .hv-cal-cell{
            font-size: .55rem;
            padding: .25rem .15rem;
            border-radius: 3px;
            color: var(--slate-500);
            text-align: center;
            background: var(--slate-100);
        }

        .hv-cal-cell.label{
            background: transparent;
            text-align: left;
            color: var(--ink);
            font-weight: 500;
            padding-left: 0;
        }

        .hv-cal-cell.booked{ background: var(--terra); color: white }
        .hv-cal-cell.confirmed{ background: var(--moss); color: white }
        .hv-cal-cell.maint{ background: var(--amber); color: white }
        .hv-cal-cell.free{ background: var(--moss-light); color: var(--moss-dark) }

        /* Mini WhatsApp */
        .hv-wa-header{
            background: #128C7E;
            padding: .55rem .75rem;
            color: white;
            display: flex;
            align-items: center;
            gap: .55rem;
        }

        .hv-wa-avatar{
            width: 28px;height:28px;border-radius:50%;
            background: var(--terra-light);
            display:flex;align-items:center;justify-content:center;
            font-size: .65rem;font-weight:700;color:var(--terra-dark);
        }

        .hv-wa-name{font-size:.72rem;font-weight:600;line-height:1.1}
        .hv-wa-status{font-size:.6rem;opacity:.85}

        .hv-wa-body{
            background: #ECE5DD;
            padding: .65rem;
            display: flex;
            flex-direction: column;
            gap: .35rem;
            min-height: 130px;
        }

        .hv-wa-msg{
            font-size: .7rem;
            line-height: 1.35;
            padding: .4rem .55rem;
            border-radius: 6px;
            max-width: 82%;
        }

        .hv-wa-msg.in{
            background: white;
            align-self: flex-start;
            border-radius: 0 6px 6px 6px;
        }

        .hv-wa-msg.out{
            background: #D9FDD3;
            align-self: flex-end;
            border-radius: 6px 6px 0 6px;
        }

        /* ────────────────────────────────────────────────────────────────
           SOCIAL PROOF STRIP
        ──────────────────────────────────────────────────────────────── */
        .proof-strip{
            border-top    : 1px solid var(--slate-200);
            border-bottom : 1px solid var(--slate-200);
            background    : var(--sand);
            padding       : 2rem clamp(1.25rem,4vw,2.5rem);
        }

        .proof-inner{
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .proof-label{
            font-size: .7rem;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .12em;
            font-weight: 500;
        }

        .proof-types{
            display: flex;
            gap: 1.75rem;
            flex-wrap: wrap;
        }

        .proof-type{
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .85rem;
            color: var(--ink);
            font-weight: 500;
            font-family: var(--display);
            font-style: italic;
        }

        .proof-type-icon{
            width: 22px; height: 22px;
            display: flex; align-items: center; justify-content: center;
            background: var(--terra-light);
            border-radius: 50%;
            color: var(--terra-dark);
        }

        /* ────────────────────────────────────────────────────────────────
           SECTION COMMONS
        ──────────────────────────────────────────────────────────────── */
        section{padding:clamp(4rem,8vw,7rem) clamp(1.25rem,4vw,2.5rem)}

        .container{max-width:1100px;margin:0 auto}

        .eyebrow{
            font-size      : .7rem;
            font-weight    : 600;
            letter-spacing : .15em;
            text-transform : uppercase;
            color          : var(--terra);
            margin-bottom  : .8rem;
            display        : inline-flex;
            align-items    : center;
            gap            : .5rem;
        }

        .eyebrow.moss{color:var(--moss)}
        .eyebrow.amber{color:#B45309}

        .eyebrow::before{
            content: '';
            width: 18px; height: 1px;
            background: currentColor;
        }

        .section-h{
            font-family    : var(--display);
            font-size      : clamp(1.85rem,3.5vw,2.85rem);
            font-weight    : 500;
            color          : var(--ink);
            line-height    : 1.1;
            letter-spacing : -0.025em;
            margin-bottom  : .85rem;
        }

        .section-h em{
            font-style: italic;
            color: var(--terra);
            font-weight: 400;
        }

        .section-p{
            font-size   : 1rem;
            color       : var(--slate-500);
            max-width   : 580px;
            line-height : 1.7;
        }

        /* ────────────────────────────────────────────────────────────────
           PAIN POINTS — el dolor del hotelero
        ──────────────────────────────────────────────────────────────── */
        .pain-section{
            background: var(--bone);
            position: relative;
        }

        .pain-head{
            text-align: center;
            margin-bottom: 3.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .pain-head .section-p{margin: 0 auto}

        .pain-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(280px,1fr));
            gap                   : 1rem;
        }

        .pain-card{
            background    : white;
            border        : 1px solid var(--slate-200);
            border-radius : var(--r2);
            padding       : 1.75rem 1.5rem;
            transition    : all .25s;
            position      : relative;
        }

        .pain-card:hover{
            border-color: var(--terra-mid);
            transform   : translateY(-3px);
            box-shadow  : var(--shadow-md);
        }

        .pain-card-icon{
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--terra-light);
            color: var(--terra-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .pain-card h3{
            font-family   : var(--display);
            font-size     : 1.1rem;
            font-weight   : 600;
            color         : var(--ink);
            margin-bottom : .55rem;
            line-height   : 1.3;
        }

        .pain-card p{
            font-size  : .88rem;
            color      : var(--slate-500);
            line-height: 1.65;
        }

        .pain-card .quote{
            font-style: italic;
            color: var(--slate-700);
            border-left: 2px solid var(--terra);
            padding-left: .85rem;
            margin-top: .85rem;
            font-size: .82rem;
            font-family: var(--display);
        }

        /* ────────────────────────────────────────────────────────────────
           SOLUTION OVERVIEW — qué hace Tentii
        ──────────────────────────────────────────────────────────────── */
        .solution{
            background: var(--ink);
            color: var(--bone);
            position: relative;
            overflow: hidden;
        }

        .solution::before{
            content: '';
            position: absolute;
            top: -300px; left: 50%;
            transform: translateX(-50%);
            width: 800px; height: 800px;
            background: radial-gradient(circle, rgba(194,65,12,.18) 0%, transparent 60%);
            pointer-events: none;
        }

        .solution-inner{
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .solution-head{
            text-align: center;
            margin-bottom: 4rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .solution-head .eyebrow{color: var(--terra-mid)}
        .solution-head .eyebrow::before{background: var(--terra-mid)}
        .solution-head .section-h{color: var(--bone); max-width: 750px}
        .solution-head .section-h em{color: var(--terra-mid)}
        .solution-head .section-p{color: rgba(252,250,245,.65); margin: 0 auto}

        .pillars{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: var(--r2);
            overflow: hidden;
        }

        @media(max-width: 800px){
            .pillars{grid-template-columns: 1fr}
        }

        .pillar{
            background: var(--ink);
            padding: 2.25rem 2rem;
            transition: background .25s;
            position: relative;
        }

        .pillar:hover{background: var(--ink-soft)}

        .pillar-num{
            font-family: var(--display);
            font-style: italic;
            font-size: .85rem;
            color: var(--terra-mid);
            margin-bottom: .85rem;
            font-weight: 500;
        }

        .pillar h3{
            font-family: var(--display);
            font-size: 1.45rem;
            font-weight: 500;
            margin-bottom: .85rem;
            color: var(--bone);
            line-height: 1.2;
            letter-spacing: -0.015em;
        }

        .pillar p{
            font-size: .92rem;
            color: rgba(252,250,245,.65);
            line-height: 1.7;
            margin-bottom: 1.1rem;
        }

        .pillar-feats{
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .45rem;
        }

        .pillar-feats li{
            font-size: .82rem;
            color: rgba(252,250,245,.85);
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            padding-left: 0;
        }

        .pillar-feats li::before{
            content: '';
            width: 14px; height: 1px;
            background: var(--terra-mid);
            margin-top: .65rem;
            flex-shrink: 0;
        }

        /* ────────────────────────────────────────────────────────────────
           WHATSAPP DEMO
        ──────────────────────────────────────────────────────────────── */
        .wa-section{
            background: var(--sand);
        }

        .wa-grid{
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 4rem;
            align-items           : center;
        }

        @media(max-width: 900px){
            .wa-grid{grid-template-columns: 1fr; gap: 3rem}
        }

        .wa-feat-list{
            display        : flex;
            flex-direction : column;
            gap            : 1.1rem;
            margin         : 2rem 0;
        }

        .wa-feat{
            display     : flex;
            align-items : flex-start;
            gap         : .9rem;
        }

        .wa-feat-icon{
            width          : 32px;
            height         : 32px;
            background     : var(--moss-light);
            color          : var(--moss);
            border-radius  : 9px;
            display        : flex;
            align-items    : center;
            justify-content: center;
            flex-shrink    : 0;
            font-size      : .9rem;
        }

        .wa-feat-text strong{
            display       : block;
            font-size     : .92rem;
            font-weight   : 600;
            color         : var(--ink);
            margin-bottom : .15rem;
        }

        .wa-feat-text span{
            font-size: .85rem;
            color    : var(--slate-500);
            line-height: 1.55;
        }

        /* Chat mockup grande */
        .chat-phone{
            background    : var(--bone);
            border-radius : 28px;
            box-shadow    : var(--shadow-lg);
            border        : 8px solid var(--ink);
            overflow      : hidden;
            max-width     : 380px;
            margin        : 0 auto;
        }

        .chat-ph-header{
            background    : #075E54;
            padding       : .9rem 1.25rem;
            display       : flex;
            align-items   : center;
            gap           : .75rem;
        }

        .chat-avatar{
            width          : 40px;
            height         : 40px;
            background     : var(--terra-light);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .8rem;
            font-weight    : 700;
            color          : var(--terra-dark);
            flex-shrink    : 0;
            font-family    : var(--display);
        }

        .chat-name{
            font-size   : .92rem;
            font-weight : 600;
            color       : white;
            line-height : 1.1;
        }

        .chat-status{
            font-size   : .72rem;
            color       : #9FE1CB;
            display     : flex;
            align-items : center;
            gap         : .3rem;
            margin-top  : .15rem;
        }

        .status-dot{
            width         : 6px;
            height        : 6px;
            background    : var(--wa);
            border-radius : 50%;
            animation     : pulse 2s infinite;
        }

        @keyframes pulse{
            0%,100%{opacity:1}
            50%{opacity:.4}
        }

        .chat-body{
            background : #ECE5DD;
            padding    : 1.1rem;
            display    : flex;
            flex-direction: column;
            gap        : .55rem;
            min-height : 360px;
            max-height : 440px;
            overflow   : hidden;
        }

        .msg-wrap{display:flex;flex-direction:column}
        .msg-wrap.right{align-items:flex-end}
        .msg-wrap.left{align-items:flex-start}

        .msg-bubble{
            max-width     : 82%;
            padding       : .55rem .8rem;
            border-radius : 8px;
            font-size     : .82rem;
            line-height   : 1.5;
            box-shadow    : 0 1px 1px rgba(0,0,0,.06);
        }

        .msg-bubble.in{
            background    : white;
            color         : #111;
            border-radius : 0 8px 8px 8px;
        }

        .msg-bubble.out{
            background    : #D9FDD3;
            color         : #111;
            border-radius : 8px 8px 0 8px;
        }

        .msg-bubble.voice{
            display: flex; align-items: center; gap: .55rem;
            min-width: 180px;
        }

        .voice-play{
            width: 26px; height: 26px;
            background: var(--wa-dark);
            border-radius: 50%;
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: .7rem;
        }

        .voice-wave{
            flex: 1;
            display: flex; align-items: center; gap: 2px;
            height: 18px;
        }

        .voice-wave span{
            flex: 1;
            background: var(--wa-dark);
            border-radius: 2px;
            opacity: .6;
        }

        .voice-time{font-size: .65rem; color: var(--slate-500)}

        .msg-meta{
            font-size  : .65rem;
            color      : #888;
            margin-top : .2rem;
            display    : flex;
            align-items: center;
            gap        : .25rem;
        }

        .msg-meta.out-t{justify-content:flex-end;color:#8696A0}

        .ticks{color:#53BDEB;font-size:.7rem}

        .chat-typing{
            display     : flex;
            align-items : center;
            gap         : .3rem;
            padding     : .55rem .85rem;
            background  : white;
            border-radius: 0 8px 8px 8px;
            width       : 56px;
        }

        .typing-dot{
            width      : 6px;
            height     : 6px;
            background : var(--slate-400);
            border-radius:50%;
            animation  : typing .9s infinite;
        }

        .typing-dot:nth-child(2){animation-delay:.2s}
        .typing-dot:nth-child(3){animation-delay:.4s}

        @keyframes typing{
            0%,100%{transform:translateY(0);opacity:.4}
            50%{transform:translateY(-3px);opacity:1}
        }

        .chat-footer-bar{
            background  : var(--bone);
            padding     : .65rem 1rem;
            display     : flex;
            align-items : center;
            gap         : .5rem;
            border-top  : 1px solid var(--slate-200);
        }

        .chat-input-fake{
            flex          : 1;
            background    : var(--slate-100);
            border        : 1px solid var(--slate-200);
            border-radius : 99px;
            height        : 32px;
        }

        .chat-send{
            width          : 32px;
            height         : 32px;
            background     : var(--wa);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
        }

        .chat-send svg{width:16px;height:16px;fill:#fff}

        /* ────────────────────────────────────────────────────────────────
           COMPARISON — vs WeSpeak / Asksuite
        ──────────────────────────────────────────────────────────────── */
        .compare-section{
            background: var(--bone);
        }

        .compare-head{
            margin-bottom: 3rem;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 3rem;
            align-items: end;
        }

        @media(max-width: 800px){
            .compare-head{grid-template-columns: 1fr; gap: 1rem}
        }

        .compare-head .section-p{margin: 0; max-width: 480px}

        .compare-table-wrap{
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: var(--r2);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .compare-table{
            width: 100%;
            border-collapse: collapse;
            font-size: .9rem;
        }

        .compare-table thead th{
            text-align: left;
            padding: 1.1rem 1.25rem;
            font-size: .78rem;
            font-weight: 600;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--slate-200);
            background: var(--sand);
        }

        .compare-table thead th.tentii-col{
            background: var(--ink);
            color: var(--bone);
            font-family: var(--display);
            font-style: italic;
            font-size: .95rem;
            text-transform: none;
            letter-spacing: -0.01em;
            font-weight: 600;
        }

        .compare-table td{
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--slate-100);
            color: var(--ink-soft);
            vertical-align: middle;
        }

        .compare-table tr:last-child td{border-bottom: none}

        .compare-table td.feature{
            font-weight: 500;
            color: var(--ink);
            font-size: .88rem;
            width: 35%;
        }

        .compare-table td.tentii-col{
            background: rgba(194,65,12,.04);
            font-weight: 500;
        }

        .check-yes{
            color: var(--moss);
            font-weight: 700;
        }

        .check-no{
            color: var(--slate-400);
            font-weight: 400;
        }

        .check-partial{
            color: var(--amber);
            font-weight: 500;
        }

        .check-text{
            font-size: .8rem;
            color: var(--slate-500);
        }

        .compare-note{
            font-size: .75rem;
            color: var(--slate-400);
            text-align: center;
            margin-top: 1.25rem;
            font-style: italic;
            font-family: var(--display);
        }

        /* ────────────────────────────────────────────────────────────────
           DIFFERENTIATORS — ¿Por qué Tentii?
        ──────────────────────────────────────────────────────────────── */
        .diff-section{
            background: var(--moss-dark);
            color: var(--bone);
            position: relative;
            overflow: hidden;
        }

        .diff-section::before{
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                    linear-gradient(rgba(255,255,255,.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        .diff-inner{
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
        }

        .diff-head{
            text-align: center;
            margin-bottom: 3.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .diff-head .eyebrow{color: var(--terra-mid)}
        .diff-head .eyebrow::before{background: var(--terra-mid)}
        .diff-head .section-h{color: var(--bone)}
        .diff-head .section-h em{color: var(--terra-mid); font-style: italic}
        .diff-head .section-p{color: rgba(252,250,245,.7); margin: 0 auto}

        .diff-grid{
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        @media(max-width: 800px){
            .diff-grid{grid-template-columns: 1fr}
        }

        .diff-card{
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: var(--r2);
            padding: 1.85rem 1.5rem;
            backdrop-filter: blur(8px);
        }

        .diff-card-num{
            font-family: var(--display);
            font-style: italic;
            font-size: 2.5rem;
            font-weight: 400;
            color: var(--terra-mid);
            line-height: 1;
            margin-bottom: 1rem;
            opacity: .7;
        }

        .diff-card h3{
            font-family: var(--display);
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--bone);
            margin-bottom: .75rem;
            line-height: 1.25;
        }

        .diff-card p{
            font-size: .88rem;
            color: rgba(252,250,245,.65);
            line-height: 1.65;
        }

        /* ────────────────────────────────────────────────────────────────
           SECTORS — ¿Para quién?
        ──────────────────────────────────────────────────────────────── */
        .sectors-section{background: var(--bone)}

        .sectors-head{
            text-align: center;
            margin-bottom: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sectors-head .section-p{margin: 0 auto}

        .sectors-grid{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .sector-card{
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: var(--r2);
            padding: 1.5rem;
            text-align: left;
            transition: all .25s;
            position: relative;
            overflow: hidden;
        }

        .sector-card:hover{
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--terra-mid);
        }

        .sector-card::before{
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--terra);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .3s;
        }

        .sector-card:hover::before{transform: scaleX(1)}

        .sector-icon{
            font-size: 1.6rem;
            margin-bottom: .85rem;
            display: block;
        }

        .sector-card h3{
            font-family: var(--display);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .4rem;
        }

        .sector-card p{
            font-size: .82rem;
            color: var(--slate-500);
            line-height: 1.55;
        }

        /* ────────────────────────────────────────────────────────────────
           PRICING
        ──────────────────────────────────────────────────────────────── */
        .pricing-section{
            background: linear-gradient(180deg, var(--sand) 0%, var(--bone) 100%);
        }

        .pricing-head{
            text-align: center;
            margin-bottom: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .pricing-head .section-p{margin: 0 auto}

        .pricing-grid{
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            max-width: 900px;
            margin: 0 auto;
        }

        @media(max-width: 720px){
            .pricing-grid{grid-template-columns: 1fr}
        }

        .price-card{
            background: white;
            border: 1px solid var(--slate-200);
            border-radius: var(--r3);
            padding: 2.25rem 2rem;
            position: relative;
            transition: all .25s;
            display: flex;
            flex-direction: column;
        }

        .price-card.featured{
            background: var(--ink);
            color: var(--bone);
            border-color: var(--ink);
            box-shadow: var(--shadow-lg);
        }

        .price-tag{
            position: absolute;
            top: -12px;
            right: 24px;
            background: var(--terra);
            color: white;
            padding: .25rem .8rem;
            border-radius: 99px;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .price-name{
            font-family: var(--display);
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .35rem;
        }

        .price-card.featured .price-name{color: var(--bone)}

        .price-desc{
            font-size: .85rem;
            color: var(--slate-500);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .price-card.featured .price-desc{color: rgba(252,250,245,.65)}

        .price-amount{
            display: flex;
            align-items: baseline;
            gap: .35rem;
            margin-bottom: .25rem;
        }

        .price-amount .currency{
            font-size: 1.1rem;
            color: var(--slate-500);
            font-weight: 500;
        }

        .price-card.featured .price-amount .currency{color: rgba(252,250,245,.65)}

        .price-amount .number{
            font-family: var(--display);
            font-size: 3.25rem;
            font-weight: 600;
            color: var(--ink);
            line-height: 1;
            letter-spacing: -0.025em;
        }

        .price-card.featured .price-amount .number{color: var(--terra-mid)}

        .price-amount .period{
            font-size: .9rem;
            color: var(--slate-500);
        }

        .price-card.featured .price-amount .period{color: rgba(252,250,245,.65)}

        .price-note{
            font-size: .75rem;
            color: var(--slate-400);
            margin-bottom: 1.85rem;
            font-style: italic;
            font-family: var(--display);
        }

        .price-card.featured .price-note{color: rgba(252,250,245,.55)}

        .price-feats{
            list-style: none;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: .65rem;
            flex: 1;
        }

        .price-feats li{
            font-size: .87rem;
            color: var(--ink-soft);
            display: flex;
            align-items: flex-start;
            gap: .65rem;
            line-height: 1.5;
        }

        .price-card.featured .price-feats li{color: rgba(252,250,245,.85)}

        .price-feats li::before{
            content: '✓';
            color: var(--moss);
            font-weight: 700;
            flex-shrink: 0;
        }

        .price-card.featured .price-feats li::before{color: var(--terra-mid)}

        .price-feats li.muted{color: var(--slate-400); font-style: italic}
        .price-feats li.muted::before{content: '−'; color: var(--slate-400)}

        .price-btn{
            width: 100%;
            padding: .85rem 1.5rem;
            border-radius: var(--r);
            font-weight: 600;
            font-size: .9rem;
            border: 1.5px solid var(--ink);
            background: white;
            color: var(--ink);
            cursor: pointer;
            transition: all .2s;
            text-align: center;
            display: block;
            font-family: var(--sans);
        }

        .price-btn:hover{background: var(--ink); color: var(--bone)}

        .price-card.featured .price-btn{
            background: var(--terra);
            color: white;
            border-color: var(--terra);
        }

        .price-card.featured .price-btn:hover{background: var(--terra-dark); border-color: var(--terra-dark)}

        /* ────────────────────────────────────────────────────────────────
           FAQ
        ──────────────────────────────────────────────────────────────── */
        .faq-section{background: var(--bone)}

        .faq-grid{
            max-width: 760px;
            margin: 0 auto;
        }

        .faq-head{
            text-align: center;
            margin-bottom: 2.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        details.faq-item{
            border-bottom: 1px solid var(--slate-200);
            padding: 1.25rem 0;
        }

        details.faq-item summary{
            cursor: pointer;
            font-family: var(--display);
            font-weight: 500;
            font-size: 1.05rem;
            color: var(--ink);
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            line-height: 1.4;
        }

        details.faq-item summary::-webkit-details-marker{display:none}

        details.faq-item summary::after{
            content: '+';
            font-size: 1.4rem;
            color: var(--terra);
            transition: transform .25s;
            font-family: var(--sans);
            font-weight: 300;
            flex-shrink: 0;
        }

        details.faq-item[open] summary::after{transform: rotate(45deg)}

        details.faq-item p{
            margin-top: .85rem;
            font-size: .92rem;
            color: var(--slate-500);
            line-height: 1.7;
        }

        /* ────────────────────────────────────────────────────────────────
           REGISTRO
        ──────────────────────────────────────────────────────────────── */
        .register-section{
            background: var(--ink);
            color: var(--bone);
            position: relative;
            overflow: hidden;
        }

        .register-section::before{
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            width: 800px; height: 800px;
            background: radial-gradient(circle, rgba(194,65,12,.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .reg-grid{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            align-items: start;
        }

        @media(max-width: 880px){
            .reg-grid{grid-template-columns: 1fr; gap: 2.5rem}
        }

        .reg-left .eyebrow{color: var(--terra-mid)}
        .reg-left .eyebrow::before{background: var(--terra-mid)}
        .reg-left h2{
            font-family: var(--display);
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 500;
            color: var(--bone);
            line-height: 1.1;
            letter-spacing: -0.025em;
            margin-bottom: 1.25rem;
        }

        .reg-left h2 em{font-style: italic; color: var(--terra-mid)}

        .reg-left p{
            color: rgba(252,250,245,.7);
            font-size: 1rem;
            line-height: 1.65;
            margin-bottom: 1.75rem;
        }

        .reg-bullets{
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .85rem;
        }

        .reg-bullets li{
            font-size: .9rem;
            color: rgba(252,250,245,.85);
            display: flex;
            align-items: flex-start;
            gap: .7rem;
        }

        .reg-bullets li::before{
            content: '';
            width: 18px; height: 18px;
            background: var(--terra);
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: .15rem;
            position: relative;
        }

        .reg-form-card{
            background: var(--bone);
            color: var(--ink);
            border-radius: var(--r3);
            padding: 2.25rem;
            box-shadow: var(--shadow-lg);
        }

        .reg-form-card h3{
            font-family: var(--display);
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .35rem;
        }

        .reg-form-card .reg-sub{
            font-size: .85rem;
            color: var(--slate-500);
            margin-bottom: 1.75rem;
        }

        .form-row{
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            margin-bottom: .85rem;
        }

        @media(max-width: 480px){
            .form-row{grid-template-columns: 1fr}
        }

        .form-group{margin-bottom: .85rem}

        .form-group label{
            display: block;
            font-size: .75rem;
            font-weight: 500;
            color: var(--slate-700);
            margin-bottom: .35rem;
            letter-spacing: .02em;
        }

        .form-group input{
            width: 100%;
            padding: .7rem .85rem;
            border: 1.5px solid var(--slate-200);
            border-radius: var(--r);
            font-size: .9rem;
            font-family: var(--sans);
            background: white;
            color: var(--ink);
            transition: border-color .15s, box-shadow .15s;
        }

        .form-group input:focus{
            outline: none;
            border-color: var(--terra);
            box-shadow: 0 0 0 3px rgba(194,65,12,.1);
        }

        .terms-row{
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            margin: 1rem 0 1.4rem;
            font-size: .8rem;
            color: var(--slate-500);
            line-height: 1.5;
        }

        .terms-row input[type=checkbox]{
            width: 16px;height:16px;
            margin-top: .15rem;
            accent-color: var(--terra);
        }

        .terms-row a{color: var(--terra); text-decoration: underline}

        .form-error{
            display: none;
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
            padding: .65rem .9rem;
            border-radius: var(--r);
            font-size: .82rem;
            margin-bottom: 1rem;
        }

        .btn-submit{
            width: 100%;
            padding: .95rem 1.5rem;
            background: var(--ink);
            color: var(--bone);
            border: none;
            border-radius: var(--r);
            font-weight: 600;
            font-size: .95rem;
            cursor: pointer;
            transition: background .2s;
            font-family: var(--sans);
        }

        .btn-submit:hover{background: var(--terra)}
        .btn-submit:disabled{opacity:.6; cursor: not-allowed}

        .form-footer{
            font-size: .75rem;
            color: var(--slate-400);
            text-align: center;
            margin-top: 1rem;
        }

        .form-footer a{color: var(--terra); font-weight: 500}

        .success-wrap{
            display: none;
            text-align: center;
            padding: 1.5rem 0;
        }

        .success-icon{
            width: 64px; height: 64px;
            background: var(--moss-light);
            color: var(--moss);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.75rem;
        }

        .success-wrap h3{
            font-family: var(--display);
            font-size: 1.4rem;
            color: var(--ink);
            margin-bottom: .65rem;
        }

        .success-wrap p{
            font-size: .9rem;
            color: var(--slate-500);
            line-height: 1.6;
        }

        /* ────────────────────────────────────────────────────────────────
           FINAL CTA
        ──────────────────────────────────────────────────────────────── */
        .final-cta{
            background: var(--bone);
            text-align: center;
            padding-top: clamp(4rem, 7vw, 6rem);
            padding-bottom: clamp(4rem, 7vw, 6rem);
        }

        .final-cta h2{
            font-family: var(--display);
            font-size: clamp(1.85rem, 3.5vw, 3rem);
            font-weight: 500;
            color: var(--ink);
            line-height: 1.1;
            letter-spacing: -0.025em;
            max-width: 720px;
            margin: 0 auto 1.25rem;
        }

        .final-cta h2 em{
            font-style: italic;
            color: var(--terra);
        }

        .final-cta p{
            color: var(--slate-500);
            font-size: 1.05rem;
            max-width: 520px;
            margin: 0 auto 2rem;
            line-height: 1.65;
        }

        .final-btns{
            display: flex;
            justify-content: center;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }

        .final-note{
            font-size: .8rem;
            color: var(--slate-400);
        }

        .final-note .sep{color: var(--slate-200)}
        .final-note strong{color: var(--ink-soft); font-weight: 500}

        /* ────────────────────────────────────────────────────────────────
           FOOTER
        ──────────────────────────────────────────────────────────────── */
        footer{
            background: var(--ink);
            color: rgba(252,250,245,.55);
            padding: 2.5rem clamp(1.25rem,4vw,2.5rem);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            font-size: .82rem;
        }

        .footer-logo{
            font-family: var(--display);
            font-style: italic;
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--bone);
            display: flex;
            align-items: baseline;
            gap: .15rem;
        }

        .footer-logo-dot{
            width: 6px; height: 6px;
            background: var(--terra);
            border-radius: 50%;
            transform: translateY(-1px);
        }

        .footer-links{
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a:hover{color: var(--bone)}

        /* WhatsApp flotante */
        .wa-float{
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px; height: 56px;
            background: var(--wa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(37,211,102,.4);
            z-index: 100;
            transition: transform .2s;
        }

        .wa-float:hover{transform: scale(1.05)}

        .wa-float svg{width: 28px; height: 28px}

        /* ────────────────────────────────────────────────────────────────
           REVEAL ANIMATION
        ──────────────────────────────────────────────────────────────── */
        .reveal{
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .7s ease, transform .7s ease;
        }

        .reveal.visible{
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     NAV
══════════════════════════════════════════════════════════ -->
<nav>
    <a href="#" class="nav-logo">
        Tentii<span class="nav-logo-dot"></span>
    </a>

    <div class="nav-links">
        <a href="#producto">Producto</a>
        <a href="#comparacion">Comparación</a>
        <a href="#sectores">Para quién</a>
        <a href="#precios">Precios</a>
        <a href="#faq">FAQ</a>
    </div>

    <div class="nav-actions">
        <a href="/login" class="btn-login">Entrar</a>
        <a href="#registro" class="btn-nav-cta">Probar gratis</a>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-text reveal">
            <span class="hero-badge">
                <span class="hero-badge-dot"></span>
                PMS nativo en la era de la IA
            </span>

            <h1>
                El sistema que <em>sí</em> entiende cómo opera tu <span class="underline">alojamiento</span> hoy.
            </h1>

            <p class="hero-sub">
                Tentii es el PMS construido desde cero con IA en el corazón. Gestiona reservas, tarifas, tours, pagos y un asistente en WhatsApp que vende por ti — todo en una sola plataforma. Sin chatbots pegados con cinta. Sin comisiones por reserva.
            </p>

            <div class="hero-btns">
                <a href="#registro" class="btn-primary">
                    Empezar gratis
                    <span class="arrow">→</span>
                </a>
                <a href="#producto" class="btn-secondary">Ver cómo funciona</a>
            </div>

            <div class="hero-note">
                <span><strong>14 días gratis</strong></span>
                <span class="sep">·</span>
                <span>Sin tarjeta</span>
                <span class="sep">·</span>
                <span>Setup en menos de un día</span>
            </div>
        </div>

        <div class="hero-visual reveal">
            <!-- Dashboard mockup -->
            <div class="hv-card dashboard">
                <div class="hv-dash-header">
                    <div class="hv-dash-dots"><span></span><span></span><span></span></div>
                    <div class="hv-dash-title">tentii.app · Glamping Vista Verde</div>
                </div>
                <div class="hv-dash-body">
                    <div class="hv-stats">
                        <div class="hv-stat">
                            <div class="hv-stat-l">Ocupación hoy</div>
                            <div class="hv-stat-n">87%<span class="up">↑12</span></div>
                        </div>
                        <div class="hv-stat">
                            <div class="hv-stat-l">Reservas IA</div>
                            <div class="hv-stat-n">14<span class="up">↑6</span></div>
                        </div>
                        <div class="hv-stat">
                            <div class="hv-stat-l">Tours hoy</div>
                            <div class="hv-stat-n">3</div>
                        </div>
                    </div>
                    <div class="hv-cal">
                        <div class="hv-cal-h">
                            Calendario · Abril 26 <span>L · M · X · J · V · S · D</span>
                        </div>
                        <div class="hv-cal-grid">
                            <div class="hv-cal-cell label">Domo 1</div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell confirmed"></div>
                            <div class="hv-cal-cell confirmed"></div>
                            <div class="hv-cal-cell confirmed"></div>

                            <div class="hv-cal-cell label">Domo 2</div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell confirmed"></div>

                            <div class="hv-cal-cell label">Cabaña</div>
                            <div class="hv-cal-cell maint"></div>
                            <div class="hv-cal-cell free"></div>
                            <div class="hv-cal-cell confirmed"></div>
                            <div class="hv-cal-cell confirmed"></div>
                            <div class="hv-cal-cell confirmed"></div>
                            <div class="hv-cal-cell booked"></div>
                            <div class="hv-cal-cell booked"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp mockup pequeño -->
            <div class="hv-card whatsapp">
                <div class="hv-wa-header">
                    <div class="hv-wa-avatar">VV</div>
                    <div>
                        <div class="hv-wa-name">Vista Verde · IA</div>
                        <div class="hv-wa-status">en línea · responde por ti</div>
                    </div>
                </div>
                <div class="hv-wa-body">
                    <div class="hv-wa-msg in">¿Tienen disponible el domo para este finde?</div>
                    <div class="hv-wa-msg out">¡Sí! Para 2 noches el domo Origen sale $720.000 con desayuno 🌿</div>
                    <div class="hv-wa-msg in">Perfecto, ¿cómo reservo?</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     PROOF STRIP
══════════════════════════════════════════════════════════ -->
<div class="proof-strip">
    <div class="proof-inner">
        <div class="proof-label">Diseñado para</div>
        <div class="proof-types">
            <div class="proof-type">
                <span class="proof-type-icon">🏡</span>
                Hoteles boutique
            </div>
            <div class="proof-type">
                <span class="proof-type-icon">⛺</span>
                Glamping
            </div>
            <div class="proof-type">
                <span class="proof-type-icon">🌲</span>
                Cabañas
            </div>
            <div class="proof-type">
                <span class="proof-type-icon">🥾</span>
                Tours y actividades
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PAIN POINTS — los dolores reales
══════════════════════════════════════════════════════════ -->
<section class="pain-section" id="dolor">
    <div class="container">
        <div class="pain-head reveal">
            <span class="eyebrow">El problema</span>
            <h2 class="section-h">Tu día empieza con un Excel <em>y termina con culpa</em>.</h2>
            <p class="section-p">Si manejas un alojamiento boutique, glamping, cabañas o tours, sabes que los problemas no aparecen en los pitch decks de los grandes PMS. Esto es lo que pasa de verdad.</p>
        </div>

        <div class="pain-grid">
            <div class="pain-card reveal">
                <div class="pain-card-icon">📵</div>
                <h3>Mensajes a las 11 de la noche que nadie respondió</h3>
                <p>El cliente preguntó disponibilidad, no le respondiste a tiempo, reservó en el competidor de al lado. Pasa todos los días. No es falta de ganas, es falta de horas.</p>
                <div class="quote">"Si no contesto en 5 minutos, ya reservaron en otro lado."</div>
            </div>

            <div class="pain-card reveal">
                <div class="pain-card-icon">📊</div>
                <h3>Tres Excels, un cuaderno y un grupo de WhatsApp</h3>
                <p>El calendario de reservas en una hoja, los pagos en otra, los tours en un cuaderno, y el equipo coordina por chat. Un cobro se pierde, una habitación queda doble-reservada, y nadie sabe qué pasó.</p>
            </div>

            <div class="pain-card reveal">
                <div class="pain-card-icon">💸</div>
                <h3>Los PMS de siempre cuestan como si fueras un Hilton</h3>
                <p>Cloud-based, módulos por separado, $200+ al mes solo el PMS, otros $200 el chatbot, comisiones del 3-5% sobre cada reserva. Te dicen que es "el estándar de la industria". Lo es… para hoteles de 200 habitaciones.</p>
            </div>

            <div class="pain-card reveal">
                <div class="pain-card-icon">🧩</div>
                <h3>Software que no entiende lo que vendes</h3>
                <p>Tu cabaña tiene tres habitaciones que también se alquilan por separado. Vendes el alojamiento y el tour al cañón. Tu temporada alta es Semana Santa, no junio. Ningún software hecho en otro continente entiende eso.</p>
            </div>

            <div class="pain-card reveal">
                <div class="pain-card-icon">🎫</div>
                <h3>Vendes tours pero los gestionas en Notion</h3>
                <p>Cupos, manifiestos de pasajeros, comisiones de guías, pagos parciales. Si además vendes el tour al huésped del hotel, fundir las dos cuentas es un dolor de cabeza manual cada noche.</p>
            </div>

            <div class="pain-card reveal">
                <div class="pain-card-icon">🤖</div>
                <h3>Compraste un chatbot y sigues operando igual</h3>
                <p>El chatbot responde, pero después tienes que copiar la reserva al PMS. Verificar el comprobante de pago a mano. Mover la disponibilidad. El "AI" es solo otra app más en tu pila — no resuelve la operación, la duplica.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     SOLUTION — qué es Tentii
══════════════════════════════════════════════════════════ -->
<section class="solution" id="producto">
    <div class="solution-inner">
        <div class="solution-head reveal">
            <span class="eyebrow">La diferencia</span>
            <h2 class="section-h">Tentii no es un chatbot con un PMS al lado. <em>Es un PMS con IA por dentro.</em></h2>
            <p class="section-p">Cuatro módulos integrados que comparten datos en tiempo real. La IA no es un add-on que vendieron como feature — es la lógica que mueve el sistema desde el primer minuto.</p>
        </div>

        <div class="pillars reveal">
            <div class="pillar">
                <div class="pillar-num">— 01</div>
                <h3>Gestión completa de alojamiento</h3>
                <p>El ciclo de reserva entero: disponibilidad, confirmación, check-in, check-out, cancelación. Soporta unidades con jerarquías (cabañas con habitaciones independientes), tarifas dinámicas con personas extra, niños y temporadas, y estados en tiempo real.</p>
                <ul class="pillar-feats">
                    <li>Máquina de estados real, no campos sueltos</li>
                    <li>Cálculo de precios día por día con modificadores</li>
                    <li>Calendario unificado de todas tus unidades</li>
                </ul>
            </div>

            <div class="pillar">
                <div class="pillar-num">— 02</div>
                <h3>Módulo de tours y actividades</h3>
                <p>Lo que ningún otro PMS tiene de verdad. Crea tours con dificultad, punto de encuentro, precios y salidas programadas. Manifiesto de pasajeros, cupos en tiempo real, y vinculación al folio del huésped si vendes ambos servicios.</p>
                <ul class="pillar-feats">
                    <li>Salidas con guía asignado y precios diferenciados</li>
                    <li>Comisiones automáticas para guías y agentes</li>
                    <li>Folio unificado: alojamiento + tour</li>
                </ul>
            </div>

            <div class="pillar">
                <div class="pillar-num">— 03</div>
                <h3>Asistente IA conectado a WhatsApp</h3>
                <p>Conoce todo tu catálogo, consulta disponibilidad y precios en tiempo real, crea reservas y guía al cliente por un funnel estructurado. Procesa notas de voz, lee comprobantes de pago con OCR y los registra en el folio. Si necesita un humano, te avisa y se desactiva.</p>
                <ul class="pillar-feats">
                    <li>Transcripción automática de notas de voz</li>
                    <li>OCR de comprobantes con Gemini Vision</li>
                    <li>Escalado inteligente a humano cuando hace falta</li>
                </ul>
            </div>

            <div class="pillar">
                <div class="pillar-num">— 04</div>
                <h3>Finanzas, comisiones y multi-tenant</h3>
                <p>Pagos parciales, saldos, múltiples métodos. Comisiones automáticas para agentes y guías (tarifa fija, por pasajero, sobre venta o mixto). Cada negocio opera aislado: zona horaria, moneda, políticas, prompt de IA y perfil propio.</p>
                <ul class="pillar-feats">
                    <li>Comisiones generadas al cerrar cada salida</li>
                    <li>Aislamiento total entre tenants</li>
                    <li>Configuración del asistente IA por negocio</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     WHATSAPP DEMO
══════════════════════════════════════════════════════════ -->
<section class="wa-section">
    <div class="container">
        <div class="wa-grid">
            <div class="wa-text reveal">
                <span class="eyebrow moss">WhatsApp · 24/7</span>
                <h2 class="section-h">Un asistente que <em>vende</em>, no solo responde.</h2>
                <p class="section-p">El asistente de Tentii no es un FAQ disfrazado. Cotiza con tus tarifas reales, bloquea la unidad cuando confirma, registra el comprobante en el folio del huésped, y si la conversación se complica te llama a ti.</p>

                <div class="wa-feat-list">
                    <div class="wa-feat">
                        <div class="wa-feat-icon">⚡</div>
                        <div class="wa-feat-text">
                            <strong>Responde en menos de 20 segundos, en cualquier idioma</strong>
                            <span>A las 3am, en domingo, en tu cumpleaños. Nunca pierde un lead por horario.</span>
                        </div>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-feat-icon">🎙️</div>
                        <div class="wa-feat-text">
                            <strong>Entiende notas de voz y comprobantes de pago</strong>
                            <span>El cliente manda un audio o una foto del Bancolombia, Tentii lo transcribe, lo lee y lo registra solo.</span>
                        </div>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-feat-icon">🧠</div>
                        <div class="wa-feat-text">
                            <strong>Conoce tu negocio de verdad</strong>
                            <span>Personalizas su prompt, cargas tus políticas, tarifas y tono. No es un bot genérico — es tu recepcionista virtual.</span>
                        </div>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-feat-icon">🤝</div>
                        <div class="wa-feat-text">
                            <strong>Sabe cuándo callarse</strong>
                            <span>Detecta cuando hace falta un humano, te escala la conversación, y se desactiva solo. Cero respuestas raras.</span>
                        </div>
                    </div>
                </div>

                <a href="#registro" class="btn-primary">Probarlo en mi WhatsApp <span class="arrow">→</span></a>
            </div>

            <div class="wa-mockup reveal">
                <div class="chat-phone">
                    <div class="chat-ph-header">
                        <div class="chat-avatar">VV</div>
                        <div>
                            <div class="chat-name">Vista Verde Glamping</div>
                            <div class="chat-status"><span class="status-dot"></span> Asistente IA · en línea</div>
                        </div>
                    </div>

                    <div class="chat-body" id="chatBody">
                        <div class="msg-wrap right">
                            <div class="msg-bubble in">¿Hola! Tienen domos para 2 personas el viernes 8?</div>
                            <div class="msg-meta">10:24 am</div>
                        </div>

                        <div class="msg-wrap left">
                            <div class="msg-bubble out">¡Hola! 👋 Sí, tenemos disponibilidad. Para 2 personas en el <strong>Domo Origen</strong> con vista al valle, son <strong>$720.000</strong> total por las 2 noches incluyendo desayuno 🌿</div>
                            <div class="msg-meta out-t">10:24 am <span class="ticks">✓✓</span> Respondido por IA</div>
                        </div>

                        <div class="msg-wrap right">
                            <div class="msg-bubble in voice">
                                <div class="voice-play">▶</div>
                                <div class="voice-wave">
                                    <span style="height:40%"></span><span style="height:70%"></span>
                                    <span style="height:90%"></span><span style="height:50%"></span>
                                    <span style="height:80%"></span><span style="height:60%"></span>
                                    <span style="height:75%"></span><span style="height:45%"></span>
                                </div>
                                <div class="voice-time">0:08</div>
                            </div>
                            <div class="msg-meta">10:25 am · "¿incluye desayuno?"</div>
                        </div>

                        <div class="msg-wrap left">
                            <div class="chat-typing">
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                            </div>
                        </div>
                    </div>

                    <div class="chat-footer-bar">
                        <div class="chat-input-fake"></div>
                        <div class="chat-send">
                            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     COMPARISON TABLE
══════════════════════════════════════════════════════════ -->
<section class="compare-section" id="comparacion">
    <div class="container">
        <div class="compare-head reveal">
            <div>
                <span class="eyebrow">Comparación honesta</span>
                <h2 class="section-h">Tentii vs <em>los chatbots</em>.</h2>
            </div>
            <p class="section-p">WeSpeak y Asksuite son buenos productos — son chatbots especializados en hotelería. Pero son solo eso: chatbots. Tu PMS, tu calendario, tus tours, tu folio, tus comisiones, tu equipo… los sigues manejando aparte. Tentii integra todo.</p>
        </div>

        <div class="compare-table-wrap reveal">
            <table class="compare-table">
                <thead>
                <tr>
                    <th>Funcionalidad</th>
                    <th class="tentii-col">Tentii</th>
                    <th>WeSpeak</th>
                    <th>Asksuite</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="feature">PMS completo (reservas, calendario, check-in/out)</td>
                    <td class="tentii-col"><span class="check-yes">✓ Incluido</span></td>
                    <td><span class="check-no">✗ No es PMS</span></td>
                    <td><span class="check-no">✗ No es PMS</span></td>
                </tr>
                <tr>
                    <td class="feature">Asistente IA en WhatsApp 24/7</td>
                    <td class="tentii-col"><span class="check-yes">✓</span></td>
                    <td><span class="check-yes">✓</span></td>
                    <td><span class="check-yes">✓</span></td>
                </tr>
                <tr>
                    <td class="feature">Módulo de tours y actividades</td>
                    <td class="tentii-col"><span class="check-yes">✓ Nativo</span></td>
                    <td><span class="check-no">✗</span></td>
                    <td><span class="check-no">✗</span></td>
                </tr>
                <tr>
                    <td class="feature">Folio unificado alojamiento + tour</td>
                    <td class="tentii-col"><span class="check-yes">✓</span></td>
                    <td><span class="check-no">✗</span></td>
                    <td><span class="check-no">✗</span></td>
                </tr>
                <tr>
                    <td class="feature">Comisiones automáticas (agentes y guías)</td>
                    <td class="tentii-col"><span class="check-yes">✓ 4 modelos</span></td>
                    <td><span class="check-no">✗</span></td>
                    <td><span class="check-no">✗</span></td>
                </tr>
                <tr>
                    <td class="feature">OCR de comprobantes de pago</td>
                    <td class="tentii-col"><span class="check-yes">✓ Gemini Vision</span></td>
                    <td><span class="check-partial">Parcial</span></td>
                    <td><span class="check-no">✗</span></td>
                </tr>
                <tr>
                    <td class="feature">Precios dinámicos por temporada y día</td>
                    <td class="tentii-col"><span class="check-yes">✓ Modificadores día a día</span></td>
                    <td><span class="check-partial">Vía PMS externo</span></td>
                    <td><span class="check-partial">Vía PMS externo</span></td>
                </tr>
                <tr>
                    <td class="feature">Comisión por reserva</td>
                    <td class="tentii-col"><span class="check-yes">0%</span></td>
                    <td><span class="check-text">Plan + fees</span></td>
                    <td><span class="check-no">3–5%</span></td>
                </tr>
                <tr>
                    <td class="feature">Precio mensual base</td>
                    <td class="tentii-col"><span class="check-yes">USD 20</span></td>
                    <td><span class="check-text">Bajo cotización</span></td>
                    <td><span class="check-text">Desde USD 199</span></td>
                </tr>
                <tr>
                    <td class="feature">Necesitas también pagar un PMS aparte</td>
                    <td class="tentii-col"><span class="check-yes">No</span></td>
                    <td><span class="check-no">Sí</span></td>
                    <td><span class="check-no">Sí</span></td>
                </tr>
                </tbody>
            </table>
        </div>
        <p class="compare-note">Datos públicos de los sitios y prensa de los competidores a abril 2026.</p>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     DIFFERENTIATORS
══════════════════════════════════════════════════════════ -->
<section class="diff-section">
    <div class="diff-inner">
        <div class="diff-head reveal">
            <span class="eyebrow">Por qué Tentii</span>
            <h2 class="section-h">Construido por gente que <em>operó</em> alojamientos. No por un VC con un deck.</h2>
            <p class="section-p">Cada decisión del producto responde a un dolor real de operación. No a una matriz de features para ganar un Capterra.</p>
        </div>

        <div class="diff-grid">
            <div class="diff-card reveal">
                <div class="diff-card-num">01</div>
                <h3>Una sola plataforma, una sola factura</h3>
                <p>No pagas un PMS, un chatbot, un channel manager y un sistema de tours. Pagas Tentii. Y todo conversa entre sí porque vive en la misma base de datos.</p>
            </div>

            <div class="diff-card reveal">
                <div class="diff-card-num">02</div>
                <h3>Pensado para tu tamaño</h3>
                <p>Hoteles boutique de 6–40 habitaciones, glamping con 8 domos, cabañas familiares, operadores con 3 tours. No tendrás 200 features que nunca usarás ni pagarás como si fueras una cadena.</p>
            </div>

            <div class="diff-card reveal">
                <div class="diff-card-num">03</div>
                <h3>El único PMS con tours nativos</h3>
                <p>Si vendes alojamiento y experiencias (cañón, cabalgata, parapente, gastronómicos), Tentii es la única opción que une ambas cuentas. Sin parches.</p>
            </div>

            <div class="diff-card reveal">
                <div class="diff-card-num">04</div>
                <h3>IA personalizada, no un bot genérico</h3>
                <p>Tu prompt, tu tono, tu catálogo, tus políticas. El asistente habla como tú, no como un GPT envasado. Y aprende del comportamiento real de tus huéspedes.</p>
            </div>

            <div class="diff-card reveal">
                <div class="diff-card-num">05</div>
                <h3>Cero comisiones. De verdad.</h3>
                <p>Pagas tu mensualidad y listo. No te quitamos un porcentaje de cada reserva, no cobramos por reservas generadas por la IA, no hay letra chica. Tu venta es tuya.</p>
            </div>

            <div class="diff-card reveal">
                <div class="diff-card-num">06</div>
                <h3>Setup en un día, no en un trimestre</h3>
                <p>Cargas tus unidades, conectas tu WhatsApp Business, ajustas el prompt y operas. Sin consultores, sin onboarding de 3 meses, sin reuniones para configurar el módulo de impuestos.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     SECTORS
══════════════════════════════════════════════════════════ -->
<section class="sectors-section" id="sectores">
    <div class="container">
        <div class="sectors-head reveal">
            <span class="eyebrow amber">Para quién</span>
            <h2 class="section-h">Si tu negocio se ve así, <em>Tentii encaja</em>.</h2>
            <p class="section-p">No somos para Hilton ni para el Airbnb del primo. Somos para los negocios reales que sostienen el turismo en la región.</p>
        </div>

        <div class="sectors-grid">
            <div class="sector-card reveal">
                <span class="sector-icon">🏡</span>
                <h3>Hoteles boutique</h3>
                <p>De 6 a 40 habitaciones. Te importa el detalle, la marca y la atención personalizada. Necesitas software que no te despersonalice.</p>
            </div>

            <div class="sector-card reveal">
                <span class="sector-icon">⛺</span>
                <h3>Glamping</h3>
                <p>Domos, tipis, cabañas-tienda. Temporada concentrada, tarifas dinámicas, mucho WhatsApp y huéspedes que llegan con preguntas raras.</p>
            </div>

            <div class="sector-card reveal">
                <span class="sector-icon">🌲</span>
                <h3>Cabañas y fincas</h3>
                <p>Unidades enteras o por habitación. Cabaña con 3 cuartos que también se vende completa. Tarifas con extras, niños, estadías largas.</p>
            </div>

            <div class="sector-card reveal">
                <span class="sector-icon">🥾</span>
                <h3>Operadores de tours</h3>
                <p>Cañón, parapente, cabalgata, gastronómico, cultural. Salidas con cupos, guías, manifiestos y comisiones. Todo en un solo lugar.</p>
            </div>

            <div class="sector-card reveal">
                <span class="sector-icon">🌄</span>
                <h3>Hotel + tours</h3>
                <p>Si tu hotel también vende experiencias, Tentii une la cuenta del huésped. No más "súmenle el tour a la habitación 4" en una libreta.</p>
            </div>

            <div class="sector-card reveal">
                <span class="sector-icon">🌐</span>
                <h3>Multi-establecimiento</h3>
                <p>Tienes dos glampings o un hotel y una agencia. Cada uno con su tenant, sus tarifas, su moneda, su asistente IA. Aislados pero gestionados desde un solo equipo.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     PRICING
══════════════════════════════════════════════════════════ -->
<section class="pricing-section" id="precios">
    <div class="container">
        <div class="pricing-head reveal">
            <span class="eyebrow">Precios honestos</span>
            <h2 class="section-h">Dos planes. <em>Cero comisiones.</em></h2>
            <p class="section-p">Lo que pagas es lo que pagas. Si una reserva se cierra por la IA, es tuya. Si vendes 200 tours, son tuyos. Sin sorpresas, sin tarifas escalonadas, sin "cargo por uso".</p>
        </div>

        <div class="pricing-grid">

            <!-- Starter -->
            <div class="price-card reveal">
                <div class="price-name">Starter</div>
                <p class="price-desc">Para alojamientos pequeños o operadores que están empezando.</p>

                <div class="price-amount">
                    <span class="currency">USD</span>
                    <span class="number">20</span>
                    <span class="period">/mes</span>
                </div>
                <p class="price-note">Pago anual · O USD 25/mes pago mensual</p>

                <ul class="price-feats">
                    <li>Hasta 10 unidades de alojamiento</li>
                    <li>Calendario y reservas ilimitadas</li>
                    <li>Asistente IA en WhatsApp</li>
                    <li>OCR de comprobantes</li>
                    <li>Notas de voz transcritas</li>
                    <li>Pagos parciales y folio del huésped</li>
                    <li>Soporte por WhatsApp</li>
                    <li class="muted">Módulo de tours</li>
                    <li class="muted">Comisiones de agentes y guías</li>
                </ul>

                <a href="#registro" class="price-btn">Empezar con Starter</a>
            </div>

            <!-- Pro -->
            <div class="price-card featured reveal">
                <span class="price-tag">Más popular</span>
                <div class="price-name">Pro</div>
                <p class="price-desc">Para alojamientos que también venden tours, multi-establecimiento o con equipo.</p>

                <div class="price-amount">
                    <span class="currency">USD</span>
                    <span class="number">104</span>
                    <span class="period">/mes</span>
                </div>
                <p class="price-note">Pago anual · O USD 124/mes pago mensual</p>

                <ul class="price-feats">
                    <li>Unidades de alojamiento ilimitadas</li>
                    <li>Módulo completo de tours y actividades</li>
                    <li>Folio unificado alojamiento + tour</li>
                    <li>Manifiesto de pasajeros y cupos en tiempo real</li>
                    <li>Comisiones automáticas (agentes + guías, 4 modelos)</li>
                    <li>Multi-tenant: varios establecimientos</li>
                    <li>Prompt de IA personalizable por negocio</li>
                    <li>Roles y permisos para tu equipo</li>
                    <li>Soporte prioritario</li>
                </ul>

                <a href="#registro" class="price-btn">Empezar con Pro</a>
            </div>
        </div>

        <p style="text-align:center;margin-top:2rem;font-size:.85rem;color:var(--slate-500)">
            ¿Tienes una cadena con más de 5 propiedades? <a href="#registro" style="color:var(--terra);font-weight:500;text-decoration:underline">Hablemos de un plan a medida →</a>
        </p>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     FAQ
══════════════════════════════════════════════════════════ -->
<section class="faq-section" id="faq">
    <div class="container">
        <div class="faq-head reveal">
            <span class="eyebrow moss">Preguntas frecuentes</span>
            <h2 class="section-h">Lo que <em>todos</em> nos preguntan.</h2>
        </div>

        <div class="faq-grid">
            <details class="faq-item reveal">
                <summary>¿Cuánto tarda el setup?</summary>
                <p>Menos de un día. Cargas tus unidades (o las importas de un Excel), conectas tu número de WhatsApp Business, ajustas el prompt del asistente con tu tono y políticas, y ya estás operando. Tenemos guías paso a paso y soporte humano por chat.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Necesito tener WhatsApp Business API?</summary>
                <p>Te ayudamos a obtenerla en el proceso de setup. Tarda entre 24 y 72 horas y la gestionamos contigo. No necesitas conocimientos técnicos.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Funciona si solo vendo alojamiento (sin tours)?</summary>
                <p>Sí. Tentii detecta automáticamente el perfil de tu negocio y adapta el menú, el dashboard y el asistente IA. Si solo vendes alojamiento, no verás módulos de tours en tu pantalla. El plan Starter está pensado exactamente para eso.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Y si solo soy operador de tours?</summary>
                <p>También. El plan Pro es ideal para operadores turísticos puros: gestiona salidas, cupos, guías con sus modelos de pago, comisiones y manifiestos. La IA en WhatsApp atiende las consultas de tus tours igual que atendería las de un hotel.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Qué pasa con mis datos? ¿Es seguro?</summary>
                <p>Cada negocio (tenant) opera completamente aislado, con su propia configuración. Usamos la infraestructura cloud estándar de la industria, cifrado en tránsito y en reposo, y nunca compartimos datos entre tenants. Tus datos son tuyos.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Puedo cancelar cuando quiera?</summary>
                <p>Sí. Sin permanencia, sin letra chica. Cancelas y exportas todos tus datos en formato estándar. Si pagaste anual y cancelas a mitad, te devolvemos lo no consumido los primeros 30 días.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Cómo se compara con WeSpeak o Asksuite?</summary>
                <p>WeSpeak y Asksuite son chatbots de WhatsApp para hoteles — buenos en lo que hacen, pero no son PMS. Si los usas, sigues necesitando un PMS aparte (Cloudbeds, Sirvoy, etc.) más tu sistema de tours, más tu Excel de comisiones. Tentii reemplaza todo eso por una sola plataforma que además incluye el asistente IA. Y sin comisiones por reserva.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Qué idiomas habla la IA?</summary>
                <p>Más de 50, incluyendo español (con variantes regionales), inglés, portugués, francés, alemán e italiano. Detecta el idioma del huésped y le responde en el suyo automáticamente.</p>
            </details>

            <details class="faq-item reveal">
                <summary>¿Hay prueba gratuita?</summary>
                <p>Sí, 14 días sin tarjeta de crédito. Tienes acceso a todas las funciones del plan Pro durante la prueba. Si al final decides que no es para ti, no pagas nada.</p>
            </details>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     REGISTRO
══════════════════════════════════════════════════════════ -->
<section class="register-section" id="registro">
    <div class="reg-grid">
        <div class="reg-left reveal">
            <span class="eyebrow">Empezar</span>
            <h2>Tu primera reserva por IA <em>antes de la cena</em>.</h2>
            <p>Crea tu cuenta gratis, configura tu primera unidad en 10 minutos y conecta WhatsApp. Si no te convence, te ayudamos a migrar de vuelta sin drama.</p>

            <ul class="reg-bullets">
                <li>14 días gratis · sin tarjeta de crédito</li>
                <li>Acceso completo al plan Pro durante la prueba</li>
                <li>Migración de tu Excel/PMS actual incluida</li>
                <li>Soporte humano por WhatsApp en español</li>
                <li>Si cancelas, exportas todo en CSV</li>
            </ul>
        </div>

        <div class="reg-form-card reveal">
            <div id="formContent">
                <h3>Crear cuenta gratis</h3>
                <p class="reg-sub">Empieza tu prueba de 14 días. Sin compromisos.</p>

                <div id="formError" class="form-error"></div>

                <form id="registerForm" novalidate>
                    <div class="form-group">
                        <label for="hotel_name">Nombre del establecimiento</label>
                        <input type="text" id="hotel_name" name="hotel_name" placeholder="Ej. Glamping Vista Verde" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Tu nombre</label>
                            <input type="text" id="name" name="name" placeholder="María Pérez" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Ciudad</label>
                            <input type="text" id="city" name="city" placeholder="Bucaramanga" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="maria@vistaverde.co" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">WhatsApp</label>
                        <input type="tel" id="phone" name="phone" placeholder="+57 300 000 0000" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirmar</label>
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Repite" required>
                        </div>
                    </div>

                    <div class="terms-row">
                        <input type="checkbox" id="terms" name="terms">
                        <label for="terms">Acepto los <a href="/terminos" target="_blank">términos</a> y la <a href="/privacidad" target="_blank">política de privacidad</a>.</label>
                    </div>

                    <button type="submit" class="btn-submit" id="btnReg">
                        Crear cuenta y empezar gratis →
                    </button>

                    <p class="form-footer">
                        ¿Ya tienes cuenta? <a href="/login">Iniciar sesión</a>
                    </p>
                </form>
            </div>

            <div id="successWrap" class="success-wrap">
                <div class="success-icon">✓</div>
                <h3>¡Listo, ya estás dentro!</h3>
                <p>Te llevamos al panel. Allí cargas tu primera unidad y conectamos tu WhatsApp.</p>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     CTA FINAL
══════════════════════════════════════════════════════════ -->
<section class="final-cta">
    <h2>¿Cuántas reservas perdiste <em>esta semana</em> por no responder a tiempo?</h2>
    <p>Hoy mismo puedes tener un asistente IA respondiendo en tu WhatsApp y un PMS de verdad gestionando tu calendario. En 10 minutos.</p>
    <div class="final-btns">
        <a href="#registro" class="btn-primary">Empezar gratis <span class="arrow">→</span></a>
        <a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#25D366" style="margin-right:.1rem"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Hablar con el equipo
        </a>
    </div>
    <p class="final-note">
        <strong>14 días gratis</strong>
        <span class="sep">·</span>
        Sin tarjeta de crédito
        <span class="sep">·</span>
        Cancela cuando quieras
    </p>
</section>

<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer>
    <div class="footer-logo">
        Tentii<span class="footer-logo-dot"></span>
    </div>

    <div class="footer-links">
        <a href="/terminos">Términos</a>
        <a href="/privacidad">Privacidad</a>
        <a href="/login">Entrar</a>
    </div>

    <div class="footer-right">
        &copy; <?= date('Y') ?> Tentii · El PMS nativo en la era de la IA
    </div>
</footer>

<!-- WhatsApp flotante -->
<a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="wa-float" title="Hablar por WhatsApp">
    <svg viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<!-- ══════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════ -->
<script>
    (function(){

        /* ── Reveal on scroll ── */
        var revEls = document.querySelectorAll('.reveal');
        var obs    = new IntersectionObserver(function(entries){
            entries.forEach(function(e, i){
                if(e.isIntersecting){
                    setTimeout(function(){ e.target.classList.add('visible'); }, i * 60);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.07 });

        revEls.forEach(function(el){ obs.observe(el); });

        /* ── Chat typing animation loop ── */
        var typingEl = document.querySelector('.chat-typing');
        if(typingEl){
            var replies = [
                'Sí, el desayuno buffet está incluido para 2 personas 🍳 ¿Confirmamos? Solo necesito tu nombre y email para reservar el domo.',
                '¡Perfecto, María! Te envío link de pago seguro para los $720.000 👇'
            ];
            var step = 0;
            function showReply(){
                typingEl.style.display = 'flex';
                setTimeout(function(){
                    typingEl.style.display = 'none';
                    var wrap = document.createElement('div');
                    wrap.className = 'msg-wrap left';
                    var bubble = document.createElement('div');
                    bubble.className = 'msg-bubble out';
                    bubble.innerHTML = replies[step % replies.length];
                    var meta = document.createElement('div');
                    meta.className = 'msg-meta out-t';
                    meta.innerHTML = '10:25 am <span class="ticks">✓✓</span> Respondido por IA';
                    wrap.appendChild(bubble);
                    wrap.appendChild(meta);
                    typingEl.parentNode.insertBefore(wrap, typingEl);
                    step++;
                    if(step < 2){
                        setTimeout(showReply, 4500);
                    }
                }, 1800);
            }
            setTimeout(showReply, 2000);
        }

        /* ── Registro ── */
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

            if(!hotel)          return showError('El nombre del establecimiento es requerido.');
            if(!name)           return showError('Tu nombre es requerido.');
            if(!email)          return showError('El email es requerido.');
            if(!phone)          return showError('El WhatsApp es requerido.');
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