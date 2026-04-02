<?php
require_once 'config.php';

// --- Validate product ID ---
 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// --- Fetch the product ---
 $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
 $stmt->execute([':id' => $id]);
 $product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// --- Fetch related products (same category, different ID) ---
 $relStmt = $pdo->prepare("SELECT * FROM products WHERE category = :cat AND id != :id ORDER BY rating DESC LIMIT 4");
 $relStmt->execute([':cat' => $product['category'], ':id' => $id]);
 $related = $relStmt->fetchAll();

 $stockClass = $product['stock'] > 20 ? 'in-stock' : ($product['stock'] > 0 ? 'low-stock' : 'out-of-stock');
 $stockText  = $product['stock'] > 20 ? 'In Stock' : ($product['stock'] > 0 ? "Only {$product['stock']} left" : 'Sold Out');

// Generate star rating HTML
 $fullStars  = floor($product['rating']);
 $halfStar   = ($product['rating'] - $fullStars) >= 0.5;
 $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
 $starsHtml  = str_repeat('<i class="fas fa-star"></i>', $fullStars)
            . ($halfStar ? '<i class="fas fa-star-half-alt"></i>' : '')
            . str_repeat('<i class="far fa-star"></i>', $emptyStars);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> — CURATED</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f0f0f;
            --bg-elevated: #1a1a1a;
            --card: #161616;
            --fg: #f0ece4;
            --fg-muted: #8a8578;
            --accent: #c8a45e;
            --accent-light: #e0c87a;
            --accent-dim: rgba(200, 164, 94, 0.12);
            --border: #2a2a2a;
            --danger: #e05555;
            --success: #5cb87a;
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
        body::before {
            content: '';
            position: fixed;
            top: -20%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(200,164,94,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

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
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a {
            color: var(--fg-muted); text-decoration: none;
            font-size: 0.85rem; font-weight: 500; letter-spacing: 1px;
            text-transform: uppercase; transition: color 0.3s;
        }
        .nav-links a:hover { color: var(--accent); }
        .back-link { display: flex; align-items: center; gap: 8px; }

        /* Product Detail Layout */
        .product-detail {
            position: relative; z-index: 1;
            max-width: 1400px; margin: 0 auto;
            padding: 60px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }
        .product-image-wrap {
            position: sticky; top: 100px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            aspect-ratio: 1;
        }
        .product-image-wrap img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.6s;
        }
        .product-image-wrap:hover img { transform: scale(1.03); }

        .product-info { padding-top: 20px; }
        .product-info .breadcrumb {
            font-size: 0.82rem; color: var(--fg-muted);
            margin-bottom: 24px;
        }
        .product-info .breadcrumb a { color: var(--accent); text-decoration: none; }

        .product-info .category-tag {
            display: inline-block;
            background: var(--accent-dim);
            color: var(--accent);
            font-size: 0.75rem; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
            padding: 6px 16px; border-radius: 100px;
            margin-bottom: 16px;
        }
        .product-info h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.15;
            margin-bottom: 20px;
        }
        .product-info .rating-row {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 24px;
        }
        .product-info .stars { color: var(--accent); font-size: 1rem; }
        .product-info .rating-num { font-weight: 600; }
        .product-info .stock-tag {
            font-size: 0.78rem; font-weight: 600;
            padding: 5px 14px; border-radius: 100px;
        }
        .stock-tag.in-stock { background: rgba(92,184,122,0.12); color: var(--success); }
        .stock-tag.low-stock { background: rgba(224,170,60,0.12); color: #e0aa3c; }
        .stock-tag.out-of-stock { background: rgba(224,85,85,0.12); color: var(--danger); }

        .product-info .price-big {
            font-size: 2.4rem; font-weight: 900;
            margin-bottom: 28px;
        }
        .product-info .price-big span { font-size: 1rem; color: var(--fg-muted); font-weight: 400; margin-left: 8px; }

        .product-info .description {
            font-size: 1.02rem; color: var(--fg-muted);
            line-height: 1.75;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
        }

        .meta-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
            margin-bottom: 36px;
        }
        .meta-item {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px;
        }
        .meta-item .meta-label {
            font-size: 0.72rem; color: var(--fg-muted);
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .meta-item .meta-value { font-weight: 700; font-size: 1.05rem; }

        .action-row {
            display: flex; gap: 12px; flex-wrap: wrap;
        }
        .btn-primary {
            flex: 1; min-width: 200px;
            background: var(--accent);
            color: var(--bg);
            border: none;
            padding: 16px 32px;
            border-radius: 100px;
            font-size: 0.95rem; font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
        .btn-primary:disabled {
            background: var(--fg-muted); cursor: not-allowed; transform: none;
        }
        .btn-secondary {
            background: transparent;
            color: var(--fg);
            border: 1px solid var(--border);
            padding: 16px 24px;
            border-radius: 100px;
            font-size: 0.95rem; font-family: inherit;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }

        /* Related Products */
        .related-section {
            position: relative; z-index: 1;
            max-width: 1400px; margin: 0 auto 80px;
            padding: 0 40px;
        }
        .related-section h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1.4rem;
            margin-bottom: 24px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        .related-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            text-decoration: none; color: inherit;
            display: flex; flex-direction: column;
            transition: transform 0.3s, border-color 0.3s;
        }
        .related-card:hover { transform: translateY(-4px); border-color: rgba(200,164,94,0.3); }
        .related-card img {
            width: 100%; aspect-ratio: 1; object-fit: cover;
            transition: transform 0.5s;
        }
        .related-card:hover img { transform: scale(1.05); }
        .related-card .rc-body { padding: 16px; }
        .related-card .rc-cat {
            font-size: 0.7rem; color: var(--accent);
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .related-card .rc-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700; font-size: 1rem;
            margin-bottom: 8px;
            display: -webkit-box; -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .related-card .rc-price { font-weight: 700; font-size: 1.1rem; }

        footer {
            position: relative; z-index: 1;
            border-top: 1px solid var(--border);
            padding: 40px; text-align: center;
            color: var(--fg-muted); font-size: 0.85rem;
        }
        footer a { color: var(--accent); text-decoration: none; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeUp 0.5s ease forwards; }

        @media (max-width: 900px) {
            .product-detail { grid-template-columns: 1fr; gap: 32px; padding: 30px 20px; }
            .product-image-wrap { position: static; }
            .related-section { padding: 0 20px; }
            nav { padding: 0 20px; }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-inner">
            <a href="index.php" class="logo">CURATED</a>
            <div class="nav-links">
                <a href="index.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Shop
                </a>
            </div>
        </div>
    </nav>

    <main class="product-detail">
        <!-- Image -->
        <div class="product-image-wrap anim" style="animation-delay: 0.05s">
            <img src="<?= htmlspecialchars($product['image']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
        </div>

        <!-- Info -->
        <div class="product-info">
            <div class="breadcrumb anim" style="animation-delay: 0.1s">
                <a href="index.php">Home</a> &nbsp;/&nbsp;
                <a href="index.php?category=<?= urlencode($product['category']) ?>">
                    <?= htmlspecialchars($product['category']) ?>
                </a> &nbsp;/&nbsp;
                <?= htmlspecialchars($product['name']) ?>
            </div>

            <div class="category-tag anim" style="animation-delay: 0.15s">
                <?= htmlspecialchars($product['category']) ?>
            </div>

            <h1 class="anim" style="animation-delay: 0.2s">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <div class="rating-row anim" style="animation-delay: 0.25s">
                <span class="stars"><?= $starsHtml ?></span>
                <span class="rating-num"><?= $product['rating'] ?></span>
                <span class="stock-tag <?= $stockClass ?>"><?= $stockText ?></span>
            </div>

            <div class="price-big anim" style="animation-delay: 0.3s">
                $<?= number_format($product['price'], 2) ?>
                <span>USD</span>
            </div>

            <p class="description anim" style="animation-delay: 0.35s">
                <?= htmlspecialchars($product['description']) ?>
            </p>

            <div class="meta-grid anim" style="animation-delay: 0.4s">
                <div class="meta-item">
                    <div class="meta-label">Product ID</div>
                    <div class="meta-value">#<?= str_pad($product['id'], 4, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Category</div>
                    <div class="meta-value"><?= htmlspecialchars($product['category']) ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Stock Available</div>
                    <div class="meta-value"><?= $product['stock'] ?> units</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Date Added</div>
                    <div class="meta-value"><?= date('M d, Y', strtotime($product['created_at'])) ?></div>
                </div>
            </div>

            <div class="action-row anim" style="animation-delay: 0.45s">
                <button class="btn-primary" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <?php if ($product['stock'] > 0): ?>
                        <i class="fas fa-shopping-bag"></i>&nbsp; Add to Cart
                    <?php else: ?>
                        Out of Stock
                    <?php endif; ?>
                </button>
                <button class="btn-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>
        </div>
    </main>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <section class="related-section">
        <h2>You Might Also Like</h2>
        <div class="related-grid">
            <?php foreach ($related as $rp): ?>
            <a href="product.php?id=<?= $rp['id'] ?>" class="related-card">
                <img src="<?= htmlspecialchars($rp['image']) ?>"
                     alt="<?= htmlspecialchars($rp['name']) ?>" loading="lazy">
                <div class="rc-body">
                    <div class="rc-cat"><?= htmlspecialchars($rp['category']) ?></div>
                    <div class="rc-title"><?= htmlspecialchars($rp['name']) ?></div>
                    <div class="rc-price">$<?= number_format($rp['price'], 2) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <footer>
        <p>CURATED E-Commerce &mdash; Product details retrieved via <code>PDO::prepare()</code> with parameterized query.
        <br><a href="admin.php">Admin Panel</a></p>
    </footer>

    <script>
        // Add to cart button feedback
        document.querySelector('.btn-primary:not([disabled])')?.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-check"></i>&nbsp; Added to Cart';
            this.style.background = '#5cb87a';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-shopping-bag"></i>&nbsp; Add to Cart';
                this.style.background = '';
            }, 2000);
        });
    </script>
</body>
</html>