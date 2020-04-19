<?php
include "includes/config.php";
include "includes/korting.php";

$title = "Kortingen Beheren";
include "includes/header.php";
// controleren of er een medewerker is ingelogd
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true && $_SESSION["userDetails"]["UserType"] == 3) {
    // post check
    if(!empty($_POST["submit"])) {
        kortingDB("open", true);
        $id = $_POST["id"];
        $hoeveelheid = $_POST["hoeveelheid"];
        $percentage = $_POST["percentage"];
        $prijs = $_POST["prijs"];
        $voorraad = $_POST["voorraad"];
        $startdatum = $_POST["startdatum"];
        $einddatum = $_POST["einddatum"];

        // waarden die 0 zijn naar null zetten
        if($hoeveelheid == 0) $hoeveelheid = null;
        if($percentage == 0) $percentage = null;
        if($prijs == 0) $prijs = null;
        if($voorraad == 0) $voorraad = null;


        // controleren of er maar 1 waarde is gezet voor hoeveelheid, percentage of prijs.
        if (
            // hoeveelheid
            (!empty($hoeveelheid) && empty($percentage) && empty($prijs)) ||
            // percentage
            (empty($hoeveelheid) && !empty($percentage) && empty($prijs)) ||
            // prijs
            (empty($hoeveelheid) && empty($percentage) && !empty($prijs)) ||
            // alleen voorraad
            (empty($hoeveelheid) && empty($percentage) && empty($prijs) && !empty($voorraad))
        ) {
            //print($hoeveelheid . " " . $percentage . " " . $prijs);

            // database interactions
            // kijken naar wat voor modificatie
            if($_POST["submit"] == "add") {
                // toevoegen
                $beschrijving = "system";
                if(!$statement = mysqli_prepare($DBConnKorting, "
                    INSERT INTO korting (StockItemID, Beschrijving, StartDatum, EindDatum, KortingHoeveelheid, KortingPercentage, Prijs, Voorraad)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) print("prepare error");
                if(!mysqli_stmt_bind_param($statement, "isssssss",
                $id, $beschrijving, $startdatum, $einddatum, $hoeveelheid, $percentage, $prijs, $voorraad)) print("param error");
                if(!mysqli_stmt_execute($statement)) print("execute error");
                mysqli_stmt_close($statement);


            } elseif ($_POST["submit"] == "update") {
                // updaten
                if(!$statement = mysqli_prepare($DBConnKorting, "UPDATE korting
                    SET StartDatum = ?, EindDatum = ?, KortingHoeveelheid = ?, KortingPercentage = ?, Prijs = ?, Voorraad = ?
                    WHERE StockItemID = ?")) print("prepare error");
                if(!mysqli_stmt_bind_param($statement, "ssssssi",
                $startdatum, $einddatum, $hoeveelheid, $percentage, $prijs, $voorraad, $id)) print("param error");
                if(!mysqli_stmt_execute($statement)) print("execute error");
                mysqli_stmt_close($statement);


            } elseif ($_POST["submit"] == "delete") {
                // verwijderen
                if(!$statement = mysqli_prepare($DBConnKorting, "DELETE FROM korting WHERE StockItemID = ?")) print("prepare error");
                if(!mysqli_stmt_bind_param($statement, "i", $id)) print("param error");
                if(!mysqli_stmt_execute($statement)) print("execute error");
                mysqli_stmt_close($statement);

            }

        } elseif (empty($hoeveelheid) && empty($percentage) && empty($prijs)){
            die("Geen kortingswaarde");
        } else {
            die("Te veel waarden gezet. Maximaal 1");
        }
        kortingDB("close");
    }
    kortingDB("open");
    // page content
    ?>
    <div class="title">Kortingen beheren</div>
    <div class="container-fluid pt-3 pb-3">
        <div class="">
            <div class="row"><h5 style="font-weight: bold">Toevoegen</h5></div>
            <div class="row" style="font-weight: bold">
                <div class="col-3">Product</div>
                <div class="col-1">Hoeveelheid</div>
                <div class="col-1">Percentage</div>
                <div class="col-1">Prijs</div>
                <div class="col-2">Op is Op Voorraad</div>
                <div class="col-1">Startdatum</div>
                <div class="col-1">Einddatum</div>
                <div class="col-2">Opties</div>
            </div>
            <?php
            print("<form method='post' autocomplete='off' class='row pt-1 pb-1'>\n");

            print("<div class='col-3'>");
            printKortingBeheerProductSelectList();
            print("</div>");
            print("<div class='col-1'><input name='hoeveelheid' class='form-control form-row' type='number' min='0' step='0.01'></div>");
            print("<div class='col-1'><input name='percentage' class='form-control form-row' type='number' min='0' step='0.01'></div>");
            print("<div class='col-1'><input name='prijs' class='form-control form-row' type='number' min='0' step='0.01'></div>");
            print("<div class='col-2'><input name='voorraad' class='form-control form-row' type='number'></div>");
            print("<div class='col-1'><input name='startdatum' class='form-control pl-1 pr-1' type='date' value='".date("Y-m-d")."' required></div>");
            print("<div class='col-1'><input name='einddatum' class='form-control pl-1 pr-1' type='date' required></div>");
            print("<div class='col-2'>
                   <button name='submit' value='add' type='submit' class='btn btn-secondary'>Toevoegen</button>
               </div>");

            print("</form>\n");
            ?>
        </div>
        <div class="">
            <div class="row"><h5 style="font-weight: bold">Inzien / Bewerken</h5></div>
            <div class="row" style="font-weight: bold">
                <div class="col-3">Product</div>
                <div class="col-1">Hoeveelheid</div>
                <div class="col-1">Percentage</div>
                <div class="col-1">Prijs</div>
                <div class="col-2">Op is Op Voorraad</div>
                <div class="col-1">Startdatum</div>
                <div class="col-1">Einddatum</div>
                <div class="col-2">Opties</div>
            </div>
            <?php
            printKortingBeheerRows();
            ?>
        </div>


    </div>
    <?php
    kortingDB("close");
} else {
    print("Niet ingelogd als medewerker.");
}
include "includes/footer.php";
?>