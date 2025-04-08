<?php
ob_start(); 
include "layout/header.php";
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect if already logged in
if (isset($_SESSION["email"])) {
    header("location: /index.php");
    exit;
}

$email = "";
$username = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Username, Email, and Password are required";
    } else {
        include "tools/db.php";
        $dbConnection = getDatabaseConnection();

        $statement = $dbConnection->prepare(
            "SELECT id, first_name, last_name, username, phone, password, created_at FROM users WHERE email = :email"
        );

        if ($statement) {
            // ✅ Correct way to bind parameters in PDO
            $statement->bindValue(':email', $email, PDO::PARAM_STR);
            $statement->execute();
            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    // ✅ Store session data
                    $_SESSION["id"] = $user['id'];
                    $_SESSION["username"] = $user['username'];
                    $_SESSION["first_name"] = $user['first_name'];
                    $_SESSION["last_name"] = $user['last_name'];
                    $_SESSION["email"] = $email;
                    $_SESSION["phone"] = $user['phone'];
                    $_SESSION["created_at"] = $user['created_at'];

                    header("location: /index.php");
                    exit;
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}

?>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="border shadow-lg p-4 rounded bg-white" style="width: 350px;">
        <h2 class="text-center mb-4">Login</h2>

        <!-- Display error message -->
        <?php if (!empty($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><?= htmlspecialchars($error) ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <form method="post">
            <!-- Username field -->
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" type="text" name="username" value="<?= htmlspecialchars($username) ?>" required />
            </div>

            <!-- Email field -->
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($email) ?>" required />
            </div>

            <!-- Password field -->
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input class="form-control" type="password" name="password" required />
            </div>

            <!-- Login and Cancel buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Log in</button>
                <a href="/index.php" class="btn btn-outline-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>
