<?php
include '../includes/auth.php';
require_admin();
include '../config.php';
include '../includes/validation.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: products.php');
    exit();
}

// Fetch current product
$stmt = $conn->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php?status=notfound');
    exit();
}

$errors = [];

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'] ?: null;
    $image_path_input = trim($_POST['image_path'] ?? '');

    // Handle image change (either keep, new path, or uploaded file)
    $imageName = $product['image'];
    if ($image_path_input !== '') {
        $imageName = $image_path_input; // store path as-is
    } elseif (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $ext;
        $dest = '../uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $dest);
    }

    // Infer category from image path if not selected
    if ($category_id === null && $imageName) {
        if (preg_match('#/products/(tees|bottoms|accessories)/#i', $imageName, $m)) {
            $catName = ucfirst(strtolower($m[1]));
            $stmt = $conn->prepare('SELECT id FROM categories WHERE LOWER(category_name) = LOWER(?) LIMIT 1');
            $stmt->bind_param('s', $catName);
            $stmt->execute();
            $stmt->bind_result($cid);
            if ($stmt->fetch()) { $category_id = $cid; }
            $stmt->close();
            if ($category_id === null) {
                $stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->bind_param('s', $catName);
                if ($stmt->execute()) { $category_id = $stmt->insert_id; }
                $stmt->close();
            }
        }
    }

    validate_required($name, 'Name', $errors);
    validate_positive($price, 'Price', $errors);

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE products SET category_id = ?, name = ?, price = ?, size = ?, description = ?, image = ? WHERE id = ?');
        $stmt->bind_param('isdsssi', $category_id, $name, $price, $size, $description, $imageName, $id);
        if ($stmt->execute()) {
            header('Location: products.php?status=updated');
            exit();
        } else {
            $errors[] = 'Failed to update product.';
        }
        $stmt->close();
    }
}

$cats = $conn->query('SELECT * FROM categories');
?>
<?php include '../includes/header.php'; ?>

<h2>Edit Product</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="mt-4">
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">-- Select Category --</option>
            <?php while ($cat = $cats->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id']==$cat['id'])?'selected':''; ?>><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo htmlspecialchars($product['price']); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Size</label>
        <input type="text" name="size" class="form-control" value="<?php echo htmlspecialchars($product['size']); ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Current Image</label><br>
        <?php if (!empty($product['image'])): ?>
            <img src="<?php echo htmlspecialchars((str_starts_with($product['image'],'/'))?$product['image']:'../uploads/'.$product['image']); ?>" alt="" style="max-width:150px; height:auto;">
        <?php else: ?>
            <span class="text-muted">No Image</span>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Replace Image (upload)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <div class="form-text">Or provide a path below (e.g., /DrewCrew/assets/products/...)</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Image path (optional)</label>
        <input type="text" name="image_path" class="form-control" value="<?php echo htmlspecialchars($product['image']); ?>">
    </div>

    <button type="submit" name="save" class="btn btn-primary">Update Product</button>
    <a href="products.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>


