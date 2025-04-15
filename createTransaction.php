<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $transaction_name = trim($_POST["transaction_name"]);
    $amount = floatval(trim($_POST["amount"])); // Cast to float
    $category = trim($_POST["category"]);
    $transaction_type = trim($_POST["transaction_type"]);
    $transaction_date = trim($_POST["transaction_date"]);
    $notes = trim($_POST["notes"]);
    $transaction_status = "Listed";

    $account_type = trim($_POST["account_type"]);
    $account_name = trim($_POST["account_name"]);

    // Step 1: Get the accountID and balance
    $getAccountSql = "SELECT `accountID`, `balance` FROM `accounts` WHERE `account_type` = ? AND `account_name` = ? AND `userID` = ? AND `account_status` != 'Deleted'";
    $stmt = $conn->prepare($getAccountSql);
    $stmt->bind_param("sss", $account_type, $account_name, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $accountID = $row["accountID"];
        $currentBalance = floatval($row["balance"]);
        $stmt->close();

        // Step 2: Validate if expense exceeds balance
        if ($transaction_type === "expense" && $amount > $currentBalance) {
            echo json_encode(["status" => "error", "message" => "Insufficient balance for this transaction."]);
            exit;
        }

        // Step 3: Insert transaction
        $insertSql = "INSERT INTO `transactions` (`userID`, `accountID`, `transaction_name`, `amount`, `category`, `transaction_type`, `transaction_date`, `notes`, `transaction_status`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sssssssss", $userID, $accountID, $transaction_name, $amount, $category, $transaction_type, $transaction_date, $notes, $transaction_status);

        if ($insertStmt->execute()) {
            // Step 4: Update balance
            $newBalance = ($transaction_type === "expense") ? $currentBalance - $amount : $currentBalance + $amount;
            $updateBalanceSql = "UPDATE `accounts` SET `balance` = ? WHERE `accountID` = ?";
            $updateStmt = $conn->prepare($updateBalanceSql);
            $updateStmt->bind_param("ds", $newBalance, $accountID);
            $updateStmt->execute();
            $updateStmt->close();

            echo json_encode(["status" => "success", "message" => "Transaction added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Insert failed: " . $insertStmt->error]);
        }

        $insertStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Account not found."]);
    }
}
?>
