<?php
// maakt functie aan voor klant
function printUserDropdown() {
    if (!empty($_SESSION["logged_in"])) {
        if (!empty($_SESSION["userDetails"]["KlantID"])) {
            print("<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"navbarDropdown\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Welkom, " . $_SESSION["userDetails"]["Voornaam"] . "</a>");
        } else {
            print("<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"navbarDropdown\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Mijn Account</a>");
        }
        // bekijk je account
        print("<div class=\"dropdown-menu\" aria-labelledby=\"navbarDropdown\"><a class=\"dropdown-item\" href=\"mijn-account.php\">Mijn Account</a>");
        // bekijk je bestellingen
        if (!empty($_SESSION["userDetails"]["KlantID"])) {
            print("<a class=\"dropdown-item\" href=\"vieworder.php\">Mijn Bestellingen</a>");
        }
        // medewerker privileges
        if (!empty($_SESSION["userDetails"]["UserType"]) && $_SESSION["userDetails"]["UserType"] == 3) {
            print("<a class=\"dropdown-item\" href=\"korting-beheer.php\">Korting Beheren</a>");
            print("<a class=\"dropdown-item\" href=\"voorraad-beheer.php\">Voorraad Beheren</a>");
            print("<a class=\"dropdown-item\" href=\"newsletter.php\">Versturen Nieuwsbrief</a>");
        }
        // uitloggen zowel medewerker als klant
        print(" <a class=\"dropdown-item\" href=\"logout.php\">Uitloggen</a>                 
            </div>");
        // niet ingelogd: inloggen en registreren
    } else {
        print("<a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"navbarDropdown\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Account</a>");
        print("<div class=\"dropdown-menu\" aria-labelledby=\"navbarDropdown\">
                                <a class=\"dropdown-item\" href=\"login.php\">Inloggen</a>
                                <a class=\"dropdown-item\" href=\"register.php\">Registreren</a>
                </div>");
    }
}
?>
<!-- showt afbeeldig WWI en dropdown menu wanneer mobile size actief is -->
<nav class="navbar navbar-expand-lg navbar-light bg-light page-head">
    <a class="navbar-brand " href="index.php"> <img src="img/wwi.png" style="height: 71px; width: 196px;"> </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>


    <!-- Home = active -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="nav-link" href="index.php">  Home <span class="sr-only">(current)</span></a>

            </li>
            <!-- Categorieën -->
            <li class="nav-item dropdown">
                <a  class="nav-link dropdown-toggle categorie" href='search.php?productTitle="."' id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Categorieën
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php
                    $DBConnProduct = newDBConn("productview");
                    $statement = mysqli_prepare($DBConnProduct, "SELECT StockGroupID, StockGroupName FROM stockgroups ORDER BY StockGroupName");
                    mysqli_stmt_execute($statement);
                    $result = mysqli_stmt_get_result($statement);
// print de categorie 'alle'
                    print("<a class='dropdown-item' href='search.php?productTitle="."'>Alle</a> ");

                    while($row = mysqli_fetch_assoc($result)) {
// print alle categorien behalve 'alle'
                        print("<a class='dropdown-item' href='search.php?Category=".$row["StockGroupID"]."'>".$row["StockGroupName"]."</a>");
                    }
                    mysqli_close($DBConnProduct);
                    ?>
                </div>
            </li>
        </ul>
        <!-- search bar -->
            <form class="search-wrapper" method="get" action="search.php">
                <input id="searchbox" type="text" name="productTitle" required class="search-box" placeholder="Waar bent u naar opzoek..." />
                <button class="close-icon" type="reset" onclick="document.getElementById('searchbox').value =''"> </button>
                <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
            </form>
            <script>
                document.getElementById('searchbox').value = '<?= $_GET["productTitle"] ?>';
            </script>



        <!-- Winkelwagentje -->
        <ul class="navbar-nav ml-auto">
            <div class="imageBox shcart">
                <div class="imageInn">
                    <img class="sh-icon" src="img/sh-icon-gr.png" alt="Default Image">
                </div>
                    <div class="hoverImg">
                        <img class="sh-icon"src="img/sh-icon-bl.png" alt="Profile Image">
                    </div>
                        <li class="nav-item">
                            <a class="nav-link" href="Shopping_Cart.php">
                                Winkelwagentje
                                <?php
                                if(!empty($_SESSION["Winkelwagen"])) {
                                    print(" (".count($_SESSION["Winkelwagen"]).")");
                                }
                                ?>
                            </a>
                        </li>
            </div>


            <!-- Account -->
            <li class="nav-item dropdown ml-auto-fixed account">
                <div class="imageBox">
                    <div class="imageInn">
                        <img class="acc-icon" src="img/user-icon-gr.png" alt="Default Image">
                    </div>
                    <div class="hoverImg">
                        <img class="acc-icon "src="img/user-icon-bl.png" alt="Profile Image">
                    </div>
                    <?php printUserDropdown(); ?>
                </div>
            </li>
        </ul>
    </div>
</nav>

