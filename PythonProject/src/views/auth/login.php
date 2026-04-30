<?php 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Learning2ne1</title>
    <link rel="stylesheet" href="../../../assets/css/Background.css">
    <style>
        :root {
            --primary-mint: #00b894;     
            --light-mint: #55efc4;       
            --bg-grey: #f8f9fa;          
            --text-dark: #2d3436;        
            --text-gray: #636e72;
            --shadow-soft: 0 10px 40px rgba(0, 184, 148, 0.15); 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: var(--primary-mint);
            height: 70px;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-card {
            background: #fff;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            border-radius: 24px; 
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px); 
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .welcome-text h2 {
            color: var(--text-dark);
            font-size: 26px;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: var(--text-gray);
            font-size: 14px;
        }

        /* FORM STYLES */
        .form-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-left: 4px; 
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 2px solid #f1f2f6; 
            outline: none;
            font-size: 15px;
            background: #fdfdfd;
            transition: all 0.2s;
            color: var(--text-dark);
        }

        .form-group input:focus {
            border-color: var(--primary-mint);
            background: #fff;
        }

        .form-group input::placeholder {
            color: #b2bec3;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none; 
        }

        .btn-primary {
            background: var(--primary-mint);
            color: #fff;
            margin-top: 1rem;
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
        }

        .btn-primary:hover {
            background: #00a383;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 184, 148, 0.4);
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #eee;
            z-index: 1;
        }

        .divider span {
            background: #fff;
            padding: 0 10px;
            color: #b2bec3;
            font-size: 12px;
            position: relative;
            z-index: 2;
        }

        .btn-guest {
            background: transparent;
            border: 2px solid #eee;
            color: var(--text-gray);
        }

        .btn-guest:hover {
            border-color: var(--text-dark);
            color: var(--text-dark);
            background: #fff;
        }

        .forgot-link {
            display: inline-block;
            float: right;
            font-size: 13px;
            color: var(--primary-mint);
            text-decoration: none;
            font-weight: 600;
            margin-top: -5px; 
            margin-bottom: 1.5rem;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <div class="header">
        <div class="logo">Learning2ne1</div>
    </div>

    <div class="main-container">
        <div class="login-card">
            
            <div class="welcome-text">
                <h2>Đăng nhập</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Tài khoản</label>
                    <input type="text" id="email" name="email" placeholder="Nhập tài khoản" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu của bạn" required>
                </div>

                <a href="#" class="forgot-link">Quên mật khẩu?</a>

                <button name='btn_dang_nhap' type="submit" class="btn btn-primary">Đăng nhập ngay</button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>
        <form method = "post">
            <button name='btn_dang_nhap_khach' type="submit" class="btn btn-guest">
                Tiếp tục với vai trò Khách
            </button>
        </form>
        </div>
    </div>


</body>
</html>