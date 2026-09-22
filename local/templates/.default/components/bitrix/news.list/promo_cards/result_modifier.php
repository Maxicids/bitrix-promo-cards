<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 */

Loc::loadMessages(__FILE__);

if (empty($arResult['ITEMS'])) {
    return;
}

$hotPeriod = 3 * 86400;
$superPriceThreshold = 20;
$now = time();

$propertyValue = static function (array $item, string $code) {
    $value = $item['PROPERTIES'][$code]['VALUE'] ?? $item['DISPLAY_PROPERTIES'][$code]['VALUE'] ?? null;

    return is_array($value) ? reset($value) : $value;
};

// DATE_ACTIVE_TO в ТЗ описано как свойство, но у элемента есть одноимённое поле — поддерживаем оба
$activeToTimestamp = static function (array $item) use ($propertyValue): ?int {
    $raw = trim((string)($propertyValue($item, 'DATE_ACTIVE_TO') ?: ($item['DATE_ACTIVE_TO'] ?? '')));
    $timestamp = $raw !== '' ? MakeTimeStamp($raw) : false;
    if (!$timestamp) {
        return null;
    }

    // дата без времени действует до конца дня
    return preg_match('/\d{1,2}:\d{2}/', $raw) ? (int)$timestamp : (int)$timestamp + 86399;
};

$discountPercent = static function (array $item) use ($propertyValue): ?float {
    $raw = str_replace([',', '%', ' '], ['.', '', ''], (string)$propertyValue($item, 'DISCOUNT_PERCENT'));

    return is_numeric($raw) ? (float)$raw : null;
};

$resizedPicture = static function ($file, int $width, int $height, string $alt): ?array {
    if (empty($file) || !is_array($file)) {
        return null;
    }

    $resized = CFile::ResizeImageGet($file, ['width' => $width, 'height' => $height], BX_RESIZE_IMAGE_PROPORTIONAL, true);

    return [
        'SRC' => $resized['src'] ?? $file['SRC'],
        'WIDTH' => $resized['width'] ?? $file['WIDTH'] ?? null,
        'HEIGHT' => $resized['height'] ?? $file['HEIGHT'] ?? null,
        'ALT' => ($file['ALT'] ?? '') ?: $alt,
    ];
};

foreach ($arResult['ITEMS'] as &$arItem) {
    $activeTo = $activeToTimestamp($arItem);
    $secondsLeft = $activeTo !== null ? $activeTo - $now : null;
    $discount = $discountPercent($arItem);
    $isSuperPrice = $discount !== null && $discount > $superPriceThreshold;

    $arItem['ACTIVE_TO_TIMESTAMP'] = $activeTo;
    $arItem['IS_HOT'] = $secondsLeft !== null && $secondsLeft > 0 && $secondsLeft < $hotPeriod;

    $arItem['DISCOUNT_PERCENT'] = $discount;
    $arItem['DISCOUNT_TEXT'] = $discount > 0 ? rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') : '';

    $arItem['BADGE'] = Loc::getMessage($isSuperPrice ? 'PROMO_CARDS_BADGE_SUPER' : 'PROMO_CARDS_BADGE_DEFAULT');
    $arItem['BADGE_CODE'] = $isSuperPrice ? 'super' : 'default';

    $arItem['CARD_PICTURE'] = $resizedPicture($arItem['PREVIEW_PICTURE'] ?? null, 800, 600, $arItem['NAME']);
    $arItem['MODAL_PICTURE'] = $resizedPicture(
        ($arItem['DETAIL_PICTURE'] ?? null) ?: ($arItem['PREVIEW_PICTURE'] ?? null),
        1200,
        900,
        $arItem['NAME']
    );
}
unset($arItem);
