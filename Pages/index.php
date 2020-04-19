<?php
// include config
include "includes/config.php";
include "includes/korting.php";
kortingDB("open");
// include header
$title = "World Wide Importers Webshop";
include "includes/header.php";

// carousel
?>
<div id="demo" class="carousel slide" data-ride="carousel">
    <ul class="carousel-indicators">
        <li data-target="#demo" data-slide-to="0" class="active"></li>
        <li data-target="#demo" data-slide-to="1"></li>
        <li data-target="#demo" data-slide-to="2"></li>
    </ul>
    <div class="carousel-inner carousel-outer">
        <div class="carousel-item active">
            <a href="https://ictm1e5-wwi.tk/search.php?Category=3" style="text-decoration: none; color: white">
                <img src="img/wall-mug.png" alt="Los Angeles" width="1100" height="500">
                <div class="carousel-caption">
                    <h3>World Wide Importers</h3>
            </a>
            <p>mokken</p>
        </div>
    </div>
    <div class="carousel-item carousel-outer">
        <a href="https://ictm1e5-wwi.tk/search.php?Category=7" style="text-decoration: none; color: white">
        <img src="img/USB.jpg" alt="Chicago" width="1100" height="500">
        <div class="carousel-caption">
            <h3>World Wide Importers</h3>
            <p>USB</p>
        </div>
    </div>
    <div class="carousel-item carousel-outer">
        <a href="https://ictm1e5-wwi.tk/search.php?Category=9" style="text-decoration: none; color: white">
        <img src="img/rcar.jpg" alt="New York" width="1100" height="500">
        <div class="carousel-caption">
            <h3>World Wide Importers</h3>
            <p> afstandbestuurbare auto's</p>
        </div>
    </div>
</div>
<a class="carousel-control-prev" href="#demo" data-slide="prev">
    <span class="carousel-control-prev-icon"></span>
</a>
<a class="carousel-control-next" href="#demo" data-slide="next">
    <span class="carousel-control-next-icon"></span>
</a>
</div>

<!-- Op is Op gedeelte-->
<h1 class="head-ban1"> OP = OP!</h1>
<div class='container' style='max-width: 1800px'>
    <div class='row'>
<!-- product boxes bij op is op -->
        <?php
        printKortingHomeOpIsOp(6);
        ?>
    </div>
</div>

<?php

// Jack's Aanbevelingen
echo "<h1 class=\"head-ban1\"> Aanbevelingen</h1><br>";

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

    // extra code korting Mike Thomas.
    $productUrl = "product.php?productID=" . $row["StockItemID"];
    $korting = getKorting($product, $price);
    if ($price != $korting["prijs"]) {
        $priceold = $price;
    } else {
        $priceold = $row["RecommendedRetailPrice"];
    }
    $price = $korting["prijs"];

    // print product zoals in search
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
                    <h3 style='margin: 0 0 30px 0;' class='title1' style=''><a href='$productUrl'>" . $name . "</a></h3>
                    <div class=\"price-o\"><a class=\"price-o\" href='$productUrl'>€" . $price . "</a>
                        <span  class='price-n'>€" . $priceold . "</span>
                    </div>
                    <a class=\"add-to-cart\" href=\"modify-shoppingcart.php?modType=add&aantal=1&productID=" . $row["StockItemID"] . "\">+ Toevoegen aan winkelwagentje</a>
                </div>
            </div>
        </div>");

/*  oude print van Jack waarmee de producten worden getoond.
 *  echo "<div class=\"banner\" style=\" height: 25rem;  width: 18rem;\">
 *  <a href=\"#\">
 *      <img class=\"imgban-1\" src=\"$imgpath\" style=\"height: 250px; width: 250px;\">
 *      <div class=\"card-body\">
 *          <a href=\"product.php?productID=$product\" class=\"link-imgban-1 btn btn-primary\">Ga naar product</a>
 *          <p class=\"imgban-1-title\">$name</p><br><br>
 *          <p class=\"imgban-1-pr\">$price</p>
 *      </div>
 *  </a>
 *  </div>"; */
}

// aanbevelingen
print("<div class='container' style='max-width: 1800px'><div class='row'>");
randaanbeveling(rand(1,227));
randaanbeveling(rand(1,227));
randaanbeveling(rand(1,227));
randaanbeveling(rand(1,227));
randaanbeveling(rand(1,227));
randaanbeveling(rand(1,227));
print("</div></div>");

kortingDB("close");
//include footer
include "includes/footer.php";
?>