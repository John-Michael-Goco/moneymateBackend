<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"])) || 
        !isset($_POST["goalID"]) || empty(trim($_POST["goalID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing userID or goalID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);
    $goalID = trim($_POST["goalID"]);

    $sql = "SELECT g.*, a.balance, a.account_name, a.account_type
            FROM `goals` g
            INNER JOIN `accounts` a ON g.accountID = a.accountID
            WHERE g.userID = ? 
            AND g.goalID = ?
            AND g.goal_status != 'Deleted' 
            AND a.account_status != 'Deleted'";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $userID, $goalID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $row["account_balance"] = $row["balance"];
            unset($row["balance"]);
            echo json_encode(["status" => "success", "goalDetails" => $row]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Goal not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "failed", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request method"]);
}

$conn->close();
