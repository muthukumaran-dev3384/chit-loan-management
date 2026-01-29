<?php
include "db.php";

$trackError = "";
$trackData  = null;

/* ---------- TRACK LOAN ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track'])) {

    $appNo = trim($_POST['application_no']);

    // Validate format
    if (!preg_match("/^APP-\d{4}-\d{6}$/", $appNo)) {
        $trackError = "Invalid Application Number format (APP-YYYY-XXXXXX)";
    } else {

        $stmt = $conn->prepare("
            SELECT 
                l.id,
                l.application_no,
                l.amount,
                l.status,
                l.applied_date,
                l.updated_at,
                c.name,
                lt.loan_name,
                lt.interest_rate,
                lt.tenure
            FROM loans l
            INNER JOIN customers c ON l.customer_id = c.id
            INNER JOIN loan_types lt ON l.loan_type_id = lt.id
            WHERE l.application_no = ?
        ");

        if(!$stmt){
            die("Query Error: ".$conn->error);
        }

        $stmt->bind_param("s", $appNo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $trackError = "No loan found for this Application Number";
        } else {
            $trackData = $res->fetch_assoc();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Track Loan Status</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Arial,sans-serif;
}
body{
    background: url("./images/trac.jpg");
}

/* HEADER */
.header{
    background:linear-gradient(135deg,#0d6efd,#1f2937);
    padding:35px 20px;
    text-align:center;
    color:#fff;
}
.header h1{
    font-size:30px;
    margin-bottom:8px;
}
.header p{
    opacity:.9;
}

/* CONTAINER */
.container{
    max-width:720px;
    margin:50px auto;
    padding:0 15px;
}

/* CARD */
.card{
    background:#fff;
    border-radius:22px;
    padding:35px;
    box-shadow:0 20px 45px rgba(0,0,0,.12);
}

/* FORM */
.card h2{
    text-align:center;
    margin-bottom:25px;
    color:#333;
}
.input-group{
    margin-bottom:18px;
}
.input-group input{
    width:100%;
    padding:15px;
    border-radius:14px;
    border:1px solid #ccc;
    font-size:15px;
}
.input-group input:focus{
    outline:none;
    border-color:#0d6efd;
}
.btn{
    width:100%;
    padding:15px;
    border:none;
    border-radius:16px;
    background:#0d6efd;
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
.btn:hover{
    background:#084298;
}

/* ERROR */
.error{
    margin-top:18px;
    padding:14px;
    border-radius:14px;
    background:#f8d7da;
    color:#842029;
    text-align:center;
}

/* RESULT */
.result{
    margin-top:30px;
    background:#f8fafc;
    border-radius:18px;
    padding:25px;
}
.result h3{
    margin-bottom:15px;
    color:#0d6efd;
    text-align:center;
}
.row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px dashed #ddd;
    font-size:15px;
}
.row:last-child{
    border-bottom:none;
}
.label{
    color:#555;
    font-weight:600;
}
.value{
    color:#222;
}

/* STATUS BADGES */
.badge{
    padding:6px 16px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}
.badge.pending{
    background:#fff3cd;
    color:#664d03;
}
.badge.approved{
    background:#d1e7dd;
    color:#0f5132;
}
.badge.rejected{
    background:#f8d7da;
    color:#842029;
}

.time{
    text-align:center;
    margin-top:15px;
    font-size:13px;
    color:#6c757d;
}

/* FOOTER */
.footer{
    text-align:center;
    margin-top:50px;
    padding:15px;
    color:#6c757d;
    font-size:13px;
}
</style>
</head>

<body>

<div class="header">
    <h1>Loan Tracking Portal</h1>
    <p>Track your loan application status in real time</p>
</div>

<div class="container">
<div class="card">

    <h2>Track Your Loan</h2>

    <form method="post">
        <div class="input-group">
            <input 
                type="text" 
                name="application_no" 
                placeholder="APP-2026-123456"
                required
            >
        </div>
        <button class="btn" name="track">Track Loan</button>
    </form>

    <?php if($trackError){ ?>
        <div class="error"><?= htmlspecialchars($trackError) ?></div>
    <?php } ?>

    <?php if($trackData){ ?>
    <div class="result">
        <h3>Loan Details</h3>

        <div class="row"><span class="label">Application No</span><span class="value"><?= htmlspecialchars($trackData['application_no']) ?></span></div>
        <div class="row"><span class="label">Customer</span><span class="value"><?= htmlspecialchars($trackData['name']) ?></span></div>
        <div class="row"><span class="label">Loan Type</span><span class="value"><?= htmlspecialchars($trackData['loan_name']) ?></span></div>
        <div class="row"><span class="label">Amount</span><span class="value">₹<?= number_format($trackData['amount'],2) ?></span></div>
        <div class="row"><span class="label">Interest</span><span class="value"><?= $trackData['interest_rate'] ?>%</span></div>
        <div class="row"><span class="label">Tenure</span><span class="value"><?= $trackData['tenure'] ?> months</span></div>
        <div class="row"><span class="label">Applied Date</span><span class="value"><?= date("d M Y",strtotime($trackData['applied_date'])) ?></span></div>

        <div class="row">
            <span class="label">Status</span>
            <span class="badge <?= strtolower($trackData['status']) ?>">
                <?= $trackData['status'] ?>
            </span>
        </div>

        <div class="time">
            Last Updated: <?= date("d M Y h:i A",strtotime($trackData['updated_at'])) ?>
        </div>
    </div>
    <?php } ?>

</div>
</div>

<div class="footer">
    © <?= date('Y') ?> Loan Management System
</div>

</body>
</html>
