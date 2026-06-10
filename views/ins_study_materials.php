<?php
    session_start();
    include('../config/config.php');
    include('../config/checklogin.php');
    instructor();
    require_once('../config/codeGen.php');
    require_once('../partials/emailscripts.php');
    // Ensure study material code parts are defined to avoid undefined variable errors
    if (!isset($a) || empty($a)) {
        $a = date('Ymd');
    }
    if (!isset($b) || empty($b)) {
        $b = strtoupper(substr(md5(uniqid('', true)), 0, 6));
    }
    // Initialize variables to prevent undefined variable warnings
    $sm_number = '';
    /* Add Study Materials */
    if (isset($_POST['add_studymaterial'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['cc_id']) && !empty($_POST['cc_id'])) {
            $cc_id = mysqli_real_escape_string($mysqli, trim($_POST['cc_id']));
        } else {
            $error = 1;
            $err = "Course ID Cannot Be Empty";
        }
        if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
            $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
            $c_name = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['c_id']) && !empty($_POST['c_id'])) {
            $c_id = mysqli_real_escape_string($mysqli, trim($_POST['c_id']));
        } else {
            $error = 1;
            $err = "Course ID Cannot Be Empty";
        }

        if (isset($_POST['i_id']) && !empty($_POST['i_id'])) {
            $i_id = mysqli_real_escape_string($mysqli, trim($_POST['i_id']));
        } else {
            $error = 1;
            $err = "Instructor ID Cannot Be Empty";
        }

        if (isset($_POST['c_category']) && !empty($_POST['c_category'])) {
            $c_category = $_POST['c_category'];
        } else {
            $error = 1;
            $err = "Course Cannot Be Empty";
        }

        if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
            $i_name = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
        } else {
            $error = 1;
            $err = "Instructor Name Cannot Be Empty";
        }

        if (isset($_POST['sm_number']) && !empty($_POST['sm_number'])) {
            $sm_number = mysqli_real_escape_string($mysqli, trim($_POST['sm_number']));
        } else {
            $error = 1;
            $err = "Study Material Cannot Be Empty";
        }

        // Payment field (use sm_price)
        if (isset($_POST['sm_price']) && !empty($_POST['sm_price'])) {
            $sm_price = floatval($_POST['sm_price']);
        } else {
            $error = 1;
            $err = "Payment Amount Cannot Be Empty";
        }

        // File upload handling (rewritten)
        $allowed_types = ['pdf', 'docx'];
        $upload_dir = "../public/sys_data/uploads/study_materials/";
        $sm_materials = $_FILES["sm_materials"]["name"] ?? '';
        $file_tmp = $_FILES["sm_materials"]["tmp_name"] ?? '';
        $file_ext = strtolower(pathinfo($sm_materials, PATHINFO_EXTENSION));
        $new_file_name = uniqid('sm_', true) . '.' . $file_ext;

        if (empty($sm_materials)) {
            $error = 1;
            $err = "No file selected for upload.";
        } elseif (!in_array($file_ext, $allowed_types)) {
            $error = 1;
            $err = "Invalid file type. Only PDF, DOCX allowed.";
        } elseif (!is_uploaded_file($file_tmp)) {
            $error = 1;
            $err = "Possible file upload attack detected.";
        } 
        
        if (!$error) {
            //prevent Double entries
            $stmt = $mysqli->prepare("SELECT * FROM lms_study_material WHERE sm_number = ?");
            $stmt->bind_param("s", $sm_number);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($sm_number == $row['sm_number']) {
                    $err =  "A Study Materials With $sm_number Exists";
                }
            } else {
                // Save study material with sm_price and payment_status 'unpaid'
                $payment_status = 'Unpaid';
                $query = "INSERT INTO lms_study_material  (c_code, sm_number, c_id, cc_id, c_name, c_category, i_id, i_name, sm_materials, sm_price) VALUES (?,?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param(
                    'sssssssssd',
                    $c_code,
                    $sm_number,
                    $c_id,
                    $cc_id,
                    $c_name,
                    $c_category,
                    $i_id,
                    $i_name,
                    $new_file_name,
                    $sm_price
                );
                $stmt->execute();
                if ($stmt) {
                    move_uploaded_file($file_tmp, $upload_dir . $new_file_name);
                    $query1 = "SELECT * FROM lms_course_categories WHERE cc_id = $cc_id AND cc_code = '$c_category'";
                    $stmt1 = $mysqli->prepare($query1);
                    $stmt1->execute();
                    $res1 = $stmt1->get_result();
                    $moduleName = '';
                    while ($cat = $res1->fetch_object()) {
                        $moduleName = $cat->cc_name." : ".$c_name ." Unit";
                        ?>
                        <script>
                            console.log("Module Name: " + "<?php echo $moduleName; ?>");
                        </script>
                        <?php
                    }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $filePath = $upload_dir . $new_file_name;
                    $mimeType = mime_content_type($filePath);
                    $fileText = '';
                    $mediaData = [];
                    $mediaAssets = [];
                    $materialTitle = '';

                    // 1. Extract text from PDF or Word
                    $fileText = extractFileContent($filePath, $mimeType);
                    $materialTitle = extractMaterialTitle($fileText, $filePath);
                    $mediaAssets = createMediaAssetStore();
                    $mediaData = extractMediaMetadata($filePath, $mimeType, $mediaAssets);

                    // 2. Generate notification
                    if (!empty($fileText)) {

                        $notification = generateNotification(
                            $fileText,
                            $mediaData,
                            $mediaAssets,
                            $moduleName,
                            $materialTitle
                        );

                    } else {

                        $notification = [
                            'subject' => 'New Course Material Available for ' .
                                (trim((string) $moduleName) !== ''
                                    ? trim((string) $moduleName)
                                    : 'Your Course'),

                            'preheader' => 'A new course material was shared in the LMS.',
                            'headline'  => $moduleName,
                            'summary'   => buildDocumentGeneralSummary($fileText),
                            'cta'       => 'Log into the LMS to view the latest material.',
                        ];
                    }
                    
                    $selectedHeroImage = selectBestHeroImageAsset($mediaAssets, $notification['summary'] ?? '', $moduleName, $materialTitle, $fileText);
                    if (is_array($selectedHeroImage)) {
                        $mediaAssets['heroImage'] = $selectedHeroImage;
                    }
                    $sret = "SELECT * FROM `lms_sys_setttings` ";
                    $sstmt = $mysqli->prepare($sret);
                    $sstmt->execute(); //ok
                    $sres = $sstmt->get_result();
                    $sys_name = '';
                    $sys_tagline = '';
                    $sys_logo = '';
                    while ($ssys = $sres->fetch_object()) {
                        $sys_name = $ssys->sys_name;
                        $sys_tagline = $ssys->sys_tagline;
                        $sys_logo = $ssys->sys_logo;
                    }
                    $emailBody = buildNotificationEmailBody($notification, $mediaAssets, 'hero-media', $moduleName, $materialTitle, $sys_name, $sys_tagline);

                    // 3. Define recipient email lists from your database
                    $adminEmails = [];
                    $adquery = "SELECT * FROM lms_admin";
                    $adstmt = $mysqli->prepare($adquery);
                    $adstmt->execute();
                    $adres=$adstmt->get_result();
                    while ($admin = $adres->fetch_object()) {
                        $adminEmails[] = $admin->a_email;
                    }
                    $adres->free();
                    $adstmt->close();

                    $instructorEmails = [];
                    $inquery = "SELECT * FROM lms_instructor WHERE i_id = $i_id";
                    $instmt = $mysqli->prepare($inquery);
                    $instmt->execute();
                    $inres = $instmt->get_result();
                    while ($instructor = $inres->fetch_object()) {
                        $instructorEmails[] = $instructor->i_email;
                    }
                    $inres->free();
                    $instmt->close();

                    $studentEmails = [];
                    $stquery1 = "SELECT s_id FROM lms_enrollments WHERE c_id = $c_id";
                    $ststmt1 = $mysqli->prepare($stquery1);
                    $ststmt1->execute();
                    $stres1 = $ststmt1->get_result();
                    while ($studentId = $stres1->fetch_object()) {
                        $studentID= $studentId->s_id;
                        $stquery2 = "SELECT * FROM lms_student WHERE s_id = $studentID";
                        $ststmt2 = $mysqli->prepare($stquery2);
                        $ststmt2->execute();
                        $stres2 = $ststmt2->get_result();
                        while ($student = $stres2->fetch_object()) {
                            $studentEmails[] = $student->s_email;
                        }
                        $stres2->free();
                        $ststmt2->close();
                    }
                    $stres1->free();
                    $ststmt1->close();

                    // 4. Send notification
                    $subject = $notification['subject'] ?: "New Course Material Available!";
                    $emailSent = sendEmailNotification($subject, $emailBody, $adminEmails, $instructorEmails, $studentEmails, $mediaAssets, $sys_logo);
                    cleanupExtractedMediaFiles($mediaAssets);

                    if ($emailSent) {
                        // On successful, redirect back with success
                        $success = "Added";
                        header("refresh:1; url=ins_study_materials.php");
                    } else {
                        $info = "Failed to send notification. Please Try Again Or Try Later";
                    }
                    
                } else {
                    $info = "Saving to Database Failed. Please Try Again Or Try Later";
                }
            }
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM `lms_sys_setttings` ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php'); ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/ins_navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/ins_sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper"><br>
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <div class="container">
                                <div class="text-right text-dark">
                                    <a class="btn btn-outline-warning" href="ins_manage_study_materials.php">View Study Materials</a>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <th>Course</th>
                                                    <th>Instructor Number</th>
                                                    <th>Instructor Name</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $id = $_SESSION['i_id'];
                                                $ret = "SELECT  *  FROM  lms_units_assaigns WHERE i_id = '$i_id' ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();
                                                while ($units = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $units->c_code; ?></td>
                                                        <td><?php echo $units->c_name; ?></td>
                                                        <td><?php echo $units->c_category; ?></td>
                                                        <td><?php echo $units->i_number; ?></td>
                                                        <td><?php echo $units->i_name; ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#add-<?php echo $units->ua_id; ?>">
                                                                <i class="fas fa-file-upload"></i>
                                                                Upload Study Materials
                                                            </a>
                                                            <!-- Upload Study Materials Modal -->
                                                            <div class="modal fade" id="add-<?php echo $units->ua_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Share Study Materials For <?php echo $units->c_name; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="c_name">Unit Name</label>
                                                                                        <input type="text" name="c_name" id="c_name" value="<?php echo $units->c_name; ?>" readonly required class="form-control" autocomplete="name">
                                                                                        <!-- Hidden Values -->
                                                                                        <input type="hidden" name="c_id" value="<?php echo $units->c_id; ?>" readonly required class="form-control" id="c_id">
                                                                                        <input type="hidden" name="cc_id" value="<?php echo $units->cc_id; ?>" readonly required class="form-control" id="cc_id">
                                                                                        <input type="hidden" name="i_id" value="<?php echo $units->i_id; ?>" readonly required class="form-control" id="i_id">
                                                                                        <input type="hidden" name="i_name" value="<?php echo $units->i_name; ?>" readonly required class="form-control" id="i_name">
                                                                                        <input type="hidden" name="c_category" value="<?php echo $units->c_category; ?>" readonly required class="form-control" id="c_category">

                                                                                    </div>
                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="c_code">Unit Code</label>
                                                                                        <input type="text" name="c_code" id="c_code" readonly value="<?php echo $units->c_code; ?>" required class="form-control" autocomplete="on">
                                                                                    </div>

                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="sm_number">Study Materials Code</label>
                                                                                        <input type="text" name="sm_number" id="sm_number" readonly value="<?php echo ($a ?? 'SM'); ?>-<?php echo ($b ?? ''); ?>" required class="form-control" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="exampleInputFile">Upload Study Materials Either as a PDF or Word Document (.pdf or .docx) </label>
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input required name="sm_materials" type="file" class="custom-file-input" id="exampleInputFile" accept=".pdf,.docx">
                                                                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <!-- Payment field (sm_price) -->
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="sm_price">Payment Amount</label>
                                                                                        <input type="number" step="0.01" min="0" name="sm_price" required class="form-control" id="sm_price" autocomplete="on">
                                                                                    </div>
                                                                                    <!-- Removed payment_method field -->
                                                                                </div>
                                                                                <hr>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="add_studymaterial" class="btn btn-outline-warning">Share Study Materials</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Modal -->
                                                        </td>
                                                    </tr>
                                                    <?php
                                                } ?>

                                            </tbody>
                                        </table>
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
        </body>
        </html>
        <?php
    }
?>
