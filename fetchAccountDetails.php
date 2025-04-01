<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["accountID"]) || empty(trim($_POST["accountID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing account ID"]);
        exit;
    }

    $accountID = trim($_POST["accountID"]);

    $sql = "SELECT * FROM `accounts` WHERE `accountID` = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $accountID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(["status" => "success", "accountDetails" => $row]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Account not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
?>
