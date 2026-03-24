<?php
include '../dbconnection.php'; // Include the database connection

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['score'])) {
    $score = $data['score'];

    // Insert the score into the database
    $stmt = $conn->prepare("INSERT INTO scores (score) VALUES (?)");
    $stmt->bind_param("i", $score);

    if ($stmt->execute()) {
        echo "Score saved successfully!";
    } else {
        echo "Error saving score: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "No score received!";
}

$conn->close();
?>