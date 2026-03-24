<?php
require_once '../dbconnection.php';

// Keep one entry per nickname (case/space-insensitive) using highest score.
$sql = "SELECT MAX(player_name) AS player_name, MAX(score) AS score
    FROM game_scores
    WHERE TRIM(player_name) <> ''
    GROUP BY LOWER(TRIM(player_name))
    ORDER BY score DESC
    LIMIT 10";

$result = $conn->query($sql);

$scores = [];
while ($row = $result->fetch_assoc()) {
    $scores[] = $row;
}

header('Content-Type: application/json');
echo json_encode($scores);
?>