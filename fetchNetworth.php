<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    $sql = "SELECT SUM(`balance`) AS networth FROM `accounts` WHERE `userID` = ? AND `account_status` != 'Deleted'";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $stmt->bind_result($networth);
        $stmt->fetch();

        echo json_encode(["status" => "success", "networth" => $networth ?? 0]);

        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
?>
