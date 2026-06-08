<?php
    session_start();
    include('../config/config.php');
    include('../config/checklogin.php');
    admin();
    require_once('../config/codeGen.php');

    if (!function_exists('generateStudentRegNo')) {
        function generateStudentRegNo()
        {
            return substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, 5) . substr(str_shuffle("1234567890"), 1, 5);
        }
    }

    // ðŸ”¹ FUNCTION TO GENERATE AND DOWNLOAD STUDENTS TEMPLATE
    function downloadStudentTemplate() {
        global $mysqli;
        
        require '../vendor/autoload.php';

        // PREPARED STATEMENT - Fetch course names
        $stmt = $mysqli->prepare("SELECT cc_name FROM lms_course_categories");
        $stmt->execute();
        $result = $stmt->get_result();
        $dropdownValues = [];

        while ($row = $result->fetch_assoc()) {
            $dropdownValues[] = $row['cc_name'];
        }
        $stmt->close();

        // LOAD EXISTING TEMPLATE
        $templatePath = "../public/sys_data/uploads/xls/Students_Template.xlsx";
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // INSERT VALUES INTO HIDDEN COLUMN (Z)
        $rowIndex = 1;
        foreach ($dropdownValues as $value) {
            $sheet->setCellValue("Z$rowIndex", $value);
            $rowIndex++;
        }

        // Set first value as default in first row (D2)
        if (!empty($dropdownValues)) {
            $sheet->setCellValue('D2', $dropdownValues[0]);
        }

        // CREATE DROPDOWN VALIDATION
        $validation = $sheet->getCell('D2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('$Z$1:$Z$' . ($rowIndex - 1));

        // APPLY TO MULTIPLE ROWS
        for ($i = 2; $i <= 200; $i++) {
            $sheet->getCell("D$i")->setDataValidation(clone $validation);
        }

        // HIDE COLUMN Z
        $sheet->getColumnDimension('Z')->setVisible(false);

        // OUTPUT FILE
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Students_Template.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ðŸ”¹ CHECK IF DOWNLOAD TEMPLATE IS REQUESTED
    if (isset($_GET['download_template']) && $_GET['download_template'] == 1) {
        downloadStudentTemplate();
    }

    /* Add Student */
    if (isset($_POST['add_std'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        $generatedStudentNo = generateStudentRegNo();


        if (isset($_POST['s_regno']) && !empty($_POST['s_regno'])) {
            $s_regno = mysqli_real_escape_string($mysqli, trim($_POST['s_regno']));
        } else {
            $s_regno = $generatedStudentNo;
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
            //prevent Double entries
            // Ensure $s_regno is defined (fallback to POST if not set earlier)
            if (empty($s_regno)) {
                $s_regno = generateStudentRegNo();
            }
            $stmt = $mysqli->prepare("SELECT * FROM lms_student WHERE s_regno = ?");
            $stmt->bind_param("s", $s_regno);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($s_regno == $row['s_regno']) {
                    $err =  "A Student  With $s_regno Already Exists";
                }
            } else {
                $query = "INSERT INTO lms_student (s_regno, s_course, s_name, s_email, s_pwd, s_phoneno, s_dpic) VALUES (?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('sssssss', $s_regno, $s_course, $s_name, $s_email, $s_pwd, $s_phoneno, $s_dpic);
                $stmt->execute();
                if ($stmt) {
                    if (!empty($fileName)) {
                        $s_dpic = $s_regno . '.' . $fileExtension;
                        move_uploaded_file($uploadedFile, "../public/sys_data/uploads/users/" . $s_dpic);

                        $updateImageQuery = "UPDATE lms_student SET s_dpic = ? WHERE s_id = ?";
                        $updateImageStmt = $mysqli->prepare($updateImageQuery);
                        $studentInsertId = $mysqli->insert_id;
                        $updateImageStmt->bind_param('si', $s_dpic, $studentInsertId);
                        $updateImageStmt->execute();
                    }
                    $success = "Added" && header("refresh:1; url=students.php");
                } else {
                    $info = "Please Try Again Or Try Later";
                }
            }
        }
    }

    /* Update Student */
    if (isset($_POST['update_student'])) {
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

            $query = "UPDATE lms_student SET s_regno =?,  s_name =?, s_email =?,  s_phoneno =?, s_dpic =? WHERE s_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssssi', $s_regno, $s_name, $s_email, $s_phoneno, $s_dpic, $s_id);
            $stmt->execute();
            if ($stmt) {
                $success = "Added" && header("refresh:1; url=students.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
    }

    /* Delete student  */
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $imageQuery = "SELECT s_dpic FROM lms_student WHERE s_id = ?";
        $imageStmt = $mysqli->prepare($imageQuery);
        $imageStmt->bind_param('i', $id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $imageRow = $imageResult->fetch_object();

        

        $adn = "DELETE FROM lms_student WHERE s_id = '$id'";
        $stmt = $mysqli->prepare($adn);
        $stmt->execute();
        $stmt->close();

        if ($stmt) {
            if (!empty($imageRow) && !empty($imageRow->s_dpic)) {
                $imagePath = "../public/sys_data/uploads/users/" . basename($imageRow->s_dpic);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $success = "Deleted" && header("refresh:1; url=students.php");
        } else {
            $err = "Try Again Later";
        }
    }

    /* Bulk Import Students */

    use DSAPI\DataSource;
    use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

    require_once('../config/DataSource.php');
    $db = new DataSource();
    $conn = $db->getConnection();
    require_once('../vendor/autoload.php');

    if (isset($_POST["upload"])) {

        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        /* Where Magic Happens */
        if (in_array($_FILES["file"]["type"], $allowedFileType)) {
            $fileNameWithoutExt = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $renamedFileName = $fileNameWithoutExt . '_' . date('Y-m-d_His') . '.' . $fileExt;
            $targetPath = '../public/sys_data/uploads/xls/' . $renamedFileName;
            move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
            $zipTargetPath = '';

            $uploadedImageDir = '../public/sys_data/uploads/users/';
            if (!is_dir($uploadedImageDir)) {
                mkdir($uploadedImageDir, 0777, true);
            }

            $zipExtractDir = dirname($targetPath) . DIRECTORY_SEPARATOR . pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            if (!is_dir($zipExtractDir)) {
                mkdir($zipExtractDir, 0777, true);
            }

            if (isset($_FILES['zip_file']) && !empty($_FILES['zip_file']['name'])) {
                $zipAllowedType = [
                    'application/zip',
                    'application/x-zip-compressed',
                    'multipart/x-zip',
                    'application/x-compressed'
                ];

                if (in_array($_FILES['zip_file']['type'], $zipAllowedType)) {
                    $zipFileNameWithoutExt = pathinfo($_FILES['zip_file']['name'], PATHINFO_FILENAME);
                    $zipFileExt = pathinfo($_FILES['zip_file']['name'], PATHINFO_EXTENSION);
                    $renamedZipFileName = $zipFileNameWithoutExt . '_' . date('Y-m-d_His') . '.' . $zipFileExt;
                    $zipTargetPath = '../public/sys_data/uploads/xls/' . $renamedZipFileName;
                    move_uploaded_file($_FILES['zip_file']['tmp_name'], $zipTargetPath);

                    if (class_exists('ZipArchive')) {
                        $zip = new ZipArchive();
                        if ($zip->open($zipTargetPath) === true) {
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

            for ($i = 1; $i < $sheetCount; $i++) {

                $studentImage = "";
                $sourceImagePath = "";
                $fileExtension = "";
                $s_dpic = "";


                $s_regno = "";
                if (empty(trim($s_regno))) {
                    $s_regno = generateStudentRegNo();
                }

                $s_name = "";
                if (isset($spreadSheetAry[$i][0])) {
                    $s_name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
                }

                $s_email = "";
                if (isset($spreadSheetAry[$i][1])) {
                    $s_email = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
                }

                $s_phoneno = "";
                if (isset($spreadSheetAry[$i][2])) {
                    $s_phoneno = mysqli_real_escape_string($conn, $spreadSheetAry[$i][2]);
                }

                $s_course = "";
                if (isset($spreadSheetAry[$i][3])) {
                    $s_course = mysqli_real_escape_string($conn, $spreadSheetAry[$i][3]);
                }

                /* Convert Student Password To Bunch Of Jumble Mumble */
                $s_pwd = "";
                if (isset($spreadSheetAry[$i][4])) {
                    $s_pwd = sha1(md5(mysqli_real_escape_string($conn, $spreadSheetAry[$i][4])));
                }

                if (isset($spreadSheetAry[$i][5]) && trim($spreadSheetAry[$i][5]) !== "") {
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

                    foreach ($sourceCandidates as $sourceImage) {
                        if (is_string($sourceImage) && file_exists($sourceImage) && is_file($sourceImage)) {
                            $sourceImagePath = $sourceImage;
                            break;
                        }
                    }
                }


                if (!empty(trim($s_name))) {
                    $query = "INSERT INTO lms_student (s_regno, s_course, s_name, s_email, s_pwd, s_phoneno, s_dpic) VALUES (?,?,?,?,?,?,?)";
                    $paramType = "sssssss";
                    $paramArray = array(
                        $s_regno,
                        $s_course,
                        $s_name,
                        $s_email,
                        $s_pwd,
                        $s_phoneno,
                        $s_dpic
                    );
                    $insertId = $db->insert($query, $paramType, $paramArray);
                    if (!empty($insertId)) {
                        if (!empty($sourceImagePath) && !empty($fileExtension)) {
                            $s_dpic = $s_regno . '.' . $fileExtension;
                            $destinationImage = $uploadedImageDir . $s_dpic;
                            if (file_exists($sourceImagePath)) {
                                copy($sourceImagePath, $destinationImage);
                                $updateQuery = "UPDATE lms_student SET s_dpic = ? WHERE s_id = ?";
                                $updateStmt = $mysqli->prepare($updateQuery);
                                $updateStmt->bind_param('si', $s_dpic, $insertId);
                                $updateStmt->execute();
                            }
                        }
                        $success = "Students Data Imported";
                    } else {
                        $err = "Data Import Failed";
                    }
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
    $ret = "SELECT * FROM `lms_sys_setttings` ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php'); ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>
                <!-- /.navbar -->

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper"><br>
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <div class="container">
                                <div class="text-right text-dark">
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Students Records </button>
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Student</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
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
                                                        Please, Use The Format Below<br><a href="students.php?download_template=1">Download The Format</a><br>
                                                        <br>Put the passport image filename in the last column.
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
                                                                <input type="text" name="s_regno" value="<?php echo generateStudentRegNo(); ?>" readonly required class="form-control" autocomplete="student-regno" id="s_regno">
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
                                                                    $ret = "SELECT  * FROM  lms_course_categories";
                                                                    $stmt = $mysqli->prepare($ret);
                                                                    $stmt->execute(); //ok
                                                                    $res = $stmt->get_result();
                                                                    while ($row = $res->fetch_object()) {
                                                                    ?>
                                                                        <option><?php echo $row->cc_name; ?></option>
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
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ret = "SELECT  * FROM  lms_student";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();
                                                while ($students = $res->fetch_object()) {
                                                ?>

                                                    <tr>
                                                        <td><?php echo $students->s_name; ?></td>
                                                        <td><?php echo $students->s_regno; ?></td>
                                                        <td><?php echo $students->s_email; ?></td>
                                                        <td><?php echo $students->s_phoneno; ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" href="view_student.php?view=<?php echo $students->s_id; ?>">
                                                                <i class="fas fa-external-link-alt"></i>
                                                                View
                                                            </a>

                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $students->s_id; ?>">
                                                                <i class="fas fa-pencil-alt"></i>
                                                                Update
                                                            </a>
                                                            <!-- Update Modal -->
                                                            <div class="modal fade" id="update-<?php echo $students->s_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Update <?php echo $students->s_name; ?> Details</h4>
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
                                                                                        <input type="text" name="s_regno" value="<?php echo $students->s_regno; ?>" readonly required class="form-control" id="s_regno" autocomplete="on">
                                                                                        <input type="hidden" name="s_id" value="<?php echo $students->s_id; ?>" required class="form-control" id="s_id">

                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="s_name">Full Name</label>
                                                                                        <input type="text" name="s_name" value="<?php echo $students->s_name; ?>" required class="form-control" id="s_name" autocomplete="name">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="s_phoneno">Phone Number</label>
                                                                                        <input type="text" name="s_phoneno" value="<?php echo $students->s_phoneno; ?>" class="form-control" id="s_phoneno" autocomplete="tel">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="s_email">Email address</label>
                                                                                        <input type="email" name="s_email" value="<?php echo $students->s_email; ?>" class="form-control" id="s_email" autocomplete="email">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">

                                                                                    <div class="form-group col-md-6">
                                                                                        <b>Student Passport<b><br><br>
                                                                                        <?php if (!empty($students->s_dpic)) { ?>
                                                                                            <div class="mb-2">
                                                                                                <img src="../public/sys_data/uploads/users/<?php echo $students->s_dpic; ?>" alt="Student Passport" style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                                                                            </div>
                                                                                        <?php } ?>
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input name="s_dpic" type="file" class="custom-file-input" id="exampleInputFile">
                                                                                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <input type="hidden" name="current_s_dpic" value="<?php echo $students->s_dpic; ?>" id="current_s_dpic">
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

                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $students->s_id; ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </a>
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="delete-<?php echo $students->s_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center text-danger">
                                                                            <h4>Delete <?php echo $students->s_name; ?> Details</h4>
                                                                            <br>
                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                            <a href="students.php?delete=<?php echo $students->s_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Delete Modal -->
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