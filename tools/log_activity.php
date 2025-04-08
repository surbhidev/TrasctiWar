<?php
function logUserActivity($username, $webpage) {
    include 'db.php'; // Include the database connection file

    // Get the database connection
    $dbConnection = getDatabaseConnection();

    // Get the client's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Prepare the SQL query using PDO syntax
    $stmt = $dbConnection->prepare(
        "INSERT INTO user_activity_logs (username, webpage, ip_address) VALUES (:username, :webpage, :ip_address)"
    );

    // Bind values
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':webpage', $webpage, PDO::PARAM_STR);
    $stmt->bindValue(':ip_address', $ip_address, PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();
}
?>
