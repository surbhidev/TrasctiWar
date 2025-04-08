<?php
ob_start();
include "layout/header.php";
include "tools/log_activity.php"; // Log activity function
include "tools/db.php"; // Database connection

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION["email"])) {
    header("Location: /login.php");
    exit;
}

// Secure session
session_regenerate_id(true);

// Log user activity
logUserActivity($_SESSION["username"], '/view_profile.php');

// Initialize variables
$username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8');
$first_name = htmlspecialchars($_SESSION["first_name"], ENT_QUOTES, 'UTF-8');
$last_name = htmlspecialchars($_SESSION["last_name"], ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_SESSION["email"], ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($_SESSION["phone"], ENT_QUOTES, 'UTF-8');
$biography = htmlspecialchars($_SESSION["biography"] ?? '', ENT_QUOTES, 'UTF-8');
$profile_image = $_SESSION["profile_image"] ?? null; // Do not sanitize binary data

$first_name_error = $last_name_error = $phone_error = $biography_error = $profile_image_error = "";
$error = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $last_name = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $biography = trim(filter_input(INPUT_POST, 'biography', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Convert to UTF-8 to prevent encoding issues
    $first_name = mb_convert_encoding($first_name, 'UTF-8', 'UTF-8');
    $last_name = mb_convert_encoding($last_name, 'UTF-8', 'UTF-8');
    $phone = mb_convert_encoding($phone, 'UTF-8', 'UTF-8');
    $biography = mb_convert_encoding($biography, 'UTF-8', 'UTF-8');

    // Validate inputs
    if (empty($first_name)) {
        $first_name_error = "First name is required";
        $error = true;
    }

    if (empty($last_name)) {
        $last_name_error = "Last name is required";
        $error = true;
    }

    if (!preg_match("/^(\+|00\d{1,3})?[- ]?\d{7,12}$/", $phone)) {
        $phone_error = "Phone number format is invalid";
        $error = true;
    }

    // Handle profile image upload securely
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile_image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $profile_image_error = "Only JPEG, PNG, and GIF images are allowed.";
            $error = true;
        } else {
            // Read and escape the file content
            $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
        }
    }

    // Update profile if no errors
    if (!$error) {
        try {
            $dbConnection = getDatabaseConnection();

            $statement = $dbConnection->prepare(
                "UPDATE users SET first_name = ?, last_name = ?, phone = ?, biography = ?, profile_image = ? WHERE email = ?"
            );

            $statement->bindParam(1, $first_name, PDO::PARAM_STR);
            $statement->bindParam(2, $last_name, PDO::PARAM_STR);
            $statement->bindParam(3, $phone, PDO::PARAM_STR);
            $statement->bindParam(4, $biography, PDO::PARAM_STR);
            $statement->bindParam(5, $profile_image, PDO::PARAM_LOB); // Use LOB for binary image
            $statement->bindParam(6, $email, PDO::PARAM_STR);
            $statement->execute();

            // Update session data securely
            $_SESSION["first_name"] = htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8');
            $_SESSION["last_name"] = htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8');
            $_SESSION["phone"] = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
            $_SESSION["biography"] = htmlspecialchars($biography, ENT_QUOTES, 'UTF-8');
            $_SESSION["profile_image"] = $profile_image; // Do not sanitize binary data

            // Redirect with success message
            $_SESSION["success_message"] = "Profile updated successfully!";
            header("Location: /profile.php");
            exit;
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-6 mx-auto border shadow p-4">
            <h2 class="text-center mb-4">Edit Profile</h2>
            <hr />

            <!-- Display success message -->
            <?php if (isset($_SESSION["success_message"])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><?= htmlspecialchars($_SESSION["success_message"], ENT_QUOTES, 'UTF-8') ?></strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION["success_message"]); ?>
            <?php } ?>

            <form method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Username</label>
                    <div class="col-sm-8">
                        <input class="form-control" value="<?= $username ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Email</label>
                    <div class="col-sm-8">
                        <input class="form-control" value="<?= $email ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">First Name*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="first_name" value="<?= $first_name ?>">
                        <span class="text-danger"><?= $first_name_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Last Name*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="last_name" value="<?= $last_name ?>">
                        <span class="text-danger"><?= $last_name_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Phone No.*</label>
                    <div class="col-sm-8">
                        <input class="form-control" name="phone" value="<?= $phone ?>">
                        <span class="text-danger"><?= $phone_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Biography</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="biography"><?= $biography ?></textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Profile Image</label>
                    <div class="col-sm-8">
                        <input type="file" class="form-control" name="profile_image">
                        <span class="text-danger"><?= $profile_image_error ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="offset-sm-4 col-sm-4 d-grid">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                    <div class="col-sm-4 d-grid">
                        <a href="/profile.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include "layout/footer.php";
?>
