<?php
include "includes/config.php";
// controleren of medewerker ingelogd is.
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true && $_SESSION["userDetails"]["UserType"] == 3) {
    // ophalen klantgegevens query
    $DBConn = newDBConn("orderedit");
    $statement = mysqli_prepare($DBConn, "SELECT * FROM klantinformatie WHERE Active = 1");
    mysqli_stmt_execute($statement);
    $klantresult = mysqli_stmt_get_result($statement);
    mysqli_stmt_close($statement);

    // ophalen kortingen voor in de body
    $statement = mysqli_prepare($DBConn, "SELECT DISTINCT * FROM productlist JOIN kortingvalide USING(StockItemID) ORDER BY RAND() LIMIT 6");
    mysqli_stmt_execute($statement);
    $productresult = mysqli_stmt_get_result($statement);

    // opstellen tabel
    $productbody = "<table>";
    while ($pRow = mysqli_fetch_assoc($productresult)) {
        $productrow = "<tr><td><a href='https://ictm1e5-wwi.tk/product.php?productID=" . $pRow["StockItemID"] ."'>".$pRow["StockItemName"]."</a></td>
        <td>&euro; ". $pRow["UnitPrice"] ."</td></tr>";
        $productbody .= $productrow;
    }
    $productbody .= "</table>";

    // loop door gegevens
    while ($row = mysqli_fetch_assoc($klantresult)) {
        $toAddress = $row["Email"];
        if(!empty($row["Tussenvoegsel"])) $naam = $row["Voornaam"] . " " . $row["Tussenvoegsel"] . " " . $row["Achternaam"];
        else $naam = $row["Voornaam"] . " " . $row["Achternaam"];

        $onderwerp = "Wide World Importers Nieuwsbrief";

        $body = "<p>Beste $naam</p>";
        $body .= "<p>In deze nieuwsbrief vind je de kortingen die op dit moment aanwezig zijn in de webshop.</p>";
        $body .= $productbody;
        $body .= "<p>Wide World Importers<br><img src='https://ictm1e5-wwi.tk/img/wwi.png'></p>";

        if(smtpmailer($toAddress, $onderwerp, $body)) {
            print("Nieuwsbrief verzonden naar: " . $toAddress . "<br>\n");
        } else {
            print("Mislukt om mail te verzenden naar: " . $toAddress . "<br>\n");
        }
    }
}