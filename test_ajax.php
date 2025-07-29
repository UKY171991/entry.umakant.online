<?php
// Test the AJAX endpoints directly
$dbPath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Testing load_customers endpoint ===\n";
    $_POST['action'] = 'load_customers';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    $stmt = $pdo->query("SELECT id, name, email FROM clients ORDER BY name");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = ['success' => true, 'data' => $customers];
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    
    echo "=== Testing load_invoices endpoint ===\n";
    $_POST['action'] = 'load_invoices';
    
    $sql = "SELECT i.*, c.name as customer_name 
            FROM incomes i 
            LEFT JOIN clients c ON i.client_id = c.id 
            WHERE 1=1
            ORDER BY i.date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = ['success' => true, 'data' => $invoices];
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
