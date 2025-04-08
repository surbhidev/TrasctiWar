<?php
ob_start(); 
session_start();
// Secure session handling
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true); // Prevent session fixation
    $_SESSION['initialized'] = true;
}
include "layout/header.php";
// Redirect logged-in users
if (isset($_SESSION["email"])) {
    header("Location: /index.php");
    exit;
}

require "tools/db.php"; // Secure PDO connection function

$username = $first_name = $last_name = $email = $phone = "";
$username_error = $first_name_error = $last_name_error = $email_error = $phone_error = $password_error = $confirm_password_error = "";
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Securely sanitize inputs
    $username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
    $first_name = trim(htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $last_name = trim(htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $phone = trim(htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        $pdo = getDatabaseConnection(); // Secure PDO connection
        
        // Validate username
        if (empty($username)) {
            $username_error = "Username is required";
            $error = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $username_error = "Username is already taken";
                $error = true;
            }
        }

        // Validate first and last names
        if (empty($first_name)) {
            $first_name_error = "First name is required";
            $error = true;
        }
        if (empty($last_name)) {
            $last_name_error = "Last name is required";
            $error = true;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_error = "Invalid email format";
            $error = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $email_error = "Email is already registered";
                $error = true;
            }
        }

        // Validate phone (international format)
        if (!preg_match("/^(\+|00\d{1,3})?[- ]?\d{7,12}$/", $phone)) {
            $phone_error = "Invalid phone number format";
            $error = true;
        }

        // Validate password security
        if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            $password_error = "Password must be at least 8 characters long, include an uppercase letter and a number.";
            $error = true;
        }

        // Confirm password match
        if ($confirm_password !== $password) {
            $confirm_password_error = "Passwords do not match";
            $error = true;
        }

        // If no validation errors, insert user data
        if (!$error) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $created_at = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "INSERT INTO users (username, first_name, last_name, email, phone, password, created_at) 
                 VALUES (:username, :first_name, :last_name, :email, :phone, :password, :created_at)"
            );
            $stmt->execute([
                'username'   => $username,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'phone'      => $phone,
                'password'   => $hashed_password,
                'created_at' => $created_at
            ]);

            $insert_id = $pdo->lastInsertId();

            // Securely store session data
            $_SESSION["id"] = $insert_id;
            $_SESSION["username"] = htmlspecialchars($username);
            $_SESSION["first_name"] = htmlspecialchars($first_name);
            $_SESSION["last_name"] = htmlspecialchars($last_name);
            $_SESSION["email"] = htmlspecialchars($email);
            $_SESSION["phone"] = htmlspecialchars($phone);
            $_SESSION["created_at"] = $created_at;

            header("Location: /index.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Database error: " . htmlspecialchars($e->getMessage())); // Prevent exposing DB errors
    }
}
?>

<!-- Registration Form (Layout unchanged) -->
<div class="container py-5">
    <div class="row">
        <div class="col-lg-6 mx-auto border shadow p-4">
            <h2 class="text-center mb-4">Register</h2>
            <hr />

            <form method="post">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Username*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="username" value="<?= htmlspecialchars($username) ?>">
                        <span class="text-danger"><?= $username_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">First Name*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>">
                        <span class="text-danger"><?= $first_name_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Last Name*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>">
                        <span class="text-danger"><?= $last_name_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Email*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="email" value="<?= htmlspecialchars($email) ?>">
                        <span class="text-danger"><?= $email_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Phone No.*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>">
                        <span class="text-danger"><?= $phone_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Password*</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="password" name="password">
                        <span class="text-danger"><?= $password_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Confirm Password*</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="password" name="confirm_password">
                        <span class="text-danger"><?= $confirm_password_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="offset-sm-4 col-sm d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                    <div class="col-sm-4 d-grid">
                        <a href="./index.php" class="btn btn-outline-primary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
