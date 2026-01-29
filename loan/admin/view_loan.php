<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* ---------- FETCH LOAN ---------- */
$id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT 
        l.*, 
        c.name AS customer_name, 
        c.salary, 
        c.email,
        t.loan_name, 
        t.loan_amount
    FROM loans l
    JOIN customers c ON c.id = l.customer_id
    JOIN loan_types t ON t.id = l.loan_type_id
    WHERE l.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();

if (!$r) {
    die("Loan not found");
}

/* ---------- ELIGIBILITY ---------- */
$eligibleAmount = $r['salary'] * 10;
$isEligible = $r['amount'] <= $eligibleAmount;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Loan Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Arial,sans-serif;
}
body{
   background: url("../images/loan.jpg") no-repeat center center fixed;
    background-size: cover;
}

/* NAVBAR */
.navbar{
    background:linear-gradient(90deg,#1e3a8a,#2563eb);
    color:#fff;
    padding:18px 35px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}
.navbar h2{font-size:20px;}
.navbar a{
    color:#fff;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}

/* CONTAINER */
.container{
    max-width:1100px;
    margin:40px auto;
    padding:30px;
    background:#fff;
    border-radius:22px;
    box-shadow:0 15px 40px rgba(0,0,0,.12);
}

/* HEADER */
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}
.page-header h3{
    font-size:24px;
    color:#1f2937;
}
.back-btn{
    background:#111827;
    padding:10px 20px;
    border-radius:12px;
    text-decoration:none;
    color:white;
    font-size:14px;
    font-weight:600;
}
.back-btn:hover{background:#d1d5db}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px;
}

/* CARD */
.card{
    background:linear-gradient(180deg,#f9fafb,#f1f5f9);
    padding:22px;
    border-radius:18px;
    box-shadow:0 8px 22px rgba(0,0,0,.08);
}
.card h4{
    font-size:13px;
    color:#6b7280;
    margin-bottom:8px;
    text-transform:uppercase;
}
.card p{
    font-size:17px;
    font-weight:600;
    color:#111827;
}

/* STATUS */
.badge{
    padding:8px 18px;
    border-radius:20px;
    font-size:13px;
    font-weight:700;
}
.pending{background:#fef3c7;color:#92400e;}
.approved{background:#dcfce7;color:#166534;}
.rejected{background:#fee2e2;color:#991b1b;}

.ok{color:#16a34a;font-weight:700;}
.fail{color:#dc2626;font-weight:700;}

/* DOCUMENTS */
.docs{
    margin-top:40px;
}
.docs h3{
    margin-bottom:20px;
    font-size:20px;
    color:#1f2937;
}

/* DOC ROW */
.doc-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px; /* 🔹 space between name & buttons */
    background:linear-gradient(180deg,#f9fafb,#f1f5f9);
    padding:18px 26px;
    border-radius:18px;
    margin-bottom:18px;
    box-shadow:0 12px 28px rgba(0,0,0,.18);
}

/* DOCUMENT NAME */
.doc-name{
    flex:1;               /* 🔹 pushes buttons to right */
    color:black;
    font-size:15px;
    font-weight:700;
    letter-spacing:.3px;
}

/* ACTION BUTTONS */
.doc-actions{
    display:flex;
    gap:14px;
}

/* BUTTON STYLE */
.doc-actions a{
    padding:10px 18px;
    min-width:90px;       /* 🔹 consistent button size */
    text-align:center;
    border-radius:12px;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    color:white;
    background:#2563eb;
    backdrop-filter:blur(6px);
    transition:.25s ease;
}

/* HOVER EFFECT */
.doc-actions a:hover{
    background:rgba(12, 47, 248, 0.67);
    transform:translateY(-2px);
}

/* MOBILE FIX */
@media(max-width:768px){
    .doc-row{
        flex-direction:column;
        align-items:flex-start;
    }
    .doc-actions{
        width:100%;
        justify-content:flex-end;
    }
}


/* MOBILE */
@media(max-width:768px){
    .page-header{flex-direction:column;align-items:flex-start;gap:12px}
    .doc-row{flex-direction:column;align-items:flex-start;gap:14px}
}
</style>
</head>

<body>

<div class="navbar">
    <h2>Loan Details</h2>
    
</div>

<div class="container">

<div class="page-header">
    <h3>Application #<?= htmlspecialchars($r['application_no']) ?></h3>
    <a href="loan_requests.php" class="back-btn">Back</a>
</div>

<div class="grid">

    <div class="card">
        <h4>Customer Name</h4>
        <p><?= htmlspecialchars($r['customer_name']) ?></p>
    </div>

    <div class="card">
        <h4>Email</h4>
        <p><?= htmlspecialchars($r['email']) ?></p>
    </div>

    <div class="card">
        <h4>Loan Type</h4>
        <p><?= htmlspecialchars($r['loan_name']) ?></p>
    </div>

    <div class="card">
        <h4>Loan Amount</h4>
        <p>₹<?= number_format($r['amount'],2) ?></p>
    </div>

    <div class="card">
        <h4>Monthly Salary</h4>
        <p>₹<?= number_format($r['salary'],2) ?></p>
    </div>

    <div class="card">
        <h4>Eligibility (10× Salary)</h4>
        <p>
            ₹<?= number_format($eligibleAmount,2) ?><br>
            <span class="<?= $isEligible ? 'ok' : 'fail' ?>">
                <?= $isEligible ? 'Eligible' : 'Exceeds Limit' ?>
            </span>
        </p>
    </div>

    <div class="card">
        <h4>Status</h4>
        <p><span class="badge <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></p>
    </div>

</div>

<div class="docs">
    <h3>Uploaded Documents</h3>

    <?php if($r['salary_slip']){ ?>
    <div class="doc-row">
        <div class="doc-name">Salary Slip</div>
        <div class="doc-actions">
            <a target="_blank" href="../uploads/loan_docs/<?= htmlspecialchars($r['salary_slip']) ?>">View</a>
            <a download href="../uploads/loan_docs/<?= htmlspecialchars($r['salary_slip']) ?>">Download</a>
        </div>
    </div>
    <?php } ?>

    <?php if($r['bank_statement']){ ?>
    <div class="doc-row">
        <div class="doc-name">Bank Statement</div>
        <div class="doc-actions">
            <a target="_blank" href="../uploads/loan_docs/<?= htmlspecialchars($r['bank_statement']) ?>">View</a>
            <a download href="../uploads/loan_docs/<?= htmlspecialchars($r['bank_statement']) ?>">Download</a>
        </div>
    </div>
    <?php } ?>

    <?php if($r['address_proof']){ ?>
    <div class="doc-row">
        <div class="doc-name">Address Proof</div>
        <div class="doc-actions">
            <a target="_blank" href="../uploads/loan_docs/<?= htmlspecialchars($r['address_proof']) ?>">View</a>
            <a download href="../uploads/loan_docs/<?= htmlspecialchars($r['address_proof']) ?>">Download</a>
        </div>
    </div>
    <?php } ?>

</div>

</div>

</body>
</html>
