<?php
session_start();
// check of winkelwagen niet leeg is in sessie
if(!empty($_SESSION["Winkelwagen"])) {
    // als winkelwagen bestaat, dan deze tijdelijk in $cart zetten.
    $cart = $_SESSION["Winkelwagen"];
}
// sloop de sessie
session_destroy();

// kijk of $cart bestaat; als $cart bestaat dan is winkelwagen niet leeg en moet die weer teruggezet worden.
if(isset($cart)) {
    // start sessie weer op en plaats de winkelwagen array weer terug.
    session_start();
    $_SESSION["Winkelwagen"] = $cart;
}
header("Location: index.php");