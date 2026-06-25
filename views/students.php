<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../config/codeGen.php');
    require_once('../vendor/autoload.php');

    $prefix= 'STD';
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // FUNCTION TO GENERATE AND DOWNLOAD STUDENTS TEMPLATE
    function downloadStudentTemplate()
    {
        global $mysqli;
        
        require '../vendor/autoload.php';

        // PREPARED STATEMENT - Fetch course names
        $stmt = $mysqli->prepare("SELECT c_name FROM courses");
        $stmt->execute();
        $result = $stmt->get_result();
        $dropdownValues = [];

        while ($row = $result->fetch_assoc()) {$dropdownValues[] = $row['c_name'];}
        $stmt->close();

        // LOAD EXISTING TEMPLATE
        $templatePath = "../public/sys_data/uploads/xls/Students_Template.xlsx";
        if (!file_exists($templatePath)) {die("Template file not found: " . $templatePath);}
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // INSERT VALUES INTO HIDDEN COLUMN (Z)
        $rowIndex = 1;
        foreach ($dropdownValues as $value)
        {
            $sheet->setCellValue("Z$rowIndex", $value);
            $rowIndex++;
        }

        // Set first value as default in first row (D2)
        if (!empty($dropdownValues)) {$sheet->setCellValue('D2', $dropdownValues[0]);}

        // CREATE DROPDOWN VALIDATION
        $validation = $sheet->getCell('D2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('=$Z$1:$Z$' . ($rowIndex - 1));

        // APPLY TO MULTIPLE ROWS
        for ($i = 2; $i <= 200; $i++) {$sheet->getCell("D$i")->setDataValidation(clone $validation);}

        // HIDE COLUMN Z
        $sheet->getColumnDimension('Z')->setVisible(false);

        if (ob_get_length()) {ob_end_clean();}

        // OUTPUT FILE
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Students_Template.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // CHECK IF DOWNLOAD TEMPLATE IS REQUESTED
    if (isset($_GET['download_template']) && $_GET['download_template'] == 1) {
        downloadStudentTemplate();
    }

    /* Add Enrollment */
    if (isset($_POST['enroll_student']) && ($role === 'admin' || $role === 'instructor')) {
        $error = 0;

        if (isset($_POST['enc_id']) && !empty($_POST['enc_id'])) {
            $c_id = mysqli_real_escape_string($mysqli, trim($_POST['enc_id']));
        } else {
            $error = 1;
            $err = "Course ID Cannot Be Empty";
        }

        if (isset($_POST['s_id']) && !empty($_POST['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_POST['s_id']));
        } else {
            $error = 1;
            $err = "Student ID Cannot Be Empty";
        }

        if (!$error) {
            $stmt = $mysqli->prepare("SELECT * FROM enrollments WHERE s_id = ? AND c_id = ?");
            $stmt->bind_param("ss", $s_id, $c_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if (mysqli_num_rows($res) > 0) {
                while ($row = $res->fetch_object()) {
                    $err = "The Student is already enrolled to this course";
                }
            } else {
                $query = "INSERT INTO enrollments (c_id, s_id) VALUES (?,?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ss', $c_id, $s_id);
                $stmt->execute();

                if ($stmt) {
                    $_SESSION['flash_success'] = "Student enrolled successfully.";
                    header("Location: students.php");
                } else {
                    $_SESSION['flash_error'] = "Failed to enroll student.";
                }
            }
        }
    }

    /* Add Student */
    if (isset($_POST['add_std']) && ($role === 'admin' || $role === 'instructor')) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $s_regno = getNextCode($mysqli, $prefix);

        if (isset($_POST['s_name']) && !empty($_POST['s_name'])) {
            $s_name = mysqli_real_escape_string($mysqli, trim($_POST['s_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['s_email']) && !empty($_POST['s_email'])) {
            $s_email = mysqli_real_escape_string($mysqli, trim($_POST['s_email']));
        } else {
            $error = 1;
            $err = "Email Cannot Be Empty";
        }

        if (isset($_POST['s_phoneno']) && !empty($_POST['s_phoneno'])) {
            $s_phoneno = mysqli_real_escape_string($mysqli, trim($_POST['s_phoneno']));
        } else {
            $error = 1;
            $err = "Phone Cannot Be Empty";
        }

        if (isset($_POST['s_course']) && !empty($_POST['s_course'])) {
            $s_course = mysqli_real_escape_string($mysqli, trim($_POST['s_course']));
        } else {
            $error = 1;
            $err = "Student Course  Cannot Be Empty";
        }

        if (isset($_POST['s_pwd']) && !empty($_POST['s_pwd'])) {
            $s_pwd = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['s_pwd']))));
        } else {
            $error = 1;
            $err = "Password Cannot Be Empty";
        }

        $s_dpic = "";
        $uploadedFile = $_FILES["s_dpic"]["tmp_name"];
        $fileName = $_FILES["s_dpic"]["name"];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!$error) {
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_code = ?");
            $stmt->bind_param("s", $s_regno);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($s_regno == $row['user_code']) {
                    $err =  "A Student  With $s_regno Already Exists";
                }
            } else {
                $must_change_password = 1;
                $status = "active";
                $inrole = "student";
                $query = "INSERT INTO users (user_code, role, name, email, password, phone, dpic, status, must_change_password) VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('sssssssss', $s_regno, $inrole, $s_name, $s_email, $s_pwd, $s_phoneno, $s_dpic, $status, $must_change_password);
                $stmt->execute();
                if ($stmt) {
                    if (!empty($fileName)) {
                        $s_dpic = $s_regno . '.' . $fileExtension;
                        move_uploaded_file($uploadedFile, "../public/sys_data/uploads/users/" . $s_dpic);

                        $updateImageQuery = "UPDATE users SET dpic = ? WHERE user_id = ?";
                        $updateImageStmt = $mysqli->prepare($updateImageQuery);
                        $studentInsertId = $mysqli->insert_id;
                        $updateImageStmt->bind_param('si', $s_dpic, $studentInsertId);
                        $updateImageStmt->execute();
                    }
                    $_SESSION['flash_success'] = "Student added successfully.";
                    header("Location: students.php");
                } else {
                    $_SESSION['flash_error'] = "Failed to add student.";
                    header("Location: students.php");
                }
            }
        }
    }

    /* Update Student */
    if (isset($_POST['update_student']) && ($role === 'admin' || $role === 'instructor')) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['s_id']) && !empty($_POST['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_POST['s_id']));
        } else {
            $error = 1;
            $err = "Student ID Cannot Be Empty";
        }

        if (isset($_POST['s_regno']) && !empty($_POST['s_regno'])) {
            $s_regno = mysqli_real_escape_string($mysqli, trim($_POST['s_regno']));
        } else {
            $error = 1;
            $err = "Admission Number Cannot Be Empty";
        }

        if (isset($_POST['s_name']) && !empty($_POST['s_name'])) {
            $s_name = mysqli_real_escape_string($mysqli, trim($_POST['s_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }

        if (isset($_POST['s_email']) && !empty($_POST['s_email'])) {
            $s_email = mysqli_real_escape_string($mysqli, trim($_POST['s_email']));
        } else {
            $error = 1;
            $err = "Email Cannot Be Empty";
        }

        if (isset($_POST['s_phoneno']) && !empty($_POST['s_phoneno'])) {
            $s_phoneno = mysqli_real_escape_string($mysqli, trim($_POST['s_phoneno']));
        } else {
            $error = 1;
            $err = "Phone Cannot Be Empty";
        }

        $currentImage = '';
        if (isset($_POST['current_s_dpic']) && !empty($_POST['current_s_dpic'])) {
            $currentImage = mysqli_real_escape_string($mysqli, trim($_POST['current_s_dpic']));
        }

        $uploadedFile = isset($_FILES["s_dpic"]) ? $_FILES["s_dpic"]["tmp_name"] : '';
        $fileName = isset($_FILES["s_dpic"]) ? $_FILES["s_dpic"]["name"] : '';
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!$error) {
            $s_dpic = $currentImage;
            if (!empty($fileName)) {
                $s_dpic = $s_regno . '.' . $fileExtension;
                move_uploaded_file($uploadedFile, "../public/sys_data/uploads/users/" . $s_dpic);

                if (!empty($currentImage) && $currentImage !== $s_dpic) {
                    $oldImagePath = "../public/sys_data/uploads/users/" . basename($currentImage);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $query = "UPDATE users SET user_code =?, name =?, email =?, phone =?, dpic =? WHERE user_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssssi', $s_regno, $s_name, $s_email, $s_phoneno, $s_dpic, $s_id);
            $stmt->execute();
            if ($stmt) {
                $_SESSION['flash_success'] = "Student updated successfully.";
                header("Location: students.php");
            } else {
                $_SESSION['flash_error'] = "Failed to update student.";
                header("Location: students.php");
            }
        }
    }

    /* Delete student  */
    if (isset($_GET['delete']) && $role === 'admin') {
        $id = intval($_GET['delete']);
        $imageQuery = "SELECT dpic FROM users WHERE user_id = ?";
        $imageStmt = $mysqli->prepare($imageQuery);
        $imageStmt->bind_param('i', $id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $imageRow = $imageResult->fetch_object();

        $adn = "DELETE FROM users WHERE user_id = '$id'";
        $stmt = $mysqli->prepare($adn);
        $stmt->execute();
        $stmt->close();

        if ($stmt) {
            if (!empty($imageRow) && !empty($imageRow->dpic)) {
                $imagePath = "../public/sys_data/uploads/users/" . basename($imageRow->dpic);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $_SESSION['flash_success'] = "Student deleted successfully.";
            header("Location: students.php");
        } else {
            $_SESSION['flash_error'] = "Failed to delete student.";
            header("Location: students.php");
        }
    }

    /* Delete Enrollment */
    if (isset($_POST['unenroll_student']) && $role === 'admin') {
        if (isset($_POST['unenc_id']) && !empty($_POST['unenc_id'])) {
            $c_id = mysqli_real_escape_string($mysqli, trim($_POST['unenc_id']));
        } else {
            $error = 1;
            $_SESSION['flash_error'] = "Course ID Cannot Be Empty";
        }

        if (isset($_POST['s_id']) && !empty($_POST['s_id'])) {
            $s_id = mysqli_real_escape_string($mysqli, trim($_POST['s_id']));
        } else {
            $error = 1;
            $_SESSION['flash_error'] = "Student ID Cannot Be Empty";
        }
        if (!$error)
        {
            $stmt = $mysqli->prepare("DELETE FROM enrollments WHERE s_id = ? AND c_id = ?");
            $stmt->bind_param("ss", $s_id, $c_id);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "Student unenrolled successfully.";
                header("Location: students.php");
            } else {
                $_SESSION['flash_error'] = "Failed to unenroll student.";
                header("Location: students.php");
            }
        }
    }

    /* Bulk Import Students */
    if (isset($_POST["upload"]) && ($role === 'admin' || $role === 'instructor'))
    {

        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (in_array($_FILES["file"]["type"], $allowedFileType))
        {
            $fileNameWithoutExt = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $renamedFileName = $fileNameWithoutExt . '_' . date('Y-m-d_His') . '.' . $fileExt;
            $targetPath = '../public/sys_data/uploads/xls/' . $renamedFileName;
            move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
            $zipTargetPath = '';

            $uploadedImageDir = '../public/sys_data/uploads/users/';
            if (!is_dir($uploadedImageDir)) {mkdir($uploadedImageDir, 0777, true);}

            $zipExtractDir = dirname($targetPath) . DIRECTORY_SEPARATOR . pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            if (!is_dir($zipExtractDir)) { mkdir($zipExtractDir, 0777, true);}

            if (isset($_FILES['zip_file']) && !empty($_FILES['zip_file']['name']))
            {
                $zipAllowedType = [
                    'application/zip',
                    'application/x-zip-compressed',
                    'multipart/x-zip',
                    'application/x-compressed'
                ];

                if (in_array($_FILES['zip_file']['type'], $zipAllowedType))
                {
                    $zipFileNameWithoutExt = pathinfo($_FILES['zip_file']['name'], PATHINFO_FILENAME);
                    $zipFileExt = pathinfo($_FILES['zip_file']['name'], PATHINFO_EXTENSION);
                    $renamedZipFileName = $zipFileNameWithoutExt . '_' . date('Y-m-d_His') . '.' . $zipFileExt;
                    $zipTargetPath = '../public/sys_data/uploads/xls/' . $renamedZipFileName;
                    move_uploaded_file($_FILES['zip_file']['tmp_name'], $zipTargetPath);

                    if (class_exists('ZipArchive'))
                    {
                        $zip = new ZipArchive();
                        if ($zip->open($zipTargetPath) === true)
                        {
                            $zip->extractTo($zipExtractDir);
                            $zip->close();
                        }
                    }
                }
            }

            $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $spreadSheet = $Reader->load($targetPath);
            $excelSheet = $spreadSheet->getActiveSheet();
            $spreadSheetAry = $excelSheet->toArray();
            $sheetCount = count($spreadSheetAry);

            for ($i = 1; $i < $sheetCount; $i++)
            {

                $s_regno = getNextCode($mysqli, $prefix);

                $s_name = "";
                if (isset($spreadSheetAry[$i][0])) {$s_name = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][0]);}

                $s_email = "";
                if (isset($spreadSheetAry[$i][1])) {$s_email = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][1]);}

                $s_phoneno = "";
                if (isset($spreadSheetAry[$i][2])) {$s_phoneno = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][2]);}

                $s_course = "";
                if (isset($spreadSheetAry[$i][3])) {$s_course = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][3]);}

                $s_pwd = "";
                if (isset($spreadSheetAry[$i][4])) {$s_pwd = sha1(md5(mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][4])));}

                $studentImage = "";
                $sourceImagePath = "";
                $fileExtension = "";
                $s_dpic = "";

                if (isset($spreadSheetAry[$i][5]) && trim($spreadSheetAry[$i][5]) !== "")
                {
                    $studentImage = trim($spreadSheetAry[$i][5]);
                    $imageName = basename($studentImage);
                    $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);

                    $sourceCandidates = [
                        $studentImage,
                        $zipExtractDir . DIRECTORY_SEPARATOR . $imageName,
                        dirname($targetPath) . DIRECTORY_SEPARATOR . $imageName,
                        '../public/sys_data/uploads/xls/' . $imageName,
                        '../public/sys_data/uploads/users/' . $imageName,
                    ];

                    foreach ($sourceCandidates as $sourceImage)
                    {
                        if (is_string($sourceImage) && file_exists($sourceImage) && is_file($sourceImage))
                        {
                            $sourceImagePath = $sourceImage;
                            break;
                        }
                    }
                }


                if (!empty(trim($s_name)))
                {
                    $must_change_password = 1;
                    $status = "active";
                    $unrole = "student";
                    $query = "INSERT INTO users (user_code, role, name, email, password, phone, dpic, status, must_change_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
                    if ($stmt)
                    {
                        $stmt->bind_param(
                            "sssssssss",
                            $s_regno,
                            $unrole,
                            $s_name,
                            $s_email,
                            $s_pwd,
                            $s_phoneno,
                            $s_dpic,
                            $status,
                            $must_change_password
                        );

                        if ($stmt->execute()) {$insertId = $stmt->insert_id;}
                    }
                    if (!empty($insertId))
                    {
                        if (!empty($sourceImagePath) && !empty($fileExtension))
                        {
                            $s_dpic = $s_regno . '.' . $fileExtension;
                            $destinationImage = $uploadedImageDir . $s_dpic;
                            if (file_exists($sourceImagePath))
                            {
                                copy($sourceImagePath, $destinationImage);
                                $updateQuery = "UPDATE users SET dpic = ? WHERE user_id = ?";
                                $updateStmt = $mysqli->prepare($updateQuery);
                                $updateStmt->bind_param('si', $s_dpic, $insertId);
                                $updateStmt->execute();
                            }
                        }
                        $_SESSION['flash_success'] = "Students Data Imported";
                    }
                    else { $_SESSION['flash_error'] = "Data Import Failed";}
                }
            }

            $cleanupTargets = [];
            if (!empty($targetPath)) {
                $cleanupTargets[] = $targetPath;
            }
            if (!empty($zipTargetPath)) {
                $cleanupTargets[] = $zipTargetPath;
            }

            foreach ($cleanupTargets as $cleanupFile) {
                if (file_exists($cleanupFile)) {
                    unlink($cleanupFile);
                }
            }

            if (!empty($zipExtractDir) && is_dir($zipExtractDir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($zipExtractDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $fileinfo) {
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }
                rmdir($zipExtractDir);
            }
        } else {
            $info = "Invalid File Type. Upload Excel File.";
        }
    }

    /* Persist System Settings  */
    $sret = "SELECT * FROM system";
    $sstmt = $mysqli->prepare($sret);
    $sstmt->execute(); //ok
    $sres = $sstmt->get_result();
    while ($sys = $sres->fetch_object())
    {
        require_once('../partials/head.php'); ?>

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
                            <div class="container text-right">
                                <div class="text-right text-dark" style="padding-top: 10px !important;">
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Students Records</button>
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Student</button>
                                </div>
                            </div>
                            <hr>
                            <div class="card">
                                <div class="col-md-12">
                                    <!-- Import  Modal -->
                                    <div class="modal fade" id="import-modal">
                                        <div class="modal-dialog  modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <!-- <h4 class="modal-title">
                                                        Allowed file types: XLS, XLSX. Please, <a href="../public/sys_data/uploads/xls/Students_Template.xlsx">Download</a> The Sample File.
                                                    </h4> -->
                                                    <h4 class="modal-title">
                                                        Please, Use The Format Below<br><a href="students.php?download_template=1">Download The Format</a>
                                                    </h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Form -->
                                                    <form id="importForm" method="post" enctype="multipart/form-data" role="form">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleInputFile">Select File</label>
                                                                    <div class="input-group">
                                                                        <div class="custom-file">
                                                                            <input required name="file" type="file" class="custom-file-input" id="exampleInputFile">
                                                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row mt-3">
                                                                <div class="form-group col-md-12">
                                                                    <label for="exampleZipFile">Select Images ZIP File</label>
                                                                    <div class="input-group">
                                                                        <div class="custom-file">
                                                                            <input required name="zip_file" type="file" accept=".zip" class="custom-file-input" id="exampleZipFile">
                                                                            <label class="custom-file-label" for="exampleZipFile">Choose ZIP file</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <button type="submit" id="uploadBtn" name="upload" class="btn btn-outline-warning" disabled>Upload File</button>
                                                        </div>
                                                    </form>
                                                    <script>
                                                        document.getElementById('exampleInputFile').addEventListener('change', validateBothFiles);
                                                        document.getElementById('exampleZipFile').addEventListener('change', validateBothFiles);

                                                        function validateBothFiles() {
                                                            const excelFile = document.getElementById('exampleInputFile').files.length > 0;
                                                            const zipFile = document.getElementById('exampleZipFile').files.length > 0;
                                                            document.getElementById('uploadBtn').disabled = !(excelFile && zipFile);
                                                        }
                                                        document.getElementById('exampleInputFile').addEventListener('change', function ()
                                                        {
                                                            let fileName = this.files[0] ? this.files[0].name : 'Choose Excel file';
                                                            this.nextElementSibling.innerText = fileName;
                                                        });
                                                        document.getElementById('exampleZipFile').addEventListener('change', function ()
                                                        {
                                                            let fileName = this.files[0] ? this.files[0].name : 'Choose ZIP file';
                                                            this.nextElementSibling.innerText = fileName;
                                                        });
                                                    </script>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Import  Modal -->

                                    <!-- Add   Modal -->
                                    <div class="modal fade" id="add-modal">
                                        <div class="modal-dialog  modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Fill All Given Fields</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <!-- Form -->
                                                    <form method="post" enctype="multipart/form-data">
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="s_regno">Registration Number</label>
                                                                <input type="text" name="s_regno" value="<?php echo getNextCode($mysqli, 'STD'); ?>" readonly required class="form-control" autocomplete="student-regno" id="s_regno">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="s_name">Full Name</label>
                                                                <input type="text" name="s_name" required class="form-control" autocomplete="name" id="s_name">
                                                            </div>
                                                        </div>`
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="s_phoneno">Phone Number</label>
                                                                <input type="text" name="s_phoneno" class="form-control" autocomplete="phone" id="s_phoneno">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="inputGroupSelect03">Course</label>
                                                                <select class="custom-select" id="inputGroupSelect03" name="s_course">
                                                                    <option selected>Choose...</option>`
                                                                    <?php
                                                                        $astmt = null;
                                                                        if ($role === 'admin')
                                                                        {
                                                                            $aret = "SELECT * FROM courses";
                                                                            $astmt = $mysqli->prepare($aret);
                                                                        }
                                                                        else if ($role === 'instructor')
                                                                        {
                                                                            $aret = "SELECT c_id, c_name
                                                                                    FROM courses c
                                                                                    WHERE i_id = ?";
                                                                            $astmt = $mysqli->prepare($aret);
                                                                            $astmt->bind_param("i", $user_id);
                                                                        }
                                                                        $astmt->execute(); 
                                                                        $ares = $astmt->get_result();
                                                                        while ($arow = $ares->fetch_object()) {
                                                                        ?>
                                                                            <option><?php echo $arow->c_name; ?></option>
                                                                        <?php
                                                                        } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-4">
                                                                <label for="s_email">Email address</label>
                                                                <input type="email" name="s_email" class="form-control" autocomplete="email" id="s_email">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <label for="s_pwd">Password</label>
                                                                <input type="password" name="s_pwd" class="form-control" autocomplete="current-password" id="s_pwd">
                                                            </div>

                                                            <div class="form-group col-md-4">
                                                                <label for="exampleInputFile">Student Passport</label>
                                                                <div class="input-group">
                                                                    <div class="custom-file">
                                                                        <input required name="s_dpic" type="file" class="custom-file-input" id="exampleInputFile">
                                                                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <hr>
                                                        <div class="text-right">
                                                            <button type="submit" name="add_std" class="btn btn-outline-warning">Add Student</button>
                                                        </div>
                                                    </form>

                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Add  Modal -->

                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>RegNo</th>
                                                    <th>Email</th>
                                                    <th>Contact</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    $vstmt = null;
                                                    $onrole = "student";
                                                    $vret = "SELECT * FROM users WHERE role = ?";
                                                    $vstmt = $mysqli->prepare($vret);
                                                    $vstmt->bind_param("s", $onrole);
                                                    $vstmt->execute();
                                                    $vres = $vstmt->get_result();
                                                    while ($students = $vres->fetch_object())
                                                    {
                                                ?>

                                                        <tr>
                                                            <td><?php echo $students->name; ?></td>
                                                            <td><?php echo $students->user_code; ?></td>
                                                            <td><?php echo $students->email; ?></td>
                                                            <td><?php echo $students->phone; ?></td>
                                                            <td>
                                                                <a class="badge badge-outline-warning" data-toggle="modal" href="#enroll-<?php echo $students->user_id; ?>">
                                                                    <i class="fas fa-user-plus"></i>
                                                                    Enroll
                                                                </a>
                                                                <!-- Enroll   Modal -->
                                                                <div class="modal fade" id="enroll-<?php echo $students->user_id; ?>">
                                                                    <div class="modal-dialog  modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h4 class="modal-title">Enroll Student</h4>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <!-- Form -->
                                                                                <form method="post">
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="Course">Course</label>
                                                                                            <select name="enc_id" style="width: 100%;" id="Course" required class="form-control select2bs4">
                                                                                                <option value="">Select Course</option>
                                                                                                <?php
                                                                                                    $cstmt = null;
                                                                                                    if ($role === 'admin')
                                                                                                    {
                                                                                                        $cret = "SELECT c_id, c_code, c_name FROM courses";
                                                                                                        $cstmt = $mysqli->prepare($cret);
                                                                                                    }
                                                                                                    else
                                                                                                    {
                                                                                                        $cret = "SELECT c_id, c_code, c_name FROM courses WHERE i_id = ?";
                                                                                                        $cstmt = $mysqli->prepare($cret);
                                                                                                        $cstmt->bind_param("i", $user_id);
                                                                                                    }
                                                                                                    $cstmt->execute();
                                                                                                    $res = $cstmt->get_result();
                                                                                                    while ($course = $res->fetch_object())
                                                                                                    {
                                                                                                        $eret = "SELECT * FROM enrollments WHERE s_id = ? AND c_id = ?";
                                                                                                        $estmt = $mysqli->prepare($eret);
                                                                                                        $estmt->bind_param("ii", $students->user_id, $course->c_id);
                                                                                                        $estmt->execute();
                                                                                                        $enrollment = $estmt->get_result();
                                                                                                        if(mysqli_num_rows($enrollment) == 0)
                                                                                                        {
                                                                                                ?>
                                                                                                            <option value="<?php echo $course->c_id; ?>">
                                                                                                                <?php echo $course->c_code; ?> - <?php echo $course->c_name; ?>
                                                                                                            </option>
                                                                                                <?php 
                                                                                                        }
                                                                                                    }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="Std_Name">Student</label>
                                                                                            <input type="text" name="s_name" value="<?php echo $students->name; ?>" readonly required class="form-control" id="Std_Name" autocomplete="on">
                                                                                            <input type="hidden" name="s_id" value="<?php echo $students->user_id; ?>" id="Std_Id" required class="form-control">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="text-right">
                                                                                        <button type="submit" name="enroll_student" class="btn btn-outline-warning">Enroll</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer justify-content-between">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End Enroll  Modal -->
                                                                <a class="badge badge-outline-warning" href="view_student.php?view=<?php echo $students->user_id; ?>">
                                                                    <i class="fas fa-eye"></i>
                                                                    View
                                                                </a>

                                                                <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $students->user_id; ?>">
                                                                    <i class="fas fa-pencil-alt"></i>
                                                                    Update
                                                                </a>
                                                                <!-- Update Modal -->
                                                                <div class="modal fade" id="update-<?php echo $students->user_id; ?>">
                                                                    <div class="modal-dialog  modal-lg">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h4 class="modal-title">Update <?php echo $students->name; ?> Details</h4>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <!-- Form -->
                                                                                <form method="post" enctype="multipart/form-data">
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="s_regno">Registration Number</label>
                                                                                            <input type="text" name="s_regno" value="<?php echo $students->user_code; ?>" readonly required class="form-control" id="s_regno" autocomplete="on">
                                                                                            <input type="hidden" name="s_id" value="<?php echo $students->user_id; ?>" required class="form-control" id="s_id">

                                                                                        </div>
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="s_name">Full Name</label>
                                                                                            <input type="text" name="s_name" value="<?php echo $students->name; ?>" required class="form-control" id="s_name" autocomplete="name">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="s_phoneno">Phone Number</label>
                                                                                            <input type="text" name="s_phoneno" value="<?php echo $students->phone; ?>" class="form-control" id="s_phoneno" autocomplete="tel">
                                                                                        </div>
                                                                                        <div class="form-group col-md-6">
                                                                                            <label for="s_email">Email address</label>
                                                                                            <input type="email" name="s_email" value="<?php echo $students->email; ?>" class="form-control" id="s_email" autocomplete="email">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">

                                                                                        <div class="form-group col-md-6">
                                                                                            <b>Student Passport<b><br><br>
                                                                                            <?php if (!empty($students->dpic)) { ?>
                                                                                                <div class="mb-2">
                                                                                                    <img src="../public/sys_data/uploads/users/<?php echo $students->dpic; ?>" alt="Student Passport" style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                                                                                </div>
                                                                                            <?php } ?>
                                                                                            <div class="input-group">
                                                                                                <div class="custom-file">
                                                                                                    <input name="s_dpic" type="file" class="custom-file-input" id="exampleInputFile">
                                                                                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                                                </div>
                                                                                            </div>
                                                                                            <input type="hidden" name="current_s_dpic" value="<?php echo $students->dpic; ?>" id="current_s_dpic">
                                                                                        </div>

                                                                                    </div>

                                                                                    <hr>
                                                                                    <div class="text-right">
                                                                                        <button type="submit" name="update_student" class="btn btn-outline-warning">Update Student</button>
                                                                                    </div>
                                                                                </form>
                                                                            </div>
                                                                            <div class="modal-footer justify-content-between">
                                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End Update Modal -->
                                                                <?php
                                                                    if($role === 'admin')
                                                                    {
                                                                ?>
                                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $students->user_id; ?>">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                            Delete
                                                                        </a>
                                                                        <!-- Delete Modal -->
                                                                        <div class="modal fade" id="delete-<?php echo $students->user_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                            <span aria-hidden="true">&times;</span>
                                                                                        </button>
                                                                                    </div>
                                                                                    <div class="modal-body text-center text-danger">
                                                                                        <h4>Delete <?php echo $students->name; ?> Details</h4>
                                                                                        <br>
                                                                                        <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                                        <a href="students.php?delete=<?php echo $students->user_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!-- End Delete Modal -->
                                                                <?php
                                                                        $unstmt = $mysqli->prepare("SELECT e.en_id, e.c_id, c.c_code, c.c_name FROM enrollments e INNER JOIN courses c ON c.c_id = e.c_id WHERE e.s_id = ? ");
                                                                        $unstmt->bind_param("s", $students->user_id);
                                                                        $unstmt->execute();
                                                                        $unres = $unstmt->get_result();
                                                                        if (mysqli_num_rows($unres) > 0)
                                                                        {
                                                                ?>
                                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#unenroll-<?php echo $students->user_id; ?>">
                                                                                <i class="fas fa-user-minus"></i>
                                                                                Unenroll
                                                                            </a>
                                                                <?php
                                                                            $encid = [];
                                                                            $encname = [];
                                                                            $enccode = [];
                                                                            while ($unenrollments = $unres->fetch_object())
                                                                            {
                                                                                $encid  [] = $unenrollments->c_id;
                                                                                $encname [] = $unenrollments->c_name;
                                                                                $enccode [] = $unenrollments->c_code;

                                                                            }
                                                                ?>
                                                                                <!-- Unenroll Modal -->
                                                                                    <div class="modal fade" id="unenroll-<?php echo $students->user_id; ?>">
                                                                                        <div class="modal-dialog modal-lg">
                                                                                            <div class="modal-content">
                                                                                                <div class="modal-header">
                                                                                                    <h4 class="modal-title">Unenroll Student</h4>
                                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                                        <span aria-hidden="true">&times;</span>
                                                                                                    </button>
                                                                                                </div>
                                                                                                <div class="modal-body">
                                                                                                    <!-- Form -->
                                                                                                    <form method="post">
                                                                                                        <div class="row">
                                                                                                            <div class="form-group col-md-6">
                                                                                                                <label for="Course">Course</label>
                                                                                                                <select name="unenc_id" style="width: 100%;" id="Course" required class="form-control">
                                                                                                                    <option value="">Select Course</option>
                                                                                                                    <?php for ($i = 0; $i < count($encid); $i++) { ?>
                                                                                                                        <option value="<?php echo $encid[$i]; ?>">
                                                                                                                            <?php echo $enccode[$i] . " - " . $encname[$i]; ?>
                                                                                                                        </option>
                                                                                                                    <?php } ?>
                                                                                                                </select>
                                                                                                            </div>
                                                                                                            <div class="form-group col-md-6">
                                                                                                                <label for="Std_Name">Student</label>
                                                                                                                <input type="text" name="s_name" value="<?php echo $students->name; ?>" readonly required class="form-control" id="Std_Name" autocomplete="on">
                                                                                                                <input type="hidden" name="s_id" value="<?php echo $students->user_id; ?>" id="Std_Id" required class="form-control">
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="text-right">
                                                                                                            <button type="submit" name="unenroll_student" class="btn btn-outline-warning">Unenroll Student</button>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                </div>
                                                                                                <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                <!-- End Unenroll Modal -->
                                                                <?php  
                                                                        }
                                                                    } 
                                                            
                                                                    }
                                                                ?>
                                                            </td>
                                                        </tr>
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