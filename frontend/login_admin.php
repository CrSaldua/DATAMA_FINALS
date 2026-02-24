<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Portal | City Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: #1e293b; padding: 40px; border-radius: 12px; border: 1px solid #334155; width: 320px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #020617; border: 1px solid #334155; color: white; border-radius: 6px; box-sizing: border-box; text-align: center; }
        button { width: 100%; padding: 12px; background: #ef4444; border: none; color: white; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #dc2626; }
        .error { color: #f87171; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="margin-top:0; color: #ef4444;">Staff Access</h2>
        <p style="font-size: 12px; color: #94a3b8;">Restricted Area</p>

        <?php if(!empty($error_msg)): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>

        <form method="POST" autocomplete="off">
            <input type="hidden" name="action" value="login_admin">
            
            <input type="text" name="employee_code" placeholder="Employee Code (6-Digits)" maxlength="6" pattern="\d{6}" required title="Please enter your 6-digit ID" autocomplete="off">
            
            <input type="password" name="password" placeholder="Access Key" required autocomplete="new-password">
            
            <button type="submit">Unlock System</button>
        </form>
        
        <br>
        <a href="index.php" style="color: #64748b; font-size: 12px; text-decoration: none;">&larr; Back to Library</a>
    </div>
</body>
</html>