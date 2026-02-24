<div class="header">
    <div class="page-title">
        <h1>📚 Library Catalog</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Member') ?></p>
    </div>
    <form method="POST" style="margin:0;">
        <input type="hidden" name="action" value="logout">
        <button class="btn" style="width:auto; background:#ef4444; color:white;">Logout</button>
    </form>
</div>

<h3 style="margin-top:0; color:#9ca3af; text-transform:uppercase; font-size:12px; letter-spacing:1px;">Available Books</h3>
<div class="grid-4" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:20px; margin-bottom:40px;">
    <?php 
    // Fetch Books with Real-Time Availability Calculation
    $sql = "SELECT b.*, 
            (b.total_copies - (SELECT COUNT(*) FROM loans WHERE book_id = b.book_id AND status = 'borrowed')) as avail 
            FROM books b 
            ORDER BY b.title ASC";
    $books = $pdo->query($sql);
    
    while($b = $books->fetch()): 
        $canBorrow = $b['avail'] > 0;
    ?>
    <div class="stat-card" style="display:block; position: relative;">
        <h3 style="color:#38bdf8; margin:0 0 5px 0; font-size:18px;">
            <?= htmlspecialchars($b['title']) ?>
        </h3>
        
        <p style="font-size:12px; color:#9ca3af; margin:0 0 15px 0;">
            <?= htmlspecialchars($b['category']) ?> <br> 
            <span style="opacity:0.6">ISBN: <?= htmlspecialchars($b['isbn']) ?></span>
        </p>
        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <span class="badge <?= $canBorrow ? 'bg-green' : 'bg-red' ?>">
                <?= $b['avail'] ?> Available
            </span>
        </div>

        <?php if($canBorrow): ?>
        <form method="POST">
            <input type="hidden" name="action" value="borrow_self">
            <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
            <button class="btn btn-primary">Borrow Now</button>
        </form>
        <?php else: ?>
            <button class="btn" disabled style="background:#1f2937; color:#4b5563; cursor:not-allowed; border:1px solid #374151;">
                Out of Stock
            </button>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>

<h3 style="color:#9ca3af; text-transform:uppercase; font-size:12px; letter-spacing:1px;">My Active Loans</h3>
<div class="panel">
    <?php 
    // Fetch only THIS user's loans
    $stmt = $pdo->prepare("SELECT l.*, b.title 
                           FROM loans l 
                           JOIN books b ON l.book_id = b.book_id 
                           WHERE l.member_id = ? AND l.status = 'borrowed' 
                           ORDER BY l.due_date ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $myLoans = $stmt->fetchAll();
    
    if(count($myLoans) > 0): 
    ?>
    <table>
        <tr>
            <th>Book Title</th>
            <th>Borrowed Date</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>
        <?php foreach($myLoans as $my): 
            $isOverdue = strtotime($my['due_date']) < time();
            $statusClass = $isOverdue ? 'bg-red' : 'bg-green';
            $statusText = $isOverdue ? 'OVERDUE' : 'ACTIVE';
        ?>
        <tr>
            <td style="font-weight:600; color:#f3f4f6;">
                <?= htmlspecialchars($my['title']) ?>
            </td>
            <td><?= $my['borrow_date'] ?></td>
            <td style="color: <?= $isOverdue ? '#ef4444' : '#fff' ?>">
                <?= $my['due_date'] ?>
            </td>
            <td>
                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <div style="padding:40px; text-align:center; color:#6b7280;">
            You have no active loans right now.
        </div>
    <?php endif; ?>
</div>