<?php
include "includes/config.php";
include "includes/bevestigings-mail.php";
include "includes/korting.php";
$DBconn = newDBConn("orderedit");
include "includes/header.php";

/* controle of gebruiker is ingelogd */
if($_SESSION["logged_in"] == true) {
    /* controle of gebruiker zijn winkelwagentje niet leeg is */
    if (!empty($_SESSION["Winkelwagen"])) {

        kortingDB("open");
        foreach ($_SESSION["Winkelwagen"] as $productID => $aantal) {
            /* vraagt product info ophalen */
            $statement = mysqli_prepare($DBconn, "SELECT UnitPrice, QuantityOnHand FROM productlist WHERE StockItemID = ?");
            mysqli_stmt_bind_param($statement, "i", $productID);
            mysqli_stmt_execute($statement);
            $Result = mysqli_stmt_get_result($statement);
            $row = mysqli_fetch_assoc($Result);
            mysqli_stmt_close($statement);

            // check voorraad > aantal
            if ($row["QuantityOnHand"] >= $aantal) {
                $korting = getKorting($productID, $row["UnitPrice"]);
                $cart[$productID] = ["aantal" => $aantal, "prijs" => $korting["prijs"], "Actuele voorraad" => $row["QuantityOnHand"]];
            } else {
                print "Aantal is hoger dan de actuele voorraad";
            }
        }
        kortingDB("close");
        mysqli_autocommit($DBconn, false);
        $querySuccess = true;

        $KlantID = (int)$_SESSION["userDetails"]["KlantID"];

        $statement = mysqli_prepare($DBconn, "INSERT INTO bestelling (KlantID, BestellingDatum) VALUES (?, CURRENT_DATE())");
        mysqli_stmt_bind_param($statement, "i", $KlantID);
        if (!mysqli_stmt_execute($statement)) {
            $querySuccess = false;
            print "insert bestelling mislukt";
        }
        mysqli_stmt_close($statement);

        $BestellingID = mysqli_insert_id($DBconn);
//        print($BestellingID);

        Foreach ($cart as $StockItemID => $values) {
            $statement = mysqli_prepare($DBconn, "INSERT INTO bestellingregel (BestellingID, StockItemID, Aantal, PrijsPP) VALUES ($BestellingID, ?, ?, ?)");
//            print_r($values);
            mysqli_stmt_bind_param($statement, "iis", $StockItemID, $values["aantal"], $values["prijs"]);
            if (!mysqli_stmt_execute($statement)) {
                $querySuccess = false;
                print "insert bestellingregel mislukt";
            }
            mysqli_stmt_close($statement);
        }

        if ($querySuccess) {
            // success, commit changes
            mysqli_commit($DBconn);


            // legen winkelwagen
            unset($_SESSION["Winkelwagen"]);
            mysqli_autocommit($DBconn, true);

            // voorraad bijwerken
            Foreach ($cart as $itemID => $values) {
                $sql = "UPDATE stockitemholdings SET QuantityOnHand=QuantityOnHand-".$values["aantal"]." WHERE StockItemID = ".$itemID;
                if (!$result = mysqli_query($DBconn, $sql)) {
                    print "update voorraad mislukt";
                }
            }

            // bevestiging sturen
            stuurbevestigingsmail($KlantID, $BestellingID);
            ?>
            <div class="po-box">
                <h3 class="po-title"> Uw bestelling is geplaatst! </h3>
                <a href="vieworder.php" type="button" class="btn btn-primary po-but">
                    Bekijk mijn bestelling(en)
                </a>

                <a href="index.php" type="button" class="btn btn-primary po-but" >
                    Verder Winkelen
                </a>
            </div>

<?php
        } else {
            // geen success, rollback changes
            mysqli_rollback($DBconn);
            //die("Error writing to database, please contact support.");
            print("help");
        }
        mysqli_autocommit($DBconn, true);
        mysqli_close($DBconn);

    }
}
// include "includes/footer.php"
?>
</body>
</html>