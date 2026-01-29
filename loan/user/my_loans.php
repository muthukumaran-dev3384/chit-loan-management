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
$stmt = $conn->prepare("SELECT id, name FROM customers WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$uid = $user['id'];

/* ---------- FETCH LOANS ---------- */
$stmt = $conn->prepare("
    SELECT 
        l.id,
        l.application_no,
        lt.loan_name,
        lt.interest_rate,
        l.amount,
        l.status,
        l.applied_date
    FROM loans l
    JOIN loan_types lt ON lt.id = l.loan_type_id
    WHERE l.customer_id=?
    ORDER BY l.id DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$loans = $stmt->get_result();

/* ---------- COUNTS ---------- */
function getCount($conn,$uid,$status=null){
    if($status){
        $q=$conn->prepare("SELECT COUNT(*) c FROM loans WHERE customer_id=? AND status=?");
        $q->bind_param("is",$uid,$status);
    } else {
        $q=$conn->prepare("SELECT COUNT(*) c FROM loans WHERE customer_id=?");
        $q->bind_param("i",$uid);
    }
    $q->execute();
    return $q->get_result()->fetch_assoc()['c'] ?? 0;
}

$total    = getCount($conn,$uid);
$pending  = getCount($conn,$uid,'Pending');
$approved = getCount($conn,$uid,'Approved');
?>

<!DOCTYPE html>
<html>
<head>
<title>My Loans</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
   background: url("../images/ba.jpg");
    min-height:100vh;
}

/* HEADER */
.topbar{
    background:#0d6efd;
    color:#fff;
    padding:18px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 6px 18px rgba(0,0,0,0.2);
}
.topbar h2{font-size:20px;}
.topbar span{opacity:0.9;font-size:14px;}

/* CONTAINER */
.container{
    padding:30px;
}

/* STATS */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-bottom:30px;
}
.card{
    background:rgba(255,255,255,0.95);
    border-radius:16px;
    padding:22px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
}
.card h3{
    font-size:15px;
    color:#555;
}
.card p{
    font-size:28px;
    font-weight:700;
    margin-top:10px;
    color:#0d6efd;
}

/* TABLE */
.table-box{
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
}
table{
    width:100%;
    border-collapse:collapse;
}
th{
    background:#0d6efd;
    color:#fff;
    padding:14px;
    font-size:14px;
}
td{
    padding:14px;
    text-align:center;
    border-bottom:1px solid #eee;
    font-size:14px;
}
tr:hover{background:#f1f6ff;}

/* STATUS */
.status{
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}
.Pending{background:#fff3cd;color:#856404;}
.Approved{background:#d1e7dd;color:#0f5132;}
.Rejected{background:#f8d7da;color:#842029;}

/* BUTTON */
.btn{
    padding:7px 14px;
    border-radius:8px;
    font-size:13px;
    text-decoration:none;
    color:#fff;
}
.btn-view{background:#198754;}
.btn-view:hover{opacity:0.85;}

/* BACK */
.back-btn{
    display:inline-block;
    margin-bottom:20px;
    background:#6c757d;
    color:#fff;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
}
.back-btn:hover{background:#5a6268;}

/* EMPTY */
.no-data{
    text-align:center;
    padding:50px;
    color:#777;
    font-size:15px;
}

/* FOOTER */
footer{
    margin-top:40px;
    text-align:center;
    color:#777;
    font-size:13px;
}
</style>
</head>

<body>

<div class="topbar">
    <h2>My Loan Applications</h2>
    <span>Welcome, <?= htmlspecialchars($user['name']) ?></span>
</div>

<div class="container">

<a href="dashboard.php" class="back-btn">← Back to Dashboard</a>


<!-- TABLE -->
<div class="table-box">
<?php if($loans->num_rows>0){ ?>
<table>
<tr>
    <th>#</th>
    <th>Application No</th>
    <th>Loan Type</th>
    <th>Interest</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Applied Date</th>
    <th>Action</th>
</tr>

<?php $i=1; while($r=$loans->fetch_assoc()){ ?>
<tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($r['application_no']) ?></td>
    <td><?= htmlspecialchars($r['loan_name']) ?></td>
    <td><?= $r['interest_rate'] ?>%</td>
    <td>₹<?= number_format($r['amount'],2) ?></td>
    <td><span class="status <?= $r['status'] ?>"><?= $r['status'] ?></span></td>
    <td><?= date("d M Y",strtotime($r['applied_date'])) ?></td>
    <td>
        <a class="btn btn-view" href="../loan_track.php?app=<?= urlencode($r['application_no']) ?>">Track</a>
    </td>
</tr>
<?php } ?>
</table>
<?php } else { ?>
<div class="no-data">You have not applied for any loans yet.</div>
<?php } ?>
</div>

<footer>
© <?= date("Y") ?> Online Chit & Finance Loan Tracking System
</footer>

</div>
</body>
</html>
