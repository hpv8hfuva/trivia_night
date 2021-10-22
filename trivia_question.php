<?php
/** DATABASE SETUP **/
include("database_credentials.php"); // define variables

/** SETUP **/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $username, $password, $dbname);
// $db = new mysql("localhost", "root", "", "dbname"); // XAMPP Settings 
// Example using URL rewriting: we add the user information
// directly to the URI with a query string (GET parameters)

// Deal with the current session 
if (isset($_COOKIE["email"])) { // validate the email coming in
    $stmt = $mysqli->prepare("select * from user where email = ?;");
    $stmt->bind_param("s", $_COOKIE["email"]);
    if (!$stmt->execute()) {
        die("Error checking for user");
    } else { 
        // result succeeded
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        
        if (empty($data)) {
            // user was NOT found!
            header("Location: trivia_login.php");
            exit();
        } 
        // The user WAS found (SECURITY ALERT: we only checked against
        // their email address -- this is not a secure method of
        // keeping track of users!  We more likely want a unique
        // session ID for this user instead!  
    }
} else {
    // User did not supply email GET parameter, so send them
    // to the login page
    header("Location: trivia_login.php");
    exit();
}

// Read a random question from the database
$topic = $_GET["category"];
$res = $mysqli->query("select id, points, question, genre from question where genre='$topic' order by rand() limit 1;");
if ($res === false) {
    die("MySQL database failed");
}
$data = $res->fetch_all(MYSQLI_ASSOC);
if (!isset($data[0])) {
    die("No questions in the database");
}

// Update user last category played
$stmt = $mysqli->prepare("update user set last_category = ? where email = ?;");
$stmt->bind_param("ss", $_GET["category"], $_COOKIE["email"]);    
$stmt->execute(); 

$question = $data[0]; 

// Message variable to display if needed
$message = "";

// If the user submitted (POST) an answer to a question, we should check
// to see if they got it right!
$updated_points = $_COOKIE["score"];
$test = isset($_COOKIE["question_history"]) ? $_COOKIE["question_history"]: array(); 

if (isset($_POST["questionid"])) {
    $qid = $_POST["questionid"];
    $answer = $_POST["answer"]; 
    // Use prepare with parameter binding to avoid SQL injection and
    // other attacks.  This will ensure that MySQL correctly escapes
    // the passed value and ensure that it is an integer.
    $stmt = $mysqli->prepare("select * from question where id = ?;");
    $stmt->bind_param("i", $qid);
    if (!$stmt->execute()) {
        // did not work
        $message = "<div class='alert alert-info'>Error: could not find previous question</div>";
    } else {
        // worked
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        
        if (!isset($data[0])) {
            $message = "<div class='alert alert-info'>Error: could not find previous question</div>";
        } else {
            // found question 
            if ($data[0]["answer"] == $answer) {
                $message = "<div class='alert alert-success'><b>$answer</b> was correct!</div>";    
                $updated_points += $data[0]["points"];
                setcookie("score", $data[0]["points"] + $_COOKIE["score"], time()+3600, "/", "",  0); 
                $stmt = $mysqli->prepare("update user set score = ? where email = ?;");
                $stmt->bind_param("is", $_COOKIE["score"], $_COOKIE["email"]);    
                $stmt->execute(); 
            } else { 
                $message = "<div class='alert alert-danger'><b>$answer</b> was incorrect! The answer was: {$data[0]['answer']}</div>"; 
            } 
            setcookie("question_history[$qid]", $qid, time()+3600, "/", "",  0);  
            $test[$qid] = $qid;
        }
        
    } 
} 
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">  

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="your name">
        <meta name="description" content="include some description about your page">  

        <title>Trivia Game</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous"> 
    </head>

    <body>


        <div class="container" style="margin-top: 15px;">
            <div class="row col-xs-8">
                <h1>CS4640 Television Trivia Game</h1>
                <h3>Hello <?=$_COOKIE["name"]?>! Score: <?=$updated_points?></h3>
            </div>
            <div class="row">
                <div class="col-xs-8 mx-auto">
                    <div class="accordion" id="accordionExample">  
                    <?php     
                        foreach($test as $qid) { 
                            $stmt = $mysqli->prepare("select * from question where id = ?;");
                            $stmt->bind_param("i", $qid);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $question_data = $res->fetch_all(MYSQLI_ASSOC); 

                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?=$qid?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?=$qid?>" aria-expanded="false" aria-controls="collapse<?=$qid?>">
                                        <?=$question_data[0]["question"]?>
                                    </button>
                                </h2>
                                <div id="collapse<?=$qid?>" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <strong><?=$question_data[0]["answer"]?></strong>
                                    </div> 
                                </div>
                            </div>
                            <?php
                        } 
                    ?>  
                    </div>  
                </div>
            </div>
            <div>
                <div> 
                    <form action="trivia_question.php?category=<?=$_GET["category"]?>" method="post">
                        <div class="h-100 p-5 bg-light border rounded-3">
                        <h2>Question</h2>
                        <p><?=$question["question"]?></p>
                        <input type="hidden" name="questionid" value="<?=$question["id"]?>"/>
                        <input type="hidden" name="points" value="<?=$question["points"]?>"/>
                        </div>
                        <?=$message?>
                        <div class="h-10 p-5 mb-3">
                            <input type="text" class="form-control" id="answer" name="answer" placeholder="Type your answer here">
                        </div>
                        <div class="text-center">                
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a href="trivia_login.php" class="btn btn-danger">Log out</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    </body>
</html>