<?php
require "./connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $nickname = $_POST["nickname"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    $sql = "UPDATE `users` SET first_name = ?, last_name = ?, nickname = ?, hash_password = ? WHERE userID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssi", $first_name, $last_name, $nickname, $password, $userID);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Details Updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Error: " . $stmt->error]);
        }

        $stmt->close(); 
    } else {
        echo json_encode(["status" => "failed", "message" => "Error in preparing statement: " . $conn->error]);
    }
}

$conn->close();
?>
