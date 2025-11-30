<?php
include 'connection.php';

if (isset($_POST['daftar'])){
    $username = htmlentities(strip_tags(trim ($_POST['username'])));
    $Password = htmlentities(strip_tags(trim ($_POST['Password'])));
    $Telp = htmlentities(strip_tags(trim ($_POST['No_Telp'])));
    $email = htmlentities(strip_tags(trim ($_POST['Email'])));

    $error_massage = "";


    //validasi input kosong
    if (empty($username) && empty($Password) && empty($Telp) && empty($email)){
        $error_massage = "Semua data harus diisi";
    } elseif (!$username){
        $error_massage = "Username tidak boleh kosong";
    } elseif (!$Password){
        $error_massage = "Password tidak boleh kosong";
    } elseif (!$Telp){
        $error_massage = "No Telp tidak boleh kosong";
    } elseif (!$email){
        $error_massage = "Email tidak boleh kosong";
}

    //validasi format input
    if (!is_numeric($Telp)){
        $error_massage = "Nomor Telepon harus berupa angka";
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false){
        $error_massage = "Format email tidak valid";
    }

    //cek username sudah ada atau belum
    $username = mysqli_real_escape_string($conn, $username);
    $query_username = "SELECT * FROM user WHERE Username='$username' LIMIT 1";
    $result_username = mysqli_query($conn, $query_username);
    $num_rows_username = mysqli_num_rows($result_username);


    //cek No_Telp sudah ada atau belum
    $Telp = mysqli_real_escape_string($conn, $Telp);
    $query_telp = "SELECT * FROM user WHERE No_Telp='$Telp' LIMIT 1";
    $result_telp = mysqli_query($conn, $query_telp);
    $num_rows_telp = mysqli_num_rows($result_telp);


    //cek Email sudah ada atau belum
    $email = mysqli_real_escape_string($conn, $email);
    $query_email = "SELECT * FROM user WHERE Email='$email' LIMIT 1";
    $result_email = mysqli_query($conn, $query_email);  
    $num_rows_email = mysqli_num_rows($result_email);

    if ($num_rows_username >= 1){
        $error_massage = "Username sudah terdaftar";
    }

    if ($num_rows_telp >= 1){
        $error_massage = "Nomor Telepon sudah terdaftar";
    }

    if ($num_rows_email >= 1){
        $error_massage = "Email sudah terdaftar";
    }

    if ($error_massage == ""){
        //insert data ke database
        $username  = mysqli_real_escape_string($conn, $username);
        $hashed_Password = password_hash($Password, PASSWORD_DEFAULT);
        $Telp      = mysqli_real_escape_string($conn, $Telp);
        $email     = mysqli_real_escape_string($conn, $email);
    

    $query = "INSERT INTO user (Username, Password, No_Telp, Email) VALUES ('$username', '$hashed_Password', '$Telp', '$email')";
    $result = mysqli_query($conn, $query);

    if ($result){
        $massage = "Pendaftaran berhasil!";
        $massage = urlencode($massage);
        header ("Location: index.php?massage=$massage");
    } else {
        die ("Pendaftaran gagal: " . mysqli_error($conn));
    }
}
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Cafe</title>
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
            max-width: 450px;
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
        
        .required {
            color: #e74c3c;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"] {
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
        
        .password-hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .icon {
            font-size: 50px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">☕</div>
        <h2>Daftar Akun Baru</h2>
        <p class="subtitle">Bergabunglah dengan Cafe kami!</p>
        
        <?php if (!empty($error_massage)): ?>
            <div class="error">⚠️ <?php echo $error_massage; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['massage'])): ?>
            <div class="success">✅ <?php echo htmlspecialchars($_GET['massage']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input type="text" id="username" name="username" placeholder="Pilih username unik" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="Password">Password <span class="required">*</span></label>
                <input type="password" id="Password" name="Password" placeholder="Minimal 6 karakter" required>
                <div class="password-hint">💡 Gunakan kombinasi huruf dan angka</div>
            </div>
            
            <div class="form-group">
                <label for="No_Telp">Nomor Telepon <span class="required">*</span></label>
                <input type="tel" id="No_Telp" name="No_Telp" placeholder="08xxxxxxxxxx" required value="<?php echo isset($_POST['No_Telp']) ? htmlspecialchars($_POST['No_Telp']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="Email">Email <span class="required">*</span></label>
                <input type="email" id="Email" name="Email" placeholder="email@example.com" required value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>">
            </div>
            
            <button type="submit" name="daftar">📝 Daftar Sekarang</button>
        </form>
        
        <div class="link">
            Sudah punya akun? <a href="login.php">🔑 Login disini</a>
        </div>
    </div>

    <script>
        // Auto focus ke input pertama
        document.getElementById('username').focus();
        
        // Validasi nomor telepon (hanya angka)
        document.getElementById('No_Telp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>