<?php
session_start();
include "../db.php";

/* ---------- APPROVE / REJECT ---------- */
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE loans SET status='Approved', approved_date=NOW() WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: loan_requests.php");
    exit();
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    $stmt = $conn->prepare("UPDATE loans SET status='Rejected', updated_at=NOW() WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: loan_requests.php");
    exit();
}

/* ---------- SEARCH ---------- */
$search = trim($_GET['search'] ?? "");

/* ---------- FETCH LOAN REQUESTS ---------- */
$sql = "
SELECT 
    l.*, 
    c.name AS customer_name, c.salary AS customer_salary,
    t.loan_name, t.loan_amount
FROM loans l
JOIN customers c ON c.id = l.customer_id
JOIN loan_types t ON t.id = l.loan_type_id
";

if ($search !== "") {
    $sql .= " WHERE l.application_no LIKE '%$search%' OR c.name LIKE '%$search%'";
}

$sql .= " ORDER BY l.id DESC";
$q = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin | Loan Requests</title>
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


/* HEADER */
.header{
    background:linear-gradient(90deg,#1e3a8a,#2563eb);
    padding:18px 35px;
    color:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}
.header h2{font-size:22px;}
.header a{
    color:#fff;
    text-decoration:none;
    font-size:14px;
    opacity:.9;
}
.header a:hover{opacity:1}

/* CONTAINER */
.container{
    max-width:1400px;
    margin:35px auto;
    padding:0 20px;
}

/* TOP BAR */
.top-bar{
    background:#fff;
    padding:20px 25px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}
.top-bar a{
    background:#111827;
    padding:10px 18px;
    border-radius:10px;
    text-decoration:none;
    color:white;
    font-size:14px;
    font-weight:600;
}
.top-bar a:hover{background:linear-gradient(135deg,#2563eb,#1e40af)}

/* SEARCH */
.search-box{
    display:flex;
    gap:10px;
}
.search-box input{
    padding:11px 16px;
    width:260px;
    border-radius:12px;
    border:1px solid #d1d5db;
    font-size:14px;
    outline:none;
}
.search-box input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}

/* TABLE */
.table-wrap{
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 12px 35px rgba(0,0,0,.1);
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:16px;
    text-align:center;
    font-size:14px;
    border-bottom:1px solid #f1f5f9;
}
th{
    background:#2563eb;
    color:#fff;
    font-size:15px;
}
tr:hover{background:#f8fafc}

/* BADGES */
.badge{
    padding:7px 16px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
}
.pending{background:#fef3c7;color:#92400e}
.approved{background:#dcfce7;color:#166534}
.rejected{background:#fee2e2;color:#991b1b}

/* BUTTONS */
.btn{
    padding:8px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:600;
    text-decoration:none;
    color:#fff;
    display:inline-block;
    margin:3px;
}
.btn-view{background:#2563eb}
.btn-approve{background:#16a34a}
.btn-reject{background:#dc2626}
.btn:hover{opacity:.9}

/* WARNING */
.warning{
    color:#dc2626;
    font-size:12px;
    font-weight:600;
}

/* EMPTY */
.empty{
    padding:40px;
    color:#6b7280;
    font-size:15px;
}

/* MOBILE */
@media(max-width:900px){
    .top-bar{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }
    .search-box input{width:100%}
}
</style>
</head>

<body>

<div class="header">
    <h2>Loan Requests</h2>
</div>

<div class="container">

<div class="top-bar">
    <a href="dashboard.php">Back</a>
    <form class="search-box" method="get">
        <input type="text" name="search" placeholder="Search Application No / Name"
               value="<?= htmlspecialchars($search) ?>">
    </form>
</div>

<div class="table-wrap">
<table>
<tr>
    <th>Application No</th>
    <th>Customer</th>
    <th>Loan Type</th>
    <th>Loan Amount</th>
    <th>Salary</th>
    <th>Eligibility</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php if($q->num_rows==0){ ?>
<tr><td colspan="8" class="empty">No loan requests found</td></tr>
<?php } ?>

<?php while($r=$q->fetch_assoc()):
    $eligibleAmount = $r['customer_salary'] * 10;
    $eligible = $r['amount'] <= $eligibleAmount;
?>
<tr>
    <td><?= htmlspecialchars($r['application_no']) ?></td>
    <td><?= htmlspecialchars($r['customer_name']) ?></td>
    <td><?= htmlspecialchars($r['loan_name']) ?></td>
    <td>₹<?= number_format($r['amount'],2) ?></td>
    <td>₹<?= number_format($r['customer_salary'],2) ?></td>
    <td>
        ₹<?= number_format($eligibleAmount,2) ?>
        <?php if(!$eligible){ ?><br><span class="warning">Exceeds</span><?php } ?>
    </td>
    <td>
        <span class="badge <?= strtolower($r['status']) ?>">
            <?= $r['status'] ?>
        </span>
    </td>
    <td>
        <a class="btn btn-view" href="view_loan.php?id=<?= $r['id'] ?>">View</a>

        <?php if($r['status']=="Pending"){ ?>
            <a class="btn btn-approve"
               onclick="return confirm('Approve this loan?')"
               href="?approve=<?= $r['id'] ?>">Approve</a>

            <a class="btn btn-reject"
               onclick="return confirm('Reject this loan?')"
               href="?reject=<?= $r['id'] ?>">Reject</a>
        <?php } ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>

</div>

</body>
</html>
