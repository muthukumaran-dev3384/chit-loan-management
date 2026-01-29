<?php
session_start();
include "../db.php";

$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = hash('sha256', trim($_POST['password']));

    if ($username == "" || $_POST['password'] == "") {
        $error = "Please enter Username and Password";
    } else {

        $stmt = $conn->prepare(
            "SELECT id FROM admin WHERE username=? AND password=?"
        );
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $_SESSION['admin'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid Admin Credentials";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial;
}
body{
    min-height:100vh;
     background:url("../images/loan.jpg");
    display:flex;
    justify-content:center;
    align-items:center;
}

/* LOGIN CARD */
.login-box{
    width:380px;
    padding:30px;
    background:rgba(255,255,255,.92);
    border-radius:18px;
    box-shadow:0 20px 50px rgba(0,0,0,.35);
    animation:fadeIn .8s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

.login-box h2{
    text-align:center;
    margin-bottom:20px;
    color:#0d6efd;
    font-weight:700;
}

/* INPUTS */
.login-box input{
    width:100%;
    padding:14px;
    margin-bottom:14px;
    border:1px solid #ced4da;
    border-radius:10px;
    font-size:15px;
    transition:.3s;
}
.login-box input:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 3px rgba(13,110,253,.15);
}

/* BUTTON */
.login-box button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#0d6efd,#198754);
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}
.login-box button:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 25px rgba(13,110,253,.4);
}

/* ERROR */
.error{
    background:#f8d7da;
    color:#842029;
    padding:10px;
    border-radius:10px;
    text-align:center;
    margin-bottom:15px;
    font-size:14px;
}

/* FOOTER */
.footer-text{
    text-align:center;
    margin-top:15px;
    font-size:13px;
    color:#6c757d;
}
</style>
</head>

<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if($error!=""){ ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="post">
        <input type="text" name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>

    <div class="footer-text">
        Finance Loan Management System
    </div>
</div>

</body>
</html>
