<?php
include "includes/config.php";
include "includes/korting.php";

// check of de productID is gezet in GET
if(empty($_GET["productID"])) {
    // als niet gezet, herleid naar 404.php
    header("Location: 404.php");
} else {
    // als gezet, zet $productID
    $productID = $_GET["productID"];
    // converteer productid naar een int, als dat niet lukt is de ingevulde waarde voor het product fout, herleid dan naar 404.php
    if(!($productID = (int)$productID)) {
        header("Location: 404.php");
    }
}


// maak een verbinding met de database als user 'productview'
$DBConnection = newDBConn("productview");
// vraag info van het desbetreffende product op uit de database
$productStatement = mysqli_prepare($DBConnection,
    "
                SELECT S.StockItemID, S.StockItemName, S.UnitPrice, S.RecommendedRetailPrice, S.SearchDetails, SH.QuantityOnHand
                FROM stockitems S
                JOIN stockitemholdings SH USING(StockItemID)
                WHERE S.StockItemID = ?
                ");
mysqli_stmt_bind_param($productStatement, 'i', $productID);
mysqli_stmt_execute($productStatement);
$productResult = mysqli_stmt_get_result($productStatement);
if(mysqli_num_rows($productResult) == 0) {
    header("Location: 404.php");
}
$productResultRow = mysqli_fetch_array($productResult, MYSQLI_ASSOC);
$productTitle = $productResultRow["StockItemName"];
$productDescription = $productResultRow["SearchDetails"];
$productCurrentPrice = $productResultRow["UnitPrice"];
$productRecommendedPrice = $productResultRow["RecommendedRetailPrice"];
$actualQuantity = $productResultRow["QuantityOnHand"];

// kortingen
kortingDB("open");
$korting = getKorting($productID, $productCurrentPrice);
if ($korting["prijs"] != $productCurrentPrice) {
    $productRecommendedPrice = $productCurrentPrice;
    $productCurrentPrice = $korting["prijs"];
}
$voorraad = $actualQuantity;

kortingDB("close");

// Header stuff
$title = $productTitle; // zet de paginatitel naar de producttitel
include "includes/header.php";
//content here
?>
<div class="container-fluid" style="max-width: 1200px;">
    <?php

    ?>
    <div class="row">
        <div class="col-lg-5 card">
            <?php
            // Doe een query op de database om te zoeken of er afbeeldingen aanwezig zijn voor het product
            $statement = mysqli_prepare($DBConnection, "SELECT * FROM resource WHERE Type = 'IMG' AND StockItemID = ?");
            mysqli_stmt_bind_param($statement, "i", $productID); // productID gaat over het huidige product in de pagina
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);

            // check of er resulterende rijen zijn
            if(mysqli_num_rows($result) > 0) {
                // er is 1 of meer afbeeldingen gevonden
                while ($row = mysqli_fetch_assoc($result)) {
                    print("<img class='img-fluid' src='img/product/".$row['FileName']."'>");
                }
            } else {
                // er zijn geen afbeeldingen gevonden, plaats een placeholder
                print("<img class='img-fluid' src='img/image-placeholder.jpg'>");
            }
?>
            <div class="row">
                <div class="col-lg-4 col-md-12 mb-4 col">
                    <?php
                    // Doe een query op de database om te zoeken of er afbeeldingen aanwezig zijn voor het product
                    $statement = mysqli_prepare($DBConnection, "SELECT * FROM resource WHERE Type = 'IMG' AND StockItemID = ?");
                    mysqli_stmt_bind_param($statement, "i", $productID); // productID gaat over het huidige product in de pagina
                    mysqli_stmt_execute($statement);
                    $result = mysqli_stmt_get_result($statement);
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        print("<img class='img-fluid' src='img/product/".$row['FileName'] . "'>");
                    }
                    // haal video informatie op uit database met een query
                   ?>
                </div>
                <div class="col-lg-4 col-md-12 mb-4 col">
                        <?php
                        $statement = mysqli_prepare($DBConnection, "SELECT * FROM resource WHERE Type = 'VID' AND StockItemID = ?");
                        mysqli_stmt_bind_param($statement, "i", $productID); // productID gaat over het huidige product in de pagina
                        mysqli_stmt_execute($statement);
                        $result = mysqli_stmt_get_result($statement);
                        // toon de video binnen de div.
                        if (mysqli_num_rows($result) == 1){
                            $row = mysqli_fetch_assoc($result);
                            print(" <a><img class='img-fluid col' src='img/video-play-button-icon-5.jpg' alt=\"video\" data-toggle=\"modal\" data-target=\"#modal1\"></a> ");
                            ?><div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body mb-0 p-0">
                                            <div class="embed-responsive embed-responsive-16by9 z-depth-1-half"><?php
                                                    print("  <iframe src='".$row['URL']."' style='min-height: 500px'></iframe>");
                                                    ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          <?php
                        }
                        ?>
                </div>
            </div>

            <!-- Grid row -->
        </div>
        <div class="col-lg-7">
            <div class="row card">
                <h3 class="help"><b><?=$productTitle?></b></h3><br>
                <p class="help"><?=$productDescription?></p>
            </div>
            <div class="row card help">
                <?php printProductAanbieding($korting); ?>
                <span class="prijs-style"> Prijs: Retail prijs <span class="strike">€ <?=$productRecommendedPrice?></span> Bij ons € <?=$productCurrentPrice?> </span>
                <?php printProductVoorraad($korting, $voorraad); ?><br>
                <form class="row" method="get" action="modify-shoppingcart.php">
                    <input type="hidden" id="inputProductID" name="productID" value="<?=$productID?>">
                    <label for="aantal" class="col-3">Aantal: </label>
                    <input type="number" class="form-control product-pagina-aantal" id="aantal" name="aantal" min="1" max="1000"><br>
                    <button type="submit" class="knop-product btn btn-primary" name="modType" value="add">Toevoegen aan winkelwagen</button>
                </form>
            </div>

        </div>
    </div>
    <div style='margin: 0 0 0 -15px;' class="container-fluid card help">
        Wellicht bent u geinteresseerd in:
        <div class="row">
            <div class="col">
        <?php
        $DBConn = newDBConn("productview");

        function randaanbeveling($productid) {
            global $DBConn;
            $statement = mysqli_prepare($DBConn, "SELECT * FROM productlist WHERE StockItemID = ? ");
            mysqli_stmt_bind_param($statement, 'i', $productid);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $row = mysqli_fetch_assoc($result);
            if ($row['FileName'] != null){
                $imgpath = "img/product/" . $row['FileName'];
            } else {
                $imgpath = "img/image-placeholder.jpg";
            }
            $name = $row['StockItemName'];
            $price = $row['UnitPrice'];
            $product = $row['StockItemID'];
            echo "<div class=\"banner\" style=\" height: 25rem;  width: 18rem;\">
            <a href=\"product.php?productID=$product\">
              <img class=\"imgban-1\" src=\"$imgpath\" style=\"height: 250px; width: 250px;\">
              <div class=\"card-body\">
                <a href=\"product.php?productID=$product\" class=\"link-imgban-1 btn btn-primary\">Ga naar product</a>
                <p class=\"imgban-1-title\">$name</p><br><br>
                <p class=\"imgban-1-pr\">$price</p>
</div>
</a>
</div>";
        }
        randaanbeveling(rand(1,227));
        randaanbeveling(rand(1,227));
        randaanbeveling(rand(1,227));
        ?>
        </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    document.getElementById('aantal').value ='1';
</script>
<?php
// afsluiten database verbinding
mysqli_close($DBConnection);

// include footer
include "includes/footer.php";
?>

