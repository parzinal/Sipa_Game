<?php
require_once '../dbconnection.php';

// Update the SQL query to get top 10 instead of top 5
$sql = "SELECT player_name, MAX(score) as score 
        FROM game_scores 
        GROUP BY player_name 
        ORDER BY score DESC 
        LIMIT 10";  // Changed from 5 to 10

$result = $conn->query($sql);

$scores = [];
while ($row = $result->fetch_assoc()) {
    $scores[] = $row;
}

header('Content-Type: application/json');
echo json_encode($scores);
?>