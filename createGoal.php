<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = trim($_POST["userID"]);
    $goal_name = trim($_POST["goal_name"]);
    $amount = trim($_POST["amount"]);
    $start_date = date("Y-m-d");
    $target_date = trim($_POST["target_date"]);

    $account_type = trim($_POST["account_type"]);
    $account_name = trim($_POST["account_name"]);

    $goal_completion = "Ongoing";
    $goal_status = "Listed";

    // Check if goal name already exists for the user (and not deleted)
    $checkNameSql = "SELECT * FROM `goals` WHERE `userID` = ? AND `goal_title` = ? AND `goal_status` != 'Deleted'";
    $stmt = $conn->prepare($checkNameSql);
    $stmt->bind_param("ss", $userID, $goal_name);
    $stmt->execute();
    $nameResult = $stmt->get_result();

    if ($nameResult && $nameResult->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Goal name already exists."]);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Get the accountID
    $getAccountSql = "SELECT `accountID` FROM `accounts` WHERE `account_type` = ? AND `account_name` = ? AND `userID` = ? AND `account_status` != 'Deleted'";
    $stmt = $conn->prepare($getAccountSql);
    $stmt->bind_param("sss", $account_type, $account_name, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Account not found."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $row = $result->fetch_assoc();
    $accountID = $row["accountID"];
    $stmt->close();

    // Check if accountID is already associated with another goal (that’s ongoing and not deleted)
    $checkGoalSql = "SELECT * FROM `goals` WHERE `accountID` = ? AND `userID` = ? AND `goal_status` != 'Deleted' AND `goal_completion` != 'Completed'";
    $stmt = $conn->prepare($checkGoalSql);
    $stmt->bind_param("ss", $accountID, $userID);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "This account is already associated with a goal."]);
        $stmt->close();
        $conn->close();
        exit;
    }

    $stmt->close();

    // Proceed with inserting the goal
    $insertSql = "INSERT INTO `goals` (`userID`, `goal_title`, `amount`, `start_date`, `target_date`, `goal_completion`, `goal_status`, `accountID`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ssssssss", $userID, $goal_name, $amount, $start_date, $target_date, $goal_completion, $goal_status, $accountID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Goal successfully added."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert goal."]);
    }

    $stmt->close();
}

$conn->close();
?>
