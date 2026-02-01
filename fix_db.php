<?php
// fix_db.php
// Place this file in your 'public' folder (e.g., /home/batukoii/public_html/todo/public/fix_db.php)
// Then visit http://your-domain.com/fix_db.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Database Fixer</h1>";

try {
    echo "<p>Attempting to modify 'tasks' table...</p>";

    // 1. Make project_id nullable
    DB::statement("ALTER TABLE tasks MODIFY COLUMN project_id BIGINT UNSIGNED NULL");
    echo "<p style='color:green'>✅ command 1 success: project_id is now NULLABLE.</p>";
    
    // 2. Add default value just in case
    // DB::statement("ALTER TABLE tasks ALTER COLUMN project_id SET DEFAULT NULL");
    // echo "<p style='color:green'>✅ command 2 success: project_id default set to NULL.</p>";

    echo "<h3>Verify:</h3>";
    $columns = DB::select("SHOW COLUMNS FROM tasks WHERE Field = 'project_id'");
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
    echo "<h2 style='color:green'>FIX COMPLETE. You can now create tasks.</h2>";
    echo "<p>Please delete this file from your server after use.</p>";

} catch (\Exception $e) {
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
