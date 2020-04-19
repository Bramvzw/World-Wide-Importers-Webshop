<?php
include "includes/config.php";


function getCart() {
    /* @getCart() haalt de cart op uit de winkelwagen en geeft deze terug
     * Als er winkelwagen in sessie leeg is, wordt er een leeg array teruggeven
     */
    if(!empty($_SESSION["Winkelwagen"])) {
        return $_SESSION["Winkelwagen"];
    } else {
        return [];
    }
}

function setCart($cart) {
    /* @setCart($cart)
     * pakt $cart uit de input van functie en plaatst deze in de sessie waarde voor winkelwagen.
     */
    return $_SESSION["Winkelwagen"] = $cart;
}

function checkProductID($ProductID) {
    /* @checkProductID($ProductID)
     * Deze functie controleert of het gegeven product id bestaat in de database.
     * Er wordt een boolean teruggegeven. true als het product id voorkomt.
     */
    global $DBConnShoppingCart;
    $stmt = mysqli_prepare($DBConnShoppingCart, "SELECT * FROM productlist WHERE StockItemID = ?");
    mysqli_stmt_bind_param($stmt, "i", $ProductID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return mysqli_num_rows($result) == 1;
}
function checkProductStock($productID, $aantal) {
    /* @checkProductStock($productID, $aantal)
     * Deze functie controleert of er nog wel voldoende voorraad is voor het gegeven aantal van het gegeven product.
     * Het keert true terug als er nog voorraad is.
     */
    global $DBConnShoppingCart;
    $stmt = mysqli_prepare($DBConnShoppingCart, "SELECT * FROM productlist WHERE StockItemID = ?");
    mysqli_stmt_bind_param($stmt, "i", $productID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    $row = mysqli_fetch_assoc($result);
    return $aantal < $row["QuantityOnHand"];
}

function amountInCart($productID) {
    // haalt het aantal van het gegeven product in de winkelwagen op.
    global $cart;
    if (isset($cart[$productID])) {
        return $cart[$productID];
    } else return null;
}

function addToCart($productID, $aantal) {
    // Functie voor toevoegen aan winkelwagen
    global $cart;
    // checken of item al in winkelwagen zit
    if(amountInCart($productID > 0)) {
        // item zit al in winkelwagen, stuur door naar modify
        // tel items op
        $aantal += amountInCart($productID);
        // controleer voorraad en stuur door
        if (checkProductStock($productID, $aantal)) {
            return modifyCart($productID, $aantal);
        } else {
            return false;
        }
    } else {
        return $cart[$productID] = $aantal;
    }
}

function modifyCart($productID, $aantal) {
    // aanpassen winkelwagen.
    // controleert stock en wijzigt het aantal voor het gegeven product id.
    global $cart;
    if(amountInCart($productID) > 0 && checkProductStock($productID, $aantal)) {
        return $cart[$productID] = $aantal;
    } else {
        return false;
    }
}

function deleteFromCart($productID) {
    // verwijderen uit winkelwagen.
    global $cart;
    if(amountInCart($productID) > 0) {
        unset($cart[$productID]);
        return true;
    }
    return false;
}

function printRedirectAlert($msg) {
    print ("<script>
    alert('".$msg."'); 
             window.history.go(-1);
     </script>");
}

$DBConnShoppingCart = newDBConn("productview");

?>
<!doctype html>
<html>
<head>
    <title>Verwerk winkelwagen &bull; WWI</title>
</head>
<body>
<?php
// begin met het checken van GET variabelen
$modType = null;
$success = false;
$cart = getCart();

// controleer of modtype gezet is
if (!empty($_GET["modType"])) {
    // modtype filteren op input
    $modType = filter_var($_GET["modType"], FILTER_SANITIZE_STRING);
    if(!empty($_GET["productID"])) {
        // filteren op input
        $productID = (int)filter_var($_GET["productID"], FILTER_SANITIZE_NUMBER_INT);
    }
    if(!empty($_GET["aantal"])) {
        // input filteren en controleren of aantal boven 1 is.
        $aantalPreCheck = (int)filter_var($_GET["aantal"], FILTER_SANITIZE_NUMBER_INT);
        if ($aantalPreCheck >= 1) {
            $aantal = $aantalPreCheck;
        }
    }
}

// controleren of productid gegeven is, daarna gecontroleerd op type aanpassing
// per type wordt een functie opgeroepen.
if(isset($productID) && checkProductID($productID)) {
    if (isset($aantal) && $modType == "add") {
        $success = addToCart($productID, $aantal);
    } elseif (isset($aantal) && $modType == "modify") {
        $success = modifyCart($productID, $aantal);
    } elseif ($modType == "delete") {
        $success = deleteFromCart($productID);
    }
}

// als het gelukt is wordt de cart teruggezet in de sessie
if($success) {
    setCart($cart);
}

// meldingen
if(!$success) {
    printRedirectAlert("Er is iets foutgegaan.");
} elseif ($modType == "add") {
    printRedirectAlert("Item succesvol toegevoegd!");
} elseif ($modType == "modify") {
    printRedirectAlert("Item succesvol gewijzigd!");
} elseif ($modType == "delete") {
    printRedirectAlert("Item succesvol verwijderd!");
}

// sluiten verbinding
mysqli_close($DBConnShoppingCart);
?>
</body>
</html>
