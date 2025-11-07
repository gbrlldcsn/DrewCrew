<?php
include '../includes/auth.php';
require_admin();
include '../config.php';

// Ensure categories exist and return id
function ensure_category(mysqli $conn, string $name): int {
    $q = $conn->prepare('SELECT id FROM categories WHERE LOWER(category_name)=LOWER(?) LIMIT 1');
    $q->bind_param('s', $name);
    $q->execute();
    $q->bind_result($cid);
    if ($q->fetch()) { $q->close(); return (int)$cid; }
    $q->close();
    $ins = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
    $ins->bind_param('s', $name);
    $ins->execute();
    $newId = $ins->insert_id;
    $ins->close();
    return (int)$newId;
}

$map = [
    'tees' => ensure_category($conn, 'Tees'),
    'bottoms' => ensure_category($conn, 'Bottoms'),
    'accessories' => ensure_category($conn, 'Accessories'),
];

$res = $conn->query('SELECT id, name, image FROM products');
while ($row = $res->fetch_assoc()) {
    $id = (int)$row['id'];
    $name = strtolower($row['name'] ?? '');
    $img = strtolower((string)($row['image'] ?? ''));

    $targetCatId = 0;
    if (strpos($img, '/tees/') !== false || strpos($name, 'tee') !== false || strpos($name, 't-shirt') !== false) {
        $targetCatId = $map['tees'];
    } elseif (strpos($img, '/bottoms/') !== false || strpos($name, 'short') !== false || strpos($name, 'pants') !== false || strpos($name, 'jeans') !== false) {
        $targetCatId = $map['bottoms'];
    } elseif (strpos($img, '/accessories/') !== false || strpos($name, 'cap') !== false || strpos($name, 'hat') !== false || strpos($name, 'accessor') !== false) {
        $targetCatId = $map['accessories'];
    }

    if ($targetCatId > 0) {
        $upd = $conn->prepare('UPDATE products SET category_id = ? WHERE id = ?');
        $upd->bind_param('ii', $targetCatId, $id);
        $upd->execute();
        $upd->close();
    }
}

header('Location: products.php?status=categorized');
exit();
?>
