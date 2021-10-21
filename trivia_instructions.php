<?php
/** DATABASE SETUP **/
include("database_credentials.php"); // define variables

/** SETUP **/
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli($host, $username, $password, $dbname);
// $db = new mysql("localhost", "root", "", "dbname"); // XAMPP Settings
$user = null;

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
        $user = $data[0];
        if(!isset($_COOKIE["name"])) { 
            setcookie("name", $user["name"], time()+3600, "/","", 0);
            setcookie("email", $user["email"], time()+3600, "/", "",  0); 
            setcookie("score", $user["score"], time()+3600, "/", "",  0); 
        } 
    }
} else {
    // User did not supply email GET parameter, so send them
    // to the login page
    header("Location: trivia_login.php");
    exit();
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
            <div class="row col-xs-8" style="text-align:center;margin:10px;">
                <h1>CS4640 Television Trivia Game</h1>
                <h2>Hello, <?=$user["name"]?>. Welcome to Trivia Night!</h2>
            </div>
            <div class="row">
                <div class="col-xs-8 mx-auto" style="text-align:center;margin:10px;">
                <form action="trivia_categories.php" method="post">
                    <div class="h-100 p-5 bg-light border rounded-3">
                        <h1>Total Score: <?=$user["score"]?></h1>
                        <h1>Last Category Played: <?=$user["last_category"]?></h1>
                        <h3>Do you have what it takes to be a trivia master? 
                            The rules are simple: choose a trivia category, play through 10 questions,
                            and try to maximize your point total. Different questions are worth various points.
                            That's all you need to know my friend! Now click the button below and start triviaing!
                        </h3>
                        <div class="text-center">                
                            <button type="submit" class="btn btn-primary">Enter the Game</button>
                        </div>
                    </div>
                    <div class="text-center" style="margin:20px;">                
                        <a href="trivia_login.php" class="btn btn-danger">Log out</a>
                    </div>
                </form>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    </body>
</html>