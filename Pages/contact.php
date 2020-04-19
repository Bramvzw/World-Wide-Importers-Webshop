<?php
include "includes/config.php";
$title = "Contact Pagina";
// include header
include "includes/header.php";

// <insert content here>
?>
    <!--Section: Contact v.2-->



    <div class="container">




        <!--Section: Contact v.2-->
        <section class="section">

            <!--Section heading-->
            <h2 class="section-heading h1 pt-4">Contact us</h2>
            <!--Section description-->
            <p class="section-description">Zijn wij niet bereikbaar? Probeer ons dan te mailen door het onderstaande formulier in te vullen</p>

            <div class="row">

                <!--Grid column-->
                <div class="col-md-8">
                    <form id ="contact-form" name="contact-form" action="mail.php" method="POST"  onsubmit="return validateForm()" >

                        <!--Grid row-->
                        <div class="row">

                            <!--Grid column-->
                            <div class="col-md-6">
                                <div class="md-form">
                                    <div class="md-form">
                                        <label for="name" class="">Your name:</label>
                                        <input type="text" id="name" name="name" class="form-control" placeholder="Je naam hier"/>
                                    </div>
                                </div>
                            </div>
                            <!--Grid column-->

                            <!--Grid column-->
                            <div class="col-md-6">
                                <div class="md-form">
                                    <div class="md-form">
                                        <label for="email" class="">Your email:</label>
                                        <input type="text" id="email" name="email" class="form-control" placeholder="Email adres hier"/>
                                    </div>
                                </div>
                            </div>
                            <!--Grid column-->

                        </div>
                        <!--Grid row-->

                        <!--Grid row-->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="md-form">
                                    <label for="subject" class="">Subject:</label>
                                    <input type="text" id="subject" name="subject" class="form-control" placeholder="Geef een titel"/>

                                </div>
                            </div>
                        </div>
                        <!--Grid row-->

                        <!--Grid row-->
                        <div class="row">

                            <!--Grid column-->
                            <div class="col-md-12">

                                <div class="md-form">
                                    <label for="message">Your message:</label><br>
                                    <textarea type="text" id="message" name="message" class="md-textarea" placeholder="Typ hier je bericht..." style="width: 100%"></textarea>

                                </div>

                            </div>
                        </div>
                        <!--Grid row-->

                    </form>

                    <div class="center-on-small-only">
                        <a class="btn btn-primary" onclick="validateForm()">Verzenden</a>
                    </div> <div class="status" id="status"></div>
                </div>
                <!--Grid column-->

                <!--Grid column-->
                <div class="col-md-4 col-xl-3">
                    <ul class="contact-icons">
                        <li><i class="fa fa-map-marker fa-2x"></i>
                            <p>Worldstraat 99, 1111 ZZ Wide World</p>
                        </li>

                        <li><i class="fa fa-phone fa-2x"></i>
                            <p>(31)06-12345678</p>
                        </li>

                        <li><i class="fa fa-envelope fa-2x"></i>
                            <p>Info@importers.com</p>
                        </li>
                    </ul>
                </div>
                <!--Grid column-->

            </div>

        </section>
        <!--Section: Contact v.2-->




    </div>


    <script type="text/javascript" src="js/bootstrap.js"></script>

    

    <!--Section: Contact v.2-->

<?php
//include footer
include "includes/footer.php";
?>