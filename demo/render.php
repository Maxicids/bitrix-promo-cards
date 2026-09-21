<?php
/**
 * Демо-рендер шаблона promo_cards без установленного 1С-Битрикс.
 *
 * Подключает настоящие result_modifier.php и template.php из /local/...,
 * подсовывая им $arResult в том виде, в каком его отдаёт bitrix:news.list.
 *
 *   php demo/render.php            -> HTML в stdout
 *   php demo/render.php _site      -> _site/index.html + _site/style.css
 *   php -S localhost:8000 demo/render.php   -> открыть http://localhost:8000
 */

require __DIR__ . '/bitrix_stubs.php';

$root = dirname(__DIR__);
$templateDir = $root . '/local/templates/.default/components/bitrix/news.list/promo_cards';

date_default_timezone_set('Europe/Moscow');

/** Фото с Unsplash (бесплатная лицензия, хотлинк разрешён). */
function demoPicture(string $photoId, string $alt): array
{
    return [
        'ID' => crc32($photoId),
        'SRC' => 'https://images.unsplash.com/photo-' . $photoId . '?auto=format&fit=crop&w=800&h=600&q=80',
        'WIDTH' => 800,
        'HEIGHT' => 600,
        'ALT' => $alt,
    ];
}

/** Элемент в формате $arResult['ITEMS'][] компонента news.list. */
function demoItem(int $id, string $name, string $text, ?string $activeTo, $discount, ?array $picture, string $badge = ''): array
{
    return [
        'ID' => $id,
        'IBLOCK_ID' => 5,
        'NAME' => htmlspecialcharsbx($name),
        'PREVIEW_TEXT' => htmlspecialcharsbx($text),
        'PREVIEW_PICTURE' => $picture,
        'DETAIL_TEXT' => 'Подробное описание',
        'DETAIL_PAGE_URL' => '#promo-' . $id,
        'EDIT_LINK' => '',
        'DELETE_LINK' => '',
        'PROPERTIES' => [
            'DATE_ACTIVE_TO' => ['CODE' => 'DATE_ACTIVE_TO', 'VALUE' => $activeTo ?? ''],
            'DISCOUNT_PERCENT' => ['CODE' => 'DISCOUNT_PERCENT', 'VALUE' => $discount],
            'BADGE' => ['CODE' => 'BADGE', 'VALUE' => $badge],
        ],
    ];
}

$at = static fn (string $modify): string => (new DateTime())->modify($modify)->format('d.m.Y H:i:s');

$arParams = [
    'IBLOCK_ID' => 5,
    'DISPLAY_TOP_PAGER' => false,
    'DISPLAY_BOTTOM_PAGER' => false,
    'HIDE_LINK_WHEN_NO_DETAIL' => true,
];

$arResult = [
    'USER_HAVE_ACCESS' => true,
    'NAV_STRING' => '',
    'ITEMS' => [
        demoItem(1, 'Большая осенняя распродажа', 'Скидки на одежду, обувь и аксессуары во всех магазинах сети. Успейте забрать лучшие позиции.',
            $at('+1 day 6 hours'), 40, demoPicture('1513884923967-4b182ef167ab', 'Пакеты с покупками')),
        demoItem(2, 'Вторая чашка кофе в подарок', 'Закажите любой напиток до 11:00, и второй сварим бесплатно.',
            $at('+2 days 20 hours'), 15, demoPicture('1506372023823-741c83b836fe', 'Капучино')),
        demoItem(3, 'Кроссовки новой коллекции', 'Лёгкие беговые модели по специальной цене для участников клуба.',
            $at('+10 days'), 25, demoPicture('1600185365483-26d7a4cc7519', 'Кроссовки')),
        demoItem(4, 'Рюкзаки к учебному году', 'Скидка ровно 20% — по правилам это ещё «Выгода», а не «Суперцена».',
            $at('+3 days 4 hours'), '20', demoPicture('1553062407-98eeb64c6a62', 'Рюкзак')),
        demoItem(5, 'Музыка без рекламы на полгода', 'Подписка со скидкой 22,5% и беспроводные наушники в подарок.',
            (new DateTime('+30 days'))->format('d.m.Y'), '22,5', demoPicture('1505740420928-5e560c06d30e', 'Наушники')),
        demoItem(6, 'Абонемент в бассейн', 'Последние часы акции: безлимитное посещение и сауна включены.',
            $at('+5 hours'), 10, demoPicture('1530549387789-4c1017266635', 'Пловец в бассейне'), 'Суперцена'),
        demoItem(7, 'Пицца 2+1', 'Третья пицца бесплатно при заказе через сайт или приложение.',
            $at('+6 days'), 33, demoPicture('1513104890138-7c749659a591', 'Пицца')),
        demoItem(8, 'СПА-день для двоих', 'Массаж, чайная церемония и бассейн — идеальный подарок.',
            $at('+1 day 20 hours'), 30, demoPicture('1544161515-4ab6ce6db874', 'Массаж в СПА')),
    ],
];

$template = new CBitrixComponentTemplate($templateDir);
$html = $template->render($arParams, $arResult);

$outDir = $argv[1] ?? null;
$css = file_get_contents($templateDir . '/style.css');
$js = file_get_contents($templateDir . '/script.js');
$assets = $outDir
    ? '<link rel="stylesheet" href="style.css">'
    : '<style>' . $css . '</style>';
$scripts = $outDir
    ? '<script src="script.js" defer></script>'
    : '<script>' . $js . '</script>';

$page = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Акции и спецпредложения</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {$assets}
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Manrope, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f7f6fb;
            overflow-x: hidden;
        }
        .bg { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .bg span {
            position: absolute; width: 46vmax; height: 46vmax; border-radius: 50%;
            filter: blur(90px); opacity: .45; animation: drift 22s ease-in-out infinite alternate;
        }
        .bg span:nth-child(1) { top: -18vmax; left: -12vmax; background: #c4b5fd; }
        .bg span:nth-child(2) { top: 20vh; right: -18vmax; background: #fbcfe8; animation-delay: -7s; }
        .bg span:nth-child(3) { bottom: -22vmax; left: 25vw; background: #a5f3fc; animation-delay: -14s; }
        @keyframes drift { to { translate: 6vmax 8vmax; scale: 1.15; } }
        .demo { max-width: 1240px; margin: 0 auto; padding: 72px 20px 96px; }
        .demo__title {
            margin: 0 0 48px;
            font-size: clamp(34px, 6vw, 64px);
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.02;
            background: linear-gradient(100deg, #14142b 10%, #7f5af0 45%, #ff2e63 70%, #ff8a00 90%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: title 8s ease-in-out infinite alternate, rise .9s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes title { to { background-position: 100% 0; } }
        @keyframes rise { from { opacity: 0; translate: 0 24px; } }
        @media (prefers-reduced-motion: reduce) { .bg span, .demo__title { animation: none; } }
    </style>
</head>
<body>
<div class="bg" aria-hidden="true"><span></span><span></span><span></span></div>
<main class="demo">
    <h1 class="demo__title">Акции и&nbsp;спецпредложения</h1>
    {$html}
</main>
{$scripts}
</body>
</html>
HTML;

if ($outDir) {
    if (!is_dir($outDir)) {
        mkdir($outDir, 0775, true);
    }
    file_put_contents($outDir . '/index.html', $page);
    copy($templateDir . '/style.css', $outDir . '/style.css');
    copy($templateDir . '/script.js', $outDir . '/script.js');
    fwrite(STDERR, "Rendered to {$outDir}/index.html\n");
} else {
    echo $page;
}
