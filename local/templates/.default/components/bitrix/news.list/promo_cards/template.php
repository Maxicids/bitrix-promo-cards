<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

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

            $hasDetail = !$arParams['HIDE_LINK_WHEN_NO_DETAIL'] || ($arItem['DETAIL_TEXT'] && $arResult['USER_HAVE_ACCESS']);
            $detailUrl = $hasDetail ? (string)$arItem['DETAIL_PAGE_URL'] : '';
            $activeTo = $arItem['ACTIVE_TO_TIMESTAMP'];
            ?>
            <article
                class="promo-card<?= $arItem['IS_HOT'] ? ' promo-card--hot' : '' ?>"
                id="<?= $this->GetEditAreaId($arItem['ID']) ?>"
                style="--i: <?= (int)$index ?>"
                data-promo-card
                <?php if ($activeTo): ?>data-active-to="<?= (int)$activeTo ?>"<?php endif; ?>
                <?php if ($arItem['MODAL_PICTURE']): ?>data-detail-picture="<?= $arItem['MODAL_PICTURE']['SRC'] ?>"<?php endif; ?>
                <?php if ($detailUrl !== ''): ?>data-detail-url="<?= $detailUrl ?>"<?php endif; ?>
            >
                <div class="promo-card__media">
                    <div class="promo-card__picture">
                        <?php if ($arItem['CARD_PICTURE']): ?>
                            <img
                                class="promo-card__image"
                                src="<?= $arItem['CARD_PICTURE']['SRC'] ?>"
                                width="<?= (int)$arItem['CARD_PICTURE']['WIDTH'] ?>"
                                height="<?= (int)$arItem['CARD_PICTURE']['HEIGHT'] ?>"
                                alt="<?= $arItem['CARD_PICTURE']['ALT'] ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="promo-card__image promo-card__image--empty" aria-hidden="true">%</div>
                        <?php endif; ?>
                        <span class="promo-card__shine" aria-hidden="true"></span>
                    </div>

                    <div class="promo-card__badges">
                        <?php if ($activeTo): ?>
                            <span class="promo-card__badge promo-card__badge--hot" data-hot-badge<?= $arItem['IS_HOT'] ? '' : ' hidden' ?>>
                                <?= Loc::getMessage('PROMO_CARDS_HOT') ?>
                            </span>
                        <?php endif; ?>
                        <span class="promo-card__badge promo-card__badge--<?= $arItem['BADGE_CODE'] ?>">
                            <?= htmlspecialcharsbx($arItem['BADGE']) ?>
                        </span>
                    </div>

                    <?php if ($arItem['DISCOUNT_TEXT'] !== ''): ?>
                        <div class="promo-card__discount">
                            <span class="promo-card__discount-value">&minus;<?= $arItem['DISCOUNT_TEXT'] ?><small>%</small></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="promo-card__body">
                    <h3 class="promo-card__title">
                        <?php if ($detailUrl !== ''): ?>
                            <a class="promo-card__link" href="<?= $detailUrl ?>"><?= $arItem['NAME'] ?></a>
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

                        <span class="promo-card__more" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </span>
                    </div>
                </div>

                <template data-detail-text><?= $arItem['DETAIL_TEXT'] ?: $arItem['PREVIEW_TEXT'] ?></template>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
        <?= $arResult['NAV_STRING'] ?>
    <?php endif; ?>

    <dialog class="promo-modal" data-promo-modal>
        <div class="promo-modal__panel" data-modal-panel>
            <div class="promo-modal__media">
                <img class="promo-modal__image" data-modal-image alt="">
                <div class="promo-card__badges" data-modal-badges></div>
                <div data-modal-discount></div>
            </div>

            <div class="promo-modal__content" data-modal-content>
                <h2 class="promo-modal__title" data-modal-title></h2>
                <div class="promo-card__timer" data-modal-timer></div>
                <div class="promo-modal__text" data-modal-text></div>

                <div class="promo-modal__actions">
                    <a class="promo-modal__cta" href="#" data-modal-cta>
                        <?= Loc::getMessage('PROMO_CARDS_GO') ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <div class="promo-modal__nav">
                        <button class="promo-modal__button" type="button" data-modal-prev aria-label="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_PREV')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                        </button>
                        <button class="promo-modal__button" type="button" data-modal-next aria-label="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_NEXT')) ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <button class="promo-modal__close" type="button" data-modal-close aria-label="<?= htmlspecialcharsbx(Loc::getMessage('PROMO_CARDS_CLOSE')) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </dialog>
</section>
