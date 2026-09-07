<!DOCTYPE html>
<html>
<head>

    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <title>FAD PCount - Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            padding:20px;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            font-family:Arial, sans-serif;
            background:#eef3f8;
        }

        .login-container{
            width:100%;
            max-width:460px;
            background:#fff;
            padding:35px 30px;
            border-radius:18px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            text-align:center;
        }

        .logo{
            width:min(320px,90%);
            display:block;
            margin:0 auto 15px;
        }

        .subtitle{
            margin:0 0 30px;
            color:#6c757d;
            font-size:15px;
            font-weight:500;
        }

        h2{
            margin-bottom:25px;
            color:#4a5568;
            font-size:22px;
            letter-spacing:1px;
        }

        .error{
            background:#ffeaea;
            color:#c62828;
            border:1px solid #f5bcbc;
            border-radius:10px;
            padding:12px;
            margin-bottom:20px;
            font-weight:bold;
        }

        .form-group{
            margin-bottom:18px;
            text-align:left;
        }

        .form-group label{
            display:block;
            margin-bottom:8px;
            font-weight:bold;
            color:#555;
        }

        .form-group input{
            width:100%;
            padding:14px;
            border:1px solid #dcdcdc;
            border-radius:12px;
            font-size:16px;
            transition:.2s;
        }

        .form-group input:focus{
            outline:none;
            border-color:#007bff;
            box-shadow:0 0 0 3px rgba(0,123,255,.15);
        }

        .login-btn{
            width:100%;
            min-height:60px;
            border:none;
            border-radius:14px;
            background:linear-gradient(135deg,#007bff,#0056b3);
            color:#fff;
            font-size:18px;
            font-weight:600;
            cursor:pointer;
            transition:.25s;
        }

        .login-btn:hover{
            transform:translateY(-3px);
            box-shadow:0 8px 18px rgba(0,123,255,.25);
        }

        .login-btn:active{
            transform:scale(.98);
        }

        .password-container{
            position:relative;
        }

        .password-container input{
            width:100%;
            padding:14px 48px 14px 14px;
            border:1px solid #dcdcdc;
            border-radius:12px;
            font-size:16px;
        }

        .toggle-password{
            position:absolute;
            top:50%;
            right:15px;
            transform:translateY(-50%);
            cursor:pointer;
            font-size:20px;
            user-select:none;
            color:#6c757d;
            transition:.2s;
        }

        .toggle-password:hover{
            color:#007bff;
        }

        @media(max-width:360px){

            body{
                padding:10px;
            }

            .login-container{
                padding:25px 20px;
            }

            .login-btn{
                min-height:55px;
                font-size:16px;
            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <img
        src="<?= base_url('assets/logo2.png') ?>"
        class="logo"
        alt="FAD PCount"
    >

    <p class="subtitle">
        CSV Import and Report Management
    </p>

    <h2>Users Login</h2>

    <?php if($this->session->flashdata('error')): ?>

        <div class="error">
            <?= $this->session->flashdata('error'); ?>
        </div>

    <?php endif; ?>

    <?= form_open('login/auth'); ?>

        <div class="form-group">

            <label>Username</label>

            <input
                type="text"
                name="username"
                required
                autofocus
            >

        </div>

        <div class="form-group">

        <label>Password</label>

        <div class="password-container">

            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <span 
                id="togglePassword" 
                class="toggle-password">
                <i class="bi bi-eye"></i>
            </span>

        </div>

        </div>

        <button type="submit" class="login-btn">
            Login
        </button>

    <?= form_close(); ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');

    toggle.addEventListener('click', function () {

        const icon = toggle.querySelector('i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            password.type = 'password';
            icon.className = 'bi bi-eye';
        }

    });

});
</script>

</body>
</html>