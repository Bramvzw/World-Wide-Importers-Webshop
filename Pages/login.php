<?php
include "includes/config.php";
$loginerr = null;
$logged_in = false;

// check if user is already logged in
if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
    header("Location: index.php");
}

// check if login posts are set
if(!empty($_POST["submit"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
    // set values
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

    // create database connection
    $DBLogin = newDBConn("login");
    // prepare query to look for user in database
    $sql = "SELECT * FROM login LEFT JOIN klant USING(KlantID) WHERE LogonName = ?";
    $statement = mysqli_prepare($DBLogin, $sql);
    mysqli_stmt_bind_param($statement, "s", $username);
    // execute query
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    // check if user exists
    if (mysqli_num_rows($result) == 1) {
        // user exists, so check password
        $userResult = mysqli_fetch_assoc($result);
        // check if the user has a password set
        if(!empty($userResult["Password"])) {
            if(password_verify($password, $userResult["Password"])) {
                // password is correct
                // controleer of de gebruiker actief is
                if((bool)$userResult["Active"]) {
                    // sessiewaarden invullen
                    $_SESSION["logged_in"] = true;
                    $_SESSION["userDetails"] = [
                        "UID" => $userResult["LoginID"],
                        "UserType" => (int)$userResult["UserType"],
                        "LogonName" => $userResult["LogonName"]
                    ];
                    // check if user een klant is
                    if($_SESSION["userDetails"]["UserType"] == 2 AND !empty($userResult["KlantID"])) {
                        // als user een klant is, zet de klantid en voornaam in sessie.
                        $_SESSION["userDetails"]["KlantID"] = (int)$userResult["KlantID"];
                        $_SESSION["userDetails"]["Voornaam"] = $userResult["Voornaam"];
                    }

                    // set logged_in var to true, so php can redirect user at the end of page
                    $logged_in = true;
                } else {
                    $loginerr = "Account is niet actief. Neem contact op.";
                }
            } else {
                // password is incorrect
                $loginerr = "Username en/of wachtwoord incorrect.";
            }
        } else {
            // no password has been set
            $loginerr = "Wachtwoord is niet ingesteld.<br>Klik <a href='#'>hier</a> om het wachtwoord te resetten";
        }
    } else {
        // username doesn't exist
        $loginerr = "Username en/of wachtwoord incorrect.";
    }


    // close connection
    mysqli_close($DBLogin);
}

// include header
include "includes/header.php";
?>

    <form class="form-signin2" method="post">
        <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
        <label style="color: red;"><?= $loginerr ?></label>
        <label for="inputEmail" class="sr-only">Gebruikersnaam</label>
        <input type="text" class="form-control" name="username" placeholder="Gebruikersnaam" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" class="form-control" name="password" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit" name="submit" value="1">Inloggen</button>
        <div class="etc-login-form">
            <p>Nieuw hier? <a href="register.php">Maak een account.</a></p>
            <p>Wachtwoord vergeten? <a href="#">klik hier</a></p>
        </div>
    </form>

<?php
if($logged_in) {
    print ("<script>
    alert('U bent nu ingelogd.'); 
             window.location.assign('index.php');
     </script>");
}
include "includes/footer.php";



