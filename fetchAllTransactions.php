<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    $sql = "SELECT t.* 
            FROM `transactions` t
            INNER JOIN `accounts` a ON t.accountID = a.accountID
            WHERE t.userID = ? 
            AND t.transaction_status != 'Deleted' 
            AND a.account_status != 'Deleted'
            ORDER BY t.transaction_date DESC, t.transaction_type ASC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
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
