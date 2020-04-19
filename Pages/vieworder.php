<?php
// include config
include "includes/config.php";
$title = "View order";
// include header
include "includes/header.php";

if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
    $klantID = $_SESSION["userDetails"]["KlantID"];

//maakt vervinding met de database
    $DBConn = newDBConn("orderview");

//query om ordernummer uit database te halen
    $bestellingstatement = mysqli_prepare($DBConn, "SELECT * FROM bestelling WHERE KlantID = ? ORDER BY BestellingDatum DESC, BestellingID DESC;");
    mysqli_stmt_bind_param($bestellingstatement, "i", $klantID);
    mysqli_stmt_execute($bestellingstatement);
    $result = mysqli_stmt_get_result($bestellingstatement);

    echo "<h3 class=\"head-ban1\">Mijn bestellingen:</h3><br>";

    ?>

<table class="table table-striped table-bordered table-condensed"
       <thead>
       <tr>
           <th scope="=col">Ordernummer:</th>
           <th scope="col">Besteldatum:</th>
           <th scope="col">Bekijk bestelling:</th>
       </tr>
       </thead>

<?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>" . $row['BestellingID'] . "</td><td>" . $row['BestellingDatum'] . "</td><td><form action='vieworderdetail.php' method='post'>
        <input type='hidden' name='BestellingID' value='" . $row["BestellingID"] . "'>
        <input type='submit' name='bekijkbestelling' value='Bekijk bestelling'</td></tr></form>";
    }
    echo "</table>";
} else {
    echo "Je bent niet ingelogd!";
}





//include footer
include "includes/footer.php";
?>