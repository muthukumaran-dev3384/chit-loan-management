<?php
include "../db.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $mobile   = trim($_POST['mobile']);
    $salary   = floatval($_POST['salary']);
    $address  = trim($_POST['address']);
    $password = $_POST['password'];
    $cpass    = $_POST['cpassword'];

    /* ---------- VALIDATION ---------- */
    if ($name=="" || $email=="" || $mobile=="" || $salary<=0 || $address=="" || $password=="") {
        $error = "All fields are required";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $error = "Mobile number must be 10 digits";
    }
    elseif ($password !== $cpass) {
        $error = "Passwords do not match";
    }
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    }
    else {

        $check = $conn->prepare("SELECT id FROM customers WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {

            $hash = hash('sha256', $password);

            $stmt = $conn->prepare(
                "INSERT INTO customers
                (name, email, mobile, address, salary, password)
                VALUES (?,?,?,?,?,?)"
            );
            $stmt->bind_param(
                "ssssds",
                $name, $email, $mobile, $address, $salary, $hash
            );

            if ($stmt->execute()) {
                $success = "Registration successful. Please login.";
            } else {
                $error = "Registration failed. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>User Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',system-ui,-apple-system,sans-serif;
}
body{
    min-height:100vh;
    background:
        linear-gradient(135deg,rgba(13,110,253,.9),rgba(25,135,84,.9)),
        url("../images/loan.jpg") center/cover no-repeat;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* CARD */
.register-box{
    background:rgba(255,255,255,.95);
    width:460px;
    padding:35px;
    border-radius:26px;
    box-shadow:0 30px 70px rgba(0,0,0,.45);
}
.register-box h2{
    text-align:center;
    margin-bottom:25px;
    color:#0d6efd;
    font-size:26px;
}

/* GRID */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

/* INPUTS */
.register-box input,
.register-box textarea{
    width:100%;
    padding:14px;
    border:1px solid #d1d5db;
    border-radius:14px;
    font-size:15px;
    background:#f9fafb;
}
.register-box input:focus,
.register-box textarea:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 3px rgba(13,110,253,.15);
}
textarea{
    resize:none;
    height:90px;
    grid-column:1 / -1;
}

/* BUTTON */
button{
    width:100%;
    padding:15px;
    background:linear-gradient(135deg,#0d6efd,#084298);
    color:#fff;
    border:none;
    border-radius:18px;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    margin-top:10px;
}
button:hover{
    opacity:.92;
}

/* MESSAGES */
.error{
    background:#f8d7da;
    color:#842029;
    padding:12px;
    border-radius:14px;
    margin-bottom:16px;
    text-align:center;
    font-size:14px;
}
.success{
    background:#d1e7dd;
    color:#0f5132;
    padding:12px;
    border-radius:14px;
    margin-bottom:16px;
    text-align:center;
    font-size:14px;
}

/* FOOTER */
.register-box p{
    text-align:center;
    margin-top:18px;
    font-size:14px;
}
.register-box a{
    color:#0d6efd;
    text-decoration:none;
    font-weight:700;
}

/* MOBILE */
@media(max-width:520px){
    .register-box{width:95%;}
    .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<div class="register-box">
<h2>User Registration</h2>

<?php if($error){ ?><div class="error"><?= htmlspecialchars($error) ?></div><?php } ?>
<?php if($success){ ?><div class="success"><?= htmlspecialchars($success) ?></div><?php } ?>

<form method="post">

<div class="form-grid">
    <input name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>

    <input name="mobile" placeholder="Mobile Number" required>
    <input type="number" name="salary" placeholder="Monthly Salary" required>

    <textarea name="address" placeholder="Full Address" required></textarea>

    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="cpassword" placeholder="Confirm Password" required>
</div>

<button name="register">Create Account</button>
</form>

<p>
Already have an account?
<a href="login.php">Login Here</a>
</p>
</div>

</body>
</html>
