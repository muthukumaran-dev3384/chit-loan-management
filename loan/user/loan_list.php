<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

/* ---------- FETCH USER SALARY ---------- */
$stmt = $conn->prepare("
    SELECT salary 
    FROM customers 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$salary = floatval($user['salary'] ?? 0);

/* ---------- USER ELIGIBILITY ---------- */
$userEligibility = $salary * 10;

/* ---------- FETCH LOAN TYPES ---------- */
$loanTypes = $conn->query("
    SELECT id, loan_name, interest_rate, tenure, processing_fee, loan_amount
    FROM loan_types
    WHERE status='Active'
    ORDER BY loan_name
");
?>
<!DOCTYPE html>
<html>
<head>
<title>Available Loans</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Arial}
body{
    margin:0;
   background: url("../images/ba.jpg");
}

/* HEADER */
.header{
    background:linear-gradient(135deg,#2563eb,#1e3a8a);
    color:#fff;
    padding:40px 20px;
    text-align:center;
}
.header h2{
    margin:0;
    font-size:30px;
    font-weight:600;
}
.header small{
    display:block;
    margin-top:8px;
    font-size:14px;
    opacity:.9;
}

/* CONTAINER */
.container{
    max-width:1250px;
    margin:-35px auto 40px;
    padding:0 18px;
}

/* TOP BAR */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
    flex-wrap:wrap;
    gap:12px;
}
.back-btn{
    background:#1f2937;
    color:#fff;
    padding:10px 16px;
    text-decoration:none;
    border-radius:14px;
    font-size:14px;
    font-weight:600;
    box-shadow:0 8px 20px rgba(0,0,0,.25);
}
.back-btn:hover{background:#111827;}

.eligibility{
    background:linear-gradient(135deg,#dcfce7,#f0fdf4);
    color:#14532d;
    padding:12px 18px;
    border-radius:18px;
    font-size:14px;
    font-weight:700;
    box-shadow:0 6px 16px rgba(0,0,0,.12);
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

/* CARD */
.card{
    background:rgba(255,255,255,.96);
    border-radius:22px;
    padding:22px;
    box-shadow:0 18px 40px rgba(0,0,0,.15);
    transition:.3s;
    position:relative;
    overflow:hidden;
}
.card::before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(135deg,transparent,#eef2ff);
    opacity:.5;
    pointer-events:none;
}
.card:hover{
    transform:translateY(-6px);
    box-shadow:0 28px 55px rgba(0,0,0,.22);
}

/* BADGE */
.badge{
    display:inline-block;
    padding:6px 12px;
    background:linear-gradient(135deg,#e0ecff,#f8fafc);
    color:#1e40af;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
    margin-bottom:10px;
}

/* CONTENT */
.card h3{
    margin:6px 0 10px;
    color:#1e3a8a;
    font-size:18px;
}
.card p{
    margin:6px 0;
    font-size:14px;
    color:#374151;
}
.amount{
    margin:14px 0;
    padding:12px;
    background:#ecfeff;
    border-radius:16px;
    font-size:16px;
    font-weight:800;
    color:#0f766e;
    text-align:center;
}

/* BUTTON */
.apply-btn{
    display:block;
    text-align:center;
    margin-top:12px;
    padding:14px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    text-decoration:none;
    border-radius:18px;
    font-weight:700;
    font-size:15px;
    box-shadow:0 10px 22px rgba(37,99,235,.45);
}
.apply-btn:hover{opacity:.9}

.disabled{
    background:#9ca3af;
    cursor:not-allowed;
    box-shadow:none;
}

/* FOOTER */
.footer-note{
    margin-top:30px;
    text-align:center;
    font-size:13px;
    color:#475569;
}

/* MOBILE */
@media(max-width:768px){
    .top-bar{justify-content:center}
}
</style>
</head>

<body>

<div class="header">
    <h2>Available Loan Types</h2>
    <small>Loan terms fixed by admin • Eligibility based on your salary</small>
</div>

<div class="container">

<div class="top-bar">
    <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
    <div class="eligibility">
        Your Eligibility: ₹<?= number_format($userEligibility) ?>
    </div>
</div>

<div class="cards">

<?php while($r = $loanTypes->fetch_assoc()){ 
    $finalEligible = min($userEligibility, $r['loan_amount']);
?>

<div class="card">

    <span class="badge">Admin Approved</span>

    <h3><?= htmlspecialchars($r['loan_name']) ?></h3>

    <p>Interest Rate: <b><?= $r['interest_rate'] ?>%</b></p>
    <p>Tenure: <b><?= $r['tenure'] ?> months</b></p>
    <p>Processing Fee: <b><?= $r['processing_fee'] ?>%</b></p>
    <p>Max Loan Amount: <b>₹<?= number_format($r['loan_amount']) ?></b></p>

    <div class="amount">
        You Can Apply Up To ₹<?= number_format($finalEligible) ?>
    </div>

    <?php if($finalEligible > 0){ ?>
        <a class="apply-btn" href="apply_loan.php?id=<?= $r['id'] ?>">
            Apply Loan
        </a>
    <?php } else { ?>
        <div class="apply-btn disabled">Not Eligible</div>
    <?php } ?>

</div>

<?php } ?>

</div>

<div class="footer-note">
    * Final eligibility = min (Salary × 10, Loan max limit defined by admin)
</div>

</div>

</body>
</html>
