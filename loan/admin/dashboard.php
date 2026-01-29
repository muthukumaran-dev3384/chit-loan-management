<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include "../db.php";

/* ---------- DASHBOARD COUNTS ---------- */
$customers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM customers"))['c'] ?? 0;
$loans     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM loans"))['c'] ?? 0;
$pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM loans WHERE status='Pending'"))['c'] ?? 0;
$approved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM loans WHERE status='Approved'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', Arial, sans-serif;
}
body{
    background:url("../images/loan.jpg");
    background-size:cover;
    background-position:center;
    background-attachment:fixed;
    color:#111;
}

/* LAYOUT */
.wrapper{
    display:flex;
    min-height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:270px;
    background:rgba(17,24,39,.95);
    backdrop-filter:blur(8px);
    color:#fff;
    padding:25px 20px;
}
.sidebar h2{
    text-align:center;
    margin-bottom:35px;
    font-size:24px;
    color:#60a5fa;
    letter-spacing:1px;
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:14px 16px;
    margin-bottom:12px;
    color:#d1d5db;
    text-decoration:none;
    border-radius:10px;
    transition:.3s;
    font-size:15px;
}
.sidebar a:hover,
.sidebar a.active{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
}

/* MAIN */
.main{
    flex:1;
    padding:35px;
}

/* HEADER */
.header{
    background:rgba(255,255,255,.95);
    padding:20px 28px;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.15);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
}
.header h3{
    font-size:22px;
    color:#1e3a8a;
}
.header span{
    color:#555;
    font-weight:600;
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:25px;
}
.card{
    background:rgba(255,255,255,.95);
    padding:30px;
    border-radius:22px;
    box-shadow:0 18px 40px rgba(0,0,0,.18);
    text-align:center;
    transition:.3s;
}
.card:hover{
    transform:translateY(-6px);
}
.card h4{
    color:#555;
    margin-bottom:14px;
    font-size:16px;
}
.card h1{
    font-size:38px;
    color:#2563eb;
}

/* QUICK LINKS */
.quick{
    margin-top:45px;
    background:rgba(255,255,255,.95);
    padding:35px;
    border-radius:22px;
    box-shadow:0 18px 40px rgba(0,0,0,.18);
}
.quick h3{
    margin-bottom:22px;
    color:#1e3a8a;
    font-size:20px;
}
.quick a{
    display:inline-block;
    margin:12px 18px 0 0;
    padding:14px 26px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    text-decoration:none;
    border-radius:14px;
    font-size:14px;
    font-weight:600;
    transition:.3s;
}
.quick a:hover{
    transform:translateY(-3px);
    background:linear-gradient(135deg,#1e40af,#1e3a8a);
}

/* FOOTER */
.footer{
    text-align:center;
    margin-top:55px;
    font-size:13px;
    color:#e5e7eb;
}

/* MOBILE */
@media(max-width:900px){
    .sidebar{ display:none; }
    .main{ padding:20px; }
}
</style>
</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a class="active" href="dashboard.php">📊 Dashboard</a>
        <a href="loan_requests.php">📄 Loan Requests</a>
        <a href="add_loan_type.php">💼 Loan Types</a>
        <a href="report.php">📈 Reports</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="header">
            <h3>Welcome, Admin</h3>
            <span><?= date("d M Y") ?></span>
        </div>

        <div class="cards">
            <div class="card">
                <h4>Total Customers</h4>
                <h1><?= $customers ?></h1>
            </div>
            <div class="card">
                <h4>Total Loans</h4>
                <h1><?= $loans ?></h1>
            </div>
            <div class="card">
                <h4>Pending Loans</h4>
                <h1><?= $pending ?></h1>
            </div>
            <div class="card">
                <h4>Approved Loans</h4>
                <h1><?= $approved ?></h1>
            </div>
        </div>

        <div class="quick">
            <h3>Quick Actions</h3>
            <a href="loan_requests.php">Manage Loan Requests</a>
            <a href="add_loan_type.php">Manage Loan Types</a>
            <a href="report.php">View Reports</a>
        </div>

        <div class="footer">
            © <?= date("Y") ?> Online Chit & Finance Loan Tracking System
        </div>

    </div>
</div>

</body>
</html>
