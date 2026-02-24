<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>City Library | Admin OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --bg: #020617; --sidebar: #0f172a; --card-bg: #111827; --border: #1f2937; --primary: #06b6d4; --primary-hover: #0891b2; --text-main: #f3f4f6; --text-muted: #9ca3af; --danger: #ef4444; --warning: #f59e0b; --success: #10b981; }
        * { box-sizing: border-box; outline: none; }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: var(--sidebar); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 20px; }
        .logo { font-size: 20px; font-weight: 700; color: white; display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .logo span { color: var(--primary); }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px; color: var(--text-muted); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; transition: 0.2s; }
        .nav-item:hover, .nav-item.active { background: rgba(6, 182, 212, 0.1); color: var(--primary); }
        .nav-label { font-size: 11px; font-weight: 700; color: #4b5563; margin-top: 20px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .main { flex: 1; padding: 30px; overflow-y: auto; position: relative; }
        
        /* NOTIFICATION ANIMATION */
        @keyframes slideDown { from { top: -50px; opacity: 0; } to { top: 20px; opacity: 1; } }

        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 24px; font-weight: 600; margin: 0; }
        .page-title p { color: var(--text-muted); font-size: 14px; margin: 5px 0 0 0; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border); padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; }
        .icon-box { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-info h3 { margin: 0; font-size: 14px; color: var(--text-muted); font-weight: 500; }
        .stat-info h2 { margin: 5px 0 0 0; font-size: 24px; font-weight: 700; color: white; }
        .panel { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
        .panel-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .panel-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
        .search-input { background: #020617; border: 1px solid var(--border); color: white; padding: 8px 12px; border-radius: 6px; font-size: 13px; width: 250px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px 20px; color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 15px 20px; font-size: 14px; border-bottom: 1px solid var(--border); color: #e5e7eb; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }
        .split-view { display: grid; grid-template-columns: 350px 1fr; gap: 24px; }
        .form-card { background: var(--card-bg); padding: 24px; border-radius: 12px; border: 1px solid var(--border); height: fit-content; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; }
        input, select { width: 100%; background: #020617; border: 1px solid var(--border); padding: 10px; border-radius: 6px; color: white; font-size: 14px; }
        input:focus { border-color: var(--primary); }
        .btn { width: 100%; padding: 10px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .bg-green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .bg-red { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .bg-blue { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }
        .bg-yellow { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .activity-item { display: flex; align-items: center; gap: 15px; padding: 15px; border-bottom: 1px solid var(--border); }
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); }
        .activity-text { flex: 1; }
        .activity-text strong { color: white; font-weight: 500; }
        .activity-text span { color: var(--text-muted); font-size: 13px; }
        .activity-time { font-size: 12px; color: #6b7280; }
        .loan-list-item { padding: 12px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 8px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        .loan-list-item:hover, .loan-list-item.selected { border-color: var(--primary); background: rgba(6, 182, 212, 0.05); }
        .fine-box { background: #020617; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .fine-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; color: var(--text-muted); }
        .total-fine { font-size: 24px; color: var(--warning); font-weight: 700; text-align: right; margin-top: 10px; }
        
        /* NEW: Input Group for @member */
        .input-group { display: flex; background: #020617; border: 1px solid var(--border); border-radius: 6px; overflow: hidden; transition: 0.2s; }
        .input-group:focus-within { border-color: var(--primary); }
        .input-group input { border: none; border-radius: 0; flex: 1; outline: none; margin: 0; padding-left: 10px; }
        .input-addon { background: #1e293b; padding: 10px 15px; color: var(--primary); font-weight: 600; border-left: 1px solid var(--border); user-select: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo"><i data-lucide="library"></i> City <span>Library</span></div>
    
    <div class="nav-label">Admin</div>
    <a href="?page=dashboard" class="nav-item <?= $page=='dashboard'?'active':'' ?>"><i data-lucide="layout-grid" size="18"></i> Dashboard</a>
    <a href="?page=register" class="nav-item <?= $page=='register'?'active':'' ?>"><i data-lucide="user-plus" size="18"></i> Register Users</a>
    
    <div class="nav-label">Management</div>
    <a href="?page=inventory" class="nav-item <?= $page=='inventory'?'active':'' ?>"><i data-lucide="box" size="18"></i> Inventory Control</a>
    <a href="?page=return" class="nav-item <?= $page=='return'?'active':'' ?>"><i data-lucide="book-up" size="18"></i> Return a Book</a>
    
    <div class="nav-label">Reports</div>
    <a href="?page=fines" class="nav-item <?= $page=='fines'?'active':'' ?>"><i data-lucide="banknote" size="18"></i> Member Fines</a>
    <a href="?page=history" class="nav-item <?= $page=='history'?'active':'' ?>"><i data-lucide="history" size="18"></i> Loan History</a>
    <a href="?page=logs_loan" class="nav-item <?= $page=='logs_loan'?'active':'' ?>"><i data-lucide="file-text" size="18"></i> Loan Logs</a>
    <a href="?page=logs_inv" class="nav-item <?= $page=='logs_inv'?'active':'' ?>"><i data-lucide="clipboard-list" size="18"></i> Inventory Logs</a>

    <div style="margin-top:auto; padding-top:20px; border-top:1px solid var(--border);">
         <form method="POST">
            <input type="hidden" name="action" value="logout">
            <button class="nav-item" style="background:none; border:none; width:100%; cursor:pointer; color:#ef4444;">
                <i data-lucide="log-out" size="18"></i> Logout
            </button>
        </form>
    </div>
</div>

<div class="main">
    <?php if(!empty($error_msg)): ?>
        <div style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#7f1d1d; color:#fca5a5; padding:15px 25px; border-radius:8px; z-index:99999; border:1px solid #f87171; box-shadow: 0 10px 25px rgba(0,0,0,0.5); font-weight:bold; width: auto; min-width: 300px; text-align: center; animation: slideDown 0.3s ease-out;">
            ⚠️ <?= $error_msg ?>
            <span style="float:right; cursor:pointer; margin-left:15px;" onclick="this.parentElement.style.display='none'">✖</span>
        </div>
    <?php endif; ?>

    <?php if(!empty($success_msg)): ?>
        <div style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#065f46; color:#34d399; padding:15px 25px; border-radius:8px; z-index:99999; border:1px solid #10b981; box-shadow: 0 10px 25px rgba(0,0,0,0.5); font-weight:bold; width: auto; min-width: 300px; text-align: center; animation: slideDown 0.3s ease-out;">
            ✅ <?= $success_msg ?>
            <span style="float:right; cursor:pointer; margin-left:15px;" onclick="this.parentElement.style.display='none'">✖</span>
        </div>
    <?php endif; ?>

    <?php if ($page == 'dashboard'): ?>
    <div class="header">
        <div class="page-title"><h1>Dashboard Overview</h1><p>Welcome back, Admin.</p></div>
    </div>
    <div class="grid-4">
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(56,189,248,0.1); color: #38bdf8;"><i data-lucide="users"></i></div>
            <div class="stat-info"><h3>Total Members</h3><h2><?= $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn() ?></h2></div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(167,139,250,0.1); color: #a78bfa;"><i data-lucide="book-open"></i></div>
            <div class="stat-info"><h3>Total Books</h3><h2><?= $pdo->query("SELECT SUM(total_copies) FROM books")->fetchColumn() ?></h2></div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(16,185,129,0.1); color: #10b981;"><i data-lucide="check-circle"></i></div>
            <div class="stat-info"><h3>Active Loans</h3><h2><?= $pdo->query("SELECT COUNT(*) FROM loans WHERE status='borrowed'")->fetchColumn() ?></h2></div>
        </div>
        <div class="stat-card">
            <div class="icon-box" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i data-lucide="alert-circle"></i></div>
            <div class="stat-info"><h3>Overdue Books</h3><h2><?= $pdo->query("SELECT COUNT(*) FROM loans WHERE status='borrowed' AND due_date < CURDATE()")->fetchColumn() ?></h2></div>
        </div>
    </div>
    <div class="split-view" style="grid-template-columns: 2fr 1fr;">
        <div class="panel">
            <div class="panel-header"><h3>Recent Activity (MongoDB)</h3></div>
            <?php 
                $recentLogs = $db_mongo->loan_histories->find([], ['limit' => 5, 'sort' => ['timestamp' => -1]]);
                foreach($recentLogs as $log): 
                    $icon = strpos(($log['action'] ?? ''), 'BORROW') !== false ? 'book-down' : 'book-up';
                    $color = strpos(($log['action'] ?? ''), 'BORROW') !== false ? '#38bdf8' : '#10b981';
            ?>
            <div class="activity-item">
                <div class="activity-icon" style="color: <?= $color ?>"><i data-lucide="<?= $icon ?>" size="18"></i></div>
                <div class="activity-text"><strong>Loan #<?= $log['loan_id'] ?? '?' ?></strong> <span><?= $log['details'] ?? $log['action'] ?></span></div>
                <div class="activity-time"><?= date('h:i A', strtotime($log['timestamp'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="panel">
            <div class="panel-header"><h3>Quick Actions</h3></div>
            <div style="padding: 20px;">
                <button class="btn" style="background: #1f2937; margin-bottom: 10px;" onclick="location.href='?page=register'"><i data-lucide="user-plus"></i> Register Member</button>
                <button class="btn" style="background: #1f2937; margin-bottom: 10px;" onclick="location.href='?page=return'"><i data-lucide="rotate-ccw"></i> Return Book</button>
                <button class="btn" style="background: #1f2937;" onclick="location.href='?page=fines'"><i data-lucide="alert-triangle"></i> Check Fines</button>
            </div>
        </div>
    </div>

    <?php elseif ($page == 'inventory'): ?>
    <div class="header"><div class="page-title"><h1>Inventory Control</h1><p>Manage your library collection.</p></div></div>
    <div class="split-view">
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="form-card">
                <h3>Manage Stock</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="inventory_control">
                    <input type="hidden" name="inv_mode" value="update">
                    <div class="form-group">
                        <label>Select Book</label>
                        <select name="book_id" required>
                            <option value="" disabled selected>Select a book...</option>
                            <?php $bks = $pdo->query("SELECT book_id, title FROM books ORDER BY title"); while($b = $bks->fetch()) echo "<option value='{$b['book_id']}'>{$b['title']}</option>"; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Action</label>
                            <select name="update_type" required>
                                <option value="add">➕ Add Stock</option>
                                <option value="subtract">➖ Remove Stock</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Quantity</label>
                            <input type="number" name="qty" value="1" min="1" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </form>
            </div>
            <div class="form-card">
                <h3>Register New Book</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="inventory_control">
                    <input type="hidden" name="inv_mode" value="new">
                    
                    <div class="form-group"><label>Book Title</label><input type="text" name="title" required></div>
                    <div class="form-group"><label>Author</label><input type="text" name="author" required></div>
                    <div class="form-group"><label>Category</label><input type="text" name="category" required></div>
                    <div class="form-group"><label>Cover Image URL (Optional)</label><input type="text" name="cover_image" placeholder="https://example.com/image.jpg"></div>

                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1"><label>Qty</label><input type="number" name="qty" value="1" min="1" required></div>
                        <div style="flex:2"><label>ISBN</label><input type="text" name="isbn" required></div>
                    </div>
                    <button type="submit" class="btn" style="background:#0f172a; color:white; border:1px solid #1e293b;">Save New Book</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h3>Current Inventory</h3><form><input type="hidden" name="page" value="inventory"><input type="text" name="search" class="search-input" placeholder="Search title..." value="<?= htmlspecialchars($search) ?>"></form></div>
            <table>
                <tr><th>Book Title</th><th>Author</th><th>Stock (Total)</th><th>Real-Time Avail</th></tr>
                <?php 
                $sql = "SELECT b.book_id, b.title, b.category, b.total_copies, 
                        (b.total_copies - (SELECT COUNT(*) FROM loans WHERE book_id = b.book_id AND status = 'borrowed')) as real_availability,
                        CONCAT(a.author_firstname, ' ', a.author_lastname) as author 
                        FROM books b JOIN authors a ON b.author_id = a.author_id 
                        WHERE b.title LIKE ? ORDER BY b.book_id DESC LIMIT 10";
                $stmt = $pdo->prepare($sql); $stmt->execute(["%$search%"]);
                while($b = $stmt->fetch()): 
                    $avail = $b['real_availability'];
                    $availClass = ($avail > 0) ? 'bg-green' : 'bg-red';
                    if ($avail < 0) $avail = 0; 
                ?>
                <tr>
                    <td><b><?= htmlspecialchars($b['title']) ?></b><br><span style="font-size:11px; color:#64748b;"><?= htmlspecialchars($b['category']) ?></span></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td style="font-weight:bold; color:#e5e7eb;"><?= $b['total_copies'] ?></td>
                    <td><span class="badge <?= $availClass ?>"><?= $avail ?> Available</span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <?php elseif ($page == 'register'): ?>
    <div class="header"><div class="page-title"><h1>User Management</h1><p>Register new members or administrators.</p></div></div>
    
    <div class="split-view">
        <div style="display:flex; flex-direction:column; gap: 20px;">
            
            <div class="form-card">
                <h3><i data-lucide="user-plus" size="18"></i> Register Member</h3>
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="action" value="register_member">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <input type="text" name="username" placeholder="johndoe" 
                                   pattern="^(?=.*[a-zA-Z])[a-zA-Z0-9_]{4,20}$" 
                                   title="Must be 4-20 chars, contain letters, and have no spaces." required autocomplete="off">
                            <div class="input-addon">@member</div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1"><label>First Name</label><input type="text" name="fname" required autocomplete="off"></div>
                        <div style="flex:1"><label>Middle Name</label><input type="text" name="mname" placeholder="(Optional)" autocomplete="off"></div>
                        <div style="flex:1"><label>Last Name</label><input type="text" name="lname" required autocomplete="off"></div>
                    </div>

                    <div class="form-group"><label>Mobile</label><input type="text" name="phone" placeholder="0917..." required autocomplete="off"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="user@example.com" required autocomplete="off"></div>
                    <div class="form-group"><label>Password</label><input type="text" name="password" value="12345" required autocomplete="new-password"></div>
                    
                    <button type="submit" class="btn btn-primary">Register Member</button>
                </form>
            </div>

            <div class="form-card" style="border-color: #7f1d1d;">
                <h3 style="color: #f87171;"><i data-lucide="shield-alert" size="18"></i> Create New Admin</h3>
                <form method="POST" autocomplete="off">
                    <input type="hidden" name="action" value="register_admin">
                    
                    <div class="form-group">
                        <label>Employee Code (6-Digits)</label>
                        <input type="text" name="employee_code" placeholder="e.g. 100200" maxlength="6" pattern="\d{6}" required autocomplete="off">
                    </div>
                    
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1"><label>First Name</label><input type="text" name="fname" required autocomplete="off"></div>
                        <div style="flex:1"><label>Middle Name</label><input type="text" name="mname" placeholder="(Optional)" autocomplete="off"></div>
                        <div style="flex:1"><label>Last Name</label><input type="text" name="lname" required autocomplete="off"></div>
                    </div>

                    <div class="form-group"><label>Access Password</label><input type="password" name="password" required autocomplete="new-password"></div>
                    
                    <button type="submit" class="btn" style="background: #991b1b; color: white;">Create Admin</button>
                </form>
            </div>
            
        </div>
        
        <div class="panel">
            <div class="panel-header">
                <h3>Registered Users</h3>
                <form><input type="hidden" name="page" value="register"><input type="text" name="search" class="search-input" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"></form>
            </div>
            <table>
                <tr><th>User</th><th>Role</th><th>Name</th><th>Contact</th></tr>
                <?php 
                $admins = $pdo->query("SELECT * FROM admins")->fetchAll();
                foreach($admins as $a): ?>
                <tr style="background: rgba(239, 68, 68, 0.05);">
                    <td><span style="color:#f87171; font-weight:700;">ID: <?= htmlspecialchars($a['employee_code']) ?></span></td>
                    <td><span class="badge bg-red">ADMIN</span></td>
                    <td><?= htmlspecialchars($a['firstname'] . ' ' . $a['lastname']) ?></td>
                    <td><span style="font-size:11px; color:#6b7280;">STAFF</span></td>
                </tr>
                <?php endforeach; ?>

                <?php 
                $sql = "SELECT * FROM members WHERE members_firstname LIKE ? OR members_lastname LIKE ? ORDER BY member_id DESC LIMIT 10";
                $stmt = $pdo->prepare($sql); 
                $stmt->execute(["%$search%", "%$search%"]);
                while($m = $stmt->fetch()): ?>
                <tr>
                    <td><span style="color:white; font-weight:600;">@<?= htmlspecialchars($m['username']) ?></span></td>
                    <td><span class="badge bg-blue">MEMBER</span></td>
                    <td><?= htmlspecialchars($m['members_firstname'] . ' ' . $m['members_lastname']) ?></td>
                    <td><?= htmlspecialchars($m['mobile_number']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <?php elseif ($page == 'return'): 
        $selected_loan = null;
        if (isset($_GET['loan_id'])) {
            $stmtL = $pdo->prepare("SELECT l.*, b.title, m.members_firstname, m.members_lastname, DATEDIFF(CURDATE(), l.due_date) as days_late FROM loans l JOIN books b ON l.book_id = b.book_id JOIN members m ON l.member_id = m.member_id WHERE l.loan_id = ?");
            $stmtL->execute([$_GET['loan_id']]);
            $selected_loan = $stmtL->fetch();
        }
    ?>
    <div class="header"><div class="page-title"><h1>Process Return</h1><p>Select a loan to return.</p></div></div>
    <div class="split-view">
        <div class="panel">
            <div class="panel-header"><h3>Active Loans</h3><form><input type="hidden" name="page" value="return"><input type="text" name="search" class="search-input" placeholder="Search member..." value="<?= htmlspecialchars($search) ?>"></form></div>
            <div style="padding: 15px;">
                <?php 
                $sql = "SELECT l.loan_id, b.title, m.members_firstname, m.members_lastname, l.due_date FROM loans l JOIN books b ON l.book_id = b.book_id JOIN members m ON l.member_id = m.member_id WHERE l.status = 'borrowed' AND (m.members_firstname LIKE ? OR m.members_lastname LIKE ?) ORDER BY l.loan_id DESC";
                $stmt = $pdo->prepare($sql); $stmt->execute(["%$search%", "%$search%"]);
                while($l = $stmt->fetch()): 
                    $isOverdue = strtotime($l['due_date']) < time();
                    $badge = $isOverdue ? '<span class="badge bg-red">Overdue</span>' : '<span class="badge bg-green">Active</span>';
                    $isSelected = ($selected_loan && $selected_loan['loan_id'] == $l['loan_id']) ? 'selected' : '';
                ?>
                <div class="loan-list-item <?= $isSelected ?>" onclick="location.href='?page=return&loan_id=<?= $l['loan_id'] ?>'">
                    <div><div style="font-weight: 600;"><?= htmlspecialchars($l['members_firstname'] . ' ' . $l['members_lastname']) ?></div><div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($l['title']) ?></div></div><?= $badge ?>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="form-card">
            <?php if ($selected_loan): 
                $days_late = max(0, $selected_loan['days_late']);
                $fine = $days_late * 20.00; 
            ?>
            <h3>Fine Calculation</h3>
            <div style="margin-top: 20px;">
                <h2><?= htmlspecialchars($selected_loan['members_firstname'] . ' ' . $selected_loan['members_lastname']) ?></h2>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <div style="background: #1f2937; padding: 15px; border-radius: 8px; flex: 1;"><label>Overdue Days</label><div style="font-size: 20px; font-weight: 700; color: #f87171;"><?= $days_late ?></div></div>
                    <div style="background: #1f2937; padding: 15px; border-radius: 8px; flex: 1;"><label>Book Title</label><div style="font-size: 14px;"><?= htmlspecialchars($selected_loan['title']) ?></div></div>
                </div>
                <div class="fine-box">
                    <div class="fine-row"><span>Fine Rate</span> <span>₱20.00 / day</span></div>
                    <div class="fine-row"><span>Days Late</span> <span><?= $days_late ?></span></div>
                    <div style="border-top: 1px solid var(--border); margin: 10px 0;"></div>
                    <div class="fine-row"><span>Total Fine</span> <span class="total-fine">₱<?= number_format($fine, 2) ?></span></div>
                </div>
                
                <button type="button" class="btn btn-primary" onclick="confirmPayment('<?= $fine ?>')">
                    Process Return & Payment
                </button>

                <form id="returnForm" method="POST" style="display:none;">
                    <input type="hidden" name="action" value="return">
                    <input type="hidden" name="loan_id" value="<?= $selected_loan['loan_id'] ?>">
                    <input type="hidden" name="fine_amount" value="<?= $fine ?>">
                </form>

                <script>
                    function confirmPayment(amount) {
                        let text = "No Fine";
                        if(amount > 0) {
                            text = "Collect Payment: ₱" + amount;
                        }
                        if(confirm("STEP 1: Verify Book Condition.\nSTEP 2: " + text + "\n\nConfirm return transaction?")) {
                            document.getElementById('returnForm').submit();
                        }
                    }
                </script>
            </div>
            <?php else: ?><div style="display: flex; height: 100%; align-items: center; justify-content: center; color: var(--text-muted); text-align: center;"><div><i data-lucide="arrow-left" size="32"></i><br><br>Select a loan to calculate fines.</div></div><?php endif; ?>
        </div>
    </div>

    <?php elseif ($page == 'fines'): ?>
    <div class="header">
        <div class="page-title"><h1>Member Fines</h1><p>Track outstanding penalties.</p></div>
        
        <div style="display:flex; gap:10px;">
            <form method="POST" onsubmit="return confirm('Send email alerts to ALL overdue members?');">
                <input type="hidden" name="action" value="notify_overdue">
                <button class="btn" style="background: #e11d48; width: auto;"><i data-lucide="mail"></i> Email Overdue Alerts</button>
            </form>
            <form><input type="hidden" name="page" value="fines"><input type="text" name="search" class="search-input" placeholder="Search member..." value="<?= htmlspecialchars($search) ?>"></form>
        </div>
    </div>
    
    <div class="panel">
        <table>
            <tr><th>Member</th><th>Overdue Items</th><th>Total Due</th></tr>
            <?php 
            $sql = "SELECT m.member_id, m.members_firstname, m.members_lastname, COUNT(l.loan_id) as items, SUM(DATEDIFF(CURDATE(), l.due_date) * 20) as penalty FROM loans l JOIN members m ON l.member_id = m.member_id WHERE l.status='borrowed' AND l.due_date < CURDATE() AND (m.members_firstname LIKE ? OR m.members_lastname LIKE ?) GROUP BY m.member_id";
            $stmt = $pdo->prepare($sql); $stmt->execute(["%$search%", "%$search%"]);
            while($f = $stmt->fetch()): ?>
            <tr><td><div style="font-weight: 600;"><?= htmlspecialchars($f['members_firstname'] . ' ' . $f['members_lastname']) ?></div><div style="font-size: 12px; color: #f87171;"><?= $f['items'] ?> Overdue Items</div></td><td><?= $f['items'] ?></td><td><span style="font-size: 16px; font-weight: 700; color: #f87171;">₱<?= number_format($f['penalty'], 2) ?></span></td></tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php elseif ($page == 'history'): ?>
    <div class="header"><div class="page-title"><h1>Loan History</h1><p>Record of borrowing activities.</p></div><form><input type="hidden" name="page" value="history"><input type="text" name="search" class="search-input" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>"></form></div>
    <div class="panel">
        <table>
            <tr><th>ID</th><th>Borrower</th><th>Book Title</th><th>Date</th><th>Status</th></tr>
            <?php 
            $sql = "SELECT l.*, m.members_firstname, m.members_lastname, b.title FROM loans l JOIN members m ON l.member_id = m.member_id JOIN books b ON l.book_id = b.book_id WHERE m.members_lastname LIKE ? OR b.title LIKE ? ORDER BY l.loan_id DESC LIMIT 20";
            $stmt = $pdo->prepare($sql); $stmt->execute(["%$search%", "%$search%"]);
            while($r = $stmt->fetch()): $statusClass = $r['status'] == 'borrowed' ? 'bg-yellow' : 'bg-green'; ?>
            <tr><td>#<?= $r['loan_id'] ?></td><td><?= htmlspecialchars($r['members_firstname'] . ' ' . $r['members_lastname']) ?></td><td><?= htmlspecialchars($r['title']) ?></td><td><?= $r['borrow_date'] ?></td><td><span class="badge <?= $statusClass ?>"><?= strtoupper($r['status']) ?></span></td></tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <?php elseif ($page == 'logs_loan' || $page == 'logs_inv'): 
        $collection = ($page == 'logs_loan') ? $db_mongo->loan_histories : $db_mongo->book_logs;
        $title = ($page == 'logs_loan') ? "Loan Logs" : "Inventory Logs";
    ?>
    <div class="header"><div class="page-title"><h1><?= $title ?></h1><p>Technical system logs.</p></div></div>
    <div class="panel">
        <table>
            <tr><th>Action</th><th>Details</th><th>Timestamp</th></tr>
            <?php 
            $logs = $collection->find([], ['limit' => 20, 'sort' => ['timestamp' => -1]]);
            foreach($logs as $l): ?>
            <tr>
                <td><span class="badge bg-blue"><?= $l['action'] ?? 'LOG' ?></span></td>
                <td><?= $l['details'] ?? json_encode($l) ?></td>
                <td><?= $l['timestamp'] ?? '' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

</div>
<script>lucide.createIcons();</script>
</body>
</html>