<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-portal {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .portal-card {
            background: rgba(255, 255, 255, 0.96);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            min-width: 250px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .portal-card h3 {
            color: #ff7810;
            margin-bottom: 12px;
            font-size: 24px;
        }
        .portal-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .portal-card .button {
            display: inline-block;
            padding: 12px 30px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="nav">🍔 Crave Cart Admin Portal</div>
    <div class="page-title">Admin Access</div>
    
    <div class="admin-portal">
        <div class="portal-card">
            <h3>New Admin?</h3>
            <p>Create your admin account to manage the system</p>
            <a class="button" href="register.php">Register Account</a>
        </div>
        
        <div class="portal-card">
            <h3>Existing Admin?</h3>
            <p>Login with your username and password</p>
            <a class="button" href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
