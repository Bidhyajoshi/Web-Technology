<?php
require_once 'config.php';

 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Fetch product name for the feedback message
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete the product using prepared statement
        $del = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $del->execute([':id' => $id]);
        header('Location: admin.php?msg=' . urlencode('"' . $product['name'] . '" has been deleted.'));
        exit;
    }
}

// Fallback if ID was invalid
header('Location: admin.php?msg=' . urlencode('Product not found.'));
exit;