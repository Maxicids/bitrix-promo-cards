<?php

/**
 * Тестовые данные в формате, который bitrix:news.list передаёт в шаблон.
 * Даты считаются от текущего момента, чтобы на демо всегда были «горящие» акции.
 */

function demoPicture(string $unsplashId, string $alt): array
{
    return [
        'SRC' => 'https://images.unsplash.com/photo-' . $unsplashId . '?auto=format&fit=crop&w=1200&h=900&q=80',
        'WIDTH' => 1200,
        'HEIGHT' => 900,
        'ALT' => $alt,
    ];
}

function demoItem(int $id, array $data): array
{
    return [
        'ID' => $id,
        'IBLOCK_ID' => 5,
        'NAME' => htmlspecialcharsbx($data['name']),
        'PREVIEW_TEXT' => htmlspecialcharsbx($data['preview']),
        'DETAIL_TEXT' => $data['detail'],
        'PREVIEW_PICTURE' => $data['picture'],
        'DETAIL_PAGE_URL' => '',
        'EDIT_LINK' => '',
        'DELETE_LINK' => '',
        'PROPERTIES' => [
            'DATE_ACTIVE_TO' => ['VALUE' => $data['activeTo']],
            'DISCOUNT_PERCENT' => ['VALUE' => $data['discount']],
            'BADGE' => ['VALUE' => $data['badge'] ?? ''],
        ],
    ];
}

function demoDetail(string $intro, array $conditions): string
{
    return '<p>' . $intro . '</p><ul><li>' . implode('</li><li>', $conditions) . '</li></ul>';
}

$at = static fn (string $modify): string => (new DateTime())->modify($modify)->format('d.m.Y H:i:s');

$items = [
    [
        'name' => 'Большая осенняя распродажа',
        'preview' => 'Скидки на одежду, обувь и аксессуары во всех магазинах сети. Успейте забрать лучшие позиции.',
        'detail' => demoDetail('Обновляем гардероб к холодам: скидка действует на всю коллекцию сезона, включая новинки.', [
            'Скидка 40% на всю одежду и обувь',
            'Действует в магазинах и на сайте',
            'Суммируется с бонусами программы лояльности',
        ]),
        'activeTo' => $at('+1 day 6 hours'),
        'discount' => 40,
        'picture' => demoPicture('1513884923967-4b182ef167ab', 'Пакеты с покупками'),
    ],
    [
        'name' => 'Вторая чашка кофе в подарок',
        'preview' => 'Закажите любой напиток до 11:00, и второй сварим бесплатно.',
        'detail' => demoDetail('Утро начинается с кофе, а хорошее утро — с двух. Приходите с коллегой или берите с собой.', [
            'Каждый день с 8:00 до 11:00',
            'Второй напиток — любой из меню того же объёма',
            'Скидка 15% на десерты к кофе',
        ]),
        'activeTo' => $at('+2 days 20 hours'),
        'discount' => 15,
        'picture' => demoPicture('1506372023823-741c83b836fe', 'Капучино'),
    ],
    [
        'name' => 'Кроссовки новой коллекции',
        'preview' => 'Лёгкие беговые модели по специальной цене для участников клуба.',
        'detail' => demoDetail('Новая линейка беговых кроссовок с амортизирующей подошвой и дышащим верхом.', [
            'Скидка 25% для участников клуба',
            'Бесплатная примерка и доставка',
            'Обмен в течение 30 дней',
        ]),
        'activeTo' => $at('+10 days'),
        'discount' => 25,
        'picture' => demoPicture('1600185365483-26d7a4cc7519', 'Кроссовки'),
    ],
    [
        'name' => 'Рюкзаки к учебному году',
        'preview' => 'Скидка ровно 20% — по правилам это ещё «Выгода», а не «Суперцена».',
        'detail' => demoDetail('Вместительные рюкзаки с отделением для ноутбука и водоотталкивающей тканью.', [
            'Скидка 20% на все модели',
            'Гравировка имени в подарок',
            'Гарантия 2 года',
        ]),
        'activeTo' => $at('+3 days 4 hours'),
        'discount' => '20',
        'picture' => demoPicture('1553062407-98eeb64c6a62', 'Рюкзак'),
    ],
    [
        'name' => 'Музыка без рекламы на полгода',
        'preview' => 'Подписка со скидкой 22,5% и беспроводные наушники в подарок.',
        'detail' => demoDetail('Миллионы треков, подкасты и аудиокниги без рекламы и ограничений.', [
            'Скидка 22,5% при оплате за 6 месяцев',
            'Беспроводные наушники в подарок',
            'Отменить можно в любой момент',
        ]),
        'activeTo' => (new DateTime('+30 days'))->format('d.m.Y'),
        'discount' => '22,5',
        'picture' => demoPicture('1505740420928-5e560c06d30e', 'Наушники'),
    ],
    [
        'name' => 'Абонемент в бассейн',
        'preview' => 'Последние часы акции: безлимитное посещение и сауна включены.',
        'detail' => demoDetail('Олимпийский бассейн 50 метров, групповые тренировки и зона отдыха.', [
            'Безлимитное посещение в течение месяца',
            'Сауна и хаммам включены',
            'Первая персональная тренировка бесплатно',
        ]),
        'activeTo' => $at('+5 hours'),
        'discount' => 10,
        'badge' => 'Суперцена',
        'picture' => demoPicture('1530549387789-4c1017266635', 'Пловец в бассейне'),
    ],
    [
        'name' => 'Пицца 2+1',
        'preview' => 'Третья пицца бесплатно при заказе через сайт или приложение.',
        'detail' => demoDetail('Печём в дровяной печи на тонком тесте. Привезём горячей за 45 минут.', [
            'Самая недорогая пицца в заказе — бесплатно',
            'Доставка бесплатно от 1000 ₽',
            'Действует на всё меню, кроме комбо',
        ]),
        'activeTo' => $at('+6 days'),
        'discount' => 33,
        'picture' => demoPicture('1513104890138-7c749659a591', 'Пицца'),
    ],
    [
        'name' => 'СПА-день для двоих',
        'preview' => 'Массаж, чайная церемония и бассейн — идеальный подарок.',
        'detail' => demoDetail('Полный день отдыха в загородном СПА-комплексе для двоих.', [
            'Расслабляющий массаж 60 минут',
            'Чайная церемония и фрукты',
            'Подарочный сертификат в красивой упаковке',
        ]),
        'activeTo' => $at('+1 day 20 hours'),
        'discount' => 30,
        'picture' => demoPicture('1544161515-4ab6ce6db874', 'Массаж в СПА'),
    ],
];

return [
    [
        'IBLOCK_ID' => 5,
        'DISPLAY_TOP_PAGER' => false,
        'DISPLAY_BOTTOM_PAGER' => false,
        'HIDE_LINK_WHEN_NO_DETAIL' => true,
    ],
    [
        'USER_HAVE_ACCESS' => true,
        'NAV_STRING' => '',
        'ITEMS' => array_map('demoItem', range(1, count($items)), $items),
    ],
];
