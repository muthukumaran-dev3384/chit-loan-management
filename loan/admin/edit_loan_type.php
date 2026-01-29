<?php
session_start();
include "../db.php";

/* ---------- AUTH CHECK ---------- */
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* ---------- GET ID ---------- */
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: add_loan_type.php");
    exit();
}

/* ---------- FETCH LOAN TYPE ---------- */
$stmt = $conn->prepare("SELECT * FROM loan_types WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

if (!$loan) {
    die("Loan type not found");
}

$error = "";
$success = "";

/* ---------- UPDATE LOAN TYPE ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['loan_name']);
    $amount = floatval($_POST['loan_amount']);
    $rate   = floatval($_POST['interest_rate']);
    $tenure = intval($_POST['tenure']);
    $fee    = floatval($_POST['processing_fee']);
    $multi  = intval($_POST['multiplier']);

    if ($name=="" || $amount<=0 || $rate<=0 || $tenure<=0 || $multi<=0) {
        $error = "Please fill all required fields correctly.";
    } else {

        $update = $conn->prepare("
            UPDATE loan_types SET
                loan_name=?,
                loan_amount=?,
                interest_rate=?,
                tenure=?,
                processing_fee=?,
                eligibility_multiplier=?
            WHERE id=?
        ");
        $update->bind_param(
            "sddidii",
            $name,
            $amount,
            $rate,
            $tenure,
            $fee,
            $multi,
            $id
        );

        if ($update->execute()) {
            $success = "Loan type updated successfully.";
        } else {
            $error = "Failed to update loan type.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Loan Type</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',Arial,sans-serif;
}
body{
    min-height:100vh;
    background: url("../images/loan.jpg") no-repeat center center fixed;
    background-size: cover;
}

/* MAIN */
.main{
    max-width:1100px;
    margin:40px auto;
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
.back{
    background:#111827;
    color:white;
    padding:10px 18px;
    border-radius:12px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}
.back:hover{background:#1f2937;}

/* FORM BOX */
.box{
    background:rgba(255,255,255,.9);
    backdrop-filter:blur(10px);
    padding:35px;
    border-radius:22px;
    box-shadow:0 20px 45px rgba(0,0,0,.15);
    max-width:900px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:18px;
}

/* INPUTS */
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

/* BUTTON */
button{
    margin-top:25px;
    padding:14px 36px;
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
    box-shadow:0 12px 28px rgba(37,99,235,.45);
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

/* MOBILE */
@media(max-width:768px){
    .header{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }
    .box{padding:25px}
}
</style>
</head>

<body>

<div class="main">

    <div class="header">
        <h2>Edit Loan Type</h2>
        <a href="add_loan_type.php" class="back">← Back</a>
    </div>

    <div class="box">

        <?php if($error){ ?><div class="error"><?= $error ?></div><?php } ?>
        <?php if($success){ ?><div class="success"><?= $success ?></div><?php } ?>

        <form method="post">
            <div class="grid">
                <input name="loan_name" value="<?= htmlspecialchars($loan['loan_name']) ?>" required>
                <input name="loan_amount" type="number" step="0.01"
                       value="<?= $loan['loan_amount'] ?>" required>
                <input name="interest_rate" type="number" step="0.01"
                       value="<?= $loan['interest_rate'] ?>" required>
                <input name="tenure" type="number"
                       value="<?= $loan['tenure'] ?>" required>
                <input name="processing_fee" type="number" step="0.01"
                       value="<?= $loan['processing_fee'] ?>">
                <input name="multiplier" type="number"
                       value="<?= $loan['eligibility_multiplier'] ?>" required>
            </div>

            <button type="submit">Update Loan Type</button>
        </form>

    </div>

</div>

</body>
</html>
