<?php
// Check database tables
$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully\n";
    
    // Get all tables
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available tables:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check if clients table exists and has data
    if (in_array('clients', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
        $count = $stmt->fetchColumn();
        echo "\nClients table has $count records\n";
        
        if ($count > 0) {
            $stmt = $pdo->query("SELECT id, name FROM clients LIMIT 3");
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Sample clients:\n";
            foreach ($clients as $client) {
                echo "- ID: {$client['id']}, Name: {$client['name']}\n";
            }
        }
    } else {
        echo "\nClients table does not exist\n";
    }
    
    // Check if incomes table exists and has data
    if (in_array('incomes', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM incomes");
        $count = $stmt->fetchColumn();
        echo "\nIncomes table has $count records\n";
    } else {
        echo "\nIncomes table does not exist\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
