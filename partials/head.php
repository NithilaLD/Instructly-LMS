<!DOCTYPE html>
<html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Primary Meta Tags -->
  <title><?php echo isset($sys) ? $sys->sys_name : 'System' ?> </title>
  <meta name="title" content="<?php echo isset($sys) ? $sys->sys_name : 'System' ?>">
  <meta name="description" content="<?php echo isset($sys) ? $sys->sys_name : 'System'; ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://metatags.io/">
  <meta property="og:title" content="<?php echo isset($sys) ? $sys->sys_name : 'System' ?>">
  <meta property="og:description" content="<?php echo isset($sys) ? $sys->sys_name : 'System'; ?>">
  <meta property="og:image" content="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="https://metatags.io/">
  <meta property="twitter:title" content="<?php echo isset($sys) ? $sys->sys_name : 'System'; ?>">
  <meta property="twitter:description" content="<?php echo isset($sys) ? $sys->sys_name : 'System'; ?>">
  <meta property="twitter:image" content="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>">

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : ''; ?>">

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

    .nav-sidebar .nav-link.active {
      background: #2f80ed !important;
      color: #fff !important;
      border-radius: .35rem;
    }

    .nav-sidebar .nav-link.active i,
    .nav-sidebar .nav-link.active p {
      color: #fff !important;
    }

    .nav-treeview .nav-link.active {
      background: rgba(255, 255, 255, 0.14) !important;
    }

    .navbar-nav .nav-link.active {
      color: #2f80ed !important;
      font-weight: 700;
    }

    .main-header .navbar-nav > .nav-item > .nav-link {
      color: #000 !important;
      font-weight: 500;
      font-size: 1 rem;
      letter-spacing: 0.02em;
    }

    .main-header .navbar-nav > .nav-item > .nav-link i {
      font-size: 1.08rem;
      font-weight: 700;
    }

    .main-header .navbar-nav > .nav-item > .nav-link:hover {
      color: #0f5bd5 !important;
    }

    .dropdown-header {
      font-weight: 700;
      color: #000 !important;
      font-size: 0.95rem;
    }

    .dropdown-item {
      font-weight: 500;
      color: #000 !important;
    }

    .dropdown-item i {
      font-weight: 700;
      margin-right: 0.5rem;
    }

    .dropdown-item:hover {
      background-color: #e8f1ff;
      color: #0f5bd5 !important;
    }

    /* If you want to keep some visual feedback on click */
    .btn-outline-warning:active {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }
  </style>

</head>