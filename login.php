<?php
session_start();
include 'connection.php';

if (isset($_POST['login'])) {
    
    //ambil data dari form
    $username = htmlentities(strip_tags(trim ($_POST['username'])));
    $Password = htmlentities(strip_tags(trim ($_POST['Password'])));

    $error_massage = "";

    //cek data di database
    $username = mysqli_real_escape_string($conn, $username);
    $Password = mysqli_real_escape_string($conn, $Password);
    $query = "SELECT * FROM user WHERE username='$username' AND Password='$Password' LIMIT 1";
    $result = mysqli_query($conn, $query);

    $num_rows = mysqli_num_rows($result);

    //validasi input
    if (empty($username) && empty($Password)){
        $error_massage = "Username dan Password tidak boleh kosong";
    }

    if (!$username){
        $error_massage = "Username tidak boleh kosong";
    }

    if (!$Password){
        $error_massage = "Password tidak boleh kosong";
    }

    //kalo data nya ada
    if ($num_rows >= 1){
        $user = mysqli_fetch_assoc($result);

        if (password_verify($Password, $user['Password'])) {
            //set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
        
        header("Location: home.php");
        exit ();
    } else {
        $error_massage = "Username atau Password salah atau sudah ada";
    }
    
}
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            font-size: 60px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        input::placeholder {
            color: #aaa;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #e74c3c;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #28a745;
        }
        
        .link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 14px;
        }
        
        .link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
        }
        
        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔐</div>
        <h2>Login</h2>
        <p class="subtitle">Selamat datang kembali!</p>
        
        <?php if (!empty($error_massage)): ?>
            <div class="error">⚠️ <?php echo $error_massage; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['massage'])): ?>
            <div class="success">✅ <?php echo htmlspecialchars($_GET['massage']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="Password">Password</label>
                <input type="password" id="Password" name="Password" placeholder="Masukkan password" required>
            </div>
            
            <button type="submit" name="login">🔑 Login</button>
        </form>
        
        <div class="link">
            Belum punya akun? <a href="daftar.php">📝 Daftar disini</a>
        </div>
    </div>

    <script>
        // Auto focus ke input username
        document.getElementById('username').focus();
    </script>
</body>
</html>