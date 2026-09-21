<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

/** @global CMain $APPLICATION */
$APPLICATION->SetTitle('Акции и спецпредложения');

$APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'promo_cards',
    [
        'IBLOCK_TYPE' => 'content',
        'IBLOCK_ID' => '5', // ID инфоблока «Акции» — заменить на актуальный
        'NEWS_COUNT' => '12',
        'SORT_BY1' => 'ACTIVE_TO',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'SORT',
        'SORT_ORDER2' => 'ASC',
        'FILTER_NAME' => '',
        'FIELD_CODE' => ['PREVIEW_PICTURE', 'PREVIEW_TEXT', 'DATE_ACTIVE_TO'],
        'PROPERTY_CODE' => ['DATE_ACTIVE_TO', 'DISCOUNT_PERCENT', 'BADGE'],
        'CHECK_DATES' => 'Y', // закончившиеся акции не выводим
        'DETAIL_URL' => '',
        'PREVIEW_TRUNCATE_LEN' => '160',
        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
        'SET_TITLE' => 'N',
        'SET_BROWSER_TITLE' => 'N',
        'SET_META_KEYWORDS' => 'N',
        'SET_META_DESCRIPTION' => 'N',
        'SET_LAST_MODIFIED' => 'N',
        'SET_STATUS_404' => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'ADD_SECTIONS_CHAIN' => 'N',
        'HIDE_LINK_WHEN_NO_DETAIL' => 'Y',
        'PARENT_SECTION' => '',
        'PARENT_SECTION_CODE' => '',
        'INCLUDE_SUBSECTIONS' => 'Y',
        // IS_HOT считается в result_modifier.php и попадает в кеш,
        // поэтому время кеша держим небольшим (см. README.md)
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '3600',
        'CACHE_FILTER' => 'N',
        'CACHE_GROUPS' => 'Y',
        'DISPLAY_TOP_PAGER' => 'N',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'PAGER_TEMPLATE' => '.default',
        'PAGER_TITLE' => 'Акции',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_SHOW_ALL' => 'N',
        'AJAX_MODE' => 'N',
    ]
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
