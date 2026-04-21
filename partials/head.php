<!DOCTYPE html>
<html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary Meta Tags -->
  <title><?php echo $sys->sys_name ?> </title>
  <meta name="title" content="<?php echo $sys->sys_name ?>">
  <meta name="description" content="<?php echo  $sys->sys_name; ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://metatags.io/">
  <meta property="og:title" content="<?php echo $sys->sys_name ?>">
  <meta property="og:description" content="<?php echo  $sys->sys_name; ?>">
  <meta property="og:image" content="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="https://metatags.io/">
  <meta property="twitter:title" content="<?php echo $sys->sys_name?>">
  <meta property="twitter:description" content="<?php echo  $sys->sys_name; ?>">
  <meta property="twitter:image" content="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>">

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="../public/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="../public/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../public/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../public/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="../public/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
  <!-- Bootstrap Select CSS -->
  <link rel="stylesheet" href="../public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="../public/plugins/select2/css/select2.min.css">
  <!-- CK Editor CDN -->
  <script src="https://cdn.ckeditor.com/4.25.1-lts/basic/ckeditor.js"></script>
  <!-- Certifcate CSS -->
  <link rel="stylesheet" href="../public/css/cert.css">
  <!-- Data Tables -->
  <link rel="stylesheet" type="text/css" href="../public/plugins/datatable/custom_dt_html5.css">
  <style>
    /* Adjust logo size when sidebar is collapsed */
    body.sidebar-collapse .brand-link img {
      /* Example: increase width and height */
      width: 30px !important;
      height: 30px !important;
      transition: width 0.3s;
    }
    /* .btn-outline-warning:focus,
    .btn-outline-warning:active,
    .btn-outline-warning:active:focus {
        background-color: transparent !important;
        border-color: #ffc107 !important;
        color: #ffc107 !important;
        box-shadow: none !important;
    } */

    /* If you want to keep some visual feedback on click */
    .btn-outline-warning:active {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
  </style>

</head>