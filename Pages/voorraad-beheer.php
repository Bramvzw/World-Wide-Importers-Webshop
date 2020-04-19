<?php
include "includes/config.php";

// load header
$title = "Voorraadbeheer";
include "includes/header.php";

// database init
$DBConnVoorraad = newDBConn("stockedit");

// functions
function printVoorraad() {
    global $DBConnVoorraad;
    $stmt = mysqli_prepare($DBConnVoorraad, "SELECT StockItemID, StockItemName, QuantityOnHand FROM stockitemholdings JOIN stockitems USING(StockItemID)");
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    // print page open
    print("<div class='container'>\n<table class='table table-striped table-bordered'>\n");
    print("<tr>\n<th>Product</th><th>Voorraad</th>\n</tr>\n");

    // print regels
    while ($row = mysqli_fetch_assoc($result)) {
        print("<tr>\n");
        print("<td>" . $row["StockItemName"] . "</td>\n");
        print("<td><form method='post'><input class='' type='number' min='0' value='" . $row["QuantityOnHand"] . "' name='voorraad'><input type='hidden' name='id' value='" . $row["StockItemID"] . "'><button class='btn btn-secondary' type='submit' name='submit' value='1'>Update</button></form></td>\n");
        print("</tr>\n");
    }
    // print page close
    print("</table></div>\n");
}

function bewerkVoorraad() {
    global $DBConnVoorraad;
    $id = filter_var($_POST["id"], FILTER_SANITIZE_NUMBER_INT);
    $voorraad = filter_var($_POST["voorraad"], FILTER_SANITIZE_NUMBER_INT);

    $stmt = mysqli_prepare($DBConnVoorraad, "UPDATE stockitemholdings SET QuantityOnHand = ? WHERE StockItemID = ?");
    mysqli_stmt_bind_param($stmt, "ii", $voorraad, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// body
// hoofdcontrole of medewerker wel is ingelogd
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true && $_SESSION["userDetails"]["UserType"] == 3) {
    // main body
    if(!empty($_POST["submit"])) {
        bewerkVoorraad();
    }

    printVoorraad();
} else {
    // geef terug dat medewerker ingelogd moet zijn.
    print("<h1>Niet ingelogd als medewerker!</h1>");
}

// footer
include "includes/footer.php";