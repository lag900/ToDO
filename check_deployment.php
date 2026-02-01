<?php

require __DIR__ . '/../vendor/autoload.php';

$task = new \App\Models\Task();
$fillable = $task->getFillable();

echo "<h1>Deployment Check</h1>";
echo "<h2>1. Task Model Check</h2>";
if (in_array('project_id', $fillable)) {
    echo "<p style='color:green'>✅ project_id is in \$fillable</p>";
} else {
    echo "<p style='color:red'>❌ project_id is MISSING from \$fillable. Please upload app/Models/Task.php</p>";
}

echo "<h2>2. TaskService Check</h2>";
$serviceContent = file_get_contents(__DIR__ . '/../app/Services/TaskService.php');

if (strpos($serviceContent, '$data[\'project_id\'] = $board->plan') !== false && strpos($serviceContent, 'Final safety fallback') !== false) {
    echo "<p style='color:green'>✅ TaskService contains the fallback logic.</p>";
} else {
    echo "<p style='color:red'>❌ TaskService is OUTDATED. Please upload app/Services/TaskService.php</p>";
}

echo "<h2>3. Migration/Database Check</h2>";
// Try to check schema if DB connection works
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE tasks');
    $projectIdColumn = null;
    foreach ($columns as $col) {
        // Handle object or array return
        $field = is_object($col) ? $col->Field : $col['Field'];
        if ($field === 'project_id') {
            $projectIdColumn = $col;
            break;
        }
    }
    
    if ($projectIdColumn) {
        $null = is_object($projectIdColumn) ? $projectIdColumn->Null : $projectIdColumn['Null'];
        $default = is_object($projectIdColumn) ? $projectIdColumn->Default : $projectIdColumn['Default'];
        
        echo "<pre>Column details: " . print_r($projectIdColumn, true) . "</pre>";
        
        if ($null === 'YES') {
             echo "<p style='color:green'>✅ project_id is NULLABLE (Database is good)</p>";
        } else {
             echo "<p style='color:orange'>⚠️ project_id is NOT NULL (Database needs migration 2026_02_01_202000... OR strict code fallback)</p>";
        }
    } else {
        echo "<p style='color:red'>❌ project_id column NOT FOUND in database.</p>";
    }
    
} catch (\Exception $e) {
    echo "<p>Could not check database directly: " . $e->getMessage() . "</p>";
}
