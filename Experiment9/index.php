<?php
require_once 'config.php';

// --- Handle category filter ---
 $category = isset($_GET['category']) ? $_GET['category'] : '';
 $search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';

// --- Build the query dynamically ---
 $where = [];
 $params = [];

if ($category !== '' && $category !== 'All') {
    $where[] = 'category = :category';
    $params[':category'] = $category;
}

if ($search !== '') {
    $where[] = '(name LIKE :search1 OR description LIKE :search2)';
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
}

 $whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

// --- Fetch products ---
 $stmt = $pdo->prepare("SELECT * FROM products $whereClause ORDER BY featured DESC, created_at DESC");
 $stmt->execute($params);
 $products = $stmt->fetchAll();

// --- Fetch featured products separately ---
 $featuredStmt = $pdo->query("SELECT * FROM products WHERE featured = 1 ORDER BY rating DESC LIMIT 4");
 $featured = $featuredStmt->fetchAll();

// --- Fetch all categories ---
 $catStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
 $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// --- Stats ---
 $totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
 $avgPrice      = $pdo->query("SELECT ROUND(AVG(price), 2) FROM products")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CURATED — Premium E-Commerce Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ===== CSS VARIABLES ===== */
        :root {
            --bg: #0f0f0f;
            --bg-elevated: #1a1a1a;
            --card: #161616;
            --card-hover: #1e1e1e;
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
            --shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        /* ===== RESET & BASE ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--fg);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== BACKGROUND ATMOSPHERE ===== */
        body::before {
            content: '';
            position: fixed;
            top: -30%; left: -10%;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(200,164,94,0.06) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -20%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(200,164,94,0.04) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== NAVIGATION ===== */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(15,15,15,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 40px;
        }
        .nav-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 72px;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 1.6rem;
            letter-spacing: 4px;
            color: var(--accent);
            text-decoration: none;
        }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a {
            color: var(--fg-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        .nav-links a:hover { color: var(--accent); }
        .nav-links .admin-link {
            background: var(--accent-dim);
            color: var(--accent);
            padding: 8px 18px;
            border-radius: 100px;
            font-size: 0.8rem;
        }
        .nav-links .admin-link:hover { background: var(--accent); color: var(--bg); }

        /* ===== HERO ===== */
        .hero {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 80px 40px 40px;
            text-align: center;
        }
        .hero-badge {
            display: inline-block;
            background: var(--accent-dim);
            color: var(--accent);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 20px;
            border-radius: 100px;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: clamp(2.4rem, 5vw, 4rem);
            line-height: 1.1;
            margin-bottom: 16px;
        }
        .hero h1 span { color: var(--accent); }
        .hero p {
            color: var(--fg-muted);
            font-size: 1.1rem;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ===== STATS BAR ===== */
        .stats-bar {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto 50px;
            padding: 0 40px;
            display: flex;
            gap: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .stat-item {
            text-align: center;
            padding: 20px 36px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            min-width: 180px;
            transition: border-color 0.3s, transform 0.3s;
        }
        .stat-item:hover { border-color: var(--accent); transform: translateY(-2px); }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 900;
            color: var(--accent);
        }
        .stat-label { font-size: 0.8rem; color: var(--fg-muted); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        /* ===== TOOLBAR (Search + Filter) ===== */
        .toolbar {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto 40px;
            padding: 0 40px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-box {
            flex: 1;
            min-width: 260px;
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--fg-muted);
            font-size: 0.9rem;
        }
        .search-box input {
            width: 100%;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 100px;
            padding: 14px 20px 14px 46px;
            color: var(--fg);
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }
        .search-box input::placeholder { color: var(--fg-muted); }
        .search-box input:focus { border-color: var(--accent); }

        .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-pill {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--fg-muted);
            padding: 12px 22px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }
        .filter-pill:hover { border-color: var(--accent); color: var(--fg); }
        .filter-pill.active {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--bg);
            font-weight: 600;
        }

        /* ===== FEATURED SECTION ===== */
        .section-header {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto 28px;
            padding: 0 40px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.5rem;
            white-space: nowrap;
        }
        .section-header .line { flex: 1; height: 1px; background: var(--border); }

        .featured-grid {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto 60px;
            padding: 0 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ===== PRODUCT GRID ===== */
        .product-grid {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto 80px;
            padding: 0 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        /* ===== PRODUCT CARD ===== */
        .product-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.3s, box-shadow 0.35s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-6px);
            border-color: rgba(200,164,94,0.3);
            box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 0 1px rgba(200,164,94,0.1);
        }
        .product-card .img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            overflow: hidden;
            background: #111;
        }
        .product-card .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .product-card:hover .img-wrap img { transform: scale(1.08); }

        .product-card .badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background: var(--accent);
            color: var(--bg);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 100px;
        }
        .product-card .stock-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 100px;
        }
        .stock-badge.in-stock { background: rgba(92,184,122,0.15); color: var(--success); }
        .stock-badge.low-stock { background: rgba(224,170,60,0.15); color: #e0aa3c; }
        .stock-badge.out-of-stock { background: rgba(224,85,85,0.15); color: var(--danger); }

        .product-card .card-body {
            padding: 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .product-card .card-category {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 8px;
        }
        .product-card .card-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
            line-height: 1.3;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-card .card-desc {
            font-size: 0.85rem;
            color: var(--fg-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 16px;
            flex: 1;
        }
        .product-card .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .product-card .card-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--fg);
        }
        .product-card .card-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.82rem;
            color: var(--accent);
        }
        .product-card .card-rating span { color: var(--fg-muted); }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 80px 40px;
            grid-column: 1 / -1;
        }
        .empty-state i { font-size: 3rem; color: var(--fg-muted); margin-bottom: 16px; }
        .empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 8px; }
        .empty-state p { color: var(--fg-muted); }

        /* ===== FOOTER ===== */
        footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 40px;
            text-align: center;
            color: var(--fg-muted);
            font-size: 0.85rem;
        }
        footer a { color: var(--accent); text-decoration: none; }

        /* ===== TOAST NOTIFICATION ===== */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--card);
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            border-radius: var(--radius);
            padding: 16px 24px;
            color: var(--fg);
            font-size: 0.9rem;
            z-index: 999;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: var(--shadow);
        }
        .toast.show { transform: translateX(0); }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in {
            opacity: 0;
            animation: fadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            nav { padding: 0 20px; }
            .nav-links { gap: 16px; }
            .nav-links a:not(.admin-link) { display: none; }
            .hero { padding: 50px 20px 30px; }
            .stats-bar { padding: 0 20px; gap: 12px; }
            .stat-item { min-width: 140px; padding: 16px 24px; }
            .toolbar { padding: 0 20px; }
            .section-header { padding: 0 20px; }
            .featured-grid, .product-grid { padding: 0 20px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <!-- ===== NAVIGATION ===== -->
    <nav>
        <div class="nav-inner">
            <a href="index.php" class="logo">CURATED</a>
            <div class="nav-links">
                <a href="index.php">Shop</a>
                <a href="#featured">Featured</a>
                <a href="admin.php" class="admin-link">
                    <i class="fas fa-cog"></i> Admin
                </a>
            </div>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <header class="hero">
        <div class="hero-badge">PHP + MySQL Powered</div>
        <h1>Products Fetched<br>From <span>Database</span></h1>
        <p>Every product you see below is retrieved in real-time from a MySQL database using PHP PDO prepared statements.</p>
    </header>

    <!-- ===== STATS ===== -->
    <div class="stats-bar">
        <div class="stat-item animate-in" style="animation-delay: 0.1s">
            <div class="stat-number"><?= $totalProducts ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-item animate-in" style="animation-delay: 0.2s">
            <div class="stat-number">$<?= $avgPrice ?></div>
            <div class="stat-label">Avg. Price</div>
        </div>
        <div class="stat-item animate-in" style="animation-delay: 0.3s">
            <div class="stat-number"><?= count($categories) ?></div>
            <div class="stat-label">Categories</div>
        </div>
        <div class="stat-item animate-in" style="animation-delay: 0.4s">
            <div class="stat-number"><?= count($featured) ?></div>
            <div class="stat-label">Featured</div>
        </div>
    </div>

    <!-- ===== FEATURED SECTION ===== -->
    <?php if (!empty($featured)): ?>
    <div class="section-header" id="featured">
        <h2>Featured Picks</h2>
        <div class="line"></div>
    </div>
    <div class="featured-grid">
        <?php foreach ($featured as $i => $p): ?>
        <?php
            $stockClass = $p['stock'] > 20 ? 'in-stock' : ($p['stock'] > 0 ? 'low-stock' : 'out-of-stock');
            $stockText  = $p['stock'] > 20 ? 'In Stock' : ($p['stock'] > 0 ? "Only {$p['stock']} left" : 'Sold Out');
        ?>
        <a href="product.php?id=<?= $p['id'] ?>" class="product-card animate-in" style="animation-delay: <?= 0.1 * $i ?>s">
            <div class="img-wrap">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                <span class="badge">Featured</span>
                <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>
            </div>
            <div class="card-body">
                <div class="card-category"><?= htmlspecialchars($p['category']) ?></div>
                <h3 class="card-title"><?= htmlspecialchars($p['name']) ?></h3>
                <p class="card-desc"><?= htmlspecialchars($p['description']) ?></p>
                <div class="card-footer">
                    <span class="card-price">$<?= number_format($p['price'], 2) ?></span>
                    <span class="card-rating">
                        <i class="fas fa-star"></i> <?= $p['rating'] ?>
                        <span>(<?= $p['stock'] ?> in stock)</span>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ===== TOOLBAR ===== -->
    <div class="section-header">
        <h2>All Products</h2>
        <div class="line"></div>
    </div>
    <form class="toolbar" method="GET" action="index.php">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search products by name or description..."
                   value="<?= htmlspecialchars($search) ?>" aria-label="Search products">
        </div>
        <div class="filter-pills">
            <a href="?category=All<?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="filter-pill <?= ($category === '' || $category === 'All') ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
               class="filter-pill <?= $category === $cat ? 'active' : '' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </form>

    <!-- ===== PRODUCT GRID ===== -->
    <div class="product-grid">
        <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>No Products Found</h3>
            <p>Try adjusting your search or filter criteria.</p>
        </div>
        <?php else: ?>
        <?php foreach ($products as $i => $p): ?>
        <?php
            $stockClass = $p['stock'] > 20 ? 'in-stock' : ($p['stock'] > 0 ? 'low-stock' : 'out-of-stock');
            $stockText  = $p['stock'] > 20 ? 'In Stock' : ($p['stock'] > 0 ? "Only {$p['stock']} left" : 'Sold Out');
        ?>
        <a href="product.php?id=<?= $p['id'] ?>" class="product-card animate-in" style="animation-delay: <?= 0.05 * $i ?>s">
            <div class="img-wrap">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                <?php if ($p['featured']): ?>
                    <span class="badge">Featured</span>
                <?php endif; ?>
                <span class="stock-badge <?= $stockClass ?>"><?= $stockText ?></span>
            </div>
            <div class="card-body">
                <div class="card-category"><?= htmlspecialchars($p['category']) ?></div>
                <h3 class="card-title"><?= htmlspecialchars($p['name']) ?></h3>
                <p class="card-desc"><?= htmlspecialchars($p['description']) ?></p>
                <div class="card-footer">
                    <span class="card-price">$<?= number_format($p['price'], 2) ?></span>
                    <span class="card-rating">
                        <i class="fas fa-star"></i> <?= $p['rating'] ?>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer>
        <p>CURATED E-Commerce &mdash; Built with PHP, MySQL, and PDO Prepared Statements.
        <br><a href="admin.php">Admin Panel</a> to add, edit, or delete products.</p>
    </footer>

    <!-- Toast for feedback -->
    <div class="toast" id="toast"></div>

    <script>
        // Show toast notification from URL params
        const params = new URLSearchParams(window.location.search);
        const msg = params.get('msg');
        if (msg) {
            const toast = document.getElementById('toast');
            toast.textContent = decodeURIComponent(msg);
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
            // Clean URL
            const url = new URL(window.location);
            url.searchParams.delete('msg');
            history.replaceState({}, '', url);
        }

        // Intersection Observer for scroll-triggered animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.style.animationPlayState = 'running';
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-in').forEach(el => {
            el.style.animationPlayState = 'paused';
            observer.observe(el);
        });
    </script>
</body>
</html>