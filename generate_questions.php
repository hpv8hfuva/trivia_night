<?php

include("database_credentials.php"); // define variables

/** SETUP **/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $username, $password, $dbname); 
// $db = new mysql("localhost", "root", "", "dbname"); // XAMPP Settings 
$error_msg = "";
/*
#$data = json_decode(file_get_contents("https://opentdb.com/api.php?amount=20&category=20&type=multiple"), true);

print_r($data);
 
$points = 10;
$animals = "Mythology";
$stmt = $mysqli->prepare("insert into question (question, answer, points, genre) values (?,?,?,?);");
foreach($data["results"] as $qn) {
    if($qn["difficulty"] === "medium") {
        $points = 50;
    }
    else if($qn["difficulty"] === "easy") {
        $points = 25;
    }
    else {
        $points = 100;
    }
    $stmt->bind_param("ssis", $qn["question"], $qn["correct_answer"], $points, $animals);
    if (!$stmt->execute()) {
        echo "Could not add question: {$qn["question"]}\n";
    } 
}*/