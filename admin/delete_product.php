<?php
declare(strict_types=1);

include '../includes/auth.php';
require_admin();
include '../config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: products.php?status=error');
    exit();
}

$stmt = $conn->prepare('SELECT image FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    $stmt->close();
    header('Location: products.php?status=notfound');
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

$conn->begin_transaction();

try {
    $stmt = $conn->prepare('DELETE FROM products WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();

    if (!empty($product['image'])) {
        $imagePath = realpath(__DIR__ . '/../uploads/' . $product['image']);
        $uploadsDir = realpath(__DIR__ . '/../uploads');
        if ($imagePath && $uploadsDir && strpos($imagePath, $uploadsDir) === 0 && is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    $conn->commit();
    header('Location: products.php?status=deleted');
    exit();
} catch (Throwable $e) {
    $conn->rollback();
    header('Location: products.php?status=error');
    exit();
}

