<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $transaction_name = trim($_POST["transfer_name"]);
    $amount = floatval(trim($_POST["amount"]));
    $transaction_type = trim($_POST["transfer_type"]);
    $transaction_date = trim($_POST["transfer_date"]);
    $notes = trim($_POST["notes"]);
    $transaction_status = "Listed";

    $account_type_from = trim($_POST["account_type_from"]);
    $account_name_from = trim($_POST["account_name_from"]);
    $account_type_to = trim($_POST["account_type_to"]);
    $account_name_to = trim($_POST["account_name_to"]);

    // Validate type
    if ($transaction_type !== "transfer") {
        echo json_encode(["status" => "error", "message" => "Invalid transaction type."]);
        exit;
    }

    // STEP 1: Get accountID and balance of both accounts
    $getAccountSql = "SELECT `accountID`, `balance` FROM `accounts` WHERE `account_type` = ? AND `account_name` = ? AND `userID` = ?";

    // Get FROM account
    $stmtFrom = $conn->prepare($getAccountSql);
    $stmtFrom->bind_param("sss", $account_type_from, $account_name_from, $userID);
    $stmtFrom->execute();
    $resultFrom = $stmtFrom->get_result();

    // Get TO account
    $stmtTo = $conn->prepare($getAccountSql);
    $stmtTo->bind_param("sss", $account_type_to, $account_name_to, $userID);
    $stmtTo->execute();
    $resultTo = $stmtTo->get_result();

    if ($resultFrom->num_rows > 0 && $resultTo->num_rows > 0) {
        $fromData = $resultFrom->fetch_assoc();
        $toData = $resultTo->fetch_assoc();

        $accountID_from = $fromData["accountID"];
        $balance_from = floatval($fromData["balance"]);

        $accountID_to = $toData["accountID"];
        $balance_to = floatval($toData["balance"]);

        // STEP 2: Check if there is enough balance
        if ($amount > $balance_from) {
            echo json_encode(["status" => "error", "message" => "Insufficient balance in source account."]);
            exit;
        }

        // STEP 3: Insert two transactions — debit and credit
        $insertSql = "INSERT INTO `transactions` (`userID`, `accountID`, `transaction_name`, `amount`, `category`, `transaction_type`, `transaction_date`, `notes`, `transaction_status`)
                    VALUES (?, ?, ?, ?, 'Transfer', ?, ?, ?, ?)";

        $insertStmt = $conn->prepare($insertSql);

        // From account
        $transaction_type_debit = "expense";
        $insertStmt->bind_param("sisdssss", $userID, $accountID_from, $transaction_name, $amount, $transaction_type_debit, $transaction_date, $notes, $transaction_status);
        $successDebit = $insertStmt->execute();

        // To account
        $transaction_type_credit = "income";
        $insertStmt->bind_param("sisdssss", $userID, $accountID_to, $transaction_name, $amount, $transaction_type_credit, $transaction_date, $notes, $transaction_status);
        $successCredit = $insertStmt->execute();

        $insertStmt->close();

        if ($successDebit && $successCredit) {
            // STEP 4: Update balances
            $newBalanceFrom = $balance_from - $amount;
            $newBalanceTo = $balance_to + $amount;

            $updateSql = "UPDATE `accounts` SET `balance` = ? WHERE `accountID` = ?";
            $updateStmt = $conn->prepare($updateSql);

            // Update FROM account
            $updateStmt->bind_param("di", $newBalanceFrom, $accountID_from);
            $updateStmt->execute();

            // Update TO account
            $updateStmt->bind_param("di", $newBalanceTo, $accountID_to);
            $updateStmt->execute();

            $updateStmt->close();

            echo json_encode(["status" => "success", "message" => "Transfer recorded successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to insert transfer transaction."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "One or both accounts not found."]);
    }

    $stmtFrom->close();
    $stmtTo->close();
}
?>
