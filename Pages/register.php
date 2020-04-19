<?php
include "includes/config.php";
// initiate error variable;
$err = null;
$emailerr = null;
$usernameerr = null;
$passworderr = null;
$userRegistered = null;

// initialisatie van userinfo array
$userInformation = [
    "Voornaam" => null,
    "Tussenvoegsel" => null,
    "Achternaam" => null,
    "Adres" => null,
    "Woonplaats" => null,
    "Postcode" => null,
    "Land" => null,
    "Email" => null,
    "Gebruikersnaam" => null,
    "Wachtwoord1" => null,
    "Wachtwoord2" => null
];

/** check if @submit-registration is set in the @POST. when true it will initiate the process of registering the user.*/
if (isset($_POST["submit-registration"])) {
    /** Hier begint het proces van registreren van de nieuwe klant.
     * @userInformation dit is een array waar alle gebruikerinfo wordt opgeslagen, deze bevat:
     *      Voornaam, (Tussenvoegsel), Achternaam, Adres, Woonplaats, Postcode, Land, Email, Gebruikersnaam, Wachtwoord1, Wachtwoord2.
     * Daarna wordt er door de array gelopen met een foreach, die checkt of de POST gezet is van die informatie.
     * Als die gezet is, wordt de input gesaniteerd met filter_var() en die waarde wordt in de array gestopt.
     */

    $userInfoCorrect = true;
    foreach ($userInformation as $info => $waarde) {
        if(!empty($_POST[$info])) {
            if($info == "Email") {
                if($userInformation[$info] = filter_var($_POST["$info"], FILTER_SANITIZE_EMAIL)) {} else {
                    $emailerr = "Email is niet valide.";
                    $userInfoCorrect = false;
                }
            } elseif ($info == "Voornaam" || $info == "Achternaam") {
                if(!preg_match("/^[a-zA-Z ]*$/",$_POST["$info"])) {
                    $err = "$info klopt niet.";
                    $userInfoCorrect = false;
                } else {
                    $userInformation[$info] = filter_var($_POST["$info"], FILTER_SANITIZE_STRING);
                }
            } else {
                $userInformation[$info] = filter_var($_POST["$info"], FILTER_SANITIZE_STRING);
            }
        } else {
            if ($info != "Tussenvoegsel") {
                // error handling moet gedaan worden!!!!!!!!!!!!!!
                // print("Niet ingevuld: ".$info);
                $userInfoCorrect = false;
            }
        }
    }
    //print_r($userInformation);
    /**
     * Wanneer alle gebruikersinformatie correct is mag er geregistreerd worden.
     * @userInfoCorrect Wanneer deze waarde TRUE is, mag er geregistreerd worden. Deze wordt false gezet wanneer er een waarde mist.
     */
    if($userInfoCorrect) {
        // gebruikersinfo klopt, nu moet er gecheckt worden of de gebruikersnaam en email niet al voorkomen in de database.
        // aanmaken verbinding database en voer een select query uit op de login tabel om te kijken of gebruikersnaam en email al voorkomen.
        $DBConnRegister = newDBConn("klantedit");
        $statement = mysqli_prepare($DBConnRegister, "SELECT Email, LogonName FROM login WHERE Email = ? OR LogonName = ?;");
        mysqli_stmt_bind_param($statement, "ss", $userInformation["Email"], $userInformation["Gebruikersnaam"]);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $emailEnLogonNameValid = true;
        if(mysqli_num_rows($result) >= 1) {
            // username or email bestaat al, uitzoeken welke er al bestaat.
            while($row = mysqli_fetch_assoc($result)) {
                if($row["Email"] == $userInformation["Email"]) {
                    // email in gebruik
                    $emailerr = "Email is al in gebruik!";
                }
                if($row["LogonName"] == $userInformation["Gebruikersnaam"]) {
                    // logonname in gebruik
                    $usernameerr = "Gebruikersnaam is al in gebruik!";
                }
            }
            $emailEnLogonNameValid = false;
        }
        // als email en gebruikersnaam valide zijn, wordt de gebruiker geregistreerd in de database.
        if ($emailEnLogonNameValid) {
            // perform password checks
            $passwordValid = false;
            if($userInformation["Wachtwoord1"] == $userInformation["Wachtwoord2"]) {
                // wachtwoorden zijn hetzelfde, controleer of het wachtwoord sterk genoeg is.
                $uppercase = preg_match('@[A-Z]@', $userInformation["Wachtwoord1"]);
                $lowercase = preg_match('@[a-z]@', $userInformation["Wachtwoord1"]);
                $number    = preg_match('@[0-9]@', $userInformation["Wachtwoord1"]);
                if(!$uppercase || !$lowercase || !$number || strlen($userInformation["Wachtwoord1"]) < 8) {
                    // wachtwoord is niet sterk genoeg
                    $passworderr = "Wachtwoord is niet sterk genoeg. Het wachtwoord moet minimaal 8 karakters lang zijn en minimaal 1 kleine letter, 1 hoofdletter en 1 nummer bevatten.";
                } else {
                    // wachtwoord is sterk genoeg, set wachtwoord als valid.
                    $passwordValid = true;
                }
            } else {
                // wachtwoorden zijn ongelijk, geef error
                $passworderr = "Wachtwoorden zijn ongelijk!";
            }
            if ($passwordValid) {
                // wachtwoord is valide, Hash wachtwoord:
                $hashedWachtwoord = password_hash($userInformation["Wachtwoord1"], PASSWORD_DEFAULT);

                // check of er geregistreerd mag worden
                if(REGISTER_ALLOWED) {
                    //bereid sql voor en voer insert uit.
                    // zet autocommit uit en zet $querySuccess op TRUE
                    mysqli_autocommit($DBConnRegister, false);
                    $querySuccess = true;

                    // insert query naar klant
                    $stmt = mysqli_prepare($DBConnRegister, "
                    INSERT INTO klant (Voornaam, Tussenvoegsel, Achternaam, Adres, Plaatsnaam, Postcode, Land)
                    VALUES (?, ?, ?, ?, ?, ?, ?);");
                    mysqli_stmt_bind_param($stmt, "sssssss", $userInformation["Voornaam"],
                        $userInformation["Tussenvoegsel"], $userInformation["Achternaam"],
                        $userInformation["Adres"], $userInformation["Woonplaats"],
                        $userInformation["Postcode"], $userInformation["Land"]
                    );
                    if (!mysqli_stmt_execute($stmt)) {
                        $querySuccess = FALSE;
                    }
                    mysqli_stmt_close($stmt);

                    // insert query naar login
                    $stmt = mysqli_prepare($DBConnRegister, "
                    INSERT INTO login (KlantID, Email, LogonName, Password) VALUES (LAST_INSERT_ID(), ?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt, "sss", $userInformation["Email"], $userInformation["Gebruikersnaam"], $hashedWachtwoord);
                    if (!mysqli_stmt_execute($stmt)) {
                        $querySuccess = FALSE;
                    }
                    mysqli_stmt_close($stmt);
                    if ($querySuccess) {
                        // success, commit changes
                        mysqli_commit($DBConnRegister);
                        $userRegistered = true;
                    } else {
                        // geen success, rollback changes
                        mysqli_rollback($DBConnRegister);
                        die("Error writing to database, please contact support.");
                    }
                    mysqli_autocommit($DBConnRegister, true);
                } else $userRegistered = true;
            }
        }
    } else {
        // wanneer gebruikersinfo niet klopt
        $err = "Niet alles ingevuld";
    }

}

// check if user is already logged in
if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
    header("Location: index.php");
}

include "includes/header.php";
?>


<form class="form-signin" method="post">
    <div class="container-fluid">
        <h1 class="h3 mb-3 font-weight-normal">Registreer hier</h1>
        <?php if ($err != null) print ("<span style='color: red;'>".$err."</span>"); ?>
        <div class="row">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-md-9 mb-auto">
                        <label for="inputNaam">Voornaam:</label>
                        <input value="<?= $userInformation["Voornaam"] ?>" type="all" id="inputNaam" name="Voornaam" class="form-control" placeholder="Voornaam" required autofocus>
                    </div>
                    <div class="col-md-3 mb-auto">
                        <label for="inputNaam">Tussenvoegsel:</label>
                        <input value="<?= $userInformation["Tussenvoegsel"] ?>"type="all" id="inputNaam" class="form-control" name="Tussenvoegsel" placeholder="Tussenvoegsel">
                    </div>
                </div>
                <label for="inputNaam">Achternaam:</label>
                <input value="<?= $userInformation["Achternaam"] ?>" type="all" id="inputNaam" class="form-control" name="Achternaam" placeholder="Achternaam" required>
                <label for="inputAddress">Adres:</label>
                <input value="<?= $userInformation["Adres"] ?>" type="all" id="inputAddress" class="form-control" name="Adres" placeholder="Adres (straatnaam en huisnummer)" required>
                <div class="row">
                    <div class="col-md-9 mb-auto">
                        <label for="inputWoonplaats">Woonplaats:</label>
                        <input value="<?= $userInformation["Woonplaats"] ?>" type="all" id="inputWoonplaats" class="form-control" name="Woonplaats" placeholder="Woonplaats" required>
                    </div>
                    <div class="col-md-3 mb-auto">
                        <label for="inputPostcode">Postcode:</label>
                        <input value="<?= $userInformation["Postcode"] ?>" type="all" id="inputPostcode" class="form-control" name="Postcode" placeholder="1234AB" required>
                    </div>
                </div>
                <label for="inputLand">Land:</label>
                <select id="inputLand" name="Land" class="custom-select">
                    <option selected value="Nederland">Nederland</option>
                </select>
            </div>

            <div class="col-lg-6">
                <label for="inputEmail">Emailadres: <?php if($emailerr != null) { print("<span style='color: red;'>$emailerr</span>"); } ?></label>
                <input value="<?= $userInformation["Email"] ?>" type="email" id="inputEmail" class="form-control" name="Email" placeholder="Emailadres" required>
                <label for="inputUsername">Gebruikersnaam: <?php if($usernameerr != null) { print("<span style='color: red;'>$usernameerr</span>"); } ?></label>
                <input value="<?= $userInformation["Gebruikersnaam"] ?>" type="text" id="inputUsername" class="form-control" name="Gebruikersnaam" placeholder="Gebruikersnaam" required>
                <label for="inputPassword1">Wachtwoord: <?php if($passworderr != null) { print("<span style='color: red;'>$passworderr</span>"); } ?></label>
                <input type="password" id="inputPassword1" class="form-control" name="Wachtwoord1" placeholder="Wachtwoord" required>
                <label for="inputPassword2">Bevestig Wachtwoord:</label>
                <input type="password" id="inputPassword2" class="form-control" name="Wachtwoord2" placeholder="Herhaal Wachtwoord" required>
                <br>
                <button class="btn btn-lg btn-primary btn-block" name="submit-registration" type="submit">Registreer</button>
                <br>
            </div>

        </div>
    </div>
</form>


<?php
if($userRegistered) {
    print ("<script>
    alert('U bent nu geregistreerd, u wordt doorgeleid naar de inlogpagina.'); 
             window.location.assign('login.php');
     </script>");
}

include "includes/footer.php";



