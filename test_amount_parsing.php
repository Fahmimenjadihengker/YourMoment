<?php

/**
 * Test untuk verifikasi parsing nominal berdasarkan konteks
 * 
 * Jalankan: php test_amount_parsing.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\GoalSimulationService;

echo "=== TEST PARSING NOMINAL BERDASARKAN KONTEKS ===\n\n";

$testCases = [
    // Format: [message, expected_target, expected_monthly, description]

    // BUG CASE - ini yang harus diperbaiki
    [
        'saya ingin beli laptop harga 15jt, uang jajan sebulan 2jt',
        15000000,
        2000000,
        'BUG CASE: harga dulu, jajan kemudian'
    ],

    // Normal case - target di depan
    [
        'ingin rakit pc budget 10jt, dengan uang jajan sebulan 2jt, kira kira berapa lama',
        10000000,
        2000000,
        'NORMAL: budget dulu, jajan kemudian'
    ],

    // Reversed - jajan di depan, target di belakang
    [
        'uang jajan saya 3jt sebulan, pengen beli motor 25jt',
        25000000,
        3000000,
        'REVERSED: jajan dulu, target kemudian'
    ],

    // Variasi dengan "harga"
    [
        'mau beli hp harga 8jt dengan gaji 4jt per bulan',
        8000000,
        4000000,
        'VARIASI: hp harga X, gaji Y'
    ],

    // Variasi dengan "seharga"
    [
        'ingin punya laptop seharga 12jt, penghasilan bulanan 2.5jt',
        12000000,
        2500000,
        'VARIASI: seharga, penghasilan bulanan'
    ],

    // Variasi tanpa kata kunci spesifik (fallback)
    [
        'target 5jt tabungan 500rb',
        5000000,
        500000,
        'SIMPLE: target dulu'
    ],

    // Kompleks - banyak kata
    [
        'saya mahasiswa dengan uang jajan per bulan 1.5jt, ingin beli iphone 16 yang harganya sekitar 20jt, kira-kira berapa lama ya?',
        20000000,
        1500000,
        'KOMPLEKS: jajan dulu di kalimat, harga kemudian'
    ],

    // Format ribu
    [
        'harga barang 500rb, uang jajan seminggu 100rb',
        500000,
        null, // weekly should be 100000
        'FORMAT RB: harga + mingguan'
    ],

    // Angka besar tanpa suffix (> 10000)
    [
        'target 15000000, bulanan 2000000',
        15000000,
        2000000,
        'PLAIN NUMBER: tanpa jt/rb suffix'
    ],

    // Edge case - hanya 1 angka
    [
        'mau beli laptop 10jt',
        10000000,
        null,
        'EDGE: hanya target, tanpa allowance'
    ],
];

$passed = 0;
$failed = 0;

foreach ($testCases as $index => $test) {
    [$message, $expectedTarget, $expectedMonthly, $description] = $test;

    $result = GoalSimulationService::extractAmounts($message);

    $targetMatch = $result['target'] === $expectedTarget;
    $monthlyMatch = $result['monthly'] === $expectedMonthly;

    $status = ($targetMatch && $monthlyMatch) ? '✅ PASS' : '❌ FAIL';

    if ($targetMatch && $monthlyMatch) {
        $passed++;
    } else {
        $failed++;
    }

    echo "Test #" . ($index + 1) . ": {$description}\n";
    echo "  Message: \"{$message}\"\n";
    echo "  Expected: target=" . formatNumber($expectedTarget) . ", monthly=" . formatNumber($expectedMonthly) . "\n";
    echo "  Got:      target=" . formatNumber($result['target']) . ", monthly=" . formatNumber($result['monthly']);
    if ($result['weekly'] !== null) {
        echo ", weekly=" . formatNumber($result['weekly']);
    }
    echo "\n";
    echo "  Status: {$status}\n";

    if (!$targetMatch) {
        echo "    ⚠️ Target mismatch!\n";
    }
    if (!$monthlyMatch) {
        echo "    ⚠️ Monthly mismatch!\n";
    }

    echo "\n";
}

echo "========================================\n";
echo "SUMMARY: {$passed} passed, {$failed} failed\n";
echo "========================================\n";

function formatNumber($num)
{
    if ($num === null) return 'null';
    if ($num >= 1000000) {
        return ($num / 1000000) . 'jt';
    }
    if ($num >= 1000) {
        return ($num / 1000) . 'rb';
    }
    return $num;
}
