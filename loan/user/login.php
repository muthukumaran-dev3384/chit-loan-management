<?php
session_start();
/* ---------- INCLUDE DB ---------- */
include "../db.php";

/* ---------- BLOCK IF ALREADY LOGGED ---------- */
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

/* ---------- LOGIN PROCESS ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($email === "" || $password === "") {
        $error = "Please enter Email and Password";
    } else {

        /* SAME HASH AS REGISTER */
        $hashed = hash('sha256', $password);

        $stmt = $conn->prepare(
            "SELECT id FROM customers WHERE email = ? AND password = ?"
        );

        if (!$stmt) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("ss", $email, $hashed);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $_SESSION['user'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid Email or Password";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>User Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}
body{
    min-height:100vh;
    background:
        linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)),
        url("../images/loan.jpg") no-repeat center center fixed;
    background-size:cover;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* LOGIN CARD */
.login-box{
    width:420px;
    padding:40px 35px;
    background:rgba(255,255,255,.95);
    border-radius:22px;
    box-shadow:0 25px 60px rgba(0,0,0,.45);
    backdrop-filter:blur(8px);
}

/* HEADER */
.login-box h2{
    text-align:center;
    font-size:26px;
    color:#1e3a8a;
    margin-bottom:30px;
    font-weight:700;
}

/* INPUTS */
input{
    width:100%;
    padding:14px 16px;
    margin-bottom:18px;
    border-radius:12px;
    border:1px solid #cbd5f5;
    font-size:15px;
    transition:.3s;
}
input:focus{
    border-color:#2563eb;
    outline:none;
    box-shadow:0 0 0 3px rgba(37,99,235,.25);
}

/* BUTTON */
button{
    width:100%;
    padding:14px;
    background:linear-gradient(90deg,#2563eb,#1d4ed8);
    color:#fff;
    border:none;
    border-radius:14px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
    box-shadow:0 12px 28px rgba(37,99,235,.45);
}
button:hover{
    transform:translateY(-2px);
    box-shadow:0 18px 40px rgba(37,99,235,.6);
}

/* ERROR */
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    margin-bottom:18px;
    border-radius:12px;
    text-align:center;
    font-size:14px;
    font-weight:600;
}

/* FOOTER */
.login-box p{
    margin-top:22px;
    text-align:center;
    font-size:14px;
    color:#374151;
}
.login-box a{
    color:#2563eb;
    font-weight:600;
    text-decoration:none;
}
.login-box a:hover{
    text-decoration:underline;
}

/* MOBILE */
@media(max-width:480px){
    .login-box{
        width:92%;
        padding:30px 25px;
    }
}
</style>
</head>

<body>

<div class="login-box">
    <h2>User Login</h2>

    <?php if ($error !== "") { ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="post" autocomplete="off">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <p>
        Don’t have an account?
        <a href="register.php">Register Here</a>
    </p>
</div>

</body>
</html>
