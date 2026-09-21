<?php
/**
 * Шаблон promo_cards компонента bitrix:news.list — блок «Акции и спецпредложения».
 *
 * style.css и script.js из папки шаблона ядро подключает автоматически.
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
<section
    class="promo-cards"
    data-promo-cards
    data-label-left="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_LEFT')) ?>"
    data-label-days="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_DAYS')) ?>"
    data-label-ended="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_ENDED')) ?>"
>
    <?php if ($arParams['DISPLAY_TOP_PAGER']): ?>
        <?= $arResult['NAV_STRING'] ?>
    <?php endif; ?>

    <div class="promo-cards__list">
        <?php foreach ($arResult['ITEMS'] as $index => $arItem): ?>
            <?php
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $elementEdit);
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $elementDelete, $elementDeleteParams);

            $hasLink = !$arParams['HIDE_LINK_WHEN_NO_DETAIL']
                || ($arItem['DETAIL_TEXT'] && $arResult['USER_HAVE_ACCESS']);
            $discount = $arItem['DISCOUNT_PERCENT'];
            $activeTo = $arItem['ACTIVE_TO_TIMESTAMP'];
            ?>
            <article
                class="promo-card<?= $arItem['IS_HOT'] ? ' promo-card--hot' : '' ?>"
                id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
                style="--i: <?= (int)$index ?>"
                <?php if ($activeTo): ?>data-active-to="<?= (int)$activeTo ?>"<?php endif; ?>
            >
                <div class="promo-card__media">
                    <div class="promo-card__picture">
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
                            <div class="promo-card__image promo-card__image--empty" aria-hidden="true">
                                <span>%</span>
                            </div>
                        <?php endif; ?>
                        <span class="promo-card__shine" aria-hidden="true"></span>
                    </div>

                    <div class="promo-card__badges">
                        <?php if ($activeTo || $arItem['IS_HOT']): ?>
                            <?php // Бейдж выводится всегда, когда есть дата: script.js включает его, если акция «догорела» уже после кеширования ?>
                            <span class="promo-card__badge promo-card__badge--hot" data-hot-badge<?= $arItem['IS_HOT'] ? '' : ' hidden' ?>>
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
                            <span class="promo-card__discount-value">&minus;<?= rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') ?><small>%</small></span>
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

                    <div class="promo-card__footer">
                        <?php if ($activeTo): ?>
                            <div class="promo-card__timer">
                                <svg class="promo-card__timer-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5M9 2h6"/>
                                </svg>
                                <span data-timer>
                                    <?= Loc::getMessage('PROMO_CARDS_ACTIVE_TO') ?>
                                    <time datetime="<?= date('c', $activeTo) ?>"><?= date('d.m.Y', $activeTo) ?></time>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($hasLink): ?>
                            <span class="promo-card__more" aria-hidden="true">
                                <?= Loc::getMessage('PROMO_CARDS_MORE') ?>
                                <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
        <?= $arResult['NAV_STRING'] ?>
    <?php endif; ?>
</section>
