<?php
/* Dit bestand zal alle functies bevatten voor kortingen toe te passen op producten
 *
 */

// functie om de databaseconnectie te openen en sluiten voor de kortingsfuncties
function kortingDB($action, $medewerker = false) {
    global $DBConnKorting;
    if ($action == "open") {
        if($medewerker) $DBConnKorting = newDBConn("stockedit");
        else $DBConnKorting = newDBConn("productview");
    } elseif ($action == "close") {
        mysqli_close($DBConnKorting);
    }
}

// hoofdfunctie om de korting op te vragen
function getKorting($productID, $prijs) {
    // global database verbinding
    global $DBConnKorting;

    // initialiseer return array
    $return = ["type" => null, "prijs" => $prijs, "korting" => null, "opisop" => false, "voorraad" => null];

    // query voorbereiden. Selecteer geldige korting voor een product.
    $statement = mysqli_prepare($DBConnKorting, "SELECT * FROM kortingvalide WHERE StockItemID = ?");
    mysqli_stmt_bind_param($statement, "i", $productID);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    mysqli_stmt_close($statement);
    if(mysqli_num_rows($result) == 0) return $return;
    $row = mysqli_fetch_assoc($result);

    // Kijk over wat voor kortingen het gaat. Hier controleren we op hoeveelheid, percentage of prijs-override.
    if(!empty($row["KortingHoeveelheid"])) {
        // als kortinghoeveelheid gezet is.
        // zet type als "hoeveelheid"
        $return["type"] = "hoeveelheid";
        // pas prijs aan in return variabele
        $return["prijs"] = $prijs - $row["KortingHoeveelheid"];
        $return["korting"] = $row["KortingHoeveelheid"];
    } elseif (!empty($row["KortingPercentage"])) {
        // als kortingspercentage gezet is.
        // zet type als percentage.
        $return["type"] = "percentage";
        // pas prijs aan in return variabele.
        $return["prijs"] = $prijs - round(($prijs * ($row["KortingPercentage"] / 100 )), 2);
        $return["korting"] = $row["KortingPercentage"];
    } elseif (!empty($row["Prijs"])) {
        // als prijs gezet is. Dit betekent een volledige override van de prijs.
        // type blijft null.
        $return["type"] = null;
        // zet de prijs.
        $return["prijs"] = $row["Prijs"];
    }

    // kijk of er een "op is op" actie is.
    if (!empty($row["Voorraad"])) {
        // als voorraad gezet is betekent dat er een op is op actie is.
        // hiermee wordt voorraad overrided.
        // zet op is op bool naar true
        $return["opisop"] = true;
        // zet de gewenste voorraad.
        $return["voorraad"] = $row["Voorraad"];
    }


    // geef de return array terug.
    return $return;
}

function printSearchAanbieding($korting) {
    if($korting["type"] == "hoeveelheid") {
        print("<span class='product-new-label'>Aanbieding</span>");
        print("<span class='product-discount-label'>-".$korting["korting"]."</span>");
    } elseif ($korting["type"] == "percentage") {
        print("<span class='product-new-label'>Aanbieding</span>");
        print("<span class='product-discount-label'>".round($korting["korting"], 0)."%</span>");
    }
}
function printSearchOpIsOp($korting) {
    if($korting["opisop"]) {
        print("<span class='product-opisop-label'>OP IS OP</span>");
    }
}


function printKortingBeheerProductSelectList() {
    global $DBConnKorting;
    global $kortingProductlijst;
    // check of productlijst leeg is.
    if(empty($kortingProductlijst)) {
        // ophalen van producten die nog geen korting hebben,
        // dit gebeurd alleen de eerste keer dat deze functie wordt aangeroepen.
        // Anders wordt het uit de variabele gehaald.
        $statement = mysqli_prepare($DBConnKorting,"
            SELECT StockItemID, StockItemName FROM productlist
            WHERE NOT EXISTS (
                SELECT StockItemID FROM korting 
                WHERE korting.StockItemID = productlist.StockItemID)
            ORDER BY StockItemID;");
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        mysqli_stmt_close($statement);
        while($row = mysqli_fetch_assoc($result)) {
            $kortingProductlijst[$row["StockItemID"]] = $row["StockItemName"];
        }
    }
    // print de select list
    print("<select name='id' class='form-control form-group'>");
    foreach ($kortingProductlijst as $id => $name) {
        print("<option value='$id'>$name</option>");
    }
    print("</select>");
}

function printKortingBeheerRows() {
    global $DBConnKorting;

    // selecteer alle kortingen
    $statement = mysqli_prepare($DBConnKorting, "SELECT StockItemName, korting.* FROM korting JOIN stockitems USING(StockItemID)");
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    mysqli_stmt_close($statement);
    $bg = false;
    while ($row = mysqli_fetch_assoc($result)) {
        $product = $row["StockItemName"];
        $id = $row["StockItemID"];
        $beschrijving = $row["Beschrijving"];
        $startdatum = $row["StartDatum"];
        $einddatum = $row["EindDatum"];
        $hoeveelheid = $row["KortingHoeveelheid"];
        $percentage = $row["KortingPercentage"];
        $prijs = $row["Prijs"];
        $voorraad = $row["Voorraad"];
        if($bg) $bgColor = "bg-table-row-dark";
        else $bgColor = "bg-table-row-light";
        $bg = !$bg;

        print("<form method='post' autocomplete='off' class='row $bgColor pt-1 pb-1'>\n");

        print("<div class='col-3'>$product<input type='hidden' name='id' value='$id'></div>");
        print("<div class='col-1'><input name='hoeveelheid' class='form-control form-row' type='number' min='0' step='0.01' value='$hoeveelheid'></div>");
        print("<div class='col-1'><input name='percentage' class='form-control form-row' type='number' min='0' step='0.01' value='$percentage'></div>");
        print("<div class='col-1'><input name='prijs' class='form-control form-row' type='number' min='0' step='0.01' value='$prijs'></div>");
        print("<div class='col-2'><input name='voorraad' class='form-control form-row' type='number' value='$voorraad'></div>");
        print("<div class='col-1'><input name='startdatum' class='form-control pl-1 pr-1' type='date' value='$startdatum'></div>");
        print("<div class='col-1'><input name='einddatum' class='form-control pl-1 pr-1' type='date' value='$einddatum'></div>");
        print("<div class='col-2'>
                   <button name='submit' value='update' type='submit' class='btn btn-secondary'>Update</button>
                   <button name='submit' value='delete' type='submit' class='btn btn-secondary'>Delete</button>
               </div>");

        print("</form>\n");
    }
}

function printKortingHomeOpIsOp($limit) {
    global $DBConnKorting;
    // query voor producten
    $statement = mysqli_prepare($DBConnKorting, "
    SELECT DISTINCT productlist.* FROM productlist
    JOIN korting USING(StockItemID) WHERE Voorraad IS NOT NULL
    ORDER BY RAND() LIMIT ?");
    mysqli_stmt_bind_param($statement, 'i', $limit);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    while ($row = mysqli_fetch_assoc($result)) {
        $productUrl = "product.php?productID=" . $row["StockItemID"];
        if (!empty($row["FileName"])) {
            $imgpath = "img/product/" . $row["FileName"];
        } else {
            $imgpath = "img/image-placeholder.jpg";
        }

        // korting
        $korting = getKorting($row["StockItemID"], $row["UnitPrice"]);
        $prijs = $korting["prijs"];
        if ($prijs != $row["UnitPrice"]) {
            $prijsOud = $row["UnitPrice"];
        } else {
            $prijsOud= $row["RecommendedRetailPrice"];
        }

        print("
            <div class=\"col-md-2 col-sm-6\">
                <div class=\"product-grid\">
                    <div class=\"product-image\">
                        <a href='" . $productUrl . "'>
                            <img class=\"pic-1\" src='" . $imgpath . "'>
                        </a>");
        printSearchAanbieding($korting);
        printSearchOpIsOp($korting);
        print("     </div>
                    <div class=\"product-content\">
                        <h3 style='margin: 0 0 30px 0;' class=\"title1\"><a href='$productUrl'>" . $row["StockItemName"] . "</a></h3>
                        <div class=\"price-o\"><a class=\"price-o\" href='$productUrl'>€" . $prijs . "</a>
                            <span class='price-n'>€" . $prijsOud . "</span>
                        </div>
                        <a class=\"add-to-cart\" href=\"modify-shoppingcart.php?modType=add&aantal=1&productID=" . $row["StockItemID"] . "\">+ Toevoegen aan winkelwagentje</a>
                    </div>
                </div>
            </div>");
    }
}

function printProductVoorraad($korting, $echteVoorraad) {
    if (!empty($korting["voorraad"])) {
        print("<span style='color: red'>OP IS OP! Nog maar ". $korting["voorraad"] ." op voorraad.</span>");
    } else {
        print("<span>Voorraad: " . $echteVoorraad . "</span>");
    }
}

function printProductAanbieding($korting) {
    if(!empty($korting["type"])) {
        print("<span style='font-size: larger'>Aanbieding! ");
        if($korting["type"] == "hoeveelheid") {
            print("<span>€ -" . $korting["korting"] . "</span>");
        }
        if($korting["type"] == "percentage") {
            print("<span>" . $korting["korting"] . "%</span>");
        }
        print("</span>");
    }
}