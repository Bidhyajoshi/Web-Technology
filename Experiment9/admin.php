<?php
require_once 'config.php';

// --- Handle form submission (CREATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $image       = trim($_POST['image'] ?? 'https://picsum.photos/seed/' . time() . '/400/400.jpg');
    $stock       = intval($_POST['stock'] ?? 0);
    $rating      = floatval($_POST['rating'] ?? 4.5);
    $featured    = isset($_POST['featured']) ? 1 : 0;

    $errors = [];
    if ($name === '')         $errors[] = 'Product name is required.';
    if ($description === '')  $errors[] = 'Description is required.';
    if ($price <= 0)          $errors[] = 'Price must be greater than zero.';
    if ($category === '')     $errors[] = 'Category is required.';
    if ($stock < 0)           $errors[] = 'Stock cannot be negative.';
    if ($rating < 0 || $rating > 5) $errors[] = 'Rating must be between 0 and 5.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, category, image, stock, rating, featured)
            VALUES (:name, :desc, :price, :cat, :img, :stock, :rating, :feat)
        ");
        $stmt->execute([
            ':name'   => $name,
            ':desc'   => $description,
            ':price'  => $price,
            ':cat'    => $category,
            ':img'    => $image,
            ':stock'  => $stock,
            ':rating' => $rating,
            ':feat'   => $featured,
        ]);
        header('Location: admin.php?msg=' . urlencode('Product "' . $name . '" created successfully!'));
        exit;
    }
}

// --- Handle EDIT submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $editId      = intval($_POST['edit_id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $image       = trim($_POST['image'] ?? '');
    $stock       = intval($_POST['stock'] ?? 0);
    $rating      = floatval($_POST['rating'] ?? 4.5);
    $featured    = isset($_POST['featured']) ? 1 : 0;

    if ($editId > 0 && $name !== '' && $price > 0) {
        $stmt = $pdo->prepare("
            UPDATE products SET
                name = :name, description = :desc, price = :price,
                category = :cat, image = :img, stock = :stock,
                rating = :rating, featured = :feat
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'   => $name,
            ':desc'   => $description,
            ':price'  => $price,
            ':cat'    => $category,
            ':img'    => $image,
            ':stock'  => $stock,
            ':rating' => $rating,
            ':feat'   => $featured,
            ':id'     => $editId,
        ]);
        header('Location: admin.php?msg=' . urlencode('Product updated successfully!'));
        exit;
    }
}

// --- Handle edit mode (pre-fill form) ---
 $editProduct = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $editProduct = $stmt->fetch();
}

