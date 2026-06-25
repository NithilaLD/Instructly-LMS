<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin','instructor','student']);
    require_once('../config/codeGen.php');
    require_once('../partials/emailscripts.php');

    $config = require'../config/config.php';
    $apiKey = $config['smtp']['api_key'];

    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    // Ensure material code parts are defined to avoid undefined variable errors
    if (!isset($a) || empty($a)) {$a = date('Ymd');}
    if (!isset($b) || empty($b)) {$b = strtoupper(substr(md5(uniqid('', true)), 0, 6));}
    // Initialize variables to prevent undefined variable warnings
    $m_number = '';
    $u_name = '';
    /* Add Materials */
    if (isset($_POST['add_unit_material']) && ($role === 'admin' || $role === 'instructor')) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['c_id']) && !empty($_POST['c_id'])) {
            $c_id = mysqli_real_escape_string($mysqli, trim($_POST['c_id']));
        } else {
            $error = 1;
            $err = "Course ID Cannot Be Empty";
        }
        if (isset($_POST['u_code']) && !empty($_POST['u_code'])) {
            $u_code = mysqli_real_escape_string($mysqli, trim($_POST['u_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['u_name']) && !empty($_POST['u_name'])) {
            $u_name = mysqli_real_escape_string($mysqli, trim($_POST['u_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['u_id']) && !empty($_POST['u_id'])) {
            $u_id = mysqli_real_escape_string($mysqli, trim($_POST['u_id']));
        } else {
            $error = 1;
            $err = "Unit ID Cannot Be Empty";
        }

        if (isset($_POST['i_id']) && !empty($_POST['i_id'])) {
            $i_id = mysqli_real_escape_string($mysqli, trim($_POST['i_id']));
        } else {
            $error = 1;
            $err = "Instructor ID Cannot Be Empty";
        }

        if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
            $c_name = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
        } else {
            $error = 1;
            $err = "Course Cannot Be Empty";
        }

        if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
            $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
        } else {
            $error = 1;
            $err = "Course Code Cannot Be Empty";
        }

        if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
            $i_name = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
        } else {
            $error = 1;
            $err = "Instructor Name Cannot Be Empty";
        }

        if (isset($_POST['m_number']) && !empty($_POST['m_number'])) {
            $m_number = mysqli_real_escape_string($mysqli, trim($_POST['m_number']));
        } else {
            $error = 1;
            $err = "Material Number Cannot Be Empty";
        }
        
        // Payment field (use sm_price)
        if (isset($_POST['m_price']) && !empty($_POST['m_price'])) {
            $m_price = floatval($_POST['m_price']);
        } else {
            $error = 1;
            $err = "Payment Amount Cannot Be Empty";
        }
        
        if (isset($_POST['m_title']) && !empty($_POST['m_title'])) {
            $m_title = mysqli_real_escape_string($mysqli, trim($_POST['m_title']));
        } else {
            $error = 1;
            $err = "Material Title Cannot Be Empty";
        }

        // File upload handling (rewritten)
        $allowed_types = ['pdf', 'docx'];
        $upload_dir = "../public/sys_data/uploads/materials/";
        $m_name = $_FILES["material"]["name"] ?? '';
        $file_tmp = $_FILES["material"]["tmp_name"] ?? '';
        $file_ext = strtolower(pathinfo($m_name, PATHINFO_EXTENSION));
        $new_file_name = $m_number . '.' . $file_ext;

        if (empty($m_name)) {
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
            $stmt = $mysqli->prepare("SELECT * FROM materials WHERE m_number = ?");
            $stmt->bind_param("s", $m_number);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($m_number == $row['m_number']) {
                    $_SESSION['flash_error'] = "A Material With $m_number Exists";
                    header("Location: materials.php");
                    exit();
                }
            } else {
                $query = "INSERT INTO materials (m_number, u_id, m_name, m_price, m_title) VALUES (?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('sssss', $m_number, $u_id, $new_file_name, $m_price, $m_title);
                $stmt->execute();
                if ($stmt) {
                    move_uploaded_file($file_tmp, $upload_dir . $new_file_name);
                    $query1 = "SELECT c_name, c_code, i_id FROM courses WHERE c_id = ?";
                    $stmt1 = $mysqli->prepare($query1);
                    $stmt1->bind_param("s", $c_id);
                    $stmt1->execute();
                    $res1 = $stmt1->get_result();
                    $moduleName = '';
                    while ($cat = $res1->fetch_object()) {
                        $moduleName = $cat->c_name." : ".$u_name ." Unit";
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
                            $materialTitle,
                            $apiKey
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
                    $sret = "SELECT * FROM system ";
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
                    $adquery = "SELECT * FROM users WHERE role = 'admin'";
                    $adstmt = $mysqli->prepare($adquery);
                    $adstmt->execute();
                    $adres=$adstmt->get_result();
                    while ($admin = $adres->fetch_object()) {
                        $adminEmails[] = $admin->email;
                    }
                    $adres->free();
                    $adstmt->close();

                    $instructorEmails = [];
                    $inquery = "SELECT * FROM users WHERE user_id = ?";
                    $instmt = $mysqli->prepare($inquery);
                    $instmt->bind_param("s", $i_id);
                    $instmt->execute();
                    $inres = $instmt->get_result();
                    while ($instructor = $inres->fetch_object()) {
                        $instructorEmails[] = $instructor->email;
                    }
                    $inres->free();
                    $instmt->close();

                    $studentEmails = [];
                    $stquery1 = "SELECT s_id FROM enrollments WHERE c_id = ?";
                    $ststmt1 = $mysqli->prepare($stquery1);
                    $ststmt1->bind_param("s", $c_id);
                    $ststmt1->execute();
                    $stres1 = $ststmt1->get_result();
                    while ($studentId = $stres1->fetch_object()) {
                        $studentID= $studentId->s_id;
                        $stquery2 = "SELECT * FROM users WHERE user_id = ?";
                        $ststmt2 = $mysqli->prepare($stquery2);
                        $ststmt2->bind_param("s", $studentID);
                        $ststmt2->execute();
                        $stres2 = $ststmt2->get_result();
                        while ($student = $stres2->fetch_object()) {
                            $studentEmails[] = $student->email;
                        }
                        $stres2->free();
                        $ststmt2->close();
                    }
                    $stres1->free();
                    $ststmt1->close();

                    // 4. Send notification
                    $subject = $notification['subject'] ?: "New Course Material Available!";
                    // sendEmailNotification does not return a value; call it and assume success if no exception/error
                    sendEmailNotification($subject, $emailBody, $adminEmails, $instructorEmails, $studentEmails, $mediaAssets, $sys_logo);
                    $emailSent = true;
                    cleanupExtractedMediaFiles($mediaAssets);

                    if ($emailSent) {
                        // On successful, redirect back with success
                        $_SESSION['flash_success'] = "Material added successfully.";
                        header("Location: materials.php");
                        exit();
                    } else {
                        $_SESSION['flash_error'] = "Failed to send notification. Please Try Again Or Try Later";
                        header("Location: materials.php");
                        exit();
                    }
                    
                } else {
                    $_SESSION['flash_error'] = "Saving to Database Failed. Please Try Again Or Try Later";
                    header("Location: materials.php");
                    exit();
                }
            }
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php'); ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content pt-3">
                        <div class="container-fluid">
                            <div class="card">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Unit Code</th>
                                                    <th>Unit</th>
                                                    <th>Course</th>
                                                <?php
                                                    if($role === 'admin' || $role === 'student')
                                                    {
                                                ?>
                                                        <th>Instructor</th>
                                                <?php
                                                    }
                                                    if($role === 'student')
                                                    {
                                                ?>
                                                        <th>Action</th>
                                                <?php
                                                    }
                                                    else if($role === 'admin' || $role === 'instructor')
                                                    {
                                                ?>
                                                    <th>Actions</th>
                                                <?php
                                                    }
                                                ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if($role === 'admin')
                                                {
                                                    $ret = "
                                                            SELECT 
                                                                units.u_id,
                                                                units.u_code,
                                                                units.u_name,
                                                                courses.c_id,
                                                                courses.c_name,
                                                                courses.c_code,
                                                                courses.i_id,
                                                                users.user_code AS i_number,
                                                                users.name AS i_name
                                                            FROM units
                                                            INNER JOIN courses ON units.c_id = courses.c_id
                                                            INNER JOIN users ON courses.i_id = users.user_id
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                }
                                                else if($role === 'instructor')
                                                {
                                                    $ret = "
                                                            SELECT 
                                                                units.u_id,
                                                                units.u_code,
                                                                units.u_name,
                                                                courses.c_id,
                                                                courses.c_name,
                                                                courses.c_code,
                                                                courses.i_id,
                                                                users.user_code AS i_number,
                                                                users.name AS i_name
                                                            FROM units
                                                            INNER JOIN courses ON units.c_id = courses.c_id
                                                            INNER JOIN users ON courses.i_id = users.user_id
                                                            WHERE courses.i_id = ?
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("s", $user_id);
                                                }
                                                if ($role === 'student')
                                                {
                                                    $ret = "
                                                        SELECT DISTINCT
                                                            units.u_id,
                                                            units.u_code,
                                                            units.u_name,
                                                            courses.c_id,
                                                            courses.c_name,
                                                            courses.c_code,
                                                            courses.i_id,
                                                            users.user_code AS i_number,
                                                            users.name AS i_name
                                                        FROM enrollments
                                                        INNER JOIN units ON enrollments.c_id = units.c_id
                                                        INNER JOIN courses ON units.c_id = courses.c_id
                                                        INNER JOIN users ON courses.i_id = users.user_id
                                                        WHERE enrollments.s_id = ?
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("s", $user_id);
                                                }
                                                $stmt->execute(); 
                                                $res = $stmt->get_result();
                                                while ($units = $res->fetch_object())
                                                {
                                            ?>
                                                    <tr>
                                                        <td><?php echo $units->u_code; ?></td>
                                                        <td><?php echo $units->u_name; ?></td>
                                                        <td><?php echo $units->c_name; ?></td>
                                                <?php
                                                    if($role === 'admin' || $role === 'student')
                                                    {
                                                ?>
                                                        <td><?php echo $units->i_name; ?></td>
                                                <?php
                                                    }
                                                    if($role === 'student')
                                                    {
                                                ?>
                                                        <td>
                                                            <a class="badge badge-outline-warning" href="manage_materials.php?u_id=<?php echo $units->u_id; ?>">
                                                                <i class="fas fa-external-link-alt"></i>
                                                                View Study Materials
                                                            </a>
                                                        </td>
                                                <?php
                                                    }
                                                    else if($role === 'admin' || $role === 'instructor')
                                                    {
                                                ?>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#add-<?php echo $units->u_id; ?>">
                                                                <i class="fas fa-file-upload"></i>
                                                                Upload Materials
                                                            </a>
                                                            <a class="badge badge-outline-success" href="manage_materials.php?u_id=<?php echo $units->u_id; ?>">
                                                                <i class="fas fa-cog"></i>
                                                                Manage Materials
                                                            </a>
                                                            <!-- Upload Materials Modal -->
                                                            <div class="modal fade" id="add-<?php echo $units->u_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Upload Materials For <?php echo $units->u_name; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="u_name">Unit</label>
                                                                                        <input type="text" name="u_name" value="<?php echo $units->u_name; ?>" readonly required class="form-control" id="u_name" autocomplete="name">
                                                                                        <!-- Hidden Values -->
                                                                                        <input type="hidden" name="u_id" value="<?php echo $units->u_id; ?>" readonly required class="form-control" id="u_id">
                                                                                        <input type="hidden" name="c_id" value="<?php echo $units->c_id; ?>" readonly required class="form-control" id="c_id">
                                                                                        <input type="hidden" name="i_id" value="<?php echo $units->i_id; ?>" readonly required class="form-control" id="i_id">
                                                                                        <input type="hidden" name="i_name" value="<?php echo $units->i_name; ?>" readonly required class="form-control" id="i_name">
                                                                                        <input type="hidden" name="c_name" value="<?php echo $units->c_name; ?>" readonly required class="form-control" id="c_name">
                                                                                        <input type="hidden" name="c_code" value="<?php echo $units->c_code; ?>" readonly required class="form-control" id="c_code">

                                                                                    </div>
                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="u_code">Unit Code</label>
                                                                                        <input type="text" name="u_code" readonly value="<?php echo $units->u_code; ?>" required class="form-control" id="u_code" autocomplete="on">
                                                                                    </div>
                                                                                    <div class="form-group col-md-4">
                                                                                        <label for="m_number">Materials Code</label>
                                                                                        <input type="text" name="m_number" readonly value="<?php echo $a; ?>-<?php echo $b; ?>" required class="form-control" id="m_number" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="exampleInputFile">Upload Materials Either in .pdf or .docx Format (As a pdf or a word document)</label>
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input required name="material" type="file" class="custom-file-input" id="exampleInputFile">
                                                                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="m_title">Material Name (Title of the Document)</label>
                                                                                        <input type="text" name="m_title" required class="form-control" id="m_title" autocomplete="on">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="m_price">Payment Amount</label>
                                                                                        <input type="number" step="0.01" min="0" name="m_price" required class="form-control" id="m_price" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <hr>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="add_unit_material" class="btn btn-outline-warning">Upload Materials</button>
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
                                                    <?php
                                                        }
                                                    ?>
                                                    </tr>
                                            <?php
                                                } 
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php
    }
?>