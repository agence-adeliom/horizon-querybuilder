<?php

declare(strict_types=1);

require __DIR__ . '/../src/Database/DateQuery.php';

use Adeliom\HorizonQueryBuilder\Database\DateQuery;

function check(string $label, bool $ok): void
{
    echo ($ok ? "PASS" : "FAIL") . " - {$label}\n";
    if (!$ok) {
        $GLOBALS['failed'] = true;
    }
}

// Both bounds → full date_query array with formatted strings.
$dq = (new DateQuery())
    ->after(new DateTimeImmutable('2026-07-01 00:00:00'))
    ->before(new DateTimeImmutable('2026-07-02 23:59:59'));

check('generates column+inclusive+after+before', $dq->generateDateQueryArray() === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-07-01 00:00:00',
    'before' => '2026-07-02 23:59:59',
]);

// Open-ended (only after) → no 'before' key.
$dqAfter = (new DateQuery())->after('2026-01-01 00:00:00');
check('omits before when only after set', $dqAfter->generateDateQueryArray() === [
    'column' => 'post_date',
    'inclusive' => true,
    'after' => '2026-01-01 00:00:00',
]);

// Custom column + inclusive(false).
$dqCol = (new DateQuery())->column('post_modified')->inclusive(false)->after('2026-05-01 00:00:00');
check('honours custom column and inclusive(false)', $dqCol->generateDateQueryArray() === [
    'column' => 'post_modified',
    'inclusive' => false,
    'after' => '2026-05-01 00:00:00',
]);

// No bounds → getQuery() empty (so QueryBuilder can skip it).
check('getQuery empty when no bounds', (new DateQuery())->getQuery() === []);
check('getQuery non-empty when a bound is set', $dqAfter->getQuery() !== []);

echo empty($GLOBALS['failed']) ? "\nALL PASS\n" : "\nFAILURES\n";
exit(empty($GLOBALS['failed']) ? 0 : 1);
