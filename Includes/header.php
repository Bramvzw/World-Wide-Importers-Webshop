<!DOCTYPE html>
<html lang="nl">
    <head>
        <!-- required meta -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


        <!-- stylesheets -->
        <link rel="stylesheet" href="css/bootstrap.css">
        <link rel="stylesheet" href="css/custom.css">

        <!-- scripts -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="js/bootstrap.min.js"></script>

        <!-- info -->
        <!--<title>Working title</title>-->
        <?php
        if(empty($title)) {
            // print generic title
            print("<title>World Wide Importers &bull; WWI</title>");
        } else {
            print("<title>".$title." &bull; WWI</title>");
        }
        ?>

    </head>

<body>
<!-- roept navbar aan -->
<?php
include "nav.php";
?>
</body>
