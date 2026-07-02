<?php

declare(strict_types=1);

require __DIR__ . '/../src/Database/DateQuery.php';
require __DIR__ . '/../src/Database/QueryBuilder.php';

use Adeliom\HorizonQueryBuilder\Database\DateQuery;
use Adeliom\HorizonQueryBuilder\Database\QueryBuilder;

$failed = false;
function check(string $label, bool $ok): void
{
    echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$ok) {
        $GLOBALS['failed'] = true;
    }
}

// Invoke the private getWpQueryArgs() via reflection (it needs no WordPress runtime).
function argsOf(QueryBuilder $qb): array
{
    $ref = new ReflectionMethod($qb, 'getWpQueryArgs');
    $ref->setAccessible(true);

    return $ref->invoke($qb);
}

// One date query → single date_query entry, no relation key.
$qb = (new QueryBuilder())->postType('post')->addDateQuery(
    (new DateQuery())->after('2026-07-01 00:00:00')->before('2026-07-02 23:59:59')
);
$args = argsOf($qb);
check('date_query present', isset($args['date_query'][0]));
check('date_query content correct', ($args['date_query'][0] ?? null) === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-07-01 00:00:00',
    'before' => '2026-07-02 23:59:59',
]);
check('no relation key for single date query', !isset($args['date_query']['relation']));

// Empty DateQuery (no bounds) is skipped.
$qbEmpty = (new QueryBuilder())->postType('post')->addDateQuery(new DateQuery());
check('empty DateQuery skipped', !isset(argsOf($qbEmpty)['date_query']));

// Two date queries → relation AND.
$qb2 = (new QueryBuilder())->postType('post')
    ->addDateQuery((new DateQuery())->after('2026-01-01 00:00:00'))
    ->addDateQuery((new DateQuery())->before('2026-12-31 23:59:59'));
$args2 = argsOf($qb2);
check('two date queries kept', count(array_filter($args2['date_query'], 'is_array')) === 2);
check('relation AND set for multiple', ($args2['date_query']['relation'] ?? null) === 'AND');

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
