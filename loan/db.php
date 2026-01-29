<?php
/* ---------- DATABASE CONFIG ---------- */
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "chit_loan";

/* ---------- DATABASE CONNECTION ---------- */
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

/* ---------- CONNECTION CHECK ---------- */
if ($conn->connect_errno) {
    die("Database Connection Failed: " . $conn->connect_error);
}

/* ---------- SET CHARACTER SET ---------- */
$conn->set_charset("utf8mb4");

/* ---------- SESSION START (SAFE) ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
