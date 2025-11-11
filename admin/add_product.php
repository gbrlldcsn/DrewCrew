<?php
include '../includes/auth.php';
require_admin();
include '../config.php';
include '../includes/validation.php';

if (isset($_POST['save'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'] ?: null;
    $image_path_input = trim($_POST['image_path'] ?? '');

    // handle upload or path
    $imageName = null;
    if ($image_path_input !== '') {
        // Store absolute/relative path as-is so frontend can load directly
        $imageName = $image_path_input;
    } elseif (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $ext;
        $dest = '../uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $dest);
    }

    // Infer category from image path if not selected
    if ($category_id === null && $imageName) {
        // Look for /products/<category>/ in the path
        if (preg_match('#/products/(tees|bottoms|accessories)/#i', $imageName, $m)) {
            $catName = ucfirst(strtolower($m[1]));
            // Ensure category exists, then get its ID
            $stmt = $conn->prepare('SELECT id FROM categories WHERE LOWER(category_name) = LOWER(?) LIMIT 1');
            $stmt->bind_param('s', $catName);
            $stmt->execute();
            $stmt->bind_result($cid);
            if ($stmt->fetch()) {
                $category_id = $cid;
            }
            $stmt->close();
            if ($category_id === null) {
                $stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->bind_param('s', $catName);
                if ($stmt->execute()) {
                    $category_id = $stmt->insert_id;
                }
                $stmt->close();
            }
        }
    }

    $stock = isset($_POST['stock']) ? max(0, (int)$_POST['stock']) : 0;
    
    // Add stock column if it doesn't exist
    $conn->query("ALTER TABLE products ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0");

    validate_required($name, 'Name', $errors);
    validate_positive($price, 'Price', $errors);

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO products (category_id, name, price, size, description, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isdsssi', $category_id, $name, $price, $size, $description, $imageName, $stock);
        $stmt->execute();
        header('Location: products.php');
        exit();
    }
}

$cats = $conn->query('SELECT * FROM categories');
?>
<?php include '../includes/header.php'; ?>

<h2>Add Product</h2>

<form method="POST" enctype="multipart/form-data" class="mt-4" style="padding-bottom: 2rem;">
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-select">
            <option value="">-- Select Category --</option>
            <?php while ($cat = $cats->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Size</label>
        <input type="text" name="size" class="form-control" placeholder="e.g., S,M,L,XL">
    </div>
    
    <div class="mb-3">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" value="0" min="0" required>
        <div class="form-text">Initial stock quantity</div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"></textarea>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Image upload</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <div class="form-text">Or provide a path below (e.g., /DrewCrew/assets/products/accessories/accessories1.png)</div>
    </div>
    <div class="mb-3">
        <label class="form-label">Image path (optional)</label>
        <input type="text" name="image_path" class="form-control" placeholder="/DrewCrew/assets/products/...">
    </div>
    
    <button type="submit" name="save" class="btn btn-primary">Save Product</button>
    <a href="products.php" class="btn btn-secondary">Cancel</a>
</form>

<?php include '../includes/footer.php'; ?>
