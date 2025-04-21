<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    $sql = "SELECT g.*, a.balance 
            FROM `goals` g
            INNER JOIN `accounts` a ON g.accountID = a.accountID
            WHERE g.userID = ? 
            AND g.goal_status != 'Deleted' 
            AND g.goal_completion != 'Complete'
            AND a.account_status != 'Deleted'
            LIMIT 3";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $goals = [];
        while ($row = $result->fetch_assoc()) {
            $row["account_balance"] = $row["balance"];
            unset($row["balance"]);
            $goals[] = $row;
        }

        echo json_encode(["status" => "success", "goals" => $goals]);
        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
