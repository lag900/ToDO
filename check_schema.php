<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::select('DESCRIBE tasks');
foreach ($columns as $column) {
    if ($column->Field === 'project_id') {
        echo "Column: " . $column->Field . "\n";
        echo "Type: " . $column->Type . "\n";
        echo "Nullable: " . $column->Null . "\n";
        echo "Default: " . ($column->Default === null ? 'NULL' : $column->Default) . "\n";
    }
}
