<?php
/**
 * Проверка логики result_modifier.php: php demo/test.php
 */

require __DIR__ . '/bitrix_stubs.php';

$modifier = dirname(__DIR__) . '/local/templates/.default/components/bitrix/news.list/promo_cards/result_modifier.php';

function applyModifier(string $modifier, array $item): array
{
    $arParams = [];
    $arResult = ['ITEMS' => [$item]];
    include $modifier;

    return $arResult['ITEMS'][0];
}

function item($activeTo, $discount, array $fields = []): array
{
    return $fields + [
        'ID' => 1,
        'NAME' => 'Test',
        'PROPERTIES' => [
            'DATE_ACTIVE_TO' => ['VALUE' => $activeTo],
            'DISCOUNT_PERCENT' => ['VALUE' => $discount],
            'BADGE' => ['VALUE' => 'из админки'],
        ],
    ];
}

$at = static fn (string $modify, string $format = 'd.m.Y H:i:s'): string => (new DateTime())->modify($modify)->format($format);

$cases = [
    // [описание, элемент, ожидаемый IS_HOT, ожидаемый BADGE]
    ['осталось 1 час', item($at('+1 hour'), 5), true, 'Выгода'],
    ['осталось 2 дня 23 часа', item($at('+2 days 23 hours'), 5), true, 'Выгода'],
    ['осталось 3 дня 1 минута', item($at('+3 days 1 minute'), 5), false, 'Выгода'],
    ['осталось 10 дней', item($at('+10 days'), 5), false, 'Выгода'],
    ['акция уже закончилась', item($at('-1 hour'), 5), false, 'Выгода'],
    ['дата не задана', item('', 5), false, 'Выгода'],
    ['дата без времени = до конца дня', item($at('+1 day', 'd.m.Y'), 5), true, 'Выгода'],
    ['дата из поля элемента', item('', 5, ['DATE_ACTIVE_TO' => $at('+1 day')]), true, 'Выгода'],
    ['скидка ровно 20%', item('', 20), false, 'Выгода'],
    ['скидка 20.01%', item('', '20.01'), false, 'Суперцена'],
    ['скидка 21%', item('', 21), false, 'Суперцена'],
    ['скидка строкой «22,5»', item('', '22,5'), false, 'Суперцена'],
    ['скидка не задана', item('', ''), false, 'Выгода'],
    ['скидка мусор', item('', 'abc'), false, 'Выгода'],
];

$failed = 0;
foreach ($cases as [$title, $item, $expectedHot, $expectedBadge]) {
    $result = applyModifier($modifier, $item);
    $ok = $result['IS_HOT'] === $expectedHot && $result['BADGE'] === $expectedBadge;
    $failed += $ok ? 0 : 1;
    printf(
        "%s %s (IS_HOT=%s, BADGE=%s)\n",
        $ok ? '✔' : '✘',
        $title,
        var_export($result['IS_HOT'], true),
        $result['BADGE']
    );
}

echo $failed ? "\nFAILED: {$failed}\n" : "\nOK: " . count($cases) . " cases\n";
exit($failed ? 1 : 0);
