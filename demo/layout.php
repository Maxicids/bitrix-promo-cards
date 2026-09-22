<?php
/**
 * @var string $content
 * @var string $styles
 * @var string $scripts
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Акции и спецпредложения</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
    <?= $styles ?>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Manrope, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f7f6fb;
            overflow-x: hidden;
        }

        .page-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .page-bg span {
            position: absolute;
            width: 46vmax;
            height: 46vmax;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .45;
            animation: page-drift 22s ease-in-out infinite alternate;
            will-change: translate, scale;
        }

        .page-bg span:nth-child(1) {
            top: -18vmax;
            left: -12vmax;
            background: #c4b5fd;
        }

        .page-bg span:nth-child(2) {
            top: 20vh;
            right: -18vmax;
            background: #fbcfe8;
            animation-delay: -7s;
        }

        .page-bg span:nth-child(3) {
            bottom: -22vmax;
            left: 25vw;
            background: #a5f3fc;
            animation-delay: -14s;
        }

        .page {
            max-width: 1240px;
            margin: 0 auto;
            padding: 72px 20px 96px;
        }

        .page__title {
            margin: 0 0 48px;
            font-size: clamp(34px, 6vw, 64px);
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.02;
            color: transparent;
            background: linear-gradient(100deg, #14142b 10%, #7f5af0 45%, #ff2e63 70%, #ff8a00 90%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            animation: page-title 8s ease-in-out infinite alternate, page-rise .9s cubic-bezier(.2, .8, .2, 1) both;
        }

        @keyframes page-drift {
            to {
                translate: 6vmax 8vmax;
                scale: 1.15;
            }
        }

        @keyframes page-title {
            to {
                background-position: 100% 0;
            }
        }

        @keyframes page-rise {
            from {
                opacity: 0;
                translate: 0 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .page-bg span,
            .page__title {
                animation: none;
            }
        }
    </style>
</head>
<body>
<div class="page-bg" aria-hidden="true"><span></span><span></span><span></span></div>
<main class="page">
    <h1 class="page__title">Акции и&nbsp;спецпредложения</h1>
    <?= $content ?>
</main>
<?= $scripts ?>
</body>
</html>
