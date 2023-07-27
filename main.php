<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Shop Sales</title>
</head>
<body>
    <h1>Book shop Sales</h1>
    <form action="" method="post">
        <label for="customer">customer: </label>
        <input type="text" name="customer" id="customer">

        <label for="product">Product:</label>
        <input type="text" name="product" id="product">

        <label for="price">Price:</label>
        <input type="number" name="price" id="price">

        <input type="submit" value="Filter">
    </form>

<table>
<tr>
    <th>Customer</th>
    <th>Product</th>
    <th>price</th>
    <th>Date</th>
</tr>
<?php
//Handle the form submission and apply filters
if($_SERVER['REQUEST_METHOD']=== 'POST'){
    $filterCustomer = $_POST['customer'];
    $filterProdcut = $_POST['product'];
    $filterPrice = $_POST['price'];
}

// Construct the SQL query based on the filters
$sql = 'SELECT customers.customer_name, products.product_name, sales.sale_price, sales.sale_date
FROM sales
JOIN customers ON sales.customer_id = customers.customer_id
JOIN products ON sales.product_id = products.product_id
WHERE 1 = 1';



User
Szenario:
Imagine your friend - the owner of a small book shop - asks you for a simple representation of his latest sales.
He provides you a simple plain json export file.

What you need to do?:
- Design a database scheme for optimized storage
- Please note that over time, large amounts of data will accumulate
- Read the json data and save it to the database using php
- Create a simple page with filters for customer, product and price
- Output the filtered results in a table below the filters
- Add a last row for the total price of all filtered entries

Environment:
PHP 7, MySQL / MariaDB
ChatGPT
To achieve the desired tasks, we'll follow these steps:

Designing the Database Scheme:
For optimized storage and efficient querying, we'll create three main tables: customers, products, and sales.

customers table: To store customer information.
products table: To store product information.
sales table: To store sales data with references to customers and products.
Here's the SQL schema for the three tables:

sql
Copy code
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    sale_price DECIMAL(10, 2) NOT NULL,
    sale_date DATE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers (customer_id),
    FOREIGN KEY (product_id) REFERENCES products (product_id)
);
Reading JSON Data and Saving to the Database:
We'll write a PHP script to read the JSON data, parse it, and save it to the database. Assuming the JSON data is in a file named sales_data.json, the PHP script would look like this:
php
Copy code
<?php
// Replace with your database connection details
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_username';
$password = 'your_password';

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
Creating a Simple Page with Filters and Output:
We'll create an HTML page with PHP to handle the filters and display the results in a table format.
html+php
Copy code
<!DOCTYPE html>
<html>
<head>
    <title>Book Shop Sales</title>
</head>
<body>
    <h1>Book Shop Sales</h1>
    <form action="" method="post">
        <label for="customer">Customer:</label>
        <input type="text" name="customer" id="customer">

        <label for="product">Product:</label>
        <input type="text" name="product" id="product">

        <label for="price">Price:</label>
        <input type="number" name="price" id="price">

        <input type="submit" value="Filter">
    </form>

    <table>
        <tr>
            <th>Customer</th>
            <th>Product</th>
            <th>Price</th>
            <th>Date</th>
        </tr>
        <?php
        // Handle the form submission and apply filters
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filterCustomer = $_POST['customer'];
            $filterProduct = $_POST['product'];
            $filterPrice = $_POST['price'];

        // Construct the SQL query based on the filters
        $sql = 'SELECT customers.customer_name, products.product_name, sales.sale_price, sales.sale_date
                FROM sales
                JOIN customers ON sales.customer_id = customers.customer_id
                JOIN products ON sales.product_id = products.product_id
                WHERE 1 = 1';

        $params = [];

        if (!empty($filterCustomer)) {
            $sql .= ' AND customers.customer_name = ?';
                $params[] = $filterCustomer;
        }

        if (!empty($filterProduct)) {
            $sql .= ' AND products.product_name = ?';
            $params[] = $filterProduct;
        }

        if (!empty($filterPrice)) {
            $sql .= ' AND sales.sale_price = ?';
            $params[] = $filterPrice;
        }

        // Execute the filtered query and display results
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            echo '<tr>';
            echo '<td>' . $row['customer_name'] . '</td>';
            echo '<td>' . $row['product_name'] . '</td>';
            echo '<td>' . $row['sale_price'] . '</td>';
            echo '<td>' . $row['sale_date'] . '</td>';
            echo '</tr>';
        }
        // Calculate the total price of the filtered entries
        $stmt = $pdo->prepare('SELECT SUM(sale_price) AS total_price FROM sales WHERE 1 = 1');
        $stmt->execute($params);
        $totalPrice = $stmt->fetchColumn();
    }

?>
</table>

<?php if (isset($totalPrice)) : ?>
    <p>Total Price of Filtered Entries: <?php echo $totalPrice; ?></p>
<?php endif; ?>


</body>
</html>