<?php
// config
include "includes/config.php";

// page header
include "includes/header.php";

// check ingelogd
?>
<div class="container">
<?php
if($_SESSION["logged_in"] == true) {
    print("<table class='table table-bordered table-striped'>");

    // print alle klantinformatie in een tabel.
    switch ($_SESSION["userDetails"]["UserType"]) {
        case 1:
            print("<tr><td>Ingelogd als</td><td>administrator</td></tr>");
            break;
        case 2:
            print("<tr><td>Ingelogd als</td><td>klant</td></tr>");
            $DBConn = newDBConn("orderview");
            $stmt = mysqli_prepare($DBConn, "SELECT * FROM klantinformatie WHERE KlantID = ?");
            mysqli_stmt_bind_param($stmt, "i", $_SESSION["userDetails"]["KlantID"]);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            print("<tr><td>Email</td><td>". $user["Email"] ."</td></tr>");
            print("<tr><td>Gebruikersnaam</td><td>". $user["LogonName"] ."</td></tr>");
            print("<tr><td>Voornaam</td><td>". $user["Voornaam"] ."</td></tr>");
            print("<tr><td>Tussenvoegsel</td><td>". $user["Tussenvoegsel"] ."</td></tr>");
            print("<tr><td>Achternaam</td><td>". $user["Achternaam"] ."</td></tr>");
            print("<tr><td>Adres</td><td>". $user["Adres"] ."</td></tr>");
            print("<tr><td>Postcode</td><td>". $user["Postcode"] ."</td></tr>");
            print("<tr><td>Plaatsnaam</td><td>". $user["Plaatsnaam"] ."</td></tr>");
            print("<tr><td>Land</td><td>". $user["Land"] ."</td></tr>");
            break;
        case 3:
            print("<tr><td>Ingelogd als</td><td>medewerker</td></tr>");
            break;
    }



    // print aantal bestellingen.

    print("</table>");
} else {
    print "niet ingelogd";
}
?>
</div>
<?php

// end with footer
include "includes/footer.php";