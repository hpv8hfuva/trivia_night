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
if (isset($_POST["email"])) { // validate the email coming in
    $stmt = $mysqli->prepare("select * from user where email = ?;");
    $stmt->bind_param("s", $_POST["email"]);
    if (!$stmt->execute()) {
        $error_msg = "Error checking for user";
    } else { 
        // result succeeded
        $res = $stmt->get_result();
        $data = $res->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($data)) {
            // user was found!  Send to the game (with a GET parameter containing their email)
            if(!isset($_COOKIE["name"])) {
                setcookie("name", $data[0]["name"], time()+3600, "/","", 0);
                setcookie("email", $data[0]["email"], time()+3600, "/", "",  0); 
                setcookie("score", $data[0]["score"], time()+3600, "/", "",  0);   
            } 
            header("Location: trivia_categories.php");
            exit();
        } else {
            // User was not found.  For our game, we'll just insert them!
            $insert = $mysqli->prepare("insert into user (name, email) values (?, ?);");
            $insert->bind_param("ss", $_POST["name"], $_POST["email"]);
            if (!$insert->execute()) {
                $error_msg = "Error creating new user";
            } 
            // Send them to the game (with a GET parameter containing their email) 
            if(!isset($_COOKIE["name"])) {
                setcookie("name", $data[0]["name"], time()+3600, "/","", 0);
                setcookie("email", $data[0]["email"], time()+3600, "/", "",  0); 
                setcookie("score", $data[0]["score"], time()+3600, "/", "",  0);   
            } 
            header("Location: trivia_categories.php");
            exit();
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

        <title>Trivia Game Login</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous"> 
    </head>

    <body> 
        <div class="container" style="margin-top: 15px;">
            <div class="row col-xs-8">
                <h1>Trivia Night</h1>
                <p> Welcome to our trivia game!  To get started, login below or enter a new username and password to create an account</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-4">
                <?php
                    if (!empty($error_msg)) {
                        echo "<div class='alert alert-danger'>$error_msg</div>";
                    }
                ?>
                <form action="trivia_login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"/>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name"/>
                    </div>
                    <!--
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"/>
                    </div>
                    -->
                    <div class="text-center">                
                    <button type="submit" class="btn btn-primary">Log in / Create Account</button>
                    </div>
                </form>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    </body>
</html>