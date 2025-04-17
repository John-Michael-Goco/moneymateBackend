<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST["userID"]) || empty(trim($_POST["userID"]))) {
        echo json_encode(["status" => "failed", "message" => "Invalid or missing userID"]);
        exit;
    }

    $userID = trim($_POST["userID"]);

    // Group by month and transaction_type
    $sql = "SELECT DATE_FORMAT(transaction_date, '%Y-%m') AS monthTitle, transaction_type, SUM(amount) AS totalAmount
        FROM `transactions` WHERE `userID` = ? AND `transaction_status` != 'Deleted' GROUP BY monthTitle, transaction_type ORDER BY monthTitle DESC, transaction_type ASC";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $monthlyTransactions = [];
        while ($row = $result->fetch_assoc()) {
            $monthlyTransactions[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "monthlyTransactions" => $monthlyTransactions
        ]);

        $stmt->close();
    } else {
        echo json_encode([
            "status" => "failed",
            "message" => "Database error: " . $conn->error
        ]);
    }
} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid request method"
    ]);
}


$conn->close();
?>
