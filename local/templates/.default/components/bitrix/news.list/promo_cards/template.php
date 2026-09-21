<?php
/**
 * Шаблон promo_cards компонента bitrix:news.list — блок «Акции и спецпредложения».
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var string $templateFolder
 */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

if (empty($arResult['ITEMS'])) {
    return;
}

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = ['CONFIRM' => Loc::getMessage('PROMO_CARDS_DELETE_CONFIRM')];
?>
<section class="promo-cards">
    <?php if ($arParams['DISPLAY_TOP_PAGER']): ?>
        <?= $arResult['NAV_STRING'] ?>
    <?php endif; ?>

    <div class="promo-cards__list">
        <?php foreach ($arResult['ITEMS'] as $arItem): ?>
            <?php
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $elementEdit);
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $elementDelete, $elementDeleteParams);

            $hasLink = !$arParams['HIDE_LINK_WHEN_NO_DETAIL']
                || ($arItem['DETAIL_TEXT'] && $arResult['USER_HAVE_ACCESS']);
            $discount = $arItem['DISCOUNT_PERCENT'];
            ?>
            <article
                class="promo-card<?= $arItem['IS_HOT'] ? ' promo-card--hot' : '' ?>"
                id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
            >
                <div class="promo-card__media">
                    <?php if ($arItem['CARD_PICTURE']): ?>
                        <img
                            class="promo-card__image"
                            src="<?= $arItem['CARD_PICTURE']['SRC'] ?>"
                            <?php if ($arItem['CARD_PICTURE']['WIDTH']): ?>width="<?= (int)$arItem['CARD_PICTURE']['WIDTH'] ?>"<?php endif; ?>
                            <?php if ($arItem['CARD_PICTURE']['HEIGHT']): ?>height="<?= (int)$arItem['CARD_PICTURE']['HEIGHT'] ?>"<?php endif; ?>
                            alt="<?= $arItem['CARD_PICTURE']['ALT'] ?>"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="promo-card__image promo-card__image--empty" aria-hidden="true"></div>
                    <?php endif; ?>

                    <div class="promo-card__badges">
                        <?php if ($arItem['IS_HOT']): ?>
                            <span class="promo-card__badge promo-card__badge--hot">
                                <?= Loc::getMessage('PROMO_CARDS_HOT') ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($arItem['BADGE'] !== ''): ?>
                            <span class="promo-card__badge promo-card__badge--<?= $arItem['BADGE_CODE'] ?>">
                                <?= htmlspecialcharsbx($arItem['BADGE']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($discount !== null && $discount > 0): ?>
                        <div class="promo-card__discount">
                            &minus;<?= rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') ?>%
                        </div>
                    <?php endif; ?>
                </div>

                <div class="promo-card__body">
                    <h3 class="promo-card__title">
                        <?php if ($hasLink): ?>
                            <a class="promo-card__link" href="<?= $arItem['DETAIL_PAGE_URL'] ?>"><?= $arItem['NAME'] ?></a>
                        <?php else: ?>
                            <?= $arItem['NAME'] ?>
                        <?php endif; ?>
                    </h3>

                    <?php if ($arItem['PREVIEW_TEXT'] !== ''): ?>
                        <div class="promo-card__text"><?= $arItem['PREVIEW_TEXT'] ?></div>
                    <?php endif; ?>

                    <?php if ($arItem['ACTIVE_TO_TIMESTAMP']): ?>
                        <div class="promo-card__date">
                            <?= Loc::getMessage('PROMO_CARDS_ACTIVE_TO') ?>
                            <time datetime="<?= date('c', $arItem['ACTIVE_TO_TIMESTAMP']) ?>">
                                <?= date('d.m.Y', $arItem['ACTIVE_TO_TIMESTAMP']) ?>
                            </time>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
        <?= $arResult['NAV_STRING'] ?>
    <?php endif; ?>
</section>
