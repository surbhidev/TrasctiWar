<?php
session_start(); // Ensure session is started
include "layout/header.php";
require_once "tools/db.php";

// Redirect to login if the user is not authenticated
if (!isset($_SESSION["email"])) {
    header("location: /login.php");
    exit;
}

// Validate user ID from the query string
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || intval($_GET['id']) <= 0) {
    echo "<script>alert('Invalid user ID.'); window.location.href = '/index.php';</script>";
    exit;
}

$userId = intval($_GET['id']); // Convert ID to integer

$dbConnection = getDatabaseConnection();

try {
    // Prepare and execute the SQL query
    $statement = $dbConnection->prepare(
        "SELECT username, first_name, last_name, email, phone, profile_image, biography FROM users WHERE id = :id"
    );
    $statement->bindValue(':id', $userId, PDO::PARAM_INT);
    $statement->execute();

    // Fetch user details
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('User not found.'); window.location.href = '/index.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage()); // Debugging database errors
}

// Sanitize output
$username = htmlspecialchars($user['username']);
$first_name = htmlspecialchars($user['first_name']);
$last_name = htmlspecialchars($user['last_name']);
$email = htmlspecialchars($user['email']);
$phone = htmlspecialchars($user['phone']);
$biography = htmlspecialchars($user['biography'] ?? '');

// Handle profile_image (stream resource)
$profile_image = $user['profile_image'];
if (is_resource($profile_image)) {
    // Read the stream into a string
    $profile_image = stream_get_contents($profile_image);
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-6 mx-auto border shadow p-4">
            <h2 class="text-center mb-4">User Profile</h2>
            <hr />

            <div class="row mb-3">
                <div class="col-sm-4">Username</div>
                <div class="col-sm-8"><?= $username ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">First Name</div>
                <div class="col-sm-8"><?= $first_name ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">Last Name</div>
                <div class="col-sm-8"><?= $last_name ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">Email</div>
                <div class="col-sm-8"><?= $email ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">Phone</div>
                <div class="col-sm-8"><?= $phone ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">Biography</div>
                <div class="col-sm-8"><?= $biography ?></div>
            </div>

            <?php if (!empty($profile_image)) { ?>
                <div class="row mb-3">
                    <div class="col-sm-4">Profile Image</div>
                    <div class="col-sm-8">
                        <?php
                        // Check if the image is binary data
                        if (is_string($profile_image)) {
                            // Detect MIME type
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mimeType = $finfo->buffer($profile_image);

                            // Display the image using base64 encoding
                            echo '<img src="data:' . $mimeType . ';base64,' . base64_encode($profile_image) . '" alt="Profile Image" class="img-fluid">';
                        } else {
                            echo '<p class="text-danger">Invalid profile image.</p>';
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php include "layout/footer.php"; ?>