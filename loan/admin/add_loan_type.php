<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

/* ---------- ADD LOAN TYPE ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name   = trim($_POST['loan_name']);
    $amount = floatval($_POST['loan_amount']);
    $rate   = floatval($_POST['interest_rate']);
    $tenure = intval($_POST['tenure']);
    $fee    = floatval($_POST['processing_fee']);
    $multi  = intval($_POST['multiplier']);

    if ($name=="" || $amount<=0 || $rate<=0 || $tenure<=0 || $multi<=0) {
        $error = "Please fill all required fields correctly.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO loan_types
            (loan_name, loan_amount, interest_rate, tenure, processing_fee, eligibility_multiplier)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("sddidi", $name, $amount, $rate, $tenure, $fee, $multi);

        if ($stmt->execute()) {
            $success = "Loan type added successfully.";
        } else {
            $error = "Database error. Unable to add loan type.";
        }
    }
}

/* ---------- FETCH LOAN TYPES ---------- */
$loanTypes = $conn->query("SELECT * FROM loan_types ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin | Loan Types</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif;}
body{
      background: url("../images/loan.jpg") no-repeat center center fixed;
    background-size: cover;
}

/* MAIN */
.main{
    max-width:1200px;
    margin:30px auto;
    padding:20px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}
.header h2{
    font-size:26px;
    font-weight:800;
    color:white;
}
.back-btn{
    background:#111827;
    color:#fff;
    padding:10px 18px;
    border-radius:12px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}
.back-btn:hover{background:#1f2937;}

/* BOX */
.box{
    background:rgba(255,255,255,.9);
    backdrop-filter:blur(10px);
    padding:30px;
    border-radius:20px;
    box-shadow:0 20px 45px rgba(0,0,0,.12);
    margin-bottom:35px;
}

/* FORM */
.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}
input{
    width:100%;
    padding:14px 16px;
    border-radius:12px;
    border:1px solid #c7d2fe;
    font-size:14px;
    outline:none;
}
input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}
button{
    margin-top:20px;
    padding:14px 34px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    border:none;
    border-radius:14px;
    cursor:pointer;
    font-size:15px;
    font-weight:700;
}
button:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 28px rgba(37,99,235,.4);
}

/* ALERTS */
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
}
.success{
    background:#dcfce7;
    color:#166534;
    padding:14px;
    border-radius:12px;
    margin-bottom:18px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 18px 40px rgba(0,0,0,.15);
}
th,td{
    padding:16px 14px;
    text-align:center;
    border-bottom:1px solid #eee;
    font-size:14px;
}
th{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    font-weight:700;
}
tr:hover{background:#f1f5f9;}

.actions a{
    padding:8px 14px;
    border-radius:10px;
    color:#fff;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    margin:2px;
    display:inline-block;
}
.edit{background:#16a34a;}
.delete{background:#dc2626;}
.actions a:hover{opacity:.9}

/* MOBILE */
@media(max-width:768px){
    .header{flex-direction:column;gap:15px;align-items:flex-start;}
}
</style>
</head>

<body>

<div class="main">

    <div class="header">
        <h2>Manage Loan Types</h2>
        <a href="dashboard.php" class="back-btn">← Back</a>
    </div>

    <div class="box">
        <?php if($error){ ?><div class="error"><?= $error ?></div><?php } ?>
        <?php if($success){ ?><div class="success"><?= $success ?></div><?php } ?>

        <form method="post">
            <div class="form-grid">
                <input name="loan_name" placeholder="Loan Name" required>
                <input name="loan_amount" placeholder="Maximum Loan Amount" type="number" step="0.01" required>
                <input name="interest_rate" placeholder="Interest Rate (%)" type="number" step="0.01" required>
                <input name="tenure" placeholder="Tenure (Months)" type="number" required>
                <input name="processing_fee" placeholder="Processing Fee (%)" type="number" step="0.01">
                <input name="multiplier" value="10" placeholder="Eligibility Multiplier" type="number" required>
            </div>
            <button type="submit">Add Loan Type</button>
        </form>
    </div>

    <table>
        <tr>
            <th>#</th>
            <th>Loan</th>
            <th>Amount</th>
            <th>Interest</th>
            <th>Tenure</th>
            <th>Fee</th>
            <th>Multiplier</th>
            <th>Action</th>
        </tr>

        <?php $i=1; while($r=$loanTypes->fetch_assoc()){ ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($r['loan_name']) ?></td>
            <td>₹<?= number_format($r['loan_amount'],2) ?></td>
            <td><?= $r['interest_rate'] ?>%</td>
            <td><?= $r['tenure'] ?> m</td>
            <td><?= $r['processing_fee'] ?>%</td>
            <td><?= $r['eligibility_multiplier'] ?>×</td>
            <td class="actions">
                <a href="edit_loan_type.php?id=<?= $r['id'] ?>" class="edit">Edit</a>
                <a href="delete_loan_type.php?id=<?= $r['id'] ?>" class="delete"
                   onclick="return confirm('Delete this loan type?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