// --- Fetch all products for the table ---
 $allProducts = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — CURATED</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f0f0f;
            --card: #161616;
            --card-alt: #1c1c1c;
            --fg: #f0ece4;
            --fg-muted: #8a8578;
            --accent: #c8a45e;
            --accent-light: #e0c87a;
            --accent-dim: rgba(200, 164, 94, 0.12);
            --border: #2a2a2a;
            --danger: #e05555;
            --danger-dim: rgba(224,85,85,0.12);
            --success: #5cb87a;
            --success-dim: rgba(92,184,122,0.12);
            --radius: 12px;
            --radius-lg: 20px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--fg);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* NAV */
        nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(15,15,15,0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 40px;
        }
        .nav-inner {
            max-width: 1400px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between; height: 72px;
        }
        .logo {
            font-family: 'Playfair Display', serif; font-weight: 900;
            font-size: 1.6rem; letter-spacing: 4px;
            color: var(--accent); text-decoration: none;
        }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a {
            color: var(--fg-muted); text-decoration: none;
            font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;
            text-transform: uppercase; transition: color 0.3s;
        }
        .nav-links a:hover { color: var(--accent); }

        /* LAYOUT */
        .admin-layout {
            position: relative; z-index: 1;
            max-width: 1400px; margin: 0 auto;
            padding: 40px;
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 40px;
            align-items: start;
        }

        /* FORM PANEL */
        .form-panel {
            position: sticky; top: 90px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 32px;
        }
        .form-panel h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1.4rem;
            margin-bottom: 6px;
        }
        .form-panel .form-subtitle {
            color: var(--fg-muted); font-size: 0.88rem;
            margin-bottom: 28px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 0.8rem; font-weight: 600;
            color: var(--fg-muted);
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 13px 16px;
            color: var(--fg);
            font-size: 0.92rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
        }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .form-group select { cursor: pointer; }
        .form-group select option { background: var(--bg); color: var(--fg); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .checkbox-group {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px; height: 18px;
            accent-color: var(--accent);
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0; cursor: pointer;
            font-size: 0.9rem; color: var(--fg);
            text-transform: none; letter-spacing: 0; font-weight: 400;
        }

        .btn-submit {
            width: 100%;
            background: var(--accent);
            color: var(--bg);
            border: none;
            padding: 15px 24px;
            border-radius: 100px;
            font-size: 0.95rem; font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-top: 8px;
        }
        .btn-submit:hover { background: var(--accent-light); transform: translateY(-1px); }
        .btn-cancel {
            width: 100%;
            background: transparent;
            color: var(--fg-muted);
            border: 1px solid var(--border);
            padding: 13px 24px;
            border-radius: 100px;
            font-size: 0.9rem; font-family: inherit;
            cursor: pointer;
            transition: border-color 0.3s;
            margin-top: 10px;
            display: none;
        }
        .btn-cancel:hover { border-color: var(--fg-muted); }
        .btn-cancel.visible { display: block; }

        .error-box {
            background: var(--danger-dim);
            border: 1px solid rgba(224,85,85,0.3);
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            color: var(--danger);
        }
        .error-box ul { margin-left: 18px; }

        /* TABLE PANEL */
        .table-panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        .table-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .table-header h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1.3rem;
        }
        .table-header .count {
            background: var(--accent-dim);
            color: var(--accent);
            font-size: 0.78rem; font-weight: 700;
            padding: 5px 14px; border-radius: 100px;
        }

        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }
        thead th {
            text-align: left;
            padding: 14px 20px;
            background: var(--card-alt);
            color: var(--fg-muted);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        tbody td {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        tbody tr { transition: background 0.2s; }
        tbody tr:hover { background: rgba(200,164,94,0.03); }
        tbody tr:last-child td { border-bottom: none; }

        .td-product {
            display: flex; align-items: center; gap: 14px;
        }
        .td-product img {
            width: 48px; height: 48px;
            border-radius: 8px; object-fit: cover;
            border: 1px solid var(--border);
        }
        .td-product .td-name {
            font-weight: 600;
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .td-product .td-cat {
            font-size: 0.75rem; color: var(--fg-muted);
        }

        .td-price { font-weight: 700; color: var(--accent); }
        .td-stock {
            font-weight: 600;
        }
        .td-stock.low { color: #e0aa3c; }
        .td-stock.out { color: var(--danger); }

        .td-actions { display: flex; gap: 8px; }
        .btn-icon {
            width: 34px; height: 34px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: transparent;
            color: var(--fg-muted);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.82rem;
            transition: all 0.2s;
        }
        .btn-icon.edit:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }
        .btn-icon.delete:hover { border-color: var(--danger); color: var(--danger); background: var(--danger-dim); }

        .featured-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            margin-right: 6px;
        }
        .featured-dot.no { background: var(--fg-muted); opacity: 0.3; }

        .empty-table {
            text-align: center; padding: 60px 20px;
            color: var(--fg-muted);
        }
        .empty-table i { font-size: 2.4rem; margin-bottom: 12px; display: block; }

        /* TOAST */
        .toast {
            position: fixed; bottom: 30px; right: 30px;
            background: var(--card);
            border: 1px solid var(--border);
            border-left: 4px solid var(--success);
            border-radius: var(--radius);
            padding: 16px 24px;
            color: var(--fg); font-size: 0.9rem;
            z-index: 999;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }
        .toast.show { transform: translateX(0); }

        footer {
            position: relative; z-index: 1;
            border-top: 1px solid var(--border);
            padding: 40px; text-align: center;
            color: var(--fg-muted); font-size: 0.85rem;
        }
        footer a { color: var(--accent); text-decoration: none; }

        @media (max-width: 1024px) {
            .admin-layout { grid-template-columns: 1fr; }
            .form-panel { position: static; }
        }
        @media (max-width: 768px) {
            nav { padding: 0 20px; }
            .admin-layout { padding: 20px; }
            .form-panel { padding: 24px; }
            .table-header { padding: 18px 20px; }
            thead th, tbody td { padding: 10px 14px; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-inner">
            <a href="index.php" class="logo">CURATED</a>
            <div class="nav-links">
                <a href="index.php"><i class="fas fa-store"></i> Shop</a>
                <a href="admin.php" style="color: var(--accent)"><i class="fas fa-cog"></i> Admin</a>
            </div>
        </div>
    </nav>

    <div class="admin-layout">
        <!-- ===== CREATE / EDIT FORM ===== -->
        <div class="form-panel">
            <h2><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h2>
            <p class="form-subtitle">
                <?= $editProduct
                    ? 'Update the product details below.'
                    : 'Fill in the details to store a new product in the database.' ?>
            </p>

            <?php if (!empty($errors)): ?>
            <div class="error-box">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="admin.php" id="productForm">
                <input type="hidden" name="action" value="<?= $editProduct ? 'edit' : 'create' ?>">
                <?php if ($editProduct): ?>
                <input type="hidden" name="edit_id" value="<?= $editProduct['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required
                           value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"
                           placeholder="e.g. Wireless Headphones">
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required
                              placeholder="Detailed product description..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (USD) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" required
                               value="<?= $editProduct ? $editProduct['price'] : '' ?>"
                               placeholder="49.99">
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" min="0"
                               value="<?= $editProduct ? $editProduct['stock'] : '50' ?>"
                               placeholder="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select category</option>
                            <option value="Electronics" <?= ($editProduct['category'] ?? '') === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                            <option value="Clothing" <?= ($editProduct['category'] ?? '') === 'Clothing' ? 'selected' : '' ?>>Clothing</option>
                            <option value="Accessories" <?= ($editProduct['category'] ?? '') === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                            <option value="Home & Living" <?= ($editProduct['category'] ?? '') === 'Home & Living' ? 'selected' : '' ?>>Home & Living</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating (0-5)</label>
                        <input type="number" id="rating" name="rating" step="0.1" min="0" max="5"
                               value="<?= $editProduct ? $editProduct['rating'] : '4.5' ?>"
                               placeholder="4.5">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image"
                           value="<?= htmlspecialchars($editProduct['image'] ?? 'https://picsum.photos/seed/' . time() . '/400/400.jpg') ?>"
                           placeholder="https://example.com/image.jpg">
                    <small style="color: var(--fg-muted); font-size: 0.78rem;">
                        Leave default for a random placeholder image.
                    </small>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="featured" name="featured"
                           <?= ($editProduct['featured'] ?? 0) ? 'checked' : '' ?>>
                    <label for="featured">Mark as featured product</label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-<?= $editProduct ? 'save' : 'plus' ?>"></i>&nbsp;
                    <?= $editProduct ? 'Update Product' : 'Add Product to Database' ?>
                </button>

                <?php if ($editProduct): ?>
                <a href="admin.php" class="btn-cancel visible">Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- ===== PRODUCT TABLE ===== -->
        <div class="table-panel">
            <div class="table-header">
                <h2>All Products</h2>
                <span class="count"><?= count($allProducts) ?> records</span>
            </div>

            <?php if (empty($allProducts)): ?>
            <div class="empty-table">
                <i class="fas fa-inbox"></i>
                <p>No products in the database yet.</p>
                <p>Use the form to add your first product.</p>
            </div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Rating</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allProducts as $p): ?>
                        <tr>
                            <td>
                                <div class="td-product">
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="" loading="lazy">
                                    <div>
                                        <div class="td-name"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="td-cat"><?= htmlspecialchars($p['category']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="td-price">$<?= number_format($p['price'], 2) ?></td>
                            <td class="td-stock <?= $p['stock'] <= 0 ? 'out' : ($p['stock'] <= 10 ? 'low' : '') ?>">
                                <?= $p['stock'] ?>
                            </td>
                            <td><i class="fas fa-star" style="color:var(--accent);font-size:0.75rem"></i> <?= $p['rating'] ?></td>
                            <td>
                                <span class="featured-dot <?= $p['featured'] ? '' : 'no' ?>"></span>
                                <?= $p['featured'] ? 'Yes' : 'No' ?>
                            </td>
                            <td>
                                <div class="td-actions">
                                    <a href="product.php?id=<?= $p['id'] ?>" class="btn-icon edit" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="admin.php?edit=<?= $p['id'] ?>" class="btn-icon edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $p['id'] ?>"
                                       class="btn-icon delete"
                                       title="Delete"
                                       onclick="return confirm('Delete this product permanently?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>Admin Panel &mdash; Data stored in MySQL via PDO prepared statements.
        <br><a href="index.php">View Storefront</a></p>
    </footer>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script>
        // Toast notification
        const params = new URLSearchParams(window.location.search);
        const msg = params.get('msg');
        if (msg) {
            const toast = document.getElementById('toast');
            toast.textContent = decodeURIComponent(msg);
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
            const url = new URL(window.location);
            url.searchParams.delete('msg');
            history.replaceState({}, '', url);
        }

        // Auto-generate image URL from product name if image field is default
        const nameInput = document.getElementById('name');
        const imageInput = document.getElementById('image');
        if (nameInput && imageInput && !imageInput.value.startsWith('http')) {
            nameInput.addEventListener('input', () => {
                const seed = nameInput.value.toLowerCase().replace(/\s+/g, '-');
                if (seed) {
                    imageInput.value = 'https://picsum.photos/seed/' + seed + '/400/400.jpg';
                }
            });
        }
    </script>
</body>
</html>