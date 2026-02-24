<!DOCTYPE html>
<html lang="en">
<head>
    <title>Library Portal | Member Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #020617; color: white; font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        
        .card-container { background: #111827; padding: 40px; border-radius: 12px; border: 1px solid #1f2937; width: 350px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); position: relative; }
        
        h2 { margin-top: 0; color: white; font-size: 22px; margin-bottom: 20px; }
        
        input { width: 100%; padding: 12px; margin: 8px 0; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 6px; box-sizing: border-box; text-align: center; font-size: 14px; }
        input:focus { border-color: #06b6d4; outline: none; }
        
        button { width: 100%; padding: 12px; background: #06b6d4; border: none; color: white; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 15px; transition: 0.2s; }
        button:hover { background: #0891b2; }
        
        /* NOTIFICATIONS */
        .error { color: #f87171; font-size: 13px; margin-bottom: 15px; background: rgba(239,68,68,0.1); padding: 10px; border-radius: 6px; border: 1px solid #7f1d1d; }
        .success { color: #34d399; font-size: 13px; margin-bottom: 15px; background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 6px; border: 1px solid #064e3b; }
        
        .toggle-link { font-size: 13px; color: #6b7280; margin-top: 20px; display: block; cursor: pointer; }
        .toggle-link span { color: #06b6d4; text-decoration: underline; }
        
        .hidden { display: none; }

        /* --- NEW @MEMBER INPUT STYLES --- */
        .input-group { display: flex; align-items: center; background: #0f172a; border: 1px solid #334155; border-radius: 6px; margin: 8px 0; overflow: hidden; transition: 0.2s; }
        .input-group:focus-within { border-color: #06b6d4; }
        .input-group input { margin: 0; border: none; border-radius: 0; text-align: left; padding-left: 15px; flex: 1; outline: none; }
        .input-addon { background: #1e293b; padding: 12px 15px; color: #06b6d4; font-weight: 600; font-size: 14px; border-left: 1px solid #334155; user-select: none; }
    </style>
</head>
<body>

    <div class="card-container">
        <?php if(!empty($error_msg)): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <?php if(!empty($success_msg)): ?>
            <div class="success">✅ <?= htmlspecialchars($success_msg) ?></div>
        <?php endif; ?>

        <div id="login-section">
            <h2>Library Portal</h2>
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="login_member">
                
                <div class="input-group">
                    <input type="text" name="username" placeholder="johndoe" 
                           pattern="^(?=.*[a-zA-Z])[a-zA-Z0-9_]{4,20}$" 
                           title="Must be 4-20 chars, contain letters, and have no spaces." required autocomplete="off">
                    <div class="input-addon">@member</div>
                </div>
                
                <input type="password" name="password" placeholder="Password" required autocomplete="new-password">
                
                <button type="submit">Sign In</button>
            </form>
            
            <div class="toggle-link" onclick="toggleForms()">
                New here? <span>Create an Account</span>
            </div>
            
            <div style="margin-top: 10px;">
                <a href="index.php?mode=admin" style="font-size: 11px; color: #4b5563; text-decoration: none;">Admin Access</a>
            </div>
        </div>

        <div id="register-section" class="hidden">
            <h2>Create Account</h2>
            <form method="POST" autocomplete="off">
                <input type="hidden" name="action" value="register_self">
                
                <div class="input-group">
                    <input type="text" name="username" placeholder="Choose Username" 
                           pattern="^(?=.*[a-zA-Z])[a-zA-Z0-9_]{4,20}$" 
                           title="Must be 4-20 chars, contain letters, and have no spaces." required autocomplete="off">
                    <div class="input-addon">@member</div>
                </div>

                <div style="display:flex; gap:10px;">
                    <input type="text" name="fname" placeholder="First Name" required autocomplete="off" style="padding: 12px 8px;">
                    <input type="text" name="mname" placeholder="M.I (Opt)" autocomplete="off" style="padding: 12px 8px;">
                    <input type="text" name="lname" placeholder="Last Name" required autocomplete="off" style="padding: 12px 8px;">
                </div>
                
                <input type="text" name="phone" placeholder="Mobile Number" required autocomplete="off">
                <input type="email" name="email" placeholder="Email Address" required autocomplete="off">
                <input type="password" name="password" placeholder="Create Password" required autocomplete="new-password">
                
                <button type="submit" style="background: #10b981;">Register</button>
            </form>
            
            <div class="toggle-link" onclick="toggleForms()">
                Already have an account? <span>Back to Login</span>
            </div>
        </div>

    </div>

    <script>
        function toggleForms() {
            var login = document.getElementById('login-section');
            var register = document.getElementById('register-section');
            
            if (login.classList.contains('hidden')) {
                login.classList.remove('hidden');
                register.classList.add('hidden');
            } else {
                login.classList.add('hidden');
                register.classList.remove('hidden');
            }
        }
    </script>

</body>
</html>