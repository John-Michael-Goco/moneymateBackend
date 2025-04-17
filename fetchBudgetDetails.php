<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["budgetID"]) || empty(trim($_POST["budgetID"]))) {
        echo json_encode(["status" => "failed", "message" => "Missing Budget ID"]);
        exit;
    }

    $budgetID = trim($_POST["budgetID"]);

    $sql = "SELECT * FROM `budgets` WHERE `budgetID` = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $budgetID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode(["status" => "success", "budgetDetails" => $row]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Budget not found"]);
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
