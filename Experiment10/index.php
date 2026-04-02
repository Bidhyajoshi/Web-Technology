<?php
session_start();

// 1. Simulation of a Product Database (10 Products)

$products = [
    // --- LAPTOPS ---
    ["id" => 1, "name" => "MacBook Pro M3", "price" => 1999, "category" => "Laptops", "img" => "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500"],
    ["id" => 2, "name" => "Dell XPS 15", "price" => 1699, "category" => "Laptops", "img" => "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=500"],
    ["id" => 3, "name" => "ASUS Zephyrus G14", "price" => 1450, "category" => "Laptops", "img" => "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500"],
    ["id" => 4, "name" => "Razer Blade 16", "price" => 2899, "category" => "Laptops", "img" => "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=500"],

    // --- PHONES ---
    ["id" => 5, "name" => "iPhone 15 Pro", "price" => 999, "category" => "Phones", "img" => "https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=500"],
    ["id" => 6, "name" => "Samsung S24 Ultra", "price" => 1199, "category" => "Phones", "img" => "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=500"],
    ["id" => 7, "name" => "Google Pixel 8 Pro", "price" => 899, "category" => "Phones", "img" => "https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=500"],
    ["id" => 8, "name" => "OnePlus 12", "price" => 799, "category" => "Phones", "img" => "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=500"],

    // --- AUDIO & WEARABLES ---
    ["id" => 9, "name" => "Sony WH-1000XM5", "price" => 349, "category" => "Audio", "img" => "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500"],
    ["id" => 10, "name" => "Apple Watch Ultra", "price" => 799, "category" => "Wearables", "img" => "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=500"],
    ["id" => 12, "name" => "Galaxy Watch 6", "price" => 299, "category" => "Wearables", "img" => "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500"],

    // --- GAMING & ACCESSORIES ---
    ["id" => 13, "name" => "PlayStation 5 Slim", "price" => 499, "category" => "Gaming", "img" => "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=500"],
    ["id" => 14, "name" => "Xbox Series X", "price" => 499, "category" => "Gaming", "img" => "https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=500"],
    ["id" => 15, "name" => "Logitech MX Master 3S", "price" => 99, "category" => "Accessories", "img" => "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500"],
    ["id" => 16, "name" => "Keychron K2 V2", "price" => 89, "category" => "Accessories", "img" => "https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=500"],

    // --- CAMERAS & SMART HOME ---
    ["id" => 17, "name" => "Canon EOS R5", "price" => 3299, "category" => "Cameras", "img" => "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=500"],
    ["id" => 18, "name" => "DJI Mini 4 Pro", "price" => 759, "category" => "Drones", "img" => "https://images.unsplash.com/photo-1508614589041-895b88991e3e?w=500"],
    ["id" => 19, "name" => "Sonos Era 300", "price" => 449, "category" => "Home Audio", "img" => "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500"],
    
];


// 2. Handle Session logic (Adding to Cart)
if (isset($_POST['product_id'])) {
    $p_id = $_POST['product_id'];
    // Find the product in our array
    foreach ($products as $p) {
        if ($p['id'] == $p_id) {
            $_SESSION['cart'][] = $p;
            $added_name = $p['name'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NexGen Tech | Premium Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    :root {
    --brand: #6366f1;
    --bg: #f1f5f9;
    --text: #0f172a;
    --glass: rgba(255,255,255,0.7);
}

/* GLOBAL */
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, #eef2ff, #f8fafc);
    margin: 0;
    color: var(--text);
}

/* HEADER */
header {
    background: var(--glass);
    backdrop-filter: blur(15px);
    padding: 1rem 8%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid #e2e8f0;
}

header div {
    letter-spacing: 2px;
}

/* CART BADGE */
.cart-badge {
    background: var(--brand);
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    margin-left: 5px;
}

/* HERO */
.hero {
    padding: 60px 8%;
    text-align: center;
    background: linear-gradient(to right, #6366f1, #8b5cf6);
    color: white;
    border-radius: 0 0 30px 30px;
}

.hero h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.hero p {
    opacity: 0.8;
}

/* GRID */
.shop-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    padding: 50px 8%;
}

/* CARD */
.card {
    background: white;
    border-radius: 18px;
    overflow: hidden;
    transition: all 0.35s ease;
    position: relative;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

/* CARD HOVER EFFECT */
.card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 40px rgba(0,0,0,0.08);
}

/* IMAGE ZOOM */
.card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.card:hover img {
    transform: scale(1.08);
}

/* BODY */
.card-body {
    padding: 20px;
}

.category {
    font-size: 0.7rem;
    color: var(--brand);
    font-weight: 700;
    letter-spacing: 1px;
}

/* TITLE */
.card-body h3 {
    margin: 8px 0;
    font-size: 1.1rem;
}

/* PRICE */
.price {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 10px 0;
}

/* BUTTON */
.add-btn {
    width: 100%;
    background: linear-gradient(to right, #6366f1, #8b5cf6);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

/* BUTTON HOVER */
.add-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(99,102,241,0.4);
}

/* FLOATING ALERT */
.alert {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: #10b981;
    color: white;
    padding: 15px 25px;
    border-radius: 12px;
    z-index: 1000;
    animation: slideUp 0.5s ease;
}

/* ANIMATION */
@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
</head>
<body>

<header>
    <div style="font-size: 1.5rem; font-weight: 800;">NEX<span style="color:var(--brand)">GEN</span></div>
    <nav>
        <a href="cart.php" style="text-decoration:none; color:var(--text); font-weight:600;">
            🛒 Cart <span class="cart-badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
        </a>
    </nav>
</header>

<div class="hero">
    <h1>Future Technology, Today.</h1>
    <p style="color:#64748b">Browse our curated selection of high-performance gadgets.</p>
</div>

<div class="shop-container">
    <?php foreach ($products as $p): ?>
    <div class="card">
        <img src="<?php echo $p['img']; ?>" alt="Product">
        <div class="card-body">
            <span class="category"><?php echo $p['category']; ?></span>
            <h3 style="margin: 5px 0;"><?php echo $p['name']; ?></h3>
            <div class="price">$<?php echo number_format($p['price'], 2); ?></div>
            <form method="POST">
                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                <button type="submit" class="add-btn">Add to Cart</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if(isset($added_name)): ?>
    <div class="alert">Successfully added <strong><?php echo $added_name; ?></strong> to cart!</div>
    <script>setTimeout(() => { document.querySelector('.alert').style.display='none'; }, 3000);</script>
<?php endif; ?>

</body>
</html>