<?php
session_start();

// Logic to remove an item if requested
if (isset($_GET['remove'])) {
    $id_to_remove = $_GET['remove'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $id_to_remove) {
            unset($_SESSION['cart'][$key]);
            // Re-index array to prevent gaps
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bag | NexGen Tech</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== PREMIUM LIGHT THEME CART CSS ===== */
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-glow: rgba(99, 102, 241, 0.15);
    --dark: #0f172a;
    --gray: #64748b;
    --light-gray: #f1f5f9;
    --white: #ffffff;
    --border: #e2e8f0;
    --red: #ef4444;
    --red-glow: rgba(239, 68, 68, 0.1);
    --green: #10b981;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
    --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.06);
    --shadow-lg: 0 20px 40px -10px rgba(0,0,0,0.08);
    --radius: 20px;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background-color: #f8fafc; /* Light background */
    margin: 0;
    color: var(--dark);
    min-height: 100vh;
}

/* Navbar */
.navbar {
    padding: 20px 8%;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 50;
}

/* Main Layout */
.container {
    display: grid;
    grid-template-columns: 2.2fr 1fr;
    gap: 40px;
    padding: 50px 8%;
    max-width: 1400px;
    margin: 0 auto;
    align-items: start;
}

/* Left: Cart Items */
.cart-section h1 { 
    font-size: 2.2rem; 
    margin-bottom: 35px; 
    font-weight: 800;
    letter-spacing: -0.5px;
}

.cart-item {
    display: flex;
    background: var(--white);
    padding: 24px;
    border-radius: var(--radius);
    margin-bottom: 24px;
    transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
    border: 1px solid var(--border);
    align-items: center;
    box-shadow: var(--shadow-sm);
}
.cart-item:hover { 
    box-shadow: var(--shadow-md); 
    transform: translateY(-4px);
    border-color: transparent;
}

.item-img {
    width: 130px;
    height: 130px;
    border-radius: 16px;
    object-fit: cover;
    margin-right: 30px;
    background: var(--light-gray);
}

.item-details { flex-grow: 1; }
.item-details h3 { 
    margin: 0 0 8px 0; 
    font-size: 1.25rem;
    color: var(--dark);
}
.item-details p { 
    color: var(--gray); 
    margin: 0; 
    font-size: 0.9rem; 
    font-weight: 500;
}

.item-price { 
    font-weight: 800; 
    font-size: 1.3rem; 
    margin-left: 25px;
    color: var(--dark);
}

.remove-btn {
    color: var(--gray);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 700;
    margin-top: 12px;
    display: inline-block;
    transition: all 0.3s;
    position: relative;
    padding-left: 0;
}
.remove-btn:hover { 
    color: var(--red); 
    text-decoration: none;
    padding-left: 4px; /* Slide effect */
}

/* Right: Summary */
.summary-section {
    position: sticky;
    top: 110px;
}

.summary-card {
    background: var(--white);
    padding: 35px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    position: relative;
}

/* Gradient line on top of summary */
.summary-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 4px;
    background: linear-gradient(90deg, var(--primary), #a855f7);
}

.summary-card h2 { 
    margin-top: 10px;
    font-size: 1.5rem;
    margin-bottom: 30px;
    font-weight: 800;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;
    color: var(--gray);
    font-size: 0.95rem;
    font-weight: 500;
}

.total-row {
    border-top: 2px solid var(--light-gray);
    padding-top: 25px;
    margin-top: 25px;
    font-size: 1.5rem;
    font-weight: 900;
    color: var(--dark);
}

.checkout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: var(--dark);
    color: white;
    text-align: center;
    padding: 20px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1rem;
    margin-top: 30px;
    transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1);
    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2);
}
.checkout-btn:hover { 
    background: var(--primary); 
    transform: translateY(-3px);
    box-shadow: 0 15px 25px -5px var(--primary-glow);
}

/* Empty State */
.empty-state { 
    text-align: center; 
    padding: 120px 0; 
}
.empty-state h2 { 
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 30px;
}

/* Responsive */
@media (max-width: 968px) {
    .container { 
        grid-template-columns: 1fr; 
        padding: 30px 5%;
    }
    .summary-section {
        position: static;
        margin-top: 20px;
    }
    .item-img { width: 100px; height: 100px; margin-right: 20px; }
}
    </style>
</head>
<body>

<nav class="navbar">
    <div style="font-weight: 800; font-size: 1.4rem;">NEX<span style="color:var(--primary)">GEN</span></div>
    <a href="index.php" style="text-decoration:none; color:var(--primary); font-weight:600;">← Back to Shop</a>
</nav>

<div class="container">
    <div class="cart-section">
        <h1>Your Bag</h1>

        <?php 
        $subtotal = 0;
        if(!empty($_SESSION['cart'])): 
            foreach($_SESSION['cart'] as $item): 
                $subtotal += $item['price'];
        ?>
            <div class="cart-item">
                <img src="<?php echo $item['img']; ?>" class="item-img" alt="product">
                <div class="item-details">
                    <p style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; letter-spacing: 1px; color: var(--primary);">
                        <?php echo $item['category']; ?>
                    </p>
                    <h3><?php echo $item['name']; ?></h3>
                    <p>In Stock • Standard Delivery</p>
                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn">Remove</a>
                </div>
                <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
            </div>
        <?php endforeach; ?>
        
        <?php else: ?>
            <div class="empty-state">
                <h2 style="color:var(--gray)">Your bag is empty</h2>
                <a href="index.php" class="checkout-btn" style="display:inline-block; width:auto; padding: 15px 40px;">Shop Now</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($_SESSION['cart'])): ?>
    <div class="summary-section">
        <div class="summary-card">
            <h2 style="margin-top:0">Summary</h2>
            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-row">
                <span>Estimated Shipping</span>
                <span style="color:#10b981">FREE</span>
            </div>
            <div class="summary-row">
                <span>Tax</span>
                <span>$0.00</span>
            </div>

            <div class="summary-row total-row">
                <span>Total</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>

            <a href="#" class="checkout-btn" onclick="alert('Proceeding to Payment Gateway...')">Checkout Now</a>
            
            <p style="text-align: center; font-size: 0.8rem; color: var(--gray); margin-top: 20px;">
                Secure Checkout Powered by NexGen
            </p>
        </div>
    </div>
    <?php endif; ?>