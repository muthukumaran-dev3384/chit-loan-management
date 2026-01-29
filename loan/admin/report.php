<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* ---------- FILTER ---------- */
$statusFilter = $_GET['status'] ?? "";

/* ---------- SUMMARY COUNTS ---------- */
function countRow($conn, $sql){
    return mysqli_fetch_assoc(mysqli_query($conn,$sql))['c'] ?? 0;
}

$totalLoans    = countRow($conn,"SELECT COUNT(*) c FROM loans");
$pendingLoans  = countRow($conn,"SELECT COUNT(*) c FROM loans WHERE status='Pending'");
$approvedLoans = countRow($conn,"SELECT COUNT(*) c FROM loans WHERE status='Approved'");
$rejectedLoans = countRow($conn,"SELECT COUNT(*) c FROM loans WHERE status='Rejected'");

/* ---------- FETCH REPORT DATA ---------- */
$sql = "
    SELECT 
        l.application_no,
        l.amount,
        l.status,
        l.applied_date,
        l.updated_at,
        c.name   AS customer,
        c.email  AS email,
        c.mobile AS mobile,
        t.loan_name
    FROM loans l
    JOIN customers c ON c.id = l.customer_id
    JOIN loan_types t ON t.id = l.loan_type_id
";

if ($statusFilter != "") {
    $sql .= " WHERE l.status='".mysqli_real_escape_string($conn,$statusFilter)."'";
}

$sql .= " ORDER BY l.id DESC";
$data = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Loan Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
body{
     background: url("../images/loan.jpg") no-repeat center center fixed;
    background-size: cover;
}

/* MAIN */
.main{
    padding:30px;
}

/* TOP BAR */
.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}
.top-bar a{
    background:#111827;
    color:#fff;
    padding:10px 18px;
    border-radius:12px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}
.top-bar a:hover{background:#1f2937;}

.header h2{
    font-size:24px;
    font-weight:700;
    color:#1f2937;
    margin-bottom:25px;
}

/* SUMMARY CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}
.card{
    background:rgba(255,255,255,.85);
    backdrop-filter:blur(10px);
    padding:26px;
    border-radius:18px;
    box-shadow:0 15px 35px rgba(0,0,0,.12);
    text-align:center;
}
.card h3{
    font-size:14px;
    color:#6b7280;
    margin-bottom:8px;
}
.card h1{
    font-size:34px;
    font-weight:800;
    color:#2563eb;
}

/* FILTER */
.filter{
    margin-bottom:18px;
}
.filter select{
    padding:12px 18px;
    border-radius:12px;
    border:1px solid #c7d2fe;
    font-size:14px;
    outline:none;
}

/* TABLE */
.table-wrap{
    overflow-x:auto;
}
table{
    width:100%;
    background:#fff;
    border-collapse:collapse;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.15);
}
th,td{
    padding:15px 14px;
    border-bottom:1px solid #eee;
    text-align:center;
    font-size:14px;
}
th{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    font-weight:700;
}
tr:hover{background:#f1f5f9;}

.badge{
    padding:6px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:700;
}
.pending{background:#fff3cd;color:#664d03;}
.approved{background:#d1e7dd;color:#0f5132;}
.rejected{background:#f8d7da;color:#842029;}

.empty{
    padding:30px;
    text-align:center;
    background:#fff;
    border-radius:18px;
    margin-top:25px;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
    font-size:15px;
}

/* MOBILE */
@media(max-width:900px){
    th,td{font-size:13px;}
}
</style>
</head>

<body>

<div class="main">

<div class="top-bar">
    <a href="dashboard.php">← Back to Dashboard</a>
</div>

<div class="header">
    <h2>Loan Reports</h2>
</div>

<!-- SUMMARY -->
<div class="cards">
    <div class="card"><h3>Total Loans</h3><h1><?= $totalLoans ?></h1></div>
    <div class="card"><h3>Pending</h3><h1><?= $pendingLoans ?></h1></div>
    <div class="card"><h3>Approved</h3><h1><?= $approvedLoans ?></h1></div>
    <div class="card"><h3>Rejected</h3><h1><?= $rejectedLoans ?></h1></div>
</div>

<!-- FILTER -->
<div class="filter">
<form method="get">
<select name="status" onchange="this.form.submit()">
    <option value="">All Status</option>
    <option value="Pending" <?= $statusFilter=="Pending"?'selected':'' ?>>Pending</option>
    <option value="Approved" <?= $statusFilter=="Approved"?'selected':'' ?>>Approved</option>
    <option value="Rejected" <?= $statusFilter=="Rejected"?'selected':'' ?>>Rejected</option>
</select>
</form>
</div>

<!-- TABLE -->
<?php if($data->num_rows > 0){ ?>
<div class="table-wrap">
<table>
<tr>
    <th>Application No</th>
    <th>Customer Name</th>
    <th>Email</th>
    <th>Mobile</th>
    <th>Loan Type</th>
    <th>Amount</th>
    <th>Status</th>
    <th>Applied Date</th>
    <th>Last Update</th>
</tr>

<?php while($r=$data->fetch_assoc()){ ?>
<tr>
    <td><?= htmlspecialchars($r['application_no']) ?></td>
    <td><?= htmlspecialchars($r['customer']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td><?= htmlspecialchars($r['mobile']) ?></td>
    <td><?= htmlspecialchars($r['loan_name']) ?></td>
    <td>₹<?= number_format($r['amount'],2) ?></td>
    <td><span class="badge <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
    <td><?= date("d M Y",strtotime($r['applied_date'])) ?></td>
    <td><?= $r['updated_at'] ? date("d M Y H:i",strtotime($r['updated_at'])) : '-' ?></td>
</tr>
<?php } ?>
</table>
</div>
<?php } else { ?>
<div class="empty">No records found</div>
<?php } ?>

</div>
</body>
</html>
