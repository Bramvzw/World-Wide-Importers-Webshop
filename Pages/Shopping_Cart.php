<?php
include "includes/config.php";
include "includes/korting.php";
$DBSearchConn = newDBConn("productview");
$title = "Winkelwagen";
// include header
include "includes/header.php";

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div class="shopping-cart">
    <!-- Title -->
    <div class="title">
        Winkelwagentje
    </div>
</div>
<!-- Product -->
<div class="container-fluid" style="border:transparent;" >
    <?php
    $totaal = 0;
    kortingDB("open");
    foreach ($_SESSION["Winkelwagen"] as $productID => $aantal ) {

        $statement = mysqli_prepare($DBSearchConn, "SELECT StockItemID, StockItemName, UnitPrice, FileName FROM productlist WHERE StockItemID = ?");
        mysqli_stmt_bind_param($statement, "i", $productID);
        mysqli_stmt_execute($statement);
        $Result = mysqli_stmt_get_result($statement);
        mysqli_stmt_close($statement);
        $row = mysqli_fetch_assoc($Result);
        if ($row["FileName"] != null) {
            $imgPath = "img/product/".$row["FileName"];
        } else {
            $imgPath = "img/image-placeholder.jpg";
        }

        // kortingen
        $korting = getKorting($row["StockItemID"], $row["UnitPrice"]);
        $prijs = $korting["prijs"];

        $subtotaal = $aantal * $prijs;
        ?>

    <div class="row" style="border-bottom: solid 1px #E1E8EE; margin: 0;">
            <a class="sh-link" href="product.php?productID=<?=$productID?>"> <img class="sh-img" src="  <?= $imgPath ?>"/> </a>
            <div class="sh-titel">
                <h3 style="margin-right: auto;"><?php print ($row["StockItemName"]) ?> </h3>
                <div class="sh-prijs">
                    <h3 class="sh-prijs">€<?php
                        print $prijs; ?></h3>
                </div>
            </div>

    </div>
            <!-- + & i buttons -->
    <div class="row" style="position: relative; top: -100px; left: 120px;">
        <form class="input-group input-number-group" method="get" action="modify-shoppingcart.php"
                  style="width:150px; height: 48px; margin: auto;" >
                <div class="input-group-button">
                        <span class="input-number-decrement">-</span>
                </div>
                    <input type="hidden" name="productID" value="<?= $row["StockItemID"] ?>">
                    <input class="input-number" type="number" id="counter" min="1" autocomplete="off" value="<?= $aantal ?>"
                                style="width:40px; text-align: center" name="aantal">
                    <div class="input-group-button">
                        <span class="input-number-increment">+</span>
                    </div>
                <button class="icon" type="submit" name="modType" value="modify">
                    <i class="fa fa-refresh"></i>
                </button>
            <button class="icon" type="submit" name="modType" value="delete">
                <i class="fa fa-trash"></i>
            </button>
        </form>
    </div>

        <!-- prijs -->
    <div class="sh-subtotaal" style="top: -150px; margin-right: 40px;" >
        <p>Subtotaal €<?= $subtotaal ?></p>
    </div>
    <?php
    $totaal += $subtotaal;
            }
    kortingDB("close");
    ?>
</div>




<!-- Controle of er producten in winkelwagentje zitten -->
<?php  if ($totaal != 0) {
?>
    <!-- Print totaal met totaalprijs -->
    <div class="sh-totaal">
        <p>Totaal €<?= $totaal ?></p>
    </div>
    <?php
    if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
        ?>
        <!-- print button: 'plaats bestelling'-->
        <a href="place-order.php" class=" sh-bg btn btn-primary" style="position: relative;">
            <strong> plaats bestelling </strong>
        </a>

        <?php
    } else {

        ?>

        <!-- print button/pop-up: 'eerst inloggen!' -->
        <button type="button" class="btn btn-primary sh-bg" data-toggle="modal" data-target="#exampleModal">
            Plaats Bestelling
        </button>

        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eerst Inloggen</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Om een bestelling te kunnen plaatsen moet u eerst ingelogd zijn
                    </div>
                    <div class="modal-footer">
                        <a href="login.php" type="button" class="btn btn-primary">ga naar inloggen...</a>
                        <a href="register.php" type="button" class="btn btn-primary">ga naar registreren...</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>


    <!-- print 'niks in ww' td=button naar categorieën -->
<?php
    } else {
        print("<div class=\"no-sh\">
                <p> Geen items in je winkelwagentje.</p>
                   </div>");
    }
?>






<!-- het verhogen en verlagen van items in ww-->
<script>
    $('.input-number-increment').click(function() {
        var $input = $(this).parents('.input-number-group').find('.input-number');
        var val = parseInt($input.val(), 10);
        $input.val(val + 1);
    });

    $('.input-number-decrement').click(function() {
        var $input = $(this).parents('.input-number-group').find('.input-number');
        var val = parseInt($input.val(), 10);
        $input.val(Math.max(val - 1, 1));
    })
</script>

<?php
//include "includes/footer.php";
?>
<!--
fix footer!!!
styling


-->
