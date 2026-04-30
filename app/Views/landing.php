<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GuestHandle — PMS con IA para hoteles. WhatsApp 24/7, reservas automáticas, precios dinámicos y channel manager. Conectado en menos de 10 minutos.">
    <meta name="keywords" content="PMS hotelero, software hotel Colombia, WhatsApp hotel automatico, inteligencia artificial hotel, channel manager hotel">
    <meta property="og:title" content="GuestHandle — Tu hotel vende mientras duermes">
    <meta property="og:description" content="PMS + IA en WhatsApp 24/7. Reservas automáticas, precios inteligentes y cero consultas sin respuesta. Primer mes gratis.">
    <meta property="og:type" content="website">
    <title>GuestHandle — PMS + IA + WhatsApp 24/7 para Hoteles</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;1,400&display=swap" rel="stylesheet">

    <style>
        /* ────────────────────────────────────────────────────────────────
           RESET & VARIABLES
        ──────────────────────────────────────────────────────────────── */
        *{margin:0;padding:0;box-sizing:border-box}

        :root{
            --wa          : #25D366;
            --wa-dark     : #128C7E;
            --wa-light    : #DCFCE7;
            --blue        : #185FA5;
            --blue-light  : #E6F1FB;
            --blue-mid    : #378ADD;
            --blue-dark   : #0C447C;
            --teal        : #0F6E56;
            --teal-light  : #E1F5EE;
            --teal-mid    : #1D9E75;
            --navy        : #0F172A;
            --amber-light : #FEF9C3;
            --amber       : #F59E0B;
            --gray-50     : #F8FAFC;
            --gray-100    : #F1F5F9;
            --gray-200    : #E2E8F0;
            --gray-400    : #94A3B8;
            --gray-500    : #64748B;
            --gray-700    : #334155;
            --gray-900    : #0F172A;
            --white       : #FFFFFF;
            --serif       : 'Sora', sans-serif;
            --sans        : 'Plus Jakarta Sans', sans-serif;
            --r           : 10px;
            --r2          : 16px;
            --r3          : 24px;
            --shadow-sm   : 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
            --shadow-md   : 0 4px 16px rgba(15,23,42,.08), 0 2px 6px rgba(15,23,42,.05);
        }

        html{scroll-behavior:smooth}

        body{
            background  : var(--white);
            color       : var(--gray-900);
            font-family : var(--sans);
            font-size   : 16px;
            line-height : 1.65;
            overflow-x  : hidden;
        }

        a{text-decoration:none;color:inherit}
        img{display:block;max-width:100%}

        /* ────────────────────────────────────────────────────────────────
           NAVEGACIÓN
        ──────────────────────────────────────────────────────────────── */
        nav{
            background      : rgba(255,255,255,0.97);
            border-bottom   : 1px solid var(--gray-100);
            padding         : 0 clamp(1.25rem,4vw,2.5rem);
            display         : flex;
            justify-content : space-between;
            align-items     : center;
            height          : 64px;
            position        : sticky;
            top             : 0;
            z-index         : 200;
            backdrop-filter : blur(12px);
        }

        .nav-logo{
            font-family    : var(--serif);
            font-size      : 1.25rem;
            font-weight    : 700;
            color          : var(--navy);
            letter-spacing : -0.02em;
            display        : flex;
            align-items    : center;
            gap            : .45rem;
        }

        .nav-logo-dot{
            width         : 8px;
            height        : 8px;
            background    : var(--wa);
            border-radius : 50%;
            display       : inline-block;
        }

        .nav-links{
            display     : flex;
            align-items : center;
            gap         : 2rem;
        }

        .nav-links a{
            font-size  : .85rem;
            color      : var(--gray-500);
            font-weight: 400;
            transition : color .15s;
        }

        .nav-links a:hover{color:var(--navy)}

        .nav-actions{
            display     : flex;
            align-items : center;
            gap         : .65rem;
        }

        .btn-login{
            font-size    : .85rem;
            color        : var(--blue);
            font-weight  : 500;
            padding      : .45rem 1rem;
            border       : 1.5px solid var(--blue-light);
            border-radius: var(--r);
            transition   : all .15s;
        }

        .btn-login:hover{background:var(--blue-light)}

        .btn-nav-cta{
            font-size     : .85rem;
            background    : var(--wa);
            color         : var(--white);
            font-weight   : 600;
            padding       : .5rem 1.1rem;
            border-radius : var(--r);
            transition    : all .15s;
            border        : none;
            cursor        : pointer;
            display       : inline-block;
        }

        .btn-nav-cta:hover{background:var(--wa-dark)}

        /* ────────────────────────────────────────────────────────────────
           HERO
        ──────────────────────────────────────────────────────────────── */
        .hero{
            padding    : clamp(4rem,8vw,7rem) clamp(1.25rem,4vw,2.5rem) clamp(3.5rem,7vw,6rem);
            text-align : center;
            background : var(--white);
            position   : relative;
            overflow   : hidden;
        }

        .hero::before{
            content   : '';
            position  : absolute;
            top       : 0;
            left      : 0;
            right     : 0;
            height    : 380px;
            background: radial-gradient(ellipse 80% 60% at 50% -10%, #E8F8F0 0%, transparent 70%);
            pointer-events: none;
            z-index   : 0;
        }

        .hero > *{position:relative;z-index:1}

        .hero-badge{
            display       : inline-flex;
            align-items   : center;
            gap           : .5rem;
            background    : var(--wa-light);
            color         : var(--wa-dark);
            font-size     : .75rem;
            font-weight   : 600;
            letter-spacing: .04em;
            padding       : .35rem 1rem;
            border-radius : 99px;
            margin-bottom : 1.75rem;
            border        : 1px solid #BBF7D0;
        }

        .hero-badge-dot{
            width         : 6px;
            height        : 6px;
            background    : var(--wa);
            border-radius : 50%;
        }

        .hero h1{
            font-family    : var(--serif);
            font-size      : clamp(2.1rem,5.5vw,3.8rem);
            font-weight    : 700;
            color          : var(--navy);
            line-height    : 1.1;
            margin-bottom  : 1.25rem;
            letter-spacing : -0.03em;
            max-width      : 800px;
            margin-left    : auto;
            margin-right   : auto;
        }

        .hero h1 .txt-wa{color:var(--wa-dark)}

        .hero-sub{
            font-size     : 1.05rem;
            color         : var(--gray-500);
            max-width     : 540px;
            margin        : 0 auto 2.25rem;
            font-weight   : 400;
            line-height   : 1.75;
        }

        .hero-btns{
            display         : flex;
            gap             : .75rem;
            justify-content : center;
            flex-wrap       : wrap;
            margin-bottom   : 1.25rem;
        }

        .btn-primary{
            background    : var(--wa);
            color         : var(--white);
            padding       : .85rem 2rem;
            border-radius : var(--r2);
            font-weight   : 700;
            font-size     : .95rem;
            border        : none;
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-block;
            font-family   : var(--sans);
        }

        .btn-primary:hover{
            background : var(--wa-dark);
            transform  : translateY(-1px);
        }

        .btn-secondary{
            background    : var(--white);
            color         : var(--gray-700);
            padding       : .85rem 1.75rem;
            border-radius : var(--r2);
            font-weight   : 500;
            font-size     : .95rem;
            border        : 1.5px solid var(--gray-200);
            cursor        : pointer;
            transition    : all .2s;
            display       : inline-block;
        }

        .btn-secondary:hover{border-color:var(--wa);color:var(--wa-dark)}

        .hero-note{
            font-size : .78rem;
            color     : var(--gray-400);
        }

        .hero-note strong{color:var(--wa-dark);font-weight:600}

        /* ── Stats strip ── */
        .stats-strip{
            display    : flex;
            flex-wrap  : wrap;
            justify-content: center;
            gap        : 0;
            border-top : 1px solid var(--gray-100);
            border-bottom: 1px solid var(--gray-100);
            background : var(--gray-50);
        }

        .stat-item{
            flex        : 1 1 180px;
            text-align  : center;
            padding     : 1.5rem 1.25rem;
            border-right: 1px solid var(--gray-100);
        }

        .stat-item:last-child{border-right:none}

        .stat-n{
            font-family    : var(--serif);
            font-size      : 1.9rem;
            font-weight    : 700;
            color          : var(--navy);
            line-height    : 1;
            margin-bottom  : .3rem;
        }

        .stat-n span{color:var(--wa-dark)}

        .stat-l{
            font-size   : .75rem;
            color       : var(--gray-500);
            font-weight : 400;
        }

        /* ────────────────────────────────────────────────────────────────
           SECTION COMMONS
        ──────────────────────────────────────────────────────────────── */
        section{padding:clamp(3.5rem,7vw,6rem) clamp(1.25rem,4vw,2.5rem)}

        .container{max-width:1100px;margin:0 auto}

        .eyebrow{
            font-size      : .7rem;
            font-weight    : 700;
            letter-spacing : .14em;
            text-transform : uppercase;
            color          : var(--wa-dark);
            margin-bottom  : .6rem;
        }

        .eyebrow.blue{color:var(--blue)}

        .section-h{
            font-family    : var(--serif);
            font-size      : clamp(1.65rem,3.2vw,2.5rem);
            font-weight    : 700;
            color          : var(--navy);
            line-height    : 1.2;
            letter-spacing : -0.025em;
            margin-bottom  : .75rem;
        }

        .section-p{
            font-size   : .975rem;
            color       : var(--gray-500);
            max-width   : 520px;
            line-height : 1.75;
        }

        /* ────────────────────────────────────────────────────────────────
           PAIN POINTS
        ──────────────────────────────────────────────────────────────── */
        .pain-section{background:var(--white)}

        .pain-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(300px,1fr));
            gap                   : 1rem;
            margin-top            : 2.75rem;
        }

        .pain-card{
            background    : var(--gray-50);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 1.5rem;
            display       : flex;
            gap           : 1rem;
            align-items   : flex-start;
        }

        .pain-icon{
            font-size      : 1.4rem;
            line-height    : 1;
            flex-shrink    : 0;
            margin-top     : .1rem;
        }

        .pain-card h3{
            font-size     : .9rem;
            font-weight   : 600;
            color         : var(--navy);
            margin-bottom : .3rem;
            font-family   : var(--serif);
        }

        .pain-card p{
            font-size  : .82rem;
            color      : var(--gray-500);
            line-height: 1.6;
        }

        /* ────────────────────────────────────────────────────────────────
           WHATSAPP AI — demo
        ──────────────────────────────────────────────────────────────── */
        .wa-section{background:var(--gray-50)}

        .wa-grid{
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : 4rem;
            align-items           : center;
            margin-top            : 2.75rem;
        }

        .wa-feat-list{
            display        : flex;
            flex-direction : column;
            gap            : .85rem;
            margin         : 1.75rem 0;
        }

        .wa-feat{
            display     : flex;
            align-items : flex-start;
            gap         : .75rem;
            font-size   : .875rem;
            color       : var(--gray-700);
        }

        .wa-check{
            width          : 20px;
            height         : 20px;
            background     : var(--wa);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            flex-shrink    : 0;
            margin-top     : .1rem;
        }

        .wa-check::after{
            content    : '';
            width      : 6px;
            height     : 4px;
            border-left: 1.5px solid #fff;
            border-bottom:1.5px solid #fff;
            transform  : rotate(-45deg) translateY(-1px);
            display    : block;
        }

        /* Chat mockup */
        .chat-phone{
            background    : var(--white);
            border-radius : var(--r3);
            box-shadow    : var(--shadow-md);
            border        : 1px solid var(--gray-100);
            overflow      : hidden;
            max-width     : 380px;
            margin        : 0 auto;
        }

        .chat-ph-header{
            background    : #075E54;
            padding       : .85rem 1.25rem;
            display       : flex;
            align-items   : center;
            gap           : .75rem;
        }

        .chat-avatar{
            width          : 40px;
            height         : 40px;
            background     : var(--wa-light);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .75rem;
            font-weight    : 700;
            color          : var(--wa-dark);
            flex-shrink    : 0;
        }

        .chat-name{
            font-size   : .9rem;
            font-weight : 600;
            color       : var(--white);
        }

        .chat-status{
            font-size   : .72rem;
            color       : #9FE1CB;
            display     : flex;
            align-items : center;
            gap         : .3rem;
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
            padding    : 1rem;
            display    : flex;
            flex-direction: column;
            gap        : .6rem;
            min-height : 280px;
        }

        .msg-wrap{display:flex;flex-direction:column}
        .msg-wrap.right{align-items:flex-end}
        .msg-wrap.left{align-items:flex-start}

        .msg-bubble{
            max-width     : 85%;
            padding       : .55rem .85rem;
            border-radius : 8px;
            font-size     : .81rem;
            line-height   : 1.55;
        }

        .msg-bubble.in{
            background    : var(--white);
            color         : var(--gray-900);
            border-radius : 0 8px 8px 8px;
        }

        .msg-bubble.out{
            background    : #D9FDD3;
            color         : #111;
            border-radius : 8px 8px 0 8px;
        }

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
            padding     : .5rem .85rem;
            background  : var(--white);
            border-radius: 0 8px 8px 8px;
            width       : 56px;
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

        .chat-footer-bar{
            background  : var(--white);
            padding     : .65rem 1rem;
            display     : flex;
            align-items : center;
            gap         : .5rem;
            border-top  : 1px solid var(--gray-100);
        }

        .chat-input-fake{
            flex          : 1;
            background    : var(--gray-50);
            border        : 1px solid var(--gray-200);
            border-radius : 99px;
            height        : 34px;
        }

        .chat-send{
            width          : 34px;
            height         : 34px;
            background     : var(--wa);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
        }

        .chat-send svg{width:16px;height:16px;fill:#fff}

        .chat-badge{
            background    : var(--wa-light);
            color         : var(--wa-dark);
            font-size     : .7rem;
            font-weight   : 600;
            text-align    : center;
            padding       : .45rem 1rem;
            border-top    : 1px solid #BBF7D0;
        }

        /* ────────────────────────────────────────────────────────────────
           PMS MÓDULOS
        ──────────────────────────────────────────────────────────────── */
        .pms-section{background:var(--white)}

        .pms-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(300px,1fr));
            gap                   : 1.25rem;
            margin-top            : 2.75rem;
        }

        .pms-card{
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 1.75rem;
            background    : var(--white);
            transition    : border-color .2s, transform .2s, box-shadow .2s;
            position      : relative;
            overflow      : hidden;
        }

        .pms-card::before{
            content   : '';
            position  : absolute;
            top       : 0;
            left      : 0;
            width     : 3px;
            height    : 100%;
            background: var(--wa);
            transform : scaleY(0);
            transition: transform .2s;
            transform-origin: bottom;
        }

        .pms-card:hover{
            border-color : var(--gray-200);
            transform    : translateY(-2px);
            box-shadow   : var(--shadow-md);
        }

        .pms-card:hover::before{transform:scaleY(1)}

        .pms-icon{
            width          : 44px;
            height         : 44px;
            border-radius  : var(--r);
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : 1.2rem;
            margin-bottom  : 1rem;
        }

        .pms-icon.g{background:var(--wa-light)}
        .pms-icon.b{background:var(--blue-light)}
        .pms-icon.t{background:var(--teal-light)}
        .pms-icon.a{background:#FEF3C7}

        .pms-card h3{
            font-size     : .975rem;
            font-weight   : 600;
            color         : var(--navy);
            margin-bottom : .4rem;
            font-family   : var(--serif);
        }

        .pms-card p{
            font-size  : .83rem;
            color      : var(--gray-500);
            line-height: 1.65;
        }

        .pms-tag{
            display       : inline-block;
            margin-top    : .75rem;
            font-size     : .68rem;
            font-weight   : 600;
            padding       : .2rem .65rem;
            border-radius : 4px;
        }

        .pms-tag.g{background:var(--wa-light);color:var(--wa-dark)}
        .pms-tag.b{background:var(--blue-light);color:var(--blue-dark)}
        .pms-tag.t{background:var(--teal-light);color:var(--teal)}
        .pms-tag.a{background:#FEF3C7;color:#92400E}

        /* ────────────────────────────────────────────────────────────────
           ONBOARDING — 3 PASOS
        ──────────────────────────────────────────────────────────────── */
        .onboard-section{background:var(--navy);color:var(--white)}

        .onboard-section .eyebrow{color:#9FE1CB}

        .onboard-section .section-h{color:var(--white)}

        .onboard-section .section-p{color:#85B7EB}

        .steps-row{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(260px,1fr));
            gap                   : 1.5rem;
            margin-top            : 2.75rem;
            position              : relative;
        }

        .step-card{
            background    : rgba(255,255,255,.05);
            border        : 1px solid rgba(255,255,255,.1);
            border-radius : var(--r2);
            padding       : 2rem 1.75rem;
        }

        .step-num{
            font-family    : var(--serif);
            font-size      : 3.5rem;
            font-weight    : 700;
            color          : rgba(255,255,255,.08);
            line-height    : 1;
            margin-bottom  : 1rem;
        }

        .step-title{
            font-size     : 1rem;
            font-weight   : 600;
            color         : var(--white);
            margin-bottom : .4rem;
            font-family   : var(--serif);
        }

        .step-sub{
            font-size  : .83rem;
            color      : #94A3B8;
            line-height: 1.65;
        }

        .step-time{
            display      : inline-block;
            margin-top   : 1rem;
            font-size    : .7rem;
            font-weight  : 700;
            background   : rgba(37,211,102,.15);
            color        : #9FE1CB;
            padding      : .25rem .75rem;
            border-radius: 4px;
            letter-spacing:.04em;
        }

        .guarantees{
            display         : flex;
            flex-wrap       : wrap;
            justify-content : center;
            gap             : 1.5rem;
            margin-top      : 3rem;
            padding-top     : 2.5rem;
            border-top      : 1px solid rgba(255,255,255,.08);
        }

        .guar-item{
            display     : flex;
            align-items : center;
            gap         : .5rem;
            font-size   : .82rem;
            color       : #94A3B8;
        }

        .guar-icon{font-size:1rem}

        /* ────────────────────────────────────────────────────────────────
           CONVERSACIONES (inbox)
        ──────────────────────────────────────────────────────────────── */
        .inbox-section{background:var(--gray-50)}

        .inbox-grid{
            display               : grid;
            grid-template-columns : 1fr 1.1fr;
            gap                   : 4rem;
            align-items           : center;
            margin-top            : 2.75rem;
        }

        .inbox-features{
            display        : flex;
            flex-direction : column;
            gap            : 1rem;
        }

        .inbox-feat{
            display       : flex;
            align-items   : flex-start;
            gap           : .9rem;
            padding       : 1rem 1.25rem;
            background    : var(--white);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            transition    : border-color .2s;
        }

        .inbox-feat:hover{border-color:var(--blue-mid)}

        .inbox-feat-icon{
            font-size   : 1.1rem;
            flex-shrink : 0;
            margin-top  : .1rem;
        }

        .inbox-feat h4{
            font-size     : .88rem;
            font-weight   : 600;
            color         : var(--navy);
            margin-bottom : .2rem;
        }

        .inbox-feat p{
            font-size  : .8rem;
            color      : var(--gray-500);
            line-height: 1.55;
        }

        /* Inbox mockup */
        .inbox-mock{
            background    : var(--white);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            overflow      : hidden;
            box-shadow    : var(--shadow-md);
        }

        .inbox-mock-header{
            padding     : .9rem 1.25rem;
            border-bottom: 1px solid var(--gray-100);
            display     : flex;
            align-items : center;
            justify-content: space-between;
        }

        .inbox-mock-title{
            font-size  : .85rem;
            font-weight: 600;
            color      : var(--navy);
        }

        .inbox-badge{
            background    : var(--wa);
            color         : var(--white);
            font-size     : .65rem;
            font-weight   : 700;
            padding       : .15rem .55rem;
            border-radius : 99px;
        }

        .inbox-row{
            padding       : .85rem 1.25rem;
            border-bottom : 1px solid var(--gray-50);
            display       : flex;
            align-items   : center;
            gap           : .85rem;
            cursor        : default;
            transition    : background .1s;
        }

        .inbox-row:hover{background:var(--gray-50)}

        .inbox-row.active{background:var(--blue-light)}

        .inbox-av{
            width          : 36px;
            height         : 36px;
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .72rem;
            font-weight    : 700;
            flex-shrink    : 0;
        }

        .inbox-row-body{flex:1;min-width:0}

        .inbox-row-name{
            font-size     : .82rem;
            font-weight   : 600;
            color         : var(--navy);
            margin-bottom : .1rem;
        }

        .inbox-row-preview{
            font-size : .74rem;
            color     : var(--gray-400);
            white-space: nowrap;
            overflow  : hidden;
            text-overflow: ellipsis;
        }

        .inbox-row-meta{
            display        : flex;
            flex-direction : column;
            align-items    : flex-end;
            gap            : .3rem;
        }

        .inbox-row-time{font-size:.65rem;color:var(--gray-400)}

        .inbox-tag{
            font-size    : .62rem;
            font-weight  : 700;
            padding      : .15rem .45rem;
            border-radius: 4px;
            white-space  : nowrap;
        }

        .tag-wa{background:#DCFCE7;color:#166534}
        .tag-ai{background:var(--blue-light);color:var(--blue-dark)}
        .tag-human{background:#FEF3C7;color:#92400E}

        /* ────────────────────────────────────────────────────────────────
           REVENUE
        ──────────────────────────────────────────────────────────────── */
        .revenue-section{background:var(--white)}

        .revenue-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(300px,1fr));
            gap                   : 1.25rem;
            margin-top            : 2.75rem;
        }

        .rev-card{
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 1.75rem;
            background    : var(--white);
        }

        .rev-card-head{
            display       : flex;
            align-items   : center;
            gap           : .75rem;
            margin-bottom : 1.25rem;
        }

        .rev-icon{
            width          : 40px;
            height         : 40px;
            border-radius  : var(--r);
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : 1.1rem;
        }

        .rev-icon.red{background:#FEF2F2}
        .rev-icon.g{background:var(--wa-light)}

        .rev-before{
            font-size     : .78rem;
            color         : var(--gray-500);
            padding       : .6rem .9rem;
            background    : var(--gray-50);
            border-radius : var(--r);
            margin-bottom : .75rem;
            line-height   : 1.55;
        }

        .rev-before strong{color:#DC2626}

        .rev-after{
            font-size     : .78rem;
            color         : var(--gray-700);
            padding       : .6rem .9rem;
            background    : #F0FDF4;
            border-radius : var(--r);
            border        : 1px solid #BBF7D0;
            line-height   : 1.55;
        }

        .rev-after strong{color:var(--wa-dark)}

        /* ────────────────────────────────────────────────────────────────
           INTEGRACIONES
        ──────────────────────────────────────────────────────────────── */
        .integrations-section{background:var(--gray-50)}

        .int-grid{
            display         : flex;
            flex-wrap       : wrap;
            gap             : .75rem;
            margin-top      : 2rem;
            justify-content : center;
        }

        .int-pill{
            display       : flex;
            align-items   : center;
            gap           : .5rem;
            background    : var(--white);
            border        : 1px solid var(--gray-100);
            border-radius : 99px;
            padding       : .55rem 1.1rem;
            font-size     : .82rem;
            font-weight   : 500;
            color         : var(--gray-700);
            transition    : border-color .2s;
        }

        .int-pill:hover{border-color:var(--wa)}

        .int-dot{
            width         : 8px;
            height        : 8px;
            border-radius : 50%;
        }

        /* ────────────────────────────────────────────────────────────────
           TESTIMONIOS
        ──────────────────────────────────────────────────────────────── */
        .testimonials-section{background:var(--white)}

        .testimonials-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(300px,1fr));
            gap                   : 1.25rem;
            margin-top            : 2.75rem;
        }

        .tcard{
            border        : 1px solid var(--gray-100);
            border-radius : var(--r2);
            padding       : 1.75rem;
            background    : var(--white);
            display       : flex;
            flex-direction: column;
            gap           : 1.25rem;
        }

        .tcard-metrics{
            display : flex;
            gap     : .75rem;
        }

        .metric-pill{
            flex          : 1;
            text-align    : center;
            background    : var(--gray-50);
            border-radius : var(--r);
            padding       : .6rem .5rem;
        }

        .metric-pill .mn{
            font-family  : var(--serif);
            font-size    : 1.35rem;
            font-weight  : 700;
            color        : var(--navy);
            display      : block;
            line-height  : 1;
        }

        .metric-pill .mn span{color:var(--wa-dark)}

        .metric-pill .ml{
            font-size : .65rem;
            color     : var(--gray-500);
        }

        .tcard-quote{
            font-size     : .875rem;
            color         : var(--gray-700);
            line-height   : 1.7;
            font-style    : italic;
        }

        .tcard-author{
            display     : flex;
            align-items : center;
            gap         : .75rem;
        }

        .author-av{
            width          : 38px;
            height         : 38px;
            border-radius  : 50%;
            background     : var(--blue-light);
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .72rem;
            font-weight    : 700;
            color          : var(--blue-dark);
            flex-shrink    : 0;
        }

        .author-name{
            font-size  : .84rem;
            font-weight: 600;
            color      : var(--navy);
        }

        .author-role{
            font-size : .74rem;
            color     : var(--gray-400);
        }

        /* ────────────────────────────────────────────────────────────────
           PRICING
        ──────────────────────────────────────────────────────────────── */
        .pricing-section{background:var(--gray-50)}

        .pricing-grid{
            display               : grid;
            grid-template-columns : repeat(auto-fit, minmax(270px,1fr));
            gap                   : 1.25rem;
            margin-top            : 2.75rem;
            max-width             : 900px;
            margin-left           : auto;
            margin-right          : auto;
        }

        .pcard{
            background    : var(--white);
            border        : 1px solid var(--gray-200);
            border-radius : var(--r2);
            padding       : 2rem;
            position      : relative;
        }

        .pcard.featured{
            border : 2px solid var(--wa);
        }

        .p-badge{
            position      : absolute;
            top           : -13px;
            left          : 50%;
            transform     : translateX(-50%);
            background    : var(--wa);
            color         : var(--white);
            font-size     : .67rem;
            font-weight   : 700;
            padding       : .25rem 1rem;
            border-radius : 99px;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space   : nowrap;
        }

        .p-name{
            font-size      : .7rem;
            font-weight    : 700;
            letter-spacing : .1em;
            text-transform : uppercase;
            color          : var(--wa-dark);
            margin-bottom  : .5rem;
        }

        .p-name.blue{color:var(--blue)}

        .p-price{
            font-family : var(--serif);
            font-size   : 2.4rem;
            font-weight : 700;
            color       : var(--navy);
            line-height : 1;
        }

        .p-price sup{
            font-size      : .95rem;
            vertical-align : super;
            font-family    : var(--sans);
            font-weight    : 400;
            color          : var(--gray-500);
        }

        .p-period{
            font-size     : .78rem;
            color         : var(--gray-400);
            margin        : .4rem 0 1.5rem;
        }

        .p-list{
            list-style    : none;
            margin-bottom : 1.75rem;
        }

        .p-list li{
            font-size     : .84rem;
            color         : var(--gray-700);
            padding       : .4rem 0;
            display       : flex;
            align-items   : center;
            gap           : .6rem;
            border-bottom : 1px solid var(--gray-50);
        }

        .p-list li::before{
            content       : '';
            width         : 5px;
            height        : 5px;
            background    : var(--wa);
            border-radius : 50%;
            flex-shrink   : 0;
        }

        .btn-plan{
            display       : block;
            text-align    : center;
            padding       : .8rem;
            border-radius : var(--r2);
            font-size     : .875rem;
            font-weight   : 700;
            cursor        : pointer;
            border        : none;
            transition    : all .2s;
            font-family   : var(--sans);
        }

        .btn-plan.primary{background:var(--wa);color:var(--white)}
        .btn-plan.primary:hover{background:var(--wa-dark)}
        .btn-plan.outline{background:var(--white);color:var(--wa-dark);border:1.5px solid var(--wa)}
        .btn-plan.outline:hover{background:var(--wa-light)}

        /* ────────────────────────────────────────────────────────────────
           REGISTRO
        ──────────────────────────────────────────────────────────────── */
        .register-section{background:var(--white)}

        .register-grid{
            display               : grid;
            grid-template-columns : 1fr 1.1fr;
            gap                   : 4rem;
            align-items           : start;
            max-width             : 1020px;
            margin                : 0 auto;
        }

        .reg-left-step{
            display     : flex;
            align-items : flex-start;
            gap         : .85rem;
            margin-bottom: .85rem;
        }

        .reg-step-circle{
            width          : 32px;
            height         : 32px;
            background     : var(--wa-light);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            font-size      : .78rem;
            font-weight    : 700;
            color          : var(--wa-dark);
            flex-shrink    : 0;
        }

        .reg-step-text{
            font-size  : .875rem;
            color      : var(--gray-500);
            padding-top: .45rem;
        }

        .rform{
            background    : var(--gray-50);
            border        : 1px solid var(--gray-100);
            border-radius : var(--r3);
            padding       : 2.25rem;
        }

        .rform-title{
            font-family   : var(--serif);
            font-size     : 1.35rem;
            font-weight   : 700;
            color         : var(--navy);
            margin-bottom : .2rem;
        }

        .rform-sub{
            font-size     : .82rem;
            color         : var(--gray-400);
            margin-bottom : 1.75rem;
            display       : flex;
            align-items   : center;
            gap           : .4rem;
        }

        .fg{margin-bottom:1rem}

        .fg label{
            display        : block;
            font-size      : .7rem;
            font-weight    : 700;
            color          : var(--gray-500);
            letter-spacing : .07em;
            text-transform : uppercase;
            margin-bottom  : .35rem;
        }

        .fg input,
        .fg select{
            width          : 100%;
            background     : var(--white);
            border         : 1.5px solid var(--gray-200);
            border-radius  : var(--r);
            color          : var(--gray-900);
            padding        : .65rem .9rem;
            font-family    : var(--sans);
            font-size      : .875rem;
            outline        : none;
            transition     : border-color .2s;
            -webkit-appearance: none;
            appearance     : none;
        }

        .fg input::placeholder{color:var(--gray-400)}
        .fg input:focus,
        .fg select:focus{border-color:var(--wa)}

        .fg-row{
            display               : grid;
            grid-template-columns : 1fr 1fr;
            gap                   : .75rem;
        }

        .fcheck{
            display     : flex;
            align-items : flex-start;
            gap         : .6rem;
            margin      : 1rem 0;
        }

        .fcheck input[type="checkbox"]{
            width      : 15px;
            height     : 15px;
            min-width  : 15px;
            margin-top : .2rem;
            accent-color: var(--wa);
            cursor     : pointer;
        }

        .fcheck label{
            font-size     : .78rem;
            color         : var(--gray-500);
            cursor        : pointer;
            text-transform: none;
            letter-spacing: 0;
            line-height   : 1.55;
        }

        .fcheck a{color:var(--blue);font-weight:500}

        .btn-reg{
            width         : 100%;
            background    : var(--wa);
            color         : var(--white);
            border        : none;
            padding       : .9rem;
            border-radius : var(--r2);
            font-size     : .95rem;
            font-weight   : 700;
            cursor        : pointer;
            font-family   : var(--sans);
            transition    : all .2s;
        }

        .btn-reg:hover{background:var(--wa-dark);transform:translateY(-1px)}
        .btn-reg:disabled{opacity:.6;cursor:not-allowed;transform:none}

        .form-note{
            text-align : center;
            font-size  : .72rem;
            color      : var(--gray-400);
            margin-top : .75rem;
        }

        .login-link{
            text-align : center;
            margin-top : 1rem;
            font-size  : .82rem;
            color      : var(--gray-500);
        }

        .login-link a{color:var(--blue);font-weight:600}

        .form-error{
            background    : #FEF2F2;
            color         : #991B1B;
            border        : 1px solid #FECACA;
            border-radius : var(--r);
            padding       : .65rem .9rem;
            font-size     : .82rem;
            margin-bottom : 1rem;
            display       : none;
        }

        .success-wrap{
            display    : none;
            text-align : center;
            padding    : 2.5rem 1rem;
        }

        .success-icon{
            width          : 56px;
            height         : 56px;
            background     : var(--wa-light);
            border-radius  : 50%;
            display        : flex;
            align-items    : center;
            justify-content: center;
            margin         : 0 auto 1rem;
            color          : var(--wa-dark);
            font-size      : 1.5rem;
        }

        .success-wrap h3{
            font-family   : var(--serif);
            font-size     : 1.3rem;
            color         : var(--navy);
            margin-bottom : .5rem;
        }

        .success-wrap p{
            font-size  : .85rem;
            color      : var(--gray-500);
            margin-bottom: 1.5rem;
        }

        .btn-goto{
            display       : inline-block;
            background    : var(--wa);
            color         : var(--white);
            padding       : .75rem 1.75rem;
            border-radius : var(--r2);
            font-weight   : 700;
            font-size     : .9rem;
            transition    : background .2s;
        }

        .btn-goto:hover{background:var(--wa-dark)}

        /* ────────────────────────────────────────────────────────────────
           CTA FINAL
        ──────────────────────────────────────────────────────────────── */
        .final-cta{
            background   : linear-gradient(135deg, #0F172A 0%, #064E3B 100%);
            padding      : clamp(4rem,8vw,7rem) clamp(1.25rem,4vw,2.5rem);
            text-align   : center;
            color        : var(--white);
        }

        .final-cta h2{
            font-family    : var(--serif);
            font-size      : clamp(1.75rem,3.5vw,2.8rem);
            font-weight    : 700;
            color          : var(--white);
            margin-bottom  : 1rem;
            letter-spacing : -0.025em;
            max-width      : 700px;
            margin-left    : auto;
            margin-right   : auto;
        }

        .final-cta p{
            font-size     : 1rem;
            color         : #94A3B8;
            margin-bottom : 2.25rem;
            max-width     : 480px;
            margin-left   : auto;
            margin-right  : auto;
        }

        .final-btns{
            display         : flex;
            justify-content : center;
            flex-wrap       : wrap;
            gap             : .75rem;
            margin-bottom   : 1.5rem;
        }

        .final-note{
            font-size : .78rem;
            color     : #475569;
        }

        /* ────────────────────────────────────────────────────────────────
           FOOTER
        ──────────────────────────────────────────────────────────────── */
        footer{
            background : var(--navy);
            padding    : 2.5rem clamp(1.25rem,4vw,2.5rem);
            display    : flex;
            flex-wrap  : wrap;
            justify-content: space-between;
            align-items: center;
            gap        : 1rem;
        }

        .footer-logo{
            font-family   : var(--serif);
            font-size     : 1.15rem;
            font-weight   : 700;
            color         : var(--white);
            display       : flex;
            align-items   : center;
            gap           : .4rem;
        }

        .footer-logo-dot{
            width         : 7px;
            height        : 7px;
            background    : var(--wa);
            border-radius : 50%;
        }

        .footer-links{
            display : flex;
            gap     : 1.5rem;
        }

        .footer-links a{
            font-size  : .8rem;
            color      : #475569;
            transition : color .15s;
        }

        .footer-links a:hover{color:var(--gray-400)}

        .footer-right{
            font-size  : .75rem;
            color      : #334155;
        }

        .footer-right a{color:#4B8FCA}

        /* ────────────────────────────────────────────────────────────────
           WHATSAPP FLOAT
        ──────────────────────────────────────────────────────────────── */
        .wa-float{
            position      : fixed;
            bottom        : 1.75rem;
            right         : 1.75rem;
            z-index       : 999;
            width         : 52px;
            height        : 52px;
            background    : var(--wa);
            border-radius : 50%;
            display       : flex;
            align-items   : center;
            justify-content: center;
            box-shadow    : 0 4px 16px rgba(37,211,102,.35);
            transition    : transform .2s;
        }

        .wa-float:hover{transform:scale(1.08)}

        .wa-float svg{width:26px;height:26px;fill:#fff}

        /* ────────────────────────────────────────────────────────────────
           REVEAL
        ──────────────────────────────────────────────────────────────── */
        .reveal{
            opacity   : 0;
            transform : translateY(20px);
            transition: opacity .55s ease, transform .55s ease;
        }

        .reveal.visible{
            opacity   : 1;
            transform : none;
        }

        /* ────────────────────────────────────────────────────────────────
           RESPONSIVE
        ──────────────────────────────────────────────────────────────── */
        @media(max-width:860px){
            .wa-grid,
            .inbox-grid,
            .register-grid{grid-template-columns:1fr}
            .nav-links{display:none}
            .wa-grid > *:first-child{order:2}
            .wa-grid > *:last-child{order:1}
        }

        @media(max-width:640px){
            .hero h1{font-size:2rem}
            .stat-item{flex:1 1 140px}
            .fg-row{grid-template-columns:1fr}
            footer{flex-direction:column;text-align:center}
            .hero-btns{flex-direction:column;align-items:center}
            .btn-primary,.btn-secondary{width:100%;max-width:300px;text-align:center}
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════════════════
     NAVEGACIÓN
══════════════════════════════════════════════════════════ -->
<nav>
    <div class="nav-logo">
        <div class="nav-logo-dot"></div>
        GuestHandle
    </div>

    <div class="nav-links">
        <a href="#whatsapp-ia">WhatsApp IA</a>
        <a href="#pms">PMS</a>
        <a href="#precios">Precios</a>
    </div>

    <div class="nav-actions">
        <a href="/login" class="btn-login">Iniciar sesión</a>
        <a href="#registro" class="btn-nav-cta">Empezar gratis</a>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════ -->
<div class="hero">
    <div class="hero-badge">
        <div class="hero-badge-dot"></div>
        Primer mes gratis &nbsp;·&nbsp; Sin tarjeta de crédito
    </div>

    <h1>Tu hotel vende<br>mientras <span class="txt-wa">duermes</span></h1>

    <p class="hero-sub">PMS + IA en WhatsApp 24/7. Reservas automáticas, precios inteligentes y cero consultas sin respuesta. Listo en menos de 10 minutos.</p>

    <div class="hero-btns">
        <a href="#registro" class="btn-primary">Crear cuenta gratis →</a>
        <a href="#whatsapp-ia" class="btn-secondary">Ver cómo funciona</a>
    </div>

    <p class="hero-note">Sin tarjeta de crédito &nbsp;·&nbsp; <strong>30 días gratis</strong> &nbsp;·&nbsp; Cancela cuando quieras</p>
</div>

<!-- ══════════════════════════════════════════════════════════
     STATS
══════════════════════════════════════════════════════════ -->
<div class="stats-strip">
    <div class="stat-item reveal">
        <div class="stat-n"><span>+</span>28<span>%</span></div>
        <div class="stat-l">reservas directas promedio</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n"><span>&lt;</span>5<span>s</span></div>
        <div class="stat-l">tiempo de respuesta IA</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n">98<span>%</span></div>
        <div class="stat-l">tasa de respuesta WhatsApp</div>
    </div>
    <div class="stat-item reveal">
        <div class="stat-n"><span>+</span>3<span>h</span></div>
        <div class="stat-l">ahorradas por día / agente</div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PAIN POINTS
══════════════════════════════════════════════════════════ -->
<section class="pain-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow">¿Te suena familiar?</div>
            <h2 class="section-h">El costo invisible de no responder a tiempo</h2>
        </div>

        <div class="pain-grid">
            <div class="pain-card reveal">
                <div class="pain-icon">🌙</div>
                <div>
                    <h3>La reserva que se fue en la madrugada</h3>
                    <p>Son las 2am. Un huésped pregunta disponibilidad para el puente. Nadie responde. La reserva la hace en Booking.</p>
                </div>
            </div>
            <div class="pain-card reveal">
                <div class="pain-icon">📋</div>
                <div>
                    <h3>3 horas diarias en preguntas repetitivas</h3>
                    <p>Tu equipo pasa horas respondiendo "¿cuánto cuesta la doble?" en lugar de cerrar ventas complejas.</p>
                </div>
            </div>
            <div class="pain-card reveal">
                <div class="pain-icon">📉</div>
                <div>
                    <h3>Precios desactualizados en los canales</h3>
                    <p>Tienes el precio mal en 4 canales distintos y no te das cuenta hasta que ya hubo una reserva al precio equivocado.</p>
                </div>
            </div>
            <div class="pain-card reveal">
                <div class="pain-icon">💬</div>
                <div>
                    <h3>WhatsApp con 40 mensajes sin leer</h3>
                    <p>12 de esos mensajes eran consultas de reserva. Ya buscaron otra opción. Ya se fueron.</p>
                </div>
            </div>
            <div class="pain-card reveal">
                <div class="pain-icon">🔄</div>
                <div>
                    <h3>Overbooking por desincronización</h3>
                    <p>Actualizas disponibilidad en el PMS y se te olvida actualizar el motor de reservas. Problema garantizado.</p>
                </div>
            </div>
            <div class="pain-card reveal">
                <div class="pain-icon">💸</div>
                <div>
                    <h3>Comisiones del 20% a las OTAs</h3>
                    <p>Pagas comisiones enormes porque no tienes tiempo de atender la venta directa. GuestHandle lo hace por ti.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     WHATSAPP IA 24/7
══════════════════════════════════════════════════════════ -->
<section id="whatsapp-ia" class="wa-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow">WhatsApp IA 24/7</div>
            <h2 class="section-h">Tu mejor vendedor nunca descansa</h2>
            <p class="section-p">Nuestra IA atiende, cotiza y confirma reservas directamente en WhatsApp — conectada en tiempo real a tu disponibilidad y precios.</p>
        </div>

        <div class="wa-grid">
            <div class="reveal">
                <div class="wa-feat-list">
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Responde en menos de 5 segundos, las 24 horas, los 365 días</span>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Consulta disponibilidad y precios en tiempo real desde el PMS</span>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Habla el idioma del huésped: español, inglés, portugués y más</span>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Transfiere a un agente humano cuando detecta situaciones complejas</span>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Guarda el lead si no hay disponibilidad y hace seguimiento automático</span>
                    </div>
                    <div class="wa-feat">
                        <div class="wa-check"></div>
                        <span>Envía recordatorios previos al check-in y previene no-shows</span>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <div class="chat-phone">
                    <div class="chat-ph-header">
                        <div class="chat-avatar">GH</div>
                        <div>
                            <div class="chat-name">Hotel Boutique Casa Verde</div>
                            <div class="chat-status">
                                <div class="status-dot"></div>
                                Asistente IA activo
                            </div>
                        </div>
                    </div>
                    <div class="chat-body">
                        <div class="msg-wrap right">
                            <div class="msg-bubble in">Hola! tienen disponibilidad para 2 adultos del 15 al 18 de mayo?</div>
                            <div class="msg-meta">10:24 am</div>
                        </div>
                        <div class="msg-wrap left">
                            <div class="msg-bubble out">¡Hola María! Sí tenemos disponibilidad para esas fechas 🙌<br><br>• Hab. Doble Superior — $320.000/noche<br>• Suite con vista — $480.000/noche<br><br>¿Cuál te interesa? Puedo reservarte ahora mismo.</div>
                            <div class="msg-meta out-t">10:24 am <span class="ticks">✓✓</span> Respondido automáticamente</div>
                        </div>
                        <div class="msg-wrap right">
                            <div class="msg-bubble in">La doble, ¿incluye desayuno?</div>
                            <div class="msg-meta">10:25 am</div>
                        </div>
                        <div class="msg-wrap left">
                            <div class="chat-typing">
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                                <div class="typing-dot"></div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-footer-bar">
                        <div class="chat-input-fake"></div>
                        <div class="chat-send">
                            <svg viewBox="0 0 24 24"><path d="M2 21l21-9L2 3v7l15 2-15 2z"/></svg>
                        </div>
                    </div>
                    <div class="chat-badge">⚡ Respondido en 3 segundos · Powered by GuestHandle IA</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     PMS MÓDULOS
══════════════════════════════════════════════════════════ -->
<section id="pms" class="pms-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow blue">PMS completo</div>
            <h2 class="section-h">Un PMS de verdad. No solo un chatbot.</h2>
            <p class="section-p">Asksuite te da IA pero no el PMS. Pxsol tiene el PMS pero la IA es un add-on tardío. GuestHandle es los dos, integrados desde el primer día.</p>
        </div>

        <div class="pms-grid">
            <div class="pms-card reveal">
                <div class="pms-icon g">📅</div>
                <h3>Panel de ocupación</h3>
                <p>Vista calendario en tiempo real, drag & drop de reservas, detección automática de overbooking.</p>
                <span class="pms-tag g">Tiempo real</span>
            </div>
            <div class="pms-card reveal">
                <div class="pms-icon b">🛒</div>
                <h3>Motor de reservas directo</h3>
                <p>Integrado a tu sitio web, sincronizado al segundo. Tus huéspedes reservan directo, sin comisiones.</p>
                <span class="pms-tag b">Sin comisiones</span>
            </div>
            <div class="pms-card reveal">
                <div class="pms-icon t">🌐</div>
                <h3>Channel Manager</h3>
                <p>Sincroniza con Booking.com, Expedia, Airbnb y +200 canales. Disponibilidad unificada, cero sobreventas.</p>
                <span class="pms-tag t">+200 canales</span>
            </div>
            <div class="pms-card reveal">
                <div class="pms-icon a">📈</div>
                <h3>Revenue Manager IA</h3>
                <p>Precios dinámicos automáticos según ocupación, temporada y competencia. El RevPAR solo puede subir.</p>
                <span class="pms-tag a">IA automático</span>
            </div>
            <div class="pms-card reveal">
                <div class="pms-icon g">📱</div>
                <h3>Check-in / out digital</h3>
                <p>Pre check-in por WhatsApp, escaneo de documentos con IA y firma digital. Experiencia sin fricción.</p>
                <span class="pms-tag g">Sin papel</span>
            </div>
            <div class="pms-card reveal">
                <div class="pms-icon b">📊</div>
                <h3>Reportes y analítica</h3>
                <p>Dashboard de ingresos, RevPAR, ADR y ocupación. Exportable, en tiempo real, sin Excel.</p>
                <span class="pms-tag b">Tiempo real</span>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     ONBOARDING
══════════════════════════════════════════════════════════ -->
<section class="onboard-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow">Conexión fácil</div>
            <h2 class="section-h">Listo en menos de 10 minutos. En serio.</h2>
            <p class="section-p">Sin desarrollo. Sin migraciones dolorosas. Sin técnicos externos. Conectás el número de WhatsApp que ya tenés.</p>
        </div>

        <div class="steps-row">
            <div class="step-card reveal">
                <div class="step-num">01</div>
                <div class="step-title">Crea tu cuenta</div>
                <p class="step-sub">Ingresa los datos de tu hotel, tipos de habitación y tarifas base. Simple como configurar un perfil.</p>
                <span class="step-time">2 minutos</span>
            </div>
            <div class="step-card reveal">
                <div class="step-num">02</div>
                <div class="step-title">Conecta tu WhatsApp</div>
                <p class="step-sub">Escaneas un QR o conectas el número mediante API oficial de Meta. No necesitas cambiar de número.</p>
                <span class="step-time">5 minutos</span>
            </div>
            <div class="step-card reveal">
                <div class="step-num">03</div>
                <div class="step-title">Tu IA empieza a vender</div>
                <p class="step-sub">La IA aprende tu hotel en automático. Ya puede cotizar, responder y confirmar reservas. Tú solo supervisas.</p>
                <span class="step-time">Inmediato</span>
            </div>
        </div>

        <div class="guarantees reveal">
            <div class="guar-item"><div class="guar-icon">🛡️</div>30 días de garantía o te devolvemos el dinero</div>
            <div class="guar-item"><div class="guar-icon">🔧</div>Onboarding incluido sin costo</div>
            <div class="guar-item"><div class="guar-icon">💬</div>Soporte humano por WhatsApp</div>
            <div class="guar-item"><div class="guar-icon">🔄</div>Migración de datos asistida</div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     GESTIÓN DE CONVERSACIONES
══════════════════════════════════════════════════════════ -->
<section class="inbox-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow">Inbox unificado</div>
            <h2 class="section-h">Todas las conversaciones. Un solo lugar.</h2>
            <p class="section-p">IA y equipo humano trabajando juntos. Tú decides cuándo tomar el control.</p>
        </div>

        <div class="inbox-grid">
            <div class="inbox-features reveal">
                <div class="inbox-feat">
                    <div class="inbox-feat-icon">📥</div>
                    <div>
                        <h4>Vista unificada multicanal</h4>
                        <p>WhatsApp, email y webchat en una sola bandeja. Sin saltar entre apps.</p>
                    </div>
                </div>
                <div class="inbox-feat">
                    <div class="inbox-feat-icon">🤖</div>
                    <div>
                        <h4>Etiquetas automáticas por IA</h4>
                        <p>La IA clasifica y prioriza cada conversación según intención de compra.</p>
                    </div>
                </div>
                <div class="inbox-feat">
                    <div class="inbox-feat-icon">👤</div>
                    <div>
                        <h4>Traspaso IA → Agente en 1 clic</h4>
                        <p>El agente toma el control cuando lo necesita, con todo el historial visible.</p>
                    </div>
                </div>
                <div class="inbox-feat">
                    <div class="inbox-feat-icon">💡</div>
                    <div>
                        <h4>Respuestas sugeridas por IA</h4>
                        <p>El copiloto sugiere la mejor respuesta al agente humano en tiempo real.</p>
                    </div>
                </div>
                <div class="inbox-feat">
                    <div class="inbox-feat-icon">📊</div>
                    <div>
                        <h4>Métricas por agente y canal</h4>
                        <p>Tiempo de respuesta, tasa de conversión y volumen por agente en un dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <div class="inbox-mock">
                    <div class="inbox-mock-header">
                        <div class="inbox-mock-title">Bandeja de entrada</div>
                        <div class="inbox-badge">5 nuevos</div>
                    </div>
                    <div class="inbox-row active">
                        <div class="inbox-av" style="background:#DCFCE7;color:#166534">MC</div>
                        <div class="inbox-row-body">
                            <div class="inbox-row-name">María Camila</div>
                            <div class="inbox-row-preview">¿Tienen disponibilidad para...</div>
                        </div>
                        <div class="inbox-row-meta">
                            <div class="inbox-row-time">10:24</div>
                            <div class="inbox-tag tag-wa">WhatsApp</div>
                            <div class="inbox-tag tag-ai">IA activa</div>
                        </div>
                    </div>
                    <div class="inbox-row">
                        <div class="inbox-av" style="background:#EDE9FE;color:#5B21B6">JP</div>
                        <div class="inbox-row-body">
                            <div class="inbox-row-name">Juan Pablo Soto</div>
                            <div class="inbox-row-preview">Perfecto, confirmo la reserva...</div>
                        </div>
                        <div class="inbox-row-meta">
                            <div class="inbox-row-time">09:51</div>
                            <div class="inbox-tag tag-wa">WhatsApp</div>
                        </div>
                    </div>
                    <div class="inbox-row">
                        <div class="inbox-av" style="background:#FEF3C7;color:#92400E">LG</div>
                        <div class="inbox-row-body">
                            <div class="inbox-row-name">Luisa González</div>
                            <div class="inbox-row-preview">Quiero cancelar mi reserva...</div>
                        </div>
                        <div class="inbox-row-meta">
                            <div class="inbox-row-time">09:33</div>
                            <div class="inbox-tag tag-human">Agente</div>
                        </div>
                    </div>
                    <div class="inbox-row">
                        <div class="inbox-av" style="background:#E6F1FB;color:#0C447C">RM</div>
                        <div class="inbox-row-body">
                            <div class="inbox-row-name">reservas@booking.com</div>
                            <div class="inbox-row-preview">Nueva reserva confirmada...</div>
                        </div>
                        <div class="inbox-row-meta">
                            <div class="inbox-row-time">08:10</div>
                            <div class="inbox-tag tag-ai">Email</div>
                        </div>
                    </div>
                    <div class="inbox-row">
                        <div class="inbox-av" style="background:#FDF2F8;color:#86198F">AC</div>
                        <div class="inbox-row-body">
                            <div class="inbox-row-name">Andrés Castro</div>
                            <div class="inbox-row-preview">Hola, necesito una cotización...</div>
                        </div>
                        <div class="inbox-row-meta">
                            <div class="inbox-row-time">Ayer</div>
                            <div class="inbox-tag tag-wa">WhatsApp</div>
                            <div class="inbox-tag tag-ai">IA activa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     REVENUE
══════════════════════════════════════════════════════════ -->
<section class="revenue-section">
    <div class="container">
        <div class="reveal" style="max-width:600px">
            <div class="eyebrow">Revenue Manager IA</div>
            <h2 class="section-h">La IA que te hace ganar más, no solo trabajar menos.</h2>
            <p class="section-p">Precios dinámicos automáticos que se ajustan solos según demanda, eventos y competencia.</p>
        </div>

        <div class="revenue-grid">
            <div class="rev-card reveal">
                <div class="rev-card-head">
                    <div class="rev-icon red">📉</div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--navy)">Precio fijo todo el año</div>
                        <div style="font-size:.75rem;color:var(--gray-400)">Sin revenue management</div>
                    </div>
                </div>
                <div class="rev-before">
                    <strong>Pierdes dinero en temporada alta</strong> y tienes habitaciones vacías en temporada baja. Siempre le vendes al mismo precio a todo el mundo.
                </div>
                <div class="rev-after">
                    <strong>Con Revenue Manager IA:</strong> +34% RevPAR promedio. El precio sube cuando hay demanda y baja cuando hay que llenar.
                </div>
            </div>
            <div class="rev-card reveal">
                <div class="rev-card-head">
                    <div class="rev-icon red">📅</div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--navy)">Te enteras tarde de los eventos</div>
                        <div style="font-size:.75rem;color:var(--gray-400)">Sin monitoreo de demanda</div>
                    </div>
                </div>
                <div class="rev-before">
                    <strong>El festival de música del fin de semana</strong> lo sabías el jueves. Ya era tarde para subir precios.
                </div>
                <div class="rev-after">
                    <strong>Con Revenue Manager IA:</strong> el precio sube automáticamente 2 semanas antes cuando detecta demanda creciente en la zona.
                </div>
            </div>
            <div class="rev-card reveal">
                <div class="rev-card-head">
                    <div class="rev-icon red">🔄</div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--navy)">Actualización manual de canales</div>
                        <div style="font-size:.75rem;color:var(--gray-400)">Sin channel manager integrado</div>
                    </div>
                </div>
                <div class="rev-before">
                    <strong>Cambias el precio</strong> en Booking, luego en Expedia, luego en tu web… y en Airbnb te olvidaste.
                </div>
                <div class="rev-after">
                    <strong>Con Channel Manager IA:</strong> un cambio de precio se actualiza en todos tus canales en menos de 30 segundos.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     INTEGRACIONES
══════════════════════════════════════════════════════════ -->
<section class="integrations-section">
    <div class="container" style="text-align:center">
        <div class="reveal">
            <div class="eyebrow">Integraciones</div>
            <h2 class="section-h">Se conecta con las herramientas que ya usas</h2>
            <p class="section-p" style="margin:0 auto 2.5rem">¿No ves tu herramienta? Contáctanos — conectamos en 72 horas.</p>
        </div>

        <div class="int-grid reveal">
            <div class="int-pill"><div class="int-dot" style="background:#003580"></div>Booking.com</div>
            <div class="int-pill"><div class="int-dot" style="background:#E4002B"></div>Expedia</div>
            <div class="int-pill"><div class="int-dot" style="background:#FF5A5F"></div>Airbnb</div>
            <div class="int-pill"><div class="int-dot" style="background:#E6002D"></div>Despegar</div>
            <div class="int-pill"><div class="int-dot" style="background:#4285F4"></div>Google Hotel Ads</div>
            <div class="int-pill"><div class="int-dot" style="background:#00AF87"></div>TripAdvisor</div>
            <div class="int-pill"><div class="int-dot" style="background:#25D366"></div>WhatsApp API</div>
            <div class="int-pill"><div class="int-dot" style="background:#635BFF"></div>Stripe</div>
            <div class="int-pill"><div class="int-dot" style="background:#009EE3"></div>PayU</div>
            <div class="int-pill"><div class="int-dot" style="background:#009EE3"></div>Mercado Pago</div>
            <div class="int-pill"><div class="int-dot" style="background:#FF6900"></div>Zapier</div>
            <div class="int-pill"><div class="int-dot" style="background:#0078D4"></div>Outlook / Gmail</div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     TESTIMONIOS
══════════════════════════════════════════════════════════ -->
<section class="testimonials-section">
    <div class="container">
        <div class="reveal" style="text-align:center;max-width:580px;margin:0 auto 2.75rem">
            <div class="eyebrow">Resultados reales</div>
            <h2 class="section-h">Hoteles que ya duermen tranquilos</h2>
        </div>

        <div class="testimonials-grid">
            <div class="tcard reveal">
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn"><span>+</span>41<span>%</span></span>
                        <span class="ml">reservas directas</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">3<span>h</span></span>
                        <span class="ml">ahorradas/día</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn"><span>-</span>22<span>%</span></span>
                        <span class="ml">comisiones OTA</span>
                    </div>
                </div>
                <p class="tcard-quote">"Antes respondíamos 80 WhatsApps por día manualmente. Ahora la IA atiende el 94% sola y nuestras reservas directas subieron un 41% en el primer trimestre."</p>
                <div class="tcard-author">
                    <div class="author-av">MG</div>
                    <div>
                        <div class="author-name">María González</div>
                        <div class="author-role">Gerente General · Hotel Boutique Casa Verde</div>
                    </div>
                </div>
            </div>
            <div class="tcard reveal">
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn"><span>+</span>28<span>%</span></span>
                        <span class="ml">RevPAR</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn">98<span>%</span></span>
                        <span class="ml">satisfacción</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn"><span>&lt;</span>3<span>s</span></span>
                        <span class="ml">respuesta IA</span>
                    </div>
                </div>
                <p class="tcard-quote">"Configuramos todo en una tarde. Al día siguiente la IA ya estaba respondiendo consultas y cerrando reservas. No podía creer que fuera tan fácil."</p>
                <div class="tcard-author">
                    <div class="author-av" style="background:#EDE9FE;color:#5B21B6">CR</div>
                    <div>
                        <div class="author-name">Carlos Restrepo</div>
                        <div class="author-role">Director de Ventas · Apart Hotel El Poblado</div>
                    </div>
                </div>
            </div>
            <div class="tcard reveal">
                <div class="tcard-metrics">
                    <div class="metric-pill">
                        <span class="mn">89<span>%</span></span>
                        <span class="ml">menos trabajo manual</span>
                    </div>
                    <div class="metric-pill">
                        <span class="mn"><span>+</span>35<span>%</span></span>
                        <span class="ml">conversión</span>
                    </div>
                </div>
                <p class="tcard-quote">"Tenemos cabaña en zona rural sin recepcionista de noche. GuestHandle cambió completamente nuestro modelo: la IA atiende, la gente reserva y nosotros dormimos."</p>
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

<!-- ══════════════════════════════════════════════════════════
     PRECIOS
══════════════════════════════════════════════════════════ -->
<section id="precios" class="pricing-section">
    <div class="container">
        <div class="reveal" style="text-align:center">
            <div class="eyebrow" style="text-align:center">Planes</div>
            <h2 class="section-h" style="text-align:center">Precio justo. Sin sorpresas.</h2>
            <p class="section-p" style="margin:0 auto .5rem;text-align:center">Primer mes gratis en cualquier plan. Sin comisiones por reserva. Sin contratos anuales.</p>
        </div>

        <div class="pricing-grid">
            <div class="pcard reveal">
                <div class="p-name">Esencial</div>
                <div class="p-price"><sup>$</sup>99.000</div>
                <div class="p-period">COP / mes · hasta 10 unidades</div>
                <ul class="p-list">
                    <li>PMS completo + calendario</li>
                    <li>Motor de tarifas</li>
                    <li>WhatsApp IA (500 conv/mes)</li>
                    <li>Folio y punto de venta</li>
                    <li>Reportes básicos</li>
                    <li>Hasta 3 usuarios</li>
                    <li>Soporte por email</li>
                </ul>
                <a href="#registro" class="btn-plan outline">Empezar gratis</a>
            </div>

            <div class="pcard featured reveal">
                <div class="p-badge">Más popular</div>
                <div class="p-name">Profesional</div>
                <div class="p-price"><sup>$</sup>189.000</div>
                <div class="p-period">COP / mes · unidades ilimitadas</div>
                <ul class="p-list">
                    <li>Todo lo de Esencial</li>
                    <li>WhatsApp IA 24/7 ilimitado</li>
                    <li>Channel Manager (+200 canales)</li>
                    <li>Revenue Manager IA</li>
                    <li>Sitio web + motor de reservas</li>
                    <li>Inbox unificado multicanal</li>
                    <li>Check-in digital</li>
                    <li>Usuarios ilimitados</li>
                    <li>Soporte prioritario WhatsApp</li>
                </ul>
                <a href="#registro" class="btn-plan primary">Empezar 30 días gratis →</a>
            </div>
        </div>

        <p class="reveal" style="text-align:center;font-size:.78rem;color:var(--gray-400);margin-top:1.5rem">
            Sin tarjeta de crédito &nbsp;·&nbsp; Cancela en cualquier momento &nbsp;·&nbsp; Datos seguros en Colombia
        </p>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     REGISTRO
══════════════════════════════════════════════════════════ -->
<section id="registro" class="register-section">
    <div class="container">
        <div class="register-grid">

            <!-- Columna izquierda -->
            <div class="reveal">
                <div class="eyebrow">Registro gratuito</div>
                <h2 class="section-h">Empieza hoy,<br>en menos de 2 minutos.</h2>
                <p class="section-p" style="margin-bottom:2.25rem">Al registrarte, un asistente guiado te ayuda a configurar tu hotel paso a paso. En menos de 10 minutos ya tendrás la IA respondiendo por WhatsApp.</p>

                <div style="margin-bottom:2rem">
                    <div class="reg-left-step">
                        <div class="reg-step-circle">1</div>
                        <div class="reg-step-text">Completa el formulario — menos de 2 minutos</div>
                    </div>
                    <div class="reg-left-step">
                        <div class="reg-step-circle">2</div>
                        <div class="reg-step-text">El wizard te guía para configurar tu hotel</div>
                    </div>
                    <div class="reg-left-step">
                        <div class="reg-step-circle">3</div>
                        <div class="reg-step-text">30 días gratis con todas las funcionalidades</div>
                    </div>
                </div>

                <div style="padding:1.25rem;background:var(--gray-50);border-radius:var(--r2);border:1px solid var(--gray-100)">
                    <p style="font-size:.78rem;color:var(--gray-500);margin-bottom:.4rem">¿Ya tienes cuenta?</p>
                    <a href="/login" style="display:inline-flex;align-items:center;gap:.5rem;font-size:.875rem;color:var(--blue);font-weight:600">
                        Iniciar sesión en tu panel →
                    </a>
                </div>
            </div>

            <!-- Columna derecha: formulario -->
            <div class="reveal">
                <div class="rform">
                    <div id="formContent">
                        <h3 class="rform-title">Crear cuenta gratis</h3>
                        <p class="rform-sub">
                            <span style="background:var(--wa-light);color:var(--wa-dark);font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:4px">30 días gratis</span>
                            Sin tarjeta de crédito
                        </p>

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
                        <h3>¡Bienvenido a GuestHandle!</h3>
                        <p>Tu cuenta está lista. En un momento te redirigimos al asistente de configuración.</p>
                        <a href="/onboarding" class="btn-goto">Ir al wizard →</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     CTA FINAL
══════════════════════════════════════════════════════════ -->
<section class="final-cta">
    <h2>¿Cuántas reservas perdiste hoy por no responder a tiempo?</h2>
    <p>Empieza gratis hoy. Sin compromisos. En 10 minutos tu hotel ya tiene IA respondiendo en WhatsApp.</p>
    <div class="final-btns">
        <a href="#registro" class="btn-primary" style="font-size:1rem;padding:.9rem 2.25rem">Activar prueba gratuita →</a>
        <a href="https://wa.me/573000000000" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;gap:.6rem;background:rgba(255,255,255,.08);color:#fff;padding:.9rem 1.75rem;border-radius:var(--r2);font-size:.95rem;font-weight:500;border:1.5px solid rgba(255,255,255,.15);transition:all .2s">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Hablar con un asesor
        </a>
    </div>
    <p class="final-note">30 días de garantía o te devolvemos el dinero &nbsp;·&nbsp; Sin tarjeta de crédito &nbsp;·&nbsp; Cancela cuando quieras</p>
</section>

<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer>
    <div class="footer-logo">
        <div class="footer-logo-dot"></div>
        GuestHandle
    </div>

    <div class="footer-links">
        <a href="/terminos">Términos</a>
        <a href="/privacidad">Privacidad</a>
        <a href="/login">Iniciar sesión</a>
    </div>

    <div class="footer-right">
        &copy; <?= date('Y') ?> GuestHandle &middot; Todos los derechos reservados
    </div>
</footer>

<!-- WhatsApp flotante -->
<a href="https://wa.me/573000000000" target="_blank" rel="noopener" class="wa-float" title="Hablar por WhatsApp">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
                    setTimeout(function(){ e.target.classList.add('visible'); }, i * 70);
                    obs.unobserve(e.target);
                }
            });
        }, { threshold: 0.07 });

        revEls.forEach(function(el){ obs.observe(el); });

        /* ── Chat typing animation loop ── */
        var typingEl = document.querySelector('.chat-typing');
        if(typingEl){
            var replies = [
                'Sí incluye desayuno buffet para 2 personas 🍳 El total sería <strong>$960.000</strong> por 3 noches. ¿Confirmo? Solo necesito tu nombre completo y email.',
                '¡Perfecto! ¿Para qué nombre hago la reserva?'
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
                    meta.innerHTML = '10:25 am <span class="ticks">✓✓</span> Respondido automáticamente';
                    wrap.appendChild(bubble);
                    wrap.appendChild(meta);
                    typingEl.parentNode.insertBefore(wrap, typingEl);
                    step++;
                    setTimeout(showReply, 4500);
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

            if(!hotel)          return showError('El nombre del hotel es requerido.');
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