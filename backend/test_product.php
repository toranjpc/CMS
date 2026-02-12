<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Product model...\n";

try {
    $product = Modules\Accounting\Models\Product::create([
        'title' => 'test',
        'barcode' => 'test123',
        'user_id' => 1
    ]);

    echo 'Product ID: ' . $product->id . "\n";
    echo 'Product created successfully!' . "\n";

    $product->delete();
    echo 'Test passed!' . "\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

