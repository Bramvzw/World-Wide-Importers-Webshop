<?php
// functie stuur bevestigings mail
function stuurbevestigingsmail($klantID, $BestellingID) {
    $DBconn = newDBConn('orderview');
// query: klant gegevens
    $statement = mysqli_prepare($DBconn, "SELECT Email, Voornaam, Tussenvoegsel, Achternaam FROM klantinformatie WHERE KlantID = ?");
    mysqli_stmt_bind_param($statement, 'i', $klantID);
    mysqli_stmt_execute($statement);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));


// query: product gegevens
    $statement = mysqli_prepare($DBconn,
        "SELECT s.StockItemName, b.Aantal, b.PrijsPP, (b.Aantal*b.PrijsPP) as Totaal, be.klantID, s.FileName 
            From productlist as s
            join bestellingregel as b on s.StockItemID = b.StockItemID
            join bestelling as be on b.BestellingID = be.BestellingID
            WHERE be.KlantID = ? and b.BestellingID = ?");
    mysqli_stmt_bind_param($statement, "ii", $klantID, $BestellingID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
// to, date & body van bevestigingsmail
    $totaal = 0;
    $date = date('d/m/Y');
    $to = $row["Email"];
    $subject = "Bedankt voor uw bestelling";
    if(!empty($row["Tussenvoegsel"])) {
        $body = " <p>Beste" . " " . $row["Voornaam"] . " " . $row["Tussenvoegsel"] . " " . $row["Achternaam"] . "</p> <br>";
    } else {
        $body = " <p>Beste" . " " . $row["Voornaam"] . " " . $row["Achternaam"] . "</p><br>";
    }

    $body .= "<p> Bedankt voor uw bestelling! Met deze mail willen wij bevestigen dat het plaatsen van uw bestelling gelukt is. <br> ";
    $body .= "<p> Uw bestelling op  $date  met de producten: <table style='width=100% margin: 10px;'><tr><th>  Product Naam </th>  <th> Aantal </th><th>Prijs</th><th>Subtotaal</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $body .= "<tr><td>" . $row["StockItemName"] . "</td><td>" . $row["Aantal"] . "</td><td>" . "&euro;".$row["PrijsPP"] . "</td><td> &euro;" . $row["Totaal"] . "</td>";
        $totaal += $row["Totaal"];
    }
    $body .= "<tr> Het totaal van uw bestelling bedraagt: &euro;$totaal</tr>";
    $body .= "<tr><td><img src='https://ictm1e5-wwi.tk/img/wwi.png'></td></tr>";
    $body .= "</table>";
// roept de functie aan
    smtpmailer($to, $subject, $body);
}

