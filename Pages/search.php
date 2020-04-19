<?php
include "includes/config.php";
include "includes/korting.php";

//     zoeken op producttitle binnen een category
$DBSearchConn = newDBConn("productview");
mysqli_set_charset($DBSearchConn, "utf8mb4_general_ci");
if(!empty($_GET["productTitle"]) && !empty($_GET["Category"])) {
    $productTitle = filter_var($_GET["productTitle"], FILTER_SANITIZE_STRING);
    $productTitleQuery = "%".$productTitle."%";
    $category = filter_var($_GET["Category"], FILTER_SANITIZE_NUMBER_INT);
    $statement = mysqli_prepare($DBSearchConn, "SELECT productlist.* FROM productlist JOIN stockitemstockgroups USING(stockitemID) WHERE stockgroupID = ? AND StockItemName LIKE ?;");
    mysqli_stmt_bind_param($statement, "is", $category, $productTitleQuery);
    mysqli_stmt_execute($statement);
    $searchResult = mysqli_stmt_get_result($statement);


    // zoeken op alleen productitle
} elseif(!empty($_GET["productTitle"])) {
    $productTitle = filter_var($_GET["productTitle"], FILTER_SANITIZE_STRING);
    $productTitleQuery = "%".$productTitle."%";
    $statement = mysqli_prepare($DBSearchConn, "SELECT * FROM productlist WHERE StockItemName LIKE ?;");
    mysqli_stmt_bind_param($statement, "s",$productTitleQuery);
    mysqli_stmt_execute($statement);
    $searchResult = mysqli_stmt_get_result($statement);

    // zoeken op alleen category
} elseif (!empty($_GET["Category"])) {
    $category = filter_var($_GET["Category"], FILTER_SANITIZE_NUMBER_INT);
    $statement = mysqli_prepare($DBSearchConn, " SELECT productlist.* FROM productlist JOIN stockitemstockgroups USING(stockitemID) WHERE stockgroupID = ?;");
    mysqli_stmt_bind_param($statement, "i", $category);
    mysqli_stmt_execute($statement);
    $searchResult = mysqli_stmt_get_result($statement);

    // laat alle producten zien
} else {
    $statement = mysqli_prepare($DBSearchConn, "SELECT * FROM productlist;");
    mysqli_stmt_execute($statement);
    $searchResult = mysqli_stmt_get_result($statement);
}
// include header
include "includes/header.php";

  ?>
 <!-- dropdowm menu-->
    <nav class="navbar navbar-expand-lg navbar-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupported" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupported" style="font-size: 20px; margin: 40px 0 0 80px;">
            <ul class="navbar-nav">
                    <?php
                    // checkt of category leeg is
                    if(empty($_GET["Category"])) {
                        $category =0;
                    } else {
                        $category = $_GET["Category"];
                    }

                    // print categorie naam: 'alle'
                    if(empty($_GET["Category"])) {
                        print("<a class='dropdown-item' style='font-weight: bold;' href='search.php?productTitle="."'>Alle</a> ");
                    } else {
                        print("<a class='dropdown-item' href='search.php?productTitle="."'>Alle</a> ");
                    }

                    $statementCat = mysqli_prepare($DBSearchConn, "SELECT StockGroupID, StockGroupName FROM stockgroups ORDER BY StockGroupName");
                    mysqli_stmt_execute($statementCat);
                    $resultCat = mysqli_stmt_get_result($statementCat);


                        // print categorie namen behalve 'alle'
                    while($row = mysqli_fetch_assoc($resultCat)) {
                        if (empty($productTitle)) {
                            if ($row["StockGroupID"] == $category) {
                                print("<a class='dropdown-item active-item' style='font-weight: bold;' href='search.php?Category=" . $row["StockGroupID"] . "'>" . $row["StockGroupName"] . "</a>");
                            } else {
                                print("<a class='dropdown-item active-item' href='search.php?Category=" . $row["StockGroupID"] . "'>" . $row["StockGroupName"] . "</a>");
                            }
                        } else {
                            if ($row["StockGroupID"] == $category) {
                                print("<a class='dropdown-item active-item' style='font-weight: bold;' href='search.php?Category=" . $row["StockGroupID"] . "&productTitle=" . $productTitle . "'>" . $row["StockGroupName"] . "</a>");
                            } else {
                                print("<a class='dropdown-item active-item' href='search.php?Category=" . $row["StockGroupID"] . "&productTitle=" . $productTitle . "'>" . $row["StockGroupName"] . "</a>");
                            }
                        }
                    }

                    ?>
            </ul>
        </div>
    </nav>

<!-- product box  -->
    <div class="container">
        <div class="row">
            <?php

            $row_cnt = mysqli_num_rows($searchResult);

            if ($row_cnt == 0) {
                print (" <div class=\"col\">
                    <img class=\"no-products-found\" src='img/noproduct.png' style=\"position:relative; left: 300px;\">
                    </div>");
            } else {
                kortingDB("open");
                print ("<h3 class=\"Title-search-producten\">Producten</h3>
                        <div class=\"row\">");
                while ($row = mysqli_fetch_assoc($searchResult)) {
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
                        <div class=\"col-md-3 col-sm-6\">
                            <div class=\"product-grid\">
                                <div class=\"product-image\" >
                                    <a href='" . $productUrl . "'>
                                        <img class=\"pic-1\" src='" . $imgpath . "'>
                                    </a>");
                    printSearchAanbieding($korting);
                    printSearchOpIsOp($korting);
                    print(" </div>
                                <div class=\"product-content\">
                                    <h3 class=\"title1\"><a href='$productUrl' style='position: relative; top: -13px; font-size: 14px;'>" . $row["StockItemName"] . "</a></h3>
                                    <div class=\"price-o\"><a class=\"price-o\" href='$productUrl'>€" . $prijs . "</a>
                                        <span class='price-n'>€" . $prijsOud . "</span>
                                    </div>
                                    <a class=\"add-to-cart\" href=\"modify-shoppingcart.php?modType=add&aantal=1&productID=" . $row["StockItemID"] . "\">+ Toevoegen aan winkelwagentje</a>
                                </div>
                            </div>
                        </div>
                    ");
                }
                kortingDB("close");
            }
            ?>
    </div>
</div>

<?php
//include footer
include "includes/footer.php";
?>