<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $goalID = trim($_POST["goalID"]);
    $goal_name = trim($_POST["goal_name"]);
    $amount = trim($_POST["amount"]);
    $target_date = trim($_POST["target_date"]);
    $account_type = trim($_POST["account_type"]);
    $account_name = trim($_POST["account_name"]);

    // Step 1: Get the old accountID from the goal
    $oldAccountSql = "SELECT `accountID` FROM `goals` WHERE `goalID` = ? AND `goal_status` != 'Deleted'";
    $stmt = $conn->prepare($oldAccountSql);
    $stmt->bind_param("s", $goalID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "failed", "message" => "Goal not found"]);
        $stmt->close();
        $conn->close();
        exit();
    }

    $row = $result->fetch_assoc();
    $oldAccountID = $row['accountID'];
    $stmt->close();

    // Step 2: Get the accountID of the new account
    $getAccountSql = "SELECT `accountID` FROM `accounts` WHERE `account_type` = ? AND `account_name` = ? AND `userID` = ? AND `account_status` != 'Deleted'";
    $stmt = $conn->prepare($getAccountSql);
    $stmt->bind_param("sss", $account_type, $account_name, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Account not found."]);
        $stmt->close();
        $conn->close();
        exit();
    }

    $row = $result->fetch_assoc();
    $accountID = $row["accountID"];
    $stmt->close();

    // Check if goal name already exists for the user, excluding the current goalID
    $checkNameSql = "SELECT * FROM `goals` WHERE `goal_title` = ? AND `userID` = ? AND `goalID` != ? AND `goal_status` != 'Deleted'";
    $stmt = $conn->prepare($checkNameSql);
    $stmt->bind_param("sss", $goal_name, $userID, $goalID);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(["status" => "exists", "message" => "Already have a goal with the same name"]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // If accountID has changed, check if it's already used in another goal
    if ($oldAccountID !== $accountID) {
        $checkUsedSql = "SELECT * FROM `goals` WHERE `accountID` = ? AND `goalID` != ? AND `userID` = ? AND `goal_status` != 'Deleted'";
        $stmt = $conn->prepare($checkUsedSql);
        $stmt->bind_param("sss", $accountID, $goalID, $userID);
        $stmt->execute();
        $checkResult = $stmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo json_encode(["status" => "conflict", "message" => "Account is already linked to a goal."]);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
    }

    // Proceed to update the goal
    $updateGoalSql = "UPDATE `goals` SET `goal_title` = ?, `amount` = ?, `target_date` = ?, `accountID` = ? WHERE `goalID` = ? AND `userID` = ?";
    $stmt = $conn->prepare($updateGoalSql);
    $stmt->bind_param("ssssss", $goal_name, $amount, $target_date, $accountID, $goalID, $userID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Goal updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update goal"]);
    }

    $stmt->close();
    $conn->close();
}
