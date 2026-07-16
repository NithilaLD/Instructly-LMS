<?php
    include('../config/config.php');
    /* Persist System Settings */
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <meta name="keywords" content="LMS, Instruclty, Instruclty LMS, Learning Management System">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            <title><?php echo $sys->sys_name ?></title>
            <link rel="shortcut icon" href="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" type="image/png">
            <link rel="stylesheet" href="../public/css/bootstrap-4.5.0.min.css">
            <link rel="stylesheet" href="../public/css/animate.css">
            <link rel="stylesheet" href="../public/css/LineIcons.2.0.css">
            <link rel="stylesheet" href="../public/css/owl.carousel.2.3.4.min.css">
            <link rel="stylesheet" href="../public/css/owl.theme.css">
            <link rel="stylesheet" href="../public/css/magnific-popup.css">
            <link rel="stylesheet" href="../public/css/nivo-lightbox.css">
            <link rel="stylesheet" href="../public/css/main.css">
            <link rel="stylesheet" href="../public/css/responsive.css">
            <style>
                @media (max-width: 991px)
                {
                    .navbar-nav .nav-item a
                    {
                        color: #000 !important;
                    }
                    #navbarSupportedContent
                    {
                        padding: 10px 10px !important;
                        background-color: #ffffffde !important;
                        right: 0 !important;
                        z-index: 10 !important;
                        right: 0 !important;
                        left: auto !important;
                        width: auto !important;
                    }
                }
            </style>
        </head>
        <body>
            <header class="hero-area">
                <div class="overlay">
                    <span></span>
                    <span></span>
                </div>
                <div class="navbar-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <nav class="navbar navbar-expand-lg">
                                    <a class="navbar-brand">
                                        <img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" alt="Logo">
                                    </a>
                                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                        <span class="toggler-icon"></span>
                                        <span class="toggler-icon"></span>
                                        <span class="toggler-icon"></span>
                                    </button>
                                    <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                        <ul id="nav" class="navbar-nav ml-auto">
                                            <li class="nav-item active">
                                                <a class="page-scroll" href="../index.php">Home</a>
                                            </li>
                                            <li class="nav-item active">
                                                <a class="page-scroll" href="login.php">Instructly LMS Portal</a>
                                            </li>
                                            <li class="nav-item active">
                                                <a class="page-scroll" href="register.php">Register</a>
                                            </li>
                                        </ul>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="home">
                    <div class="container">
                        <div class="row space-100">
                            <div class="col-lg-6 col-md-12 col-xs-12">
                                <div class="contents">
                                    <h2 class="head-title"><?php echo $sys->sys_name; ?></h2>

                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12 col-xs-12 p-0">
                                <div class="intro-img">
                                    <img src="../public/sys_data/logo/intro.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <footer style="position: fixed; bottom: 0; left: 0; width: 100%; background: linear-gradient(90deg, #4B2EFF, #00C6FF); padding: 12px 0; text-align: center;">
                <p style="font-size: 20px; font-weight: 500; color: #fff; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($sys->sys_license); ?>
                </p>

                <a href="#" data-toggle="modal" data-target="#privacyModal" style="color: #fff; text-decoration: underline; font-weight: 700; font-size: 16px;">
                    Privacy Policy
                </a>
            </footer>
            <div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none;">
                        <div class="modal-header" style="background: linear-gradient(90deg, #4B2EFF, #00C6FF); ">
                            <h5 class="modal-title" style="font-weight: 500; font-size: 22px; color: #fff;">
                                Privacy Policy
                            </h5>
                        </div>

                        <div class="modal-body" style="background: #f8fbff; padding: 30px;">
                            <div style="
                                font-size: 20px;
                                font-weight: 400;
                                line-height: 1.6;
                                color: #121d29;
                            ">
                                <?php echo ($sys->sys_privacy_policy); ?>
                            </div>
                        </div>

                        <div class="modal-footer" style="background: #f8fbff;">
                            <button type="button" class="btn btn-primary" data-dismiss="modal"
                                style="background: linear-gradient(90deg, #4B2EFF, #00C6FF); border: none; font-weight: 600;">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="back-to-top">
                <i class="lni lni-chevron-up"></i>
            </a>
            <div id="preloader">
                <div class="loader" id="loader-1"></div>
            </div>
            <script src="../public/js/modernizr-3.7.1.min.js"></script>
            <script src="../public/plugins/jquery/jquery.min.js"></script>
            <script src="../public/js/popper.min.js"></script>
            <script src="../public/js/bootstrap-4.5.0.min.js"></script>
            <script src="../public/js/owl.carousel.2.3.4.min.js"></script>
            <script src="../public/js/nivo-lightbox.js"></script>
            <script src="../public/js/jquery.magnific-popup.min.js"></script>
            <script src="../public/js/form-validator.min.js"></script>
            <script src="../public/js/contact-form-script.js"></script>
            <script src="../public/js/main.js"></script>
            
        </body>
        </html>
<?php 
    } 
?>