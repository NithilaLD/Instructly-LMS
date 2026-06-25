<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Primary Meta Tags -->
    <title><?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS' ?> </title>
    <meta name="title" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS' ?>">
    <meta name="description" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS'; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://metatags.io/">
    <meta property="og:title" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS' ?>">
    <meta property="og:description" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS'; ?>">
    <meta property="og:image" content="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : 'logo.png'; ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://metatags.io/">
    <meta property="twitter:title" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS' ?>">
    <meta property="twitter:description" content="<?php echo isset($sys) ? $sys->sys_name : 'Instructly LMS'; ?>">
    <meta property="twitter:image" content="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : 'logo.png'; ?>">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : 'logo.png'; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : 'logo.png'; ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="../public/sys_data/logo/<?php echo isset($sys) ? $sys->sys_logo : 'logo.png'; ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../public/plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../public/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../public/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="../public/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Bootstrap Select CSS -->
    <link rel="stylesheet" href="../public/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="../public/plugins/select2/css/select2.min.css">
    <!-- Data Tables -->
    <link rel="stylesheet" type="text/css" href="../public/plugins/datatable/custom_dt_html5.css">
    <style>
      body.login-bg
      {
        background: url('../public/sys_data/logo/intro.png') no-repeat center center fixed;
        background-size: cover;
      }
      body.login-bg::before
      {
        content: "";
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 0;
      }
      .login-box
      {
          position: relative;
          z-index: 1;
      }
      body.sidebar-collapse .brand-link img
      {
        width: 30px !important;
        height: 30px !important;
        transition: width 0.3s;
      }
      .nav-sidebar .nav-link.active
      {
        background: #2f80ed !important;
        color: #fff !important;
        border-radius: .35rem;
      }
      .nav-sidebar .nav-link.active i, .nav-sidebar .nav-link.active p { color: #fff !important; }
      .nav-treeview .nav-link.active { background: rgba(255, 255, 255, 0.14) !important; }
      .navbar-nav .nav-link.active
      {
        color: #2f80ed !important;
        font-weight: 700;
      }
      .main-header .navbar-nav > .nav-item > .nav-link
      {
        color: #000 !important;
        font-weight: 500;
        font-size: 1 rem;
        letter-spacing: 0.02em;
      }
      .main-header .navbar-nav > .nav-item > .nav-link i
      {
        font-size: 1.08rem;
        font-weight: 700;
        color: #100 !important;
      }
      .navbar-right-area
      {
        display: flex;
        align-items: center;
          gap: 10px;
      }
      .navbar-greeting
      {
        font-size: 16px;
        font-weight: 500;
        color: #100 !important;
        padding: 0 10px;
        white-space: nowrap;
      }
      .navbar-icon-link
      {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transition: all 0.2s ease;
        color: #100;
      }
      .navbar-icon-link:hover
      {
        background: #193b68ff !important;
        text-decoration: none;
      }
      .navbar-icon-link i { font-size: 18px; }
      .navbar-icon-link:hover i { color: #f2f6ff !important; }
      .btn-outline-warning:active {  background-color: rgba(255, 193, 7, 0.1) !important; }
      .container {  margin-right: 0 !important;  }
      .content-wrapper {  margin-bottom: 0 !important;  }
      .btn-outline-warning {margin-left: 2px !important;}
      .alert_info {background: #84B7F9 !important;}
      @media print
      {
        .material-link
        {
            color: #000 !important;
            text-decoration: none !important;
            pointer-events: none;
            cursor: default;
        }
        .payment-status
        {
            background: transparent !important;
            color: #000 !important;
            border: none !important;
            padding: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
      }
      .swal-button--confirm
      {
        background-color: #007bff !important;
        border-color: #007bff !important;
        border-radius: .25rem;
      }
      .swal-button--confirm:hover
      {
        background-color: #0069d9 !important;
        border-color: #0069d9 !important;
      }
    div.dt-button-info
    {
        top: 70px !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        transform: translateX(-50%) !important;

        width: auto !important;

        padding: 15px 20px !important;
        border: none !important;
        border-radius: 15px !important;

        background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
        color: #fff !important;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28) !important;
        z-index: 99999 !important;
        text-align: center !important;

        animation: dtToastIn 0.25s ease-out;
        white-space: nowrap !important;
        position: fixed !important;
    }

    div.dt-button-info h2
    {
        color: #fff !important;
        font-size: 18.5px !important;
        font-weight: 700 !important;
        margin: 0 0 5px 0 !important;
        display: inline !important;
        white-space: nowrap !important;
    }

    div.dt-button-info div
    {
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 15px !important;
        line-height: 1.5 !important;
        display: inline !important;
        white-space: nowrap !important;
        margin: 0 !important;
    }

    div.dt-button-background
    {
        background: rgba(15, 23, 42, 0.25) !important;
        backdrop-filter: blur(2px);
    }

    @keyframes dtToastIn
    {
        from
        {
            opacity: 0;
            transform: translateX(-50%) translateY(-12px);
        }
        to
        {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    </style>
  </head>