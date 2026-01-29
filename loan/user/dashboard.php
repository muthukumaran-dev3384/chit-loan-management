<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

/* ---------- FETCH USER ---------- */
$stmt = $conn->prepare("SELECT * FROM customers WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$uid = $user['id'];

/* ---------- COUNTS ---------- */
function getCount($conn, $sql, $uid) {
    $s = $conn->prepare($sql);
    $s->bind_param("i", $uid);
    $s->execute();
    return $s->get_result()->fetch_assoc()['c'] ?? 0;
}

$totalLoans = getCount($conn,"SELECT COUNT(*) c FROM loans WHERE customer_id=?", $uid);
$pending    = getCount($conn,"SELECT COUNT(*) c FROM loans WHERE customer_id=? AND status='Pending'", $uid);
$approved   = getCount($conn,"SELECT COUNT(*) c FROM loans WHERE customer_id=? AND status='Approved'", $uid);
?>
<!DOCTYPE html>
<html>
<head>
<title>User Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- FONT AWESOME ICONS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}
body{
    background:
        linear-gradient(135deg, rgba(30,58,138,.85), rgba(31,41,55,.9)),
        url("../images/ba.jpg") center/cover no-repeat;
}

/* LAYOUT */
.wrapper{
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:270px;
    background:linear-gradient(180deg,#1e3a8a,#1f2937);
    color:#fff;
    padding:30px 22px;
}
.sidebar h2{
    text-align:center;
    margin-bottom:35px;
    font-size:24px;
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px 18px;
    margin-bottom:14px;
    color:#e5e7eb;
    text-decoration:none;
    border-radius:14px;
    font-size:15px;
    transition:.3s;
}
.sidebar a i{
    width:20px;
}
.sidebar a:hover,
.sidebar a.active{
    background:rgba(255,255,255,.2);
    color:#fff;
}

/* MAIN */
.main{
    flex:1;
    padding:35px;
}

/* HEADER */
.header{
    background:rgba(255,255,255,.92);
    padding:22px 30px;
    border-radius:22px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
    backdrop-filter:blur(10px);
}
.header h3{
    font-size:22px;
    color:#1e3a8a;
}
.header span{
    font-size:14px;
    color:#4b5563;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:25px;
}
.card{
    background:linear-gradient(135deg,#ffffff,#eef2ff);
    padding:30px;
    border-radius:24px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
    text-align:center;
    transition:.3s;
}
.card:hover{
    transform:translateY(-6px);
}
.card i{
    font-size:36px;
    margin-bottom:12px;
    color:#2563eb;
}
.card h4{
    color:#4b5563;
    margin-bottom:10px;
    font-size:16px;
}
.card h1{
    font-size:40px;
    color:#2563eb;
}

/* PROFILE */
.profile{
    margin-top:45px;
    background:rgba(255,255,255,.95);
    padding:35px;
    border-radius:26px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
}
.profile h3{
    margin-bottom:25px;
    color:#1e3a8a;
    font-size:22px;
}
.profile-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}
.profile p{
    background:#f8fafc;
    padding:14px 18px;
    border-radius:14px;
    font-size:14px;
    color:#374151;
    display:flex;
    gap:10px;
    align-items:center;
}
.profile i{
    color:#2563eb;
}

/* FOOTER */
.footer{
    text-align:center;
    margin-top:60px;
    font-size:13px;
    color:#e5e7eb;
}

/* MOBILE */
@media(max-width:900px){
    .sidebar{ display:none; }
    .main{ padding:22px; }
    .profile-grid{ grid-template-columns:1fr; }
}
</style>
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Loan System</h2>
        <a class="active" href="dashboard.php"><i class="fa-solid fa-gauge"></i>Dashboard</a>
        <a href="loan_list.php"><i class="fa-solid fa-file-signature"></i>Apply Loan</a>
        <a href="my_loans.php"><i class="fa-solid fa-list-check"></i>My Loans</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="header">
            <h3>Welcome, <?= htmlspecialchars($user['name']) ?></h3>
            <span><?= htmlspecialchars($user['email']) ?></span>
        </div>

        <div class="cards">
            <div class="card">
                <i class="fa-solid fa-wallet"></i>
                <h4>Total Loans</h4>
                <h1><?= $totalLoans ?></h1>
            </div>

            <div class="card">
                <i class="fa-solid fa-hourglass-half"></i>
                <h4>Pending Loans</h4>
                <h1><?= $pending ?></h1>
            </div>

            <div class="card">
                <i class="fa-solid fa-circle-check"></i>
                <h4>Approved Loans</h4>
                <h1><?= $approved ?></h1>
            </div>
        </div>

        <div class="profile">
            <h3>My Profile</h3>
            <div class="profile-grid">
                <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($user['mobile']) ?></p>
                <p><i class="fa-solid fa-indian-rupee-sign"></i> ₹<?= number_format($user['salary']) ?></p>
                <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($user['address']) ?></p>
            </div>
        </div>

        <div class="footer">
            © <?= date("Y") ?> Online Chit & Finance Loan Tracking System
        </div>

    </div>
</div>

</body>
</html>
