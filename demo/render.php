<?php

/**
 * Демо-рендер шаблона promo_cards без установленного 1С-Битрикс:
 * подключает настоящие result_modifier.php и template.php из /local/.
 *
 *   php demo/render.php _site               — собрать статическую страницу в _site/
 *   php -S localhost:8000 demo/render.php   — открыть на http://localhost:8000
 */

require __DIR__ . '/bitrix_stubs.php';

date_default_timezone_set('Europe/Moscow');

$templateDir = dirname(__DIR__) . '/local/templates/.default/components/bitrix/news.list/promo_cards';
$outDir = PHP_SAPI === 'cli' ? ($argv[1] ?? null) : null;

[$arParams, $arResult] = require __DIR__ . '/fixtures.php';

$content = (new CBitrixComponentTemplate($templateDir))->render($arParams, $arResult);
$version = static fn (string $file): string => substr(md5_file($templateDir . '/' . $file), 0, 8);

$styles = $outDir
    ? '<link rel="stylesheet" href="style.css?v=' . $version('style.css') . '">'
    : '<style>' . file_get_contents($templateDir . '/style.css') . '</style>';
$scripts = $outDir
    ? '<script src="script.js?v=' . $version('script.js') . '" defer></script>'
    : '<script>' . file_get_contents($templateDir . '/script.js') . '</script>';

ob_start();
require __DIR__ . '/layout.php';
$page = ob_get_clean();

if ($outDir === null) {
    echo $page;
    return;
}

if (!is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}
file_put_contents($outDir . '/index.html', $page);
copy($templateDir . '/style.css', $outDir . '/style.css');
copy($templateDir . '/script.js', $outDir . '/script.js');
