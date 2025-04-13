<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["transactionID"]) || empty(trim($_POST["transactionID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing Transaction ID"]);
        exit;
    }

    $transactionID = trim($_POST["transactionID"]);

    $sql = "SELECT * FROM `transactions` WHERE `transactionID` = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $transactionID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $accountID = $row['accountID'];

            // Fetch account_name and account_type from accounts table
            $accountSql = "SELECT account_name, account_type FROM `accounts` WHERE `accountID` = ?";
            if ($accountStmt = $conn->prepare($accountSql)) {
                $accountStmt->bind_param("s", $accountID);
                $accountStmt->execute();
                $accountResult = $accountStmt->get_result();

                if ($accountRow = $accountResult->fetch_assoc()) {
                    // Merge account details into the transaction details
                    $row['account_name'] = $accountRow['account_name'];
                    $row['account_type'] = $accountRow['account_type'];
                }

                $accountStmt->close();
            }

            echo json_encode(["status" => "success", "transactionDetails" => $row]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Transaction not found"]);
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
