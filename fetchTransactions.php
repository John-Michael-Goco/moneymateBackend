<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["accountID"]) || empty(trim($_POST["accountID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing accountID"]);
        exit;
    }

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);
    $accountID = trim($_POST["accountID"]);

    $sql = "SELECT * FROM `transactions` WHERE `accountID` = ? AND `userID` = ? AND `transaction_status` != 'Deleted' ORDER BY `transaction_date` ASC";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $accountID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }

        echo json_encode(["status" => "success", "transactions" => $transactions]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}
$conn->close();
