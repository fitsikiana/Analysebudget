<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>RégieBudget — Gestion Budgétaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
           REGIEBUDGET - LANDING PAGE AVEC ANIMATIONS
           Version avec animations fluides sans emojis ni icônes
           ============================================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #FFFFFF;
            color: #1F2937;
            line-height: 1.4;
            overflow-x: hidden;
        }

        /* ============================================================
           ANIMATIONS
           ============================================================ */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.92);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.04);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }
            100% {
                background-position: 200% center;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes borderGlow {
            0%, 100% {
                border-color: #16A34A;
                box-shadow: 0 0 15px rgba(22, 163, 74, 0.1);
            }
            50% {
                border-color: #DC2626;
                box-shadow: 0 0 25px rgba(220, 38, 38, 0.15);
            }
        }

        @keyframes rotateIn {
            from {
                opacity: 0;
                transform: rotate(-8deg) scale(0.9);
            }
            to {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
        }

        /* ============================================================
           CLASSES D'ANIMATION
           ============================================================ */
        .animate-fade-down {
            animation: fadeInDown 0.7s ease both;
        }

        .animate-fade-up {
            animation: fadeInUp 0.8s ease both;
        }

        .animate-fade-up-delay-1 {
            animation: fadeInUp 0.8s ease 0.1s both;
        }
        .animate-fade-up-delay-2 {
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        .animate-fade-up-delay-3 {
            animation: fadeInUp 0.8s ease 0.3s both;
        }
        .animate-fade-up-delay-4 {
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        .animate-fade-up-delay-5 {
            animation: fadeInUp 0.8s ease 0.5s both;
        }
        .animate-fade-up-delay-6 {
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .animate-fade-left {
            animation: fadeInLeft 0.8s ease both;
        }

        .animate-fade-right {
            animation: fadeInRight 0.8s ease both;
        }

        .animate-scale {
            animation: fadeInScale 0.8s ease both;
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .animate-pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        .animate-border-glow {
            animation: borderGlow 3s ease-in-out infinite;
        }

        .animate-rotate {
            animation: rotateIn 0.8s ease both;
        }

        .animate-count {
            animation: countUp 0.6s ease both;
        }

        /* ============================================================
           NAVBAR
           ============================================================ */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-bottom: 2px solid #16A34A;
            padding: 12px 24px;
            z-index: 100;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            animation: fadeInDown 0.6s ease;
        }

        .navbar.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .nav-container {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #DC2626;
            text-decoration: none;
            letter-spacing: -0.5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .logo:hover {
            transform: scale(1.02);
        }

        .logo span {
            color: #16A34A;
            transition: color 0.3s ease;
        }

        .logo:hover span {
            color: #DC2626;
        }

        .logo:hover .logo-text {
            color: #16A34A;
        }

        .btn-nav {
            background: #16A34A;
            color: #FFFFFF;
            padding: 7px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            border: 2px solid #16A34A;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-nav:hover::before {
            left: 100%;
        }

        .btn-nav:hover {
            background: #FFFFFF;
            color: #16A34A;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
        }

        /* ============================================================
           HERO
           ============================================================ */
        .hero {
            padding: 110px 24px 60px;
            background: #FFFFFF;
            text-align: center;
            border-bottom: 1px solid #E5E7EB;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(22, 163, 74, 0.03) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        .hero-badge {
            display: inline-block;
            background: #DCFCE7;
            color: #15803D;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 4px;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            animation: fadeInDown 0.7s ease;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
            color: #1F2937;
            line-height: 1.2;
            animation: fadeInUp 0.8s ease 0.1s both;
            position: relative;
            z-index: 1;
        }

        .hero h1 .red {
            color: #DC2626;
            display: inline-block;
            animation: fadeInScale 0.8s ease 0.3s both;
            position: relative;
        }

        .hero h1 .red::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: #DC2626;
            border-radius: 2px;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }

        .hero h1 .green {
            color: #16A34A;
            display: inline-block;
            animation: fadeInScale 0.8s ease 0.4s both;
            position: relative;
        }

        .hero h1 .green::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: #16A34A;
            border-radius: 2px;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite 0.5s;
        }

        .hero p {
            font-size: 0.95rem;
            color: #6B7280;
            max-width: 500px;
            margin: 0 auto 28px;
            animation: fadeInUp 0.8s ease 0.2s both;
            position: relative;
            z-index: 1;
        }

        .hero-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease 0.3s both;
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: #DC2626;
            color: #FFFFFF;
            padding: 11px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border: 2px solid #DC2626;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: #FFFFFF;
            color: #DC2626;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(220, 38, 38, 0.3);
        }

        .btn-secondary {
            background: #FFFFFF;
            color: #16A34A;
            padding: 11px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border: 2px solid #16A34A;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(22, 163, 74, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .btn-secondary:hover::before {
            left: 100%;
        }

        .btn-secondary:hover {
            background: #16A34A;
            color: #FFFFFF;
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(22, 163, 74, 0.3);
        }

        /* ============================================================
           STATS
           ============================================================ */
        .stats {
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            border-bottom: 1px solid #E5E7EB;
            padding: 32px 24px;
            position: relative;
            overflow: hidden;
        }

        .stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #DC2626, #16A34A, #DC2626);
            background-size: 200% 100%;
            animation: shimmer 4s linear infinite;
        }

        .stats-inner {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            padding: 12px 20px;
            border-radius: 8px;
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            min-width: 120px;
            transition: all 0.4s ease;
        }

        .stat-item.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-item:hover {
            transform: translateY(-4px);
            border-color: #16A34A;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .stat-item:nth-child(1) {
            transition-delay: 0.1s;
        }
        .stat-item:nth-child(2) {
            transition-delay: 0.2s;
        }
        .stat-item:nth-child(3) {
            transition-delay: 0.3s;
        }
        .stat-item:nth-child(4) {
            transition-delay: 0.4s;
        }

        .stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: #DC2626;
            display: block;
            transition: all 0.3s ease;
        }

        .stat-item:hover .stat-num {
            color: #16A34A;
            transform: scale(1.05);
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6B7280;
            margin-top: 2px;
            display: block;
            transition: color 0.3s ease;
        }

        .stat-item:hover .stat-label {
            color: #1F2937;
        }

        /* ============================================================
           FEATURES
           ============================================================ */
        .features {
            padding: 60px 24px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1F2937;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .section-title.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-sub {
            text-align: center;
            font-size: 0.85rem;
            color: #6B7280;
            margin-bottom: 36px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.1s;
        }

        .section-sub.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: #FFFFFF;
            padding: 22px 18px;
            border-radius: 10px;
            border: 1.5px solid #E5E7EB;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }

        .card.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .card:nth-child(1) {
            transition-delay: 0.1s;
        }
        .card:nth-child(2) {
            transition-delay: 0.15s;
        }
        .card:nth-child(3) {
            transition-delay: 0.2s;
        }
        .card:nth-child(4) {
            transition-delay: 0.25s;
        }
        .card:nth-child(5) {
            transition-delay: 0.3s;
        }
        .card:nth-child(6) {
            transition-delay: 0.35s;
        }

        .card:hover {
            border-color: #16A34A;
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .card-tag {
            display: inline-block;
            background: #DCFCE7;
            color: #15803D;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        .card:hover .card-tag {
            background: #DC2626;
            color: #FFFFFF;
            transform: scale(1.05);
        }

        .card h3 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #1F2937;
            transition: color 0.3s ease;
        }

        .card:hover h3 {
            color: #DC2626;
        }

        .card p {
            font-size: 0.8rem;
            color: #6B7280;
            line-height: 1.5;
            transition: color 0.3s ease;
        }

        .card:hover p {
            color: #1F2937;
        }

        /* ============================================================
           DIVIDER
           ============================================================ */
        .divider {
            height: 4px;
            background: linear-gradient(to right, #DC2626, #16A34A);
            max-width: 1000px;
            margin: 0 auto;
            border-radius: 2px;
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }

        /* ============================================================
           CTA
           ============================================================ */
        .cta {
            background: #16A34A;
            color: #FFFFFF;
            text-align: center;
            padding: 50px 24px;
            position: relative;
            overflow: hidden;
        }

        .cta::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .cta::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        .cta h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .cta h2.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .cta p {
            font-size: 0.9rem;
            margin-bottom: 24px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.1s;
        }

        .cta p.visible {
            opacity: 0.9;
            transform: translateY(0);
        }

        .btn-cta {
            background: #FFFFFF;
            color: #DC2626;
            padding: 12px 32px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border: 2px solid #FFFFFF;
            display: inline-block;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 1;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.2s;
            overflow: hidden;
        }

        .btn-cta.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .btn-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(220, 38, 38, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .btn-cta:hover::before {
            left: 100%;
        }

        .btn-cta:hover {
            background: #DC2626;
            color: #FFFFFF;
            border-color: #FFFFFF;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .footer {
            background: #1F2937;
            color: #9CA3AF;
            text-align: center;
            padding: 24px 20px;
            font-size: 0.72rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.3s;
        }

        .footer.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .footer strong {
            color: #FFFFFF;
            transition: color 0.3s ease;
        }

        .footer:hover strong {
            color: #16A34A;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */
        @media (max-width: 640px) {
            .navbar {
                padding: 10px 16px;
            }
            .logo {
                font-size: 1.2rem;
            }
            .btn-nav {
                padding: 5px 14px;
                font-size: 0.75rem;
            }
            .hero {
                padding: 90px 16px 44px;
            }
            .hero h1 {
                font-size: 1.5rem;
            }
            .hero-btns {
                flex-direction: column;
                align-items: center;
            }
            .hero-btns .btn-primary,
            .hero-btns .btn-secondary {
                width: 100%;
                max-width: 280px;
                text-align: center;
            }
            .features {
                padding: 44px 16px;
            }
            .grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .cta h2 {
                font-size: 1.2rem;
            }
            .stat-num {
                font-size: 1.4rem;
            }
            .stat-item {
                min-width: 80px;
                padding: 10px 14px;
            }
            .stats-inner {
                gap: 12px;
            }
        }

        @media (min-width: 641px) and (max-width: 900px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .stat-item {
                min-width: 100px;
            }
        }

        /* ============================================================
           SCROLLBAR
           ============================================================ */
        ::-webkit-scrollbar {
            width: 8px;
            background: #F9FAFB;
        }

        ::-webkit-scrollbar-thumb {
            background: #16A34A;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #DC2626;
        }

        ::selection {
            background: #DC2626;
            color: white;
        }
    </style>
</head>
<body>

    <!-- ============================================================
    NAVBAR
    ============================================================ -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="landing.php" class="logo">
                Régie<span>Budget</span>
            </a>
            <a href="acceuil.php" class="btn-nav">Connexion</a>
        </div>
    </nav>

    <!-- ============================================================
    HERO SECTION
    ============================================================ -->
    <section class="hero">
        <div class="hero-badge">Système de gestion budgétaire</div>
        <h1>
            Bienvenue sur le système<br>
            <span class="red">Régie</span> <span class="green">Budget</span>
        </h1>
        <p>Suivez vos dépenses, contrôlez vos budgets et générez des rapports en temps réel.</p>
        <div class="hero-btns">
            <a href="acceuil.php" class="btn-primary">Se connecter</a>
            <a href="#fonctionnalites" class="btn-secondary">Voir les fonctionnalités</a>
        </div>
    </section>

    <!-- ============================================================
    STATS SECTION
    ============================================================ -->
    <div class="stats">
        <div class="stats-inner">
            <div class="stat-item" data-delay="0">
                <span class="stat-num">Moyen</span>
                <span class="stat-label">Sécurisé</span>
            </div>
            <div class="stat-item" data-delay="1">
                <span class="stat-num">6</span>
                <span class="stat-label">Modules disponibles</span>
            </div>
            <div class="stat-item" data-delay="2">
                <span class="stat-num">PDF</span>
                <span class="stat-label">Export rapports</span>
            </div>
            <div class="stat-item" data-delay="3">
                <span class="stat-num">ANALYSE</span>
                <span class="stat-label">Analyse de données</span>
            </div>
        </div>
    </div>

    <!-- ============================================================
    FEATURES SECTION
    ============================================================ -->
    <section class="features" id="fonctionnalites">
        <h2 class="section-title">Fonctionnalités</h2>
        <p class="section-sub">Tout ce dont vous avez besoin pour gérer votre budget universitaire</p>
        <div class="grid">
            <div class="card">
                <span class="card-tag">Analyse</span>
                <h3>Tableaux de bord</h3>
                <p>Visualisation en temps réel de vos données budgétaires et dépenses.</p>
            </div>
            <div class="card">
                <span class="card-tag">Import</span>
                <h3>Import Excel</h3>
                <p>Importez facilement vos fichiers Excel pour un traitement automatique.</p>
            </div>
            <div class="card">
                <span class="card-tag">Export</span>
                <h3>Rapports PDF / Excel</h3>
                <p>Exportez vos analyses sous format PDF ou Excel en un clic.</p>
            </div>
            <div class="card">
                <span class="card-tag">Securité</span>
                <h3>Accées securisé</h3>
                <p>Authentification contrôlée et gestion des droits par profil.</p>
            </div>
            <div class="card">
                <span class="card-tag">Analyse</span>
                <h3>Analyse budget</h3>
                <p>Analyse de vos données pour mieux anticiper vos besoins.</p>
            </div>
            <div class="card">
                <span class="card-tag">Administration</span>
                <h3>Gestion utilisateurs</h3>
                <p>Créez et administrez les comptes utilisateurs en toute simplicité.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================
    DIVIDER
    ============================================================ -->
    <div class="divider"></div>

    <!-- ============================================================
    CTA SECTION
    ============================================================ -->
    <section class="cta">
        <h2>Prêt à simplifier votre gestion budgétaire ?</h2>
        <p>Connectez-vous et commencez à gérer vos budgets dès maintenant.</p>
        <a href="acceuil.php" class="btn-cta">Se connecter</a>
    </section>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <footer class="footer">
        <p><strong>RégieBudget</strong> — Solution de gestion budgétaire universitaire &nbsp;|&nbsp; &copy;</p>
    </footer>

    <!-- ============================================================
    SCRIPTS
    ============================================================ -->
    <script>
        // ============================================================
        // NAVBAR SCROLL EFFECT
        // ============================================================
        const navbar = document.getElementById('navbar');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // ============================================================
        // INTERSECTION OBSERVER - ANIMATIONS AU SCROLL
        // ============================================================
        const observerOptions = {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observer pour les statistiques
        document.querySelectorAll('.stat-item').forEach(el => {
            observer.observe(el);
        });

        // Observer pour les titres
        document.querySelectorAll('.section-title, .section-sub').forEach(el => {
            observer.observe(el);
        });

        // Observer pour les cartes
        document.querySelectorAll('.card').forEach(el => {
            observer.observe(el);
        });

        // Observer pour la CTA
        document.querySelectorAll('.cta h2, .cta p, .btn-cta').forEach(el => {
            observer.observe(el);
        });

        // Observer pour le footer
        const footerObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    document.querySelector('.footer').classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        footerObserver.observe(document.querySelector('.footer'));

        // ============================================================
        // ANIMATION DES STATISTIQUES AU SCROLL
        // ============================================================
        const statItems = document.querySelectorAll('.stat-item');
        
        const statObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const delay = parseInt(el.getAttribute('data-delay')) || 0;
                    setTimeout(() => {
                        el.classList.add('visible');
                    }, delay * 150);
                }
            });
        }, { threshold: 0.3 });

        statItems.forEach(item => {
            statObserver.observe(item);
        });

        // ============================================================
        // SMOOTH SCROLL POUR LES ANCRES
        // ============================================================
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // ============================================================
        // PARALLAX EFFECT SUR LE HERO
        // ============================================================
        document.addEventListener('mousemove', function(e) {
            const hero = document.querySelector('.hero');
            if (!hero) return;
            
            const x = (e.clientX / window.innerWidth - 0.5) * 6;
            const y = (e.clientY / window.innerHeight - 0.5) * 6;
            
            const title = hero.querySelector('h1');
            const badge = hero.querySelector('.hero-badge');
            const desc = hero.querySelector('p');
            const btns = hero.querySelector('.hero-btns');
            
            if (title) {
                title.style.transform = `translate(${x * 0.5}px, ${y * 0.5}px)`;
            }
            if (badge) {
                badge.style.transform = `translate(${x * 0.3}px, ${y * 0.3}px)`;
            }
            if (desc) {
                desc.style.transform = `translate(${x * 0.4}px, ${y * 0.4}px)`;
            }
            if (btns) {
                btns.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px)`;
            }
        });
        // ANIMATION DES STATISTIQUES AU CHARGEMENT
        function animateStats() {
            const stats = document.querySelectorAll('.stat-num');
            stats.forEach(stat => {
                const text = stat.textContent.trim();
                const num = parseInt(text);
                if (!isNaN(num) && num > 0 && num < 1000) {
                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                let current = 0;
                                const target = num;
                                const duration = 1200;
                                const step = target / (duration / 16);
                                
                                const timer = setInterval(() => {
                                    current += step;
                                    if (current >= target) {
                                        current = target;
                                        clearInterval(timer);
                                    }
                                    stat.textContent = Math.floor(current);
                                }, 16);
                                
                                observer.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.5 });
                    observer.observe(stat);
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            animateStats();
        });
        console.log('RegieBudget - Landing Page avec animations fluides');
    </script>
</body>
</html>