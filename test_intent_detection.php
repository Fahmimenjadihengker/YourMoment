<?php
require_once "vendor/autoload.php";

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ChatIntentDetector;

$detector = new ChatIntentDetector();

$messages = [
    "dengan jumlah uang yang saya miliki sekarang buatkan strategi pengeluaran paling hemat 1 bulan ke depan",
    "cara paling hemat 1 bulan ke depan",
    "dengan saldo saya untuk 1 bulan ke depan"
];

echo "=== Testing Intent Detection ===\n\n";

foreach ($messages as $msg) {
    $intents = $detector->detectMultiple($msg);
    echo "Message: " . $msg . "\n";
    echo "Intent: " . json_encode($intents, JSON_PRETTY_PRINT) . "\n\n";
}
