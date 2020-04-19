<?php
// include config
include "includes/config.php";
$title = "World Wide Importers Webshop";
// include header
include "includes/header.php";

if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
    $klantID = $_SESSION["userDetails"]["KlantID"];

//maakt verbinding met de database
    $DBConn = newDBConn("orderview");

    if (!empty($_POST["BestellingID"])) {
        $BestellingID = (int)filter_var($_POST["BestellingID"], FILTER_SANITIZE_NUMBER_INT);
    } else die("BestellingID bestaat niet");

    $orderstatement = mysqli_prepare($DBConn, "SELECT BestellingID, BestellingDatum, opmerkingen FROM bestelling WHERE BestellingID = ?");
    mysqli_stmt_bind_param($orderstatement, "i", $BestellingID);
    mysqli_stmt_execute($orderstatement);
    $result = mysqli_stmt_get_result($orderstatement);
?>

    <h3 class="head-ban1">Mijn bestelling:</h3><br>

    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<h5 class=\"head-ban1\">Ordernummer: " . $row['BestellingID'] . "</h5>";
        echo "<h5 class=\"head-ban1\">Besteldatum: " . $row['BestellingDatum'] . "</h5><br>";
        if ($row['opmerkingen'] != "") {
            echo "<h5 class=\"head-ban1\">Opmerking: " . $row['opmerkingen'] . "</h5><br>";
        }
    }

    $totaal = 0;
// Nog een join maken and the WHERE uit btijden met AND Bind param word 2x ii achter bestelling id komt ,klant ID
    $statement = mysqli_prepare($DBConn,
        "SELECT s.StockItemName, b.Aantal, b.PrijsPP, (b.Aantal*b.PrijsPP) as Totaal, be.klantID, s.FileName 
            From productlist as s
            join bestellingregel as b on s.StockItemID = b.StockItemID
            join bestelling as be on b.BestellingID = be.BestellingID
            WHERE b.BestellingID = ? and be.KlantID = ?");
    mysqli_stmt_bind_param($statement, "ii", $BestellingID,$klantID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
?>

    <table class="table table-bordered table-condensed"
    <thead>
    <tr>
        <th scope="col">Afbeelding product:</th>
        <th scope="col">Product naam:</th>
        <th scope="col">Aantal:</th>
        <th scope="col">Prijs per stuk:</th>
        <th scope="col">Subtotaal:</th>
    </tr>
    </thead>

<?php

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['FileName'] != null){
            $imgpath = "img/product/" . $row['FileName'];
        } else {
            $imgpath = "img/image-placeholder.jpg";
        }
        echo "<tr><td><img src=\"$imgpath\" height='100px' width='100px'></td><td>" . $row['StockItemName'] . "</td><td>" . $row['Aantal'] . "</td><td>" . $row['PrijsPP'] . "</td><td>" . $row['Totaal'] . "</td></tr>";
        $totaal += $row['Totaal'];
    }
    echo "</table><br>";
    echo "<h5 class=\"head-ban1\">Het totaal bedrag van de besteling is: <b>€$totaal ,-</b></h5>";
} else {
    echo "je bent niet ingelogd";
}
//include footer
include "includes/footer.php";
?>