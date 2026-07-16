<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRole('admin');
    require_once('../config/codeGen.php');
    if (isset($_SESSION['active_tab']))
    {
        $active_tab = $_SESSION['active_tab'];
        unset($_SESSION['active_tab']);
    }
    else { $active_tab = "custom-tabs-one-home-tab"; }

    /* Update Default Company Settings */
    if (isset($_POST['update_company_profile'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['sys_id']) && !empty($_POST['sys_id'])) {
            $sys_id = mysqli_real_escape_string($mysqli, trim($_POST['sys_id']));
        } else {
            $error = 1;
            $err = "ID Cannot Be Empty";
        }
        if (isset($_POST['sys_name']) && !empty($_POST['sys_name'])) {
            $sys_name = mysqli_real_escape_string($mysqli, trim($_POST['sys_name']));
        } else {
            $error = 1;
            $err = "Sys Name Cannot Be Empty";
        }
        if (isset($_POST['sys_tagline']) && !empty($_POST['sys_tagline'])) {
            $sys_tagline = $_POST['sys_tagline'];
        } else {
            $error = 1;
            $err = "System Tagline Cannot Be Empty";
        }


        /*  $cc_dpic = $_FILES["cc_dpic"]["name"];
        move_uploaded_file($_FILES["cc_dpic"]["tmp_name"], "../public/sys_data/uploads/courses/" . $_FILES["cc_dpic"]["name"]); */

        if (!$error) {
            $query = "UPDATE system  SET sys_name = ?, sys_tagline =? WHERE sys_id =?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sss', $sys_name, $sys_tagline, $sys_id);
            if ($stmt->execute()) {
                logAuditAction($mysqli, 'update_company_profile', 'System name and tagline updated', 'settings', 'system', (string) $sys_id);
                $_SESSION['success'] = "Details updated successfully";
                $_SESSION['active_tab'] = "custom-tabs-one-home";
                header("Location: settings.php");
                exit;
            } else {
                $_SESSION['error'] = "Please try again or try later";
                $_SESSION['active_tab'] = "custom-tabs-one-home";
                header("Location: settings.php");
                exit;
            }
        }
    }


    /* Update Default System Settings */
    if (isset($_POST['update_company_logo'])) {
        $error = 0;

        if (isset($_POST['sys_id']) && !empty($_POST['sys_id'])) {
            $sys_id = mysqli_real_escape_string($mysqli, trim($_POST['sys_id']));
        } else {
            $error = 1;
            $err = "ID Cannot Be Empty";
        }

        if (!$error) {
            $new_logo_name = "logo.png";
            $logo_path = "../public/sys_data/logo/" . $new_logo_name;

            // Get current logo from DB
            $current_logo = "";
            $check_stmt = $mysqli->prepare("SELECT sys_logo FROM system WHERE sys_id = ?");
            $check_stmt->bind_param("s", $sys_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($row = $check_result->fetch_assoc()) {
                $current_logo = $row['sys_logo'];
            }

            // Remove old logo first
            if (!empty($current_logo) && file_exists("../public/sys_data/logo/" . $current_logo)) {
                unlink("../public/sys_data/logo/" . $current_logo);
            }

            // Save new uploaded file as logo.png
            if (isset($_FILES["sys_logo"]["tmp_name"]) && is_uploaded_file($_FILES["sys_logo"]["tmp_name"])) {
                if (move_uploaded_file($_FILES["sys_logo"]["tmp_name"], $logo_path)) {
                    $query = "UPDATE system SET sys_logo = ? WHERE sys_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('ss', $new_logo_name, $sys_id);

                    if ($stmt->execute()) {
                        logAuditAction($mysqli, 'update_company_logo', 'System logo updated', 'settings', 'system', (string) $sys_id);
                        $_SESSION['success'] = "Logo updated successfully";
                        $_SESSION['active_tab'] = "custom-tabs-one-profile";
                        header("Location: settings.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "Database update failed";
                        $_SESSION['active_tab'] = "custom-tabs-one-profile";
                        header("Location: settings.php");
                        exit;
                    }
                } else {
                    $_SESSION['error'] = "Failed to upload logo";
                    $_SESSION['active_tab'] = "custom-tabs-one-profile";
                    header("Location: settings.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Please select a valid image";
                $_SESSION['active_tab'] = "custom-tabs-one-profile";
                header("Location: settings.php");
                exit;
            }
        } else {
            $_SESSION['error'] = $err;
            $_SESSION['active_tab'] = "custom-tabs-one-profile";
            header("Location: settings.php");
            exit;
        }
    }


    /* Update Company License */
    if (isset($_POST['update_company_license'])) {

        $sys_id = $_POST['sys_id'];
        $sys_license = $_POST['sys_license'];

        $stmt = $mysqli->prepare("
            UPDATE system
            SET sys_license = ?
            WHERE sys_id = ?
        ");

        $stmt->bind_param("si", $sys_license, $sys_id);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'update_company_license', 'System license updated', 'settings', 'system', (string) $sys_id);
            $_SESSION['success'] = "License updated successfully";
            $_SESSION['active_tab'] = "custom-tabs-one-license";
            header("Location: settings.php");
            exit;
        }
        else {
            $_SESSION['error'] = "Please try again or try later";
            $_SESSION['active_tab'] = "custom-tabs-one-license";
            header("Location: settings.php");
            exit;
        }
    }

    /* Update Privacy Policy */
    if (isset($_POST['update_company_privacy'])) {

        $sys_id = $_POST['sys_id'];
        $sys_privacy_policy = $_POST['sys_privacy_policy'];

        $stmt = $mysqli->prepare("
            UPDATE system
            SET sys_privacy_policy = ?
            WHERE sys_id = ?
        ");

        $stmt->bind_param("si", $sys_privacy_policy, $sys_id);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'update_company_privacy', 'System privacy policy updated', 'settings', 'system', (string) $sys_id);
            $_SESSION['success'] = "Privacy Policy updated successfully";
            $_SESSION['active_tab'] = "custom-tabs-one-privacy";
            header("Location: settings.php");
            exit;
        } else {
            $_SESSION['error'] = "Please try again or try later";
            $_SESSION['active_tab'] = "custom-tabs-one-privacy";
            header("Location: settings.php");
            exit;
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php');
    ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <!-- ./row -->
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-lg-12">
                                                <div class="card card-warning card-tabs">
                                                    <div class="card-header p-0 pt-1" style="background: #84B7F9 !important;">
                                                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab">Company Details</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab">Company Logo</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="custom-tabs-one-license-tab" data-toggle="pill"
                                                                    href="#custom-tabs-one-license" role="tab">
                                                                    License
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="custom-tabs-one-privacy-tab" data-toggle="pill"
                                                                    href="#custom-tabs-one-privacy" role="tab">
                                                                    Privacy Policy
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="tab-content" id="custom-tabs-one-tabContent">
                                                            <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel">
                                                                <form method="post" enctype="multipart/form-data">
                                                                    <div class="row">
                                                                        <div class=" col-md-12">
                                                                            <label for="sys_name">Company Name</label>
                                                                            <input type="text" name="sys_name" value="<?php echo $sys->sys_name; ?>" required class="form-control" id="sys_name" autocomplete="name">
                                                                            <input type="hidden" name="sys_id" value="<?php echo $sys->sys_id; ?>" required class="form-control" id="sys_id">

                                                                        </div>
                                                                    </div><br>
                                                                    <div class="row">
                                                                        <div class="form-group col-md-12">
                                                                            <label for="sys_tagline">Company Tagline</label>
                                                                            <textarea type="text" name="sys_tagline" class="form-control" id="sys_tagline" autocomplete="on"><?php echo $sys->sys_tagline; ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <hr>
                                                                    <div class="text-right">
                                                                        <button type="submit" name="update_company_profile" class="btn btn-outline-warning">Update Details</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel">
                                                                <form method="post" enctype="multipart/form-data" role="form">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <b>Current Logo Preview</b><br><br>

                                                                                <div class="mb-3">
                                                                                    <img
                                                                                        id="logoPreview"
                                                                                        src="<?php echo !empty($sys->sys_logo) ? '../public/sys_data/logo/' . htmlspecialchars($sys->sys_logo) . '?v=' . time() : '../public/sys_data/logo/default.png'; ?>"
                                                                                        alt="Logo Preview"
                                                                                        style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; padding: 8px; border-radius: 8px; object-fit: contain;"
                                                                                    >
                                                                                </div>

                                                                                <b>Select New File</b><br><br>
                                                                                <div class="input-group">
                                                                                    <div class="custom-file">
                                                                                        <input
                                                                                            required
                                                                                            name="sys_logo"
                                                                                            type="file"
                                                                                            class="custom-file-input"
                                                                                            id="sys_logo"
                                                                                            accept="image/*"
                                                                                        >
                                                                                        <label class="custom-file-label" for="sys_logo">Choose file</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <input type="hidden" name="sys_id" value="<?php echo $sys->sys_id; ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="text-right">
                                                                        <button type="submit" name="update_company_logo" class="btn btn-outline-warning">
                                                                            Upload File
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="tab-pane fade" id="custom-tabs-one-license" role="tabpanel">
                                                                <form method="post">
                                                                    <div class="form-group">
                                                                        <label for="sys_license">License</label>
                                                                        <textarea
                                                                            name="sys_license"
                                                                            id="sys_license"
                                                                            class="form-control"
                                                                            rows="12"
                                                                            required><?php echo htmlspecialchars($sys->sys_license); ?></textarea>
                                                                    </div>
                                                                    <input type="hidden" name="sys_id"
                                                                        value="<?php echo $sys->sys_id; ?>">

                                                                    <div class="text-right">
                                                                        <button type="submit"
                                                                            name="update_company_license"
                                                                            class="btn btn-outline-warning">
                                                                            Update License
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="tab-pane fade" id="custom-tabs-one-privacy" role="tabpanel">
                                                                <form method="post">
                                                                    <div class="form-group">
                                                                        <label for="sys_privacy_policy">Privacy Policy</label>
                                                                        <textarea
                                                                            name="sys_privacy_policy"
                                                                            id="sys_privacy_policy"
                                                                            class="form-control"
                                                                            rows="15"
                                                                            required><?php echo htmlspecialchars($sys->sys_privacy_policy); ?></textarea>
                                                                    </div>
                                                                    <input type="hidden" name="sys_id"
                                                                        value="<?php echo $sys->sys_id; ?>">

                                                                    <div class="text-right">
                                                                        <button type="submit"
                                                                            name="update_company_privacy"
                                                                            class="btn btn-outline-warning">
                                                                            Update Privacy Policy
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- /.card -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
            <script>
                document.getElementById('sys_logo').addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    const preview = document.getElementById('logoPreview');

                    if (file) {
                        preview.src = URL.createObjectURL(file);
                    }
                });
            </script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <?php if (isset($_SESSION['success'])): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: "<?= htmlspecialchars($_SESSION['success']) ?>",
                            timer: 2500,
                            showConfirmButton: false
                        });
                    });
                </script>
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        Swal.fire({
                            icon: "error",
                            title: "Rejected",
                            text: "<?= htmlspecialchars($_SESSION['error']) ?>"
                        });
                    });
                </script>
            <?php unset($_SESSION['error']); endif; ?>
            <?php $active_tab = $active_tab ?? 'custom-tabs-one-home-tab'; ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const activeTab = <?php echo json_encode($active_tab); ?>;
                    const tabLink = document.querySelector('a[href="#' + activeTab + '"]');

                    if (tabLink) {
                        $(tabLink).tab('show');
                    }
                });
            </script>
        </body>
    </html>
    <?php 
        }
?>