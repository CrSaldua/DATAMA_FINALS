<div class="login-box">
        <h2 style="margin-top:0;">Library Portal</h2>
        <?php if(!empty($error_msg)): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <label style="display:block; text-align:left; color:#9ca3af; font-size:12px; margin-bottom:5px;">Username (Admin) or Mobile (Member)</label>
            <input type="text" name="login_input" placeholder="Enter ID..." required>
            
            <label style="display:block; text-align:left; color:#9ca3af; font-size:12px; margin-bottom:5px;">Password</label>
            <input type="password" name="password" placeholder="Enter Password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>