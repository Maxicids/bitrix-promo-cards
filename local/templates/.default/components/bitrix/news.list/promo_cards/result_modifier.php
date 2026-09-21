<?php
/**
 * Шаблон promo_cards компонента bitrix:news.list — подготовка данных.
 *
 * Для каждого элемента:
 *  - IS_HOT   => true, если до окончания акции осталось меньше 3 суток;
 *  - BADGE    => «Суперцена» при скидке больше 20%, иначе «Выгода»;
 *  - DISCOUNT_PERCENT — нормализованное число для вывода;
 *  - CARD_PICTURE — уменьшенная картинка анонса.
 *
 * Важно: result_modifier.php выполняется до записи $arResult в кеш компонента,
 * поэтому IS_HOT «замораживается» на время CACHE_TIME. См. README.md.
 *
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

$hotPeriod = 3 * 86400;     // «менее 3 дней», в секундах
$superPriceFrom = 20;       // скидка строго больше 20%

if (empty($arResult['ITEMS']) || !is_array($arResult['ITEMS'])) {
    return;
}

/**
 * Значение свойства элемента: сначала из PROPERTIES, затем из DISPLAY_PROPERTIES.
 */
$getPropertyValue = static function (array $item, string $code) {
    if (isset($item['PROPERTIES'][$code]['VALUE'])) {
        return $item['PROPERTIES'][$code]['VALUE'];
    }
    if (isset($item['DISPLAY_PROPERTIES'][$code]['VALUE'])) {
        return $item['DISPLAY_PROPERTIES'][$code]['VALUE'];
    }

    return null;
};

/**
 * Дата окончания акции -> timestamp.
 * В ТЗ DATE_ACTIVE_TO указано как свойство, но у элемента инфоблока есть
 * одноимённое стандартное поле «Окончание активности». Поддерживаем оба варианта:
 * приоритет у свойства, иначе берём поле (FIELD_CODE = DATE_ACTIVE_TO).
 * Если время не указано (тип «Дата»), акция действует до конца дня.
 */
$getActiveToTimestamp = static function (array $item) use ($getPropertyValue): ?int {
    $raw = $getPropertyValue($item, 'DATE_ACTIVE_TO');
    if (is_array($raw)) {
        $raw = reset($raw);
    }
    if ((string)$raw === '') {
        $raw = $item['DATE_ACTIVE_TO'] ?? ($item['ACTIVE_TO'] ?? '');
    }
    $raw = trim((string)$raw);
    if ($raw === '') {
        return null;
    }

    $timestamp = MakeTimeStamp($raw);
    if (!$timestamp) {
        return null;
    }
    if (!preg_match('/\d{1,2}:\d{2}/', $raw)) {
        $timestamp += 86399; // 23:59:59 того же дня
    }

    return (int)$timestamp;
};

$now = time();

foreach ($arResult['ITEMS'] as &$arItem) {
    // --- IS_HOT ---------------------------------------------------------------
    $activeTo = $getActiveToTimestamp($arItem);
    $secondsLeft = $activeTo !== null ? $activeTo - $now : null;

    $arItem['ACTIVE_TO_TIMESTAMP'] = $activeTo;
    $arItem['IS_HOT'] = $secondsLeft !== null
        && $secondsLeft > 0
        && $secondsLeft < $hotPeriod;

    // --- DISCOUNT_PERCENT -----------------------------------------------------
    $discountRaw = $getPropertyValue($arItem, 'DISCOUNT_PERCENT');
    if (is_array($discountRaw)) {
        $discountRaw = reset($discountRaw);
    }
    $discountRaw = str_replace([',', '%', ' '], ['.', '', ''], (string)$discountRaw);
    $discount = is_numeric($discountRaw) ? (float)$discountRaw : null;

    $arItem['DISCOUNT_PERCENT'] = $discount;

    // --- BADGE ----------------------------------------------------------------
    // По ТЗ значение BADGE вычисляется всегда и перекрывает то, что внесено в свойство.
    // BADGE_CODE — служебный код для CSS-модификатора бейджа.
    $isSuperPrice = $discount !== null && $discount > $superPriceFrom;
    $arItem['BADGE_CODE'] = $isSuperPrice ? 'super' : 'default';
    $arItem['BADGE'] = $isSuperPrice
        ? Loc::getMessage('PROMO_CARDS_BADGE_SUPER')
        : Loc::getMessage('PROMO_CARDS_BADGE_DEFAULT');

    // --- Картинка анонса ------------------------------------------------------
    $arItem['CARD_PICTURE'] = null;
    if (!empty($arItem['PREVIEW_PICTURE']) && is_array($arItem['PREVIEW_PICTURE'])) {
        $resized = CFile::ResizeImageGet(
            $arItem['PREVIEW_PICTURE'],
            ['width' => 640, 'height' => 400],
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true
        );

        $arItem['CARD_PICTURE'] = [
            'SRC' => $resized['src'] ?? ($arItem['PREVIEW_PICTURE']['SRC'] ?? ''),
            'WIDTH' => $resized['width'] ?? ($arItem['PREVIEW_PICTURE']['WIDTH'] ?? null),
            'HEIGHT' => $resized['height'] ?? ($arItem['PREVIEW_PICTURE']['HEIGHT'] ?? null),
            // news.list сам подставляет NAME, если ALT не заполнен
            'ALT' => ($arItem['PREVIEW_PICTURE']['ALT'] ?? '') ?: $arItem['NAME'],
        ];
    }
}
unset($arItem);
