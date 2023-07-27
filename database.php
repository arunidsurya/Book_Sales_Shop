<?php

//Establishing data base connection
$host ='localhost';
$dbname ='sales_db';
$username = 'sales_stores';
$password = 'sales.store$';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $jsonFile = file_get_contents('sales_data.json');
    $salesData = json_decode($jsonFile, true);

    foreach ($salesData as $sale) {
    $customerName = $sale['customer'];
    $customerEmail = $sale['customer_email'];
    $productName = $sale['product'];
    $productPrice = $sale['price'];
    $saleDate = $sale['date'];

// Check if the customer already exists in the database, if not, insert them
    $stmt = $pdo->prepare('SELECT customer_id FROM customers WHERE customer_name = ?');
    $stmt->execute([$customerName]);
    $customerId = $stmt->fetchColumn();

    if (!$customerId) {
            $stmt = $pdo->prepare('INSERT INTO customers (customer_name, customer_email) VALUES (?, ?)');
            $stmt->execute([$customerName, $customerEmail]);
            $customerId = $pdo->lastInsertId();
        }

// Check if the product already exists in the database, if not, insert it
        $stmt = $pdo->prepare('SELECT product_id FROM products WHERE product_name = ?');
        $stmt->execute([$productName]);
        $productId = $stmt->fetchColumn();

        if (!$productId) {
            $stmt = $pdo->prepare('INSERT INTO products (product_name, product_price) VALUES (?, ?)');
            $stmt->execute([$productName, $productPrice]);
            $productId = $pdo->lastInsertId();
        }

// Insert the sale record into the sales table
        $stmt = $pdo->prepare('INSERT INTO sales (customer_id, product_id, sale_price, sale_date) VALUES (?, ?, ?, ?)');
        $stmt->execute([$customerId, $productId, $productPrice, $saleDate]);
    }

    echo 'Data imported successfully!';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>