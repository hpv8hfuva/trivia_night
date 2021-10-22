<?php
/** DATABASE SETUP **/
include("database_credentials.php"); // define variables

/** SETUP **/ 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $username, $password, $dbname); 
// $db = new mysql("localhost", "root", "", "dbname"); // XAMPP Settings 
$error_msg = "";

// Check if the user submitted the form (the form in the HTML below
// submits back to this page, which is okay for now.  We will check for
// form data and determine whether to re-show this form with a message
// or to redirect the user to the trivia game.  
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
    }
} else {
    // User did not supply email GET parameter, so send them
    // to the login page
    header("Location: trivia_login.php"); 
    exit();
}

$results = $mysqli->query("select distinct(genre) from question;");
$categories = $results->fetch_all(MYSQLI_ASSOC);

// reset cookies
foreach ($_COOKIE["question_history"] as $key => $val){
    unset($_COOKIE["question_history[$key]"]);
    setcookie("question_history[$key]", "", time()+3600, "/", "",  0);
}


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">  

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="author" content="your name">
        <meta name="description" content="include some description about your page">  

        <title>Trivia Game Login</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous"> 
    </head>

    <body> 
         <div class="container" style="margin-top: 15px; text-align: center;">
            <div class="row col-xs-8">
                <h1>Please Choose a Trivia Category</h1>
            </div>
            
            <div class="row justify-content-center"> 
                <?php 
                    foreach($categories as $category) { 
                        ?>
                        <div class="col-sm-6">
                            <div class="card" style="margin: 20px;">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $category["genre"]?></h5>
                                    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                                    <a href="trivia_question.php?category=<?= $category["genre"]?>" class="btn btn-primary">Let's play</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                ?>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    </body>
</html>