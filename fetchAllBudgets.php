<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    // Updated ORDER BY clause
    $sql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `transaction_status` != 'Deleted'";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $budgets = [];
        while ($row = $result->fetch_assoc()) {
            $budgets[] = $row;
        }

        echo json_encode(["status" => "success", "budgets" => $budgets]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$userID = 6;

    // Updated ORDER BY clause
    $sql = "SELECT * FROM `budgets` WHERE `userID` = ? AND `budget_status` != 'Deleted'";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $budgets = [];
        while ($row = $result->fetch_assoc()) {
            $budgets[] = $row;
        }

        echo json_encode(["status" => "success", "budgets" => $budgets]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
$conn->close();