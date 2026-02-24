<?php
// --- PRE-FETCH CART DATA ---
$cart_count = 0;
$cartBooks = [];

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
    $clean_cart = array_values($_SESSION['cart']); 
    $placeholders = implode(',', array_fill(0, count($clean_cart), '?'));
    $stmtCart = $pdo->prepare("SELECT title FROM books WHERE book_id IN ($placeholders)");
    $stmtCart->execute($clean_cart); 
    $cartBooks = $stmtCart->fetchAll(PDO::FETCH_COLUMN);
}

// --- NEW: FETCH CATEGORIES FOR FILTER ---
$stmtCats = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
$allCategories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);

// --- GET CURRENT FILTER ---
$activeCategory = $_GET['category'] ?? 'All';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>City Library | Catalog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --bg: #020617; --card-bg: #111827; --primary: #06b6d4; --text-main: #f3f4f6; }
        body { background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; padding-bottom: 80px; }
        
        /* HEADER & NAVIGATION */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #1f2937; padding-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .search-bar { background: #1e293b; border: 1px solid #334155; padding: 10px 15px; border-radius: 8px; color: white; width: 250px; transition: 0.3s; }
        .search-bar:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2); }
        
        /* --- AMAZING CATEGORY FILTER UI --- */
        .category-scroll {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 5px;
            margin-bottom: 30px;
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
            -webkit-overflow-scrolling: touch;
        }
        .category-scroll::-webkit-scrollbar { height: 4px; }
        .category-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        
        .cat-pill {
            background: #1e293b;
            color: #9ca3af;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cat-pill:hover { background: #334155; color: white; transform: translateY(-2px); }
        .cat-pill.active { 
            background: rgba(6, 182, 212, 0.15); 
            color: var(--primary); 
            border-color: var(--primary); 
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2); 
        }

        /* CARD SYSTEM */
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        .flip-card { background-color: transparent; width: 100%; height: 400px; perspective: 1000px; transition: transform 0.2s; }
        .flip-card:hover { transform: translateY(-5px); }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; border-radius: 12px; }
        .flip-card.flipped .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; -webkit-backface-visibility: hidden; backface-visibility: hidden; border-radius: 12px; overflow: hidden; border: 1px solid #1f2937; }
        .flip-card-front { background: var(--card-bg); display: flex; flex-direction: column; padding-bottom: 15px; }
        .flip-card-back { background: #1f2937; transform: rotateY(180deg); padding: 25px; display: flex; flex-direction: column; box-sizing: border-box; }
        .book-cover { width: 100%; height: 210px; object-fit: cover; background: #0f172a; }
        
        .desc-text { font-size: 14px; color: #d1d5db; line-height: 1.6; margin: 0; overflow-y: auto; text-align: left; padding-right: 5px; }
        .desc-text::-webkit-scrollbar { width: 4px; }
        .desc-text::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }

        .info-hint { position: absolute; top: 180px; right: 10px; color: white; background: var(--primary); padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 11; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.3); transition: 0.2s; }
        .info-hint:hover { transform: scale(1.1); }

        /* BUTTONS */
        .btn { padding: 10px 15px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; color: white; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-primary { background: var(--primary); }
        .btn-danger { background: #ef4444; }
        .btn-dark { background: #1f2937; border: 1px solid #374151; color: #e5e7eb; }
        .btn-cart { background: #10b981; }
        .btn:disabled { background: #374151; cursor: not-allowed; opacity: 0.6; color: #9ca3af; }

        /* --- UPDATED BACK BUTTON STYLE --- */
        .btn-back {
            background: rgba(6, 182, 212, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-back:hover {
            background: var(--primary);
            color: white;
        }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; position: absolute; top: 10px; right: 10px; z-index: 10; }
        .bg-green { background: #065f46; color: #34d399; }
        .bg-red { background: #7f1d1d; color: #f87171; }

        /* MODALS */
        .cart-float { position: fixed; bottom: 30px; right: 30px; background: #06b6d4; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5); cursor: pointer; z-index: 9999; transition: transform 0.2s; }
        .cart-float:hover { transform: scale(1.1); }
        .cart-count-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 12px; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid var(--bg); }
        
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); display: flex; justify-content: center; align-items: center; z-index: 10000; backdrop-filter: blur(5px); }
        .receipt-card { background: white; color: black; padding: 30px; border-radius: 12px; width: 450px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .loan-panel { background: #111827; padding: 25px; border-radius: 12px; border: 1px solid #1f2937; width: 800px; max-width: 90%; max-height: 80vh; overflow-y: auto; }
        .term-check { display: flex; align-items: center; gap: 10px; background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #e5e7eb; text-align: left; }
        .term-check input { width: 18px; height: 18px; cursor: pointer; }
        .term-check label { font-size: 13px; color: #374151; font-weight: 600; cursor: pointer; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid #1f2937; color: #d1d5db; font-size: 14px; }
    </style>
</head>
<body>

    <?php if(!empty($error_msg)): ?>
        <div style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#7f1d1d; color:#fca5a5; padding:15px 25px; border-radius:8px; z-index:99999; border:1px solid #f87171; box-shadow: 0 10px 25px rgba(0,0,0,0.5); font-weight:bold; width: 80%; max-width: 600px; text-align: center; animation: slideDown 0.3s ease-out;">
            ⚠️ <?= $error_msg ?>
            <span style="float:right; cursor:pointer;" onclick="this.parentElement.style.display='none'">✖</span>
        </div>
    <?php endif; ?>

    <div class="header">
        <div>
            <h1 style="margin:0; color:var(--primary); font-size: 24px;">📚 City Library</h1>
            <p style="margin:5px 0 0; color:#9ca3af; font-size: 14px;">Welcome, <?= htmlspecialchars($_SESSION['member_name'] ?? 'Guest') ?></p>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <form method="GET" style="margin:0; display:flex;">
                <?php if($activeCategory !== 'All'): ?><input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>"><?php endif; ?>
                <input type="text" name="search" class="search-bar" placeholder="Search books..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </form>
            
            <button class="btn btn-dark" onclick="document.getElementById('loan-modal').style.display='flex'"><i data-lucide="history" size="18"></i> My Loans</button>
            <?php if($cart_count > 0): ?>
            <button class="btn btn-cart" onclick="document.getElementById('checkout-modal').style.display='flex'"><i data-lucide="shopping-bag" size="18"></i> Cart (<?= $cart_count ?>)</button>
            <?php endif; ?>
            <form method="POST" style="margin:0;"><input type="hidden" name="action" value="logout"><button class="btn btn-danger">Logout</button></form>
        </div>
    </div>

    <div class="category-scroll">
        <a href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="cat-pill <?= $activeCategory === 'All' ? 'active' : '' ?>">
            <i data-lucide="layers" size="14"></i> All Books
        </a>

        <?php foreach($allCategories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?>&search=<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
               class="cat-pill <?= $activeCategory === $cat ? 'active' : '' ?>">
               <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="book-grid">
        <?php 
        $searchQuery = "%" . ($_GET['search'] ?? '') . "%";
        $params = [$searchQuery, $searchQuery];
        
        $sql = "SELECT b.*, b.available_copies as avail 
                FROM books b 
                WHERE (b.title LIKE ? OR b.category LIKE ?) ";

        if ($activeCategory !== 'All') {
            $sql .= " AND b.category = ? ";
            $params[] = $activeCategory;
        }

        $sql .= " ORDER BY b.title ASC";
        
        $stmt = $pdo->prepare($sql); 
        $stmt->execute($params);
        $books = $stmt->fetchAll();
        
        if(count($books) == 0): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: #6b7280; background: #111827; border-radius: 12px; border: 1px dashed #374151;">
                <i data-lucide="ghost" size="48" style="margin-bottom:15px; opacity:0.5;"></i>
                <p>No books found for this category or search.</p>
                <a href="index.php" class="btn btn-primary" style="display:inline-flex; margin-top:10px;">View All Books</a>
            </div>
        <?php else: 
            foreach($books as $b): 
            $canBorrow = $b['avail'] > 0; 
            $inCart = in_array($b['book_id'], $_SESSION['cart']);
        ?>
        <div class="flip-card" id="book-card-<?= $b['book_id'] ?>">
            <div class="flip-card-inner">
                
                <div class="flip-card-front">
                    <span class="badge <?= $canBorrow ? 'bg-green' : 'bg-red' ?>"><?= $b['avail'] ?> Left</span>
                    <img src="<?= htmlspecialchars($b['cover_image']) ?>" class="book-cover" alt="Cover">
                    
                    <div class="info-hint" onclick="document.getElementById('book-card-<?= $b['book_id'] ?>').classList.add('flipped')">
                        <i data-lucide="info" size="16"></i>
                    </div>

                    <div style="padding:15px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <strong style="color:white; font-size:15px; display:block;"><?= htmlspecialchars($b['title']) ?></strong>
                            <span style="color:#9ca3af; font-size:12px;"><?= htmlspecialchars($b['category']) ?></span>
                        </div>
                        <div style="margin-top: 10px;">
                            <?php if($inCart): ?>
                                <form method="POST"><input type="hidden" name="action" value="remove_from_cart"><input type="hidden" name="book_id" value="<?= $b['book_id'] ?>"><button class="btn btn-danger" style="width:100%"><i data-lucide="trash-2" size="16"></i> Remove</button></form>
                            <?php elseif($canBorrow): ?>
                                <form method="POST"><input type="hidden" name="action" value="add_to_cart"><input type="hidden" name="book_id" value="<?= $b['book_id'] ?>"><button class="btn btn-primary" style="width:100%"><i data-lucide="shopping-cart" size="16"></i> Add to Cart</button></form>
                            <?php else: ?>
                                <button class="btn btn-dark" style="width:100%; cursor:not-allowed;">Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="flip-card-back">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; width: 100%; border-bottom: 1px solid #374151; padding-bottom: 10px;">
                        <h3 style="font-size: 16px; margin: 0; color: #06b6d4;">About this Book</h3>
                        
                        <button class="btn-back" onclick="document.getElementById('book-card-<?= $b['book_id'] ?>').classList.remove('flipped')">
                            Back
                        </button>
                    </div>
                    <p class="desc-text"><?= htmlspecialchars($b['description'] ?? 'No description available.') ?></p>
                </div>

            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <div id="loan-modal" class="modal-overlay" style="display:none;" onclick="this.style.display='none'">
        <div class="loan-panel" onclick="event.stopPropagation()">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;"><h3 style="margin:0; color:white;">My Loan History</h3><button class="btn btn-dark" onclick="document.getElementById('loan-modal').style.display='none'">Close</button></div>
            <?php 
            $stmt = $pdo->prepare("SELECT l.*, b.title FROM loans l JOIN books b ON l.book_id = b.book_id WHERE l.member_id = ? AND l.status = 'borrowed' ORDER BY l.due_date ASC");
            $stmt->execute([$_SESSION['member_id']]); $myLoans = $stmt->fetchAll();
            if(count($myLoans) > 0): ?>
            <table><tr><th>Book</th><th>Borrowed</th><th>Due Date</th></tr><?php foreach($myLoans as $my): $isOverdue = strtotime($my['due_date']) < time(); ?><tr><td style="font-weight:600; color:#f3f4f6;"><?= htmlspecialchars($my['title']) ?></td><td><?= $my['borrow_date'] ?></td><td style="color: <?= $isOverdue ? '#ef4444' : '#fff' ?>"><?= $my['due_date'] ?></td></tr><?php endforeach; ?></table>
            <?php else: ?><div style="padding:40px; text-align:center; color:#6b7280; border: 1px dashed #374151; border-radius: 8px;">No active loans.</div><?php endif; ?>
        </div>
    </div>

    <?php if($cart_count > 0): ?>
    <div class="cart-float" onclick="document.getElementById('checkout-modal').style.display='flex'"><div class="cart-count-badge"><?= $cart_count ?></div><i data-lucide="shopping-bag" color="white"></i></div>
    <div id="checkout-modal" class="modal-overlay" style="display:none;" onclick="this.style.display='none'">
        <div class="receipt-card" onclick="event.stopPropagation()">
            <h2 style="margin-top:0; color:#ef4444;">⚠️ Confirm Borrowing</h2>
            <p style="color:#6b7280; font-size:14px;">You are about to borrow <strong><?= $cart_count ?> book(s)</strong>.</p>
            <div style="text-align:left; margin: 15px 0; background:#f9fafb; padding:15px; border-radius:8px; max-height:150px; overflow-y:auto; border: 1px solid #e5e7eb;">
                <?php foreach($cartBooks as $title): ?>
                    <div style="padding: 8px 0; border-bottom: 1px solid #eee; font-size:13px; font-weight:600;">📚 <?= htmlspecialchars($title) ?></div>
                <?php endforeach; ?>
            </div>
            <div class="term-check">
                <input type="checkbox" id="agree_term" onchange="document.getElementById('confirm_btn').disabled = !this.checked">
                <label for="agree_term">I agree to return these within 7 days. Late fees of ₱20/day apply.</label>
            </div>
            <div style="display:flex; gap:10px;">
                <button class="btn btn-dark" onclick="document.getElementById('checkout-modal').style.display='none'" style="flex:1;">Cancel</button>
                <form method="POST" style="flex:1; margin:0;"><input type="hidden" name="action" value="checkout"><button id="confirm_btn" class="btn btn-primary" style="width:100%;" disabled>Confirm & Borrow</button></form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>lucide.createIcons();</script>
</body>
</html>