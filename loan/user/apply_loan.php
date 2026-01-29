<?php
session_start();
include "../db.php";

/* ---------- LOGIN CHECK ---------- */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];
$error = $success = $appNo = "";

/* ---------- FETCH CUSTOMER ---------- */
$cq = $conn->prepare("SELECT id, name, salary FROM customers WHERE email=?");
$cq->bind_param("s", $email);
$cq->execute();
$cust = $cq->get_result()->fetch_assoc();
if (!$cust) die("Invalid user");

/* ---------- CHECK ACTIVE LOAN ---------- */
$chk = $conn->prepare("
    SELECT id FROM loans 
    WHERE customer_id=? AND status IN ('Pending','Approved')
");
$chk->bind_param("i", $cust['id']);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    die("<h2 style='text-align:center;color:red;margin-top:60px'>
         You already have an active loan.</h2>");
}

/* ---------- FETCH LOAN TYPE ---------- */
$loanId = intval($_GET['id'] ?? 0);
$lq = $conn->prepare("
    SELECT loan_name, interest_rate, tenure, loan_amount
    FROM loan_types WHERE id=? AND status='Active'
");
$lq->bind_param("i", $loanId);
$lq->execute();
$loan = $lq->get_result()->fetch_assoc();
if (!$loan) die("Invalid loan type");

/* ---------- ELIGIBILITY ---------- */
$salaryLimit = $cust['salary'] * 10;
$loanLimit   = $loan['loan_amount'];
$maxAllowed  = min($salaryLimit, $loanLimit);

/* ---------- FILE UPLOAD ---------- */
function uploadFile($file, $prefix) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return false;
    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $dir = "../uploads/loan_docs/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $name = $prefix."_".uniqid().".".$ext;
    return move_uploaded_file($file['tmp_name'], $dir.$name) ? $name : false;
}

/* ---------- APPLY LOAN ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['amount']) || empty($_POST['aadhaar'])) {
        $error = "Form submission failed. Please upload smaller files (≤5MB).";
    } else {

        $amount  = floatval($_POST['amount']);
        $aadhaar = trim($_POST['aadhaar']);

        if ($amount <= 0 || $amount > $maxAllowed) {
            $error = "Loan amount must be within ₹".number_format($maxAllowed);
        }
        elseif (!preg_match("/^[0-9]{12}$/", $aadhaar)) {
            $error = "Invalid Aadhaar number";
        }
        else {

            $salarySlip = uploadFile($_FILES['salary_slip'], "salary");
            $bankStmt   = uploadFile($_FILES['bank_statement'], "bank");
            $address    = uploadFile($_FILES['address_proof'], "address");

            if (!$salarySlip || !$bankStmt || !$address) {
                $error = "Invalid document upload (PDF/JPG/PNG, max 5MB each)";
            } else {

                $appNo = "APP-".date("Y")."-".rand(100000,999999);

                $ins = $conn->prepare("
                    INSERT INTO loans
                    (application_no, customer_id, loan_type_id, amount,
                     aadhaar_no, salary_slip, bank_statement, address_proof,
                     salary, status, applied_date)
                    VALUES (?,?,?,?,?,?,?,?,?,'Pending',CURDATE())
                ");

                $ins->bind_param(
                    "siidssssd",
                    $appNo,
                    $cust['id'],
                    $loanId,
                    $amount,
                    $aadhaar,
                    $salarySlip,
                    $bankStmt,
                    $address,
                    $cust['salary']
                );

                if ($ins->execute()) {
                    $success = "Loan applied successfully";
                } else {
                    $error = "Database error occurred";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Apply Loan</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{box-sizing:border-box;font-family:'Segoe UI',Arial}
body{
    margin:0;
  background: url("../images/ba.jpg");
}

/* BACK */
.back-btn{
    position:fixed;
    top:20px;
    left:20px;
    background:#1f2937;
    color:#fff;
    padding:10px 18px;
    border-radius:14px;
    border:none;
    font-size:14px;
    cursor:pointer;
    box-shadow:0 10px 25px rgba(0,0,0,.3);
}
.back-btn:hover{background:#111827}

/* HEADER */
.header{
    background:linear-gradient(135deg,#2563eb,#1e3a8a);
    padding:45px 20px;
    color:#fff;
    text-align:center;
    font-size:28px;
    font-weight:600;
}

/* CONTAINER */
.container{
    max-width:980px;
    margin:-40px auto 40px;
    background:rgba(255,255,255,.95);
    padding:45px;
    border-radius:26px;
    box-shadow:0 25px 55px rgba(0,0,0,.18);
    backdrop-filter:blur(8px);
}

/* INFO */
.loan-box{
    background:linear-gradient(135deg,#eef3ff,#ffffff);
    padding:22px;
    border-radius:18px;
    margin-bottom:28px;
    font-size:15px;
    line-height:1.8;
}

/* FORM */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
}
.field label{
    font-weight:600;
    margin-bottom:8px;
    display:block;
}
.field input{
    width:100%;
    padding:15px;
    border-radius:14px;
    border:1px solid #cbd5f5;
    font-size:15px;
}
.field input:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.2);
}
input[readonly]{background:#f1f5f9}

/* EMI */
.emi{
    background:linear-gradient(135deg,#e0ecff,#f8fafc);
    padding:20px;
    border-radius:16px;
    margin:30px 0;
    font-weight:700;
    text-align:center;
    color:#1e3a8a;
}

/* BUTTON */
button.submit{
    width:100%;
    padding:16px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    border:none;
    border-radius:18px;
    font-size:17px;
    font-weight:600;
    cursor:pointer;
}
button.submit:hover{opacity:.9}

/* MESSAGE */
.msg{
    text-align:center;
    padding:16px;
    border-radius:16px;
    margin-bottom:22px;
    font-weight:600;
}
.error{background:#fee2e2;color:#991b1b}
.success{background:#dcfce7;color:#14532d}

@media(max-width:768px){
    .form-grid{grid-template-columns:1fr}
    .container{padding:30px}
}
</style>

<script>
const MAX = <?= $maxAllowed ?>;
const RATE = <?= $loan['interest_rate'] ?> / 12 / 100;
const TENURE = <?= $loan['tenure'] ?>;

function calcEMI(){
    let a=document.getElementById("amount");
    let v=parseFloat(a.value||0);
    if(v>MAX){v=MAX;a.value=MAX;}
    if(v>0){
        let emi=(v*RATE*Math.pow(1+RATE,TENURE))/(Math.pow(1+RATE,TENURE)-1);
        document.getElementById("emi").innerHTML="Estimated EMI: ₹"+Math.round(emi);
    }
}
</script>
</head>

<body>

<button class="back-btn" onclick="history.back()">← Back</button>

<div class="header">Apply Loan</div>

<div class="container">

<div class="loan-box">
<b>Loan:</b> <?= htmlspecialchars($loan['loan_name']) ?><br>
<b>Interest:</b> <?= $loan['interest_rate'] ?>% |
<b>Tenure:</b> <?= $loan['tenure'] ?> months |
<b>Max Eligible:</b> ₹<?= number_format($maxAllowed) ?>
</div>

<?php if($error){?><div class="msg error"><?= $error ?></div><?php }?>
<?php if($success){?><div class="msg success"><?= $success ?><br><b><?= $appNo ?></b></div><?php }?>

<form method="post" enctype="multipart/form-data">

<div class="form-grid">
    <div class="field">
        <label>Salary</label>
        <input value="<?= $cust['salary'] ?>" readonly>
    </div>

    <div class="field">
        <label>Loan Amount</label>
        <input id="amount" name="amount" oninput="calcEMI()" required>
    </div>

    <div class="field">
        <label>Aadhaar Number</label>
        <input name="aadhaar" maxlength="12" required>
    </div>

    <div class="field">
        <label>Salary Slip</label>
        <input type="file" name="salary_slip" required>
    </div>

    <div class="field">
        <label>Bank Statement</label>
        <input type="file" name="bank_statement" required>
    </div>

    <div class="field">
        <label>Address Proof</label>
        <input type="file" name="address_proof" required>
    </div>
</div>

<div class="emi" id="emi">Estimated EMI: ₹0</div>

<button type="submit" class="submit">Submit Loan Application</button>

</form>
</div>

</body>
</html>
