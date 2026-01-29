<?php
include "db.php";
$trackData = null;
$trackError = "";
?>
<!DOCTYPE html>
<html>
<head>
<title>Online Chit & Finance Loan Tracking System</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
}
body{
    background:#f3f6fb;
    color:#1f2937;
}

/* NAVBAR */
.navbar{
    position:sticky;
    top:0;
    z-index:100;
    background:rgba(31,41,55,0.95);
    backdrop-filter:blur(10px);
    padding:18px 50px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 8px 25px rgba(0,0,0,.25);
}
.navbar h2{
    font-size:22px;
    font-weight:700;
}
.navbar a{
    color:#e5e7eb;
    text-decoration:none;
    margin-left:22px;
    font-size:15px;
    font-weight:500;
}
.navbar a:hover{color:#fff;}

/* HERO */
.hero{
    min-height:90vh;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    background:
        linear-gradient(135deg,rgba(13,110,253,.9),rgba(30,41,59,.95)),
        url("images/loan.jpg") center/cover no-repeat;
    color:#fff;
    padding:40px 20px;
}
.hero-box{
    max-width:850px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(10px);
    border-radius:28px;
    padding:55px 40px;
    box-shadow:0 30px 70px rgba(0,0,0,.4);
}
.hero h1{
    font-size:42px;
    margin-bottom:18px;
    line-height:1.2;
}
.hero p{
    font-size:18px;
    margin-bottom:35px;
    line-height:1.7;
    opacity:.95;
}
.buttons a{
    display:inline-block;
    margin:10px;
    padding:16px 36px;
    font-size:16px;
    font-weight:600;
    color:#fff;
    text-decoration:none;
    border-radius:14px;
    transition:.3s;
}
.admin-btn{
    background:linear-gradient(135deg,#dc3545,#b02a37);
}
.user-btn{
    background:linear-gradient(135deg,#198754,#0f5132);
}
.buttons a:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 25px rgba(0,0,0,.35);
}

/* FEATURES */
.features{
    padding:70px 40px;
    background:#fff;
    text-align:center;
}
.features h2{
    font-size:32px;
    margin-bottom:40px;
    color:#0d6efd;
}
.feature-box{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:30px;
}
.feature{
    background:#f9fafb;
    padding:35px 25px;
    border-radius:22px;
    box-shadow:0 12px 30px rgba(0,0,0,.1);
    font-size:16px;
    font-weight:600;
}
.feature:hover{
    transform:translateY(-6px);
    transition:.3s;
}

/* TRACK SECTION */
.track{
    background:linear-gradient(135deg,#eef2ff,#f8fafc);
    padding:70px 20px;
    text-align:center;
}
.track h2{
    font-size:30px;
    margin-bottom:25px;
}
.track-box{
    max-width:520px;
    margin:auto;
    background:rgba(255,255,255,.95);
    padding:40px;
    border-radius:24px;
    box-shadow:0 25px 55px rgba(0,0,0,.15);
}
.track-box input{
    width:100%;
    padding:16px;
    margin-bottom:18px;
    border-radius:14px;
    border:1px solid #d1d5db;
    font-size:15px;
}
.track-box input:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 3px rgba(13,110,253,.15);
}
.track-box button{
    width:100%;
    padding:16px;
    background:linear-gradient(135deg,#0d6efd,#084298);
    color:#fff;
    border:none;
    border-radius:16px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
.track-box button:hover{opacity:.9;}

/* RESULT CARD */
.result{
    margin-top:25px;
    text-align:left;
    background:#f9fafb;
    padding:25px;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
}
.result p{
    margin:8px 0;
    font-size:15px;
}
.badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:700;
}
.Pending{background:#fff3cd;color:#664d03;}
.Approved{background:#d1e7dd;color:#0f5132;}
.Rejected{background:#f8d7da;color:#842029;}

.error{
    background:#f8d7da;
    color:#842029;
    padding:12px;
    border-radius:12px;
    margin-top:18px;
    font-size:14px;
}

/* FOOTER */
footer{
    background:#1f2937;
    color:#9ca3af;
    text-align:center;
    padding:20px;
    font-size:14px;
}

/* MOBILE */
@media(max-width:768px){
    .navbar{padding:15px 20px;}
    .hero h1{font-size:30px;}
    .hero-box{padding:35px 25px;}
}
</style>
</head>

<body>

<div class="navbar">
    <h2>Chit & Finance Loan System</h2>
    <div>
        <a href="index.php">Home</a>
        <a href="user/login.php">User Login</a>
        <a href="admin/login.php">Admin Login</a>
        <a href="loan_track.php">Loan Track</a>
    </div>
</div>

<div class="hero">
    <div class="hero-box">
        <h1>Online Chit & Finance Loan Tracking System</h1>
        <p>
            Transparent loan processing, salary-based eligibility,
            EMI clarity and real-time loan tracking — all in one secure platform.
        </p>
        <div class="buttons">
            <a href="admin/login.php" class="admin-btn">Admin Login</a>
            <a href="user/login.php" class="user-btn">User Login</a>
        </div>
    </div>
</div>

<!-- FEATURES -->
<div class="features">
    <h2>System Features</h2>
    <div class="feature-box">
        <div class="feature">✔ Real-Time Loan Tracking</div>
        <div class="feature">✔ EMI & Interest Transparency</div>
        <div class="feature">✔ Salary-Based Eligibility</div>
        <div class="feature">✔ Secure Admin Approval</div>
    </div>
</div>

<footer>
    © <?= date("Y") ?> Online Chit & Finance Loan Tracking System | Final Year Project
</footer>

</body>
</html>
