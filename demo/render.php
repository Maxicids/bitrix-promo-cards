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

/** Картинка-заглушка (SVG data URI), чтобы демо не зависело от внешних ресурсов. */
function demoPicture(string $emoji, string $from, string $to): array
{
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="400" viewBox="0 0 640 400">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="' . $from . '"/><stop offset="1" stop-color="' . $to . '"/>'
        . '</linearGradient></defs><rect width="640" height="400" fill="url(#g)"/>'
        . '<text x="320" y="235" font-size="140" text-anchor="middle">' . $emoji . '</text></svg>';

    return [
        'ID' => crc32($emoji),
        'SRC' => 'data:image/svg+xml;base64,' . base64_encode($svg),
        'WIDTH' => 640,
        'HEIGHT' => 400,
        'ALT' => '',
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
        demoItem(1, 'Чёрная пятница в сентябре', 'Скидка на всю технику до конца недели. Успейте забрать лучшие позиции.',
            $at('+1 day'), 35, demoPicture('🛒', '#ffd6a5', '#ff9a8b')),
        demoItem(2, 'Кофе к завтраку', 'Вторая чашка в подарок при заказе до 11:00.',
            $at('+2 days 20 hours'), 15, demoPicture('☕', '#e9d5ff', '#c4b5fd')),
        demoItem(3, 'Осенняя распродажа обуви', 'Новая коллекция по специальным ценам.',
            $at('+10 days'), 25, demoPicture('👟', '#bbf7d0', '#86efac'), 'Выгода'),
        demoItem(4, 'Ровно 20% на аксессуары', 'Граничный случай: скидка ровно 20% — это ещё «Выгода».',
            $at('+3 days 1 hour'), '20', demoPicture('🎒', '#bae6fd', '#7dd3fc')),
        demoItem(5, 'Подписка на полгода', 'Дата без времени и скидка строкой «22,5» — всё нормализуется.',
            (new DateTime('+30 days'))->format('d.m.Y'), '22,5', null),
        demoItem(6, 'Абонемент в бассейн', 'Последний день акции — метка «Заканчивается!».',
            $at('+5 hours'), 10, demoPicture('🏊', '#fde68a', '#fca5a5'), 'Суперцена'),
    ],
];

$template = new CBitrixComponentTemplate($templateDir);
$html = $template->render($arParams, $arResult);

$outDir = $argv[1] ?? null;
$cssHref = $outDir ? 'style.css' : null;
$css = $cssHref ? '' : '<style>' . file_get_contents($templateDir . '/style.css') . '</style>';
$cssLink = $cssHref ? '<link rel="stylesheet" href="' . $cssHref . '">' : '';
$generatedAt = date('d.m.Y H:i');

$page = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Акции и спецпредложения — демо шаблона promo_cards</title>
    {$cssLink}{$css}
    <style>
        body { margin: 0; padding: 32px 16px; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f9fafb; }
        .demo { max-width: 1200px; margin: 0 auto; }
        .demo h1 { margin: 0 0 8px; }
        .demo p { margin: 0 0 24px; color: #6b7280; }
    </style>
</head>
<body>
<main class="demo">
    <h1>Акции и спецпредложения</h1>
    <p>Демо-рендер шаблона <code>bitrix:news.list / promo_cards</code> на тестовых данных. Сгенерировано {$generatedAt} (МСК).</p>
    {$html}
</main>
</body>
</html>
HTML;

if ($outDir) {
    if (!is_dir($outDir)) {
        mkdir($outDir, 0775, true);
    }
    file_put_contents($outDir . '/index.html', $page);
    copy($templateDir . '/style.css', $outDir . '/style.css');
    fwrite(STDERR, "Rendered to {$outDir}/index.html\n");
} else {
    echo $page;
}
