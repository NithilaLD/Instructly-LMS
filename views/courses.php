<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    require_once('../config/codeGen.php');
    require_once('../vendor/autoload.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // FUNCTION TO GENERATE AND DOWNLOAD COURSES TEMPLATE
    function downloadCoursesTemplate()
    {
        global $mysqli;
        
        require '../vendor/autoload.php';

        // PREPARED STATEMENT - Fetch instructor names
        $stmt = $mysqli->prepare("SELECT name, user_code FROM users WHERE role = 'instructor'");
        $stmt->execute();
        $result = $stmt->get_result();
        $dropdownValues = [];

        while ($row = $result->fetch_assoc()) {
            $dropdownValues[] = $row['user_code']." - ".$row['name'];
        }
        $stmt->close();

        // LOAD EXISTING TEMPLATE
        $templatePath = "../public/sys_data/uploads/xls/Courses_Template.xlsx";
        if (!file_exists($templatePath)) {
            die("Template file not found: " . $templatePath);
        }
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // INSERT VALUES INTO HIDDEN COLUMN (Z)
        $rowIndex = 1;
        foreach ($dropdownValues as $value) {
            $sheet->setCellValue("Z$rowIndex", $value);
            $rowIndex++;
        }

        // Set first value as default in first row (B2)
        if (!empty($dropdownValues)) {
            $sheet->setCellValue('B2', $dropdownValues[0]);
        }

        // CREATE DROPDOWN VALIDATION
        $validation = $sheet->getCell('B2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('=$Z$1:$Z$' . ($rowIndex - 1));

        // APPLY TO MULTIPLE ROWS
        for ($i = 2; $i <= 200; $i++) {
            $sheet->getCell("B$i")->setDataValidation(clone $validation);
        }

        // HIDE COLUMN Z
        $sheet->getColumnDimension('Z')->setVisible(false);
        
        if (ob_get_length()) {
            ob_end_clean();
        }

        // OUTPUT FILE
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Courses_Template.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // CHECK IF DOWNLOAD TEMPLATE IS REQUESTED
    if (isset($_GET['download_template']) && $_GET['download_template'] == 1 && $role === 'admin') {downloadCoursesTemplate();}

    /* Add Course */
    if (isset($_POST['add_course_cat']) && ($role === 'admin' || $role === 'instructor'))
    {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $c_code = '';

        if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
            $c_name = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }
        if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
            $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['c_ins']) && !empty($_POST['c_ins'])) {
            $c_ins = mysqli_real_escape_string($mysqli, trim($_POST['c_ins']));
        } else {
            $error = 1;
            $err = "Course Instructor Cannot Be Empty";
        }
        
        if (isset($_POST['c_desc']) && !empty($_POST['c_desc'])) {
            $c_desc = mysqli_real_escape_string($mysqli, trim($_POST['c_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }

        $c_dpic = '';
        $uploadedCourseImage = isset($_FILES["c_dpic"]["name"]) ? $_FILES["c_dpic"]["name"] : '';
        if (!empty($uploadedCourseImage)) {
            $imageExtension = pathinfo($uploadedCourseImage, PATHINFO_EXTENSION);
            $c_dpic = $c_code . '.' . $imageExtension;
        }
        else {
            $error = 1;
            $err = "Image is not Found";
        }

        if (!$error) {
            //prevent Double entries
            $stmt = $mysqli->prepare("SELECT * FROM courses WHERE c_code = ?");
            $stmt->bind_param("s", $c_code);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($c_code == $row['c_code']) {
                    $err =  "A Course With $c_code Exists";
                }
            } else {
                $query = "INSERT INTO courses (c_name, c_code, i_id, c_desc, c_dpic, c_date, al_date) VALUES (?,?,?,?,?,NOW(),NOW())";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('sssss', $c_name, $c_code, $c_ins, $c_desc, $c_dpic);
                $stmt->execute();
                if ($stmt) {
                    move_uploaded_file($_FILES["c_dpic"]["tmp_name"], "../public/sys_data/uploads/courses/" . $c_dpic);
                    $_SESSION['flash_success'] = "Course added successfully.";
                    header("Location: courses.php");
                    exit;
                } else {
                    $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                    header("Location: courses.php");
                    exit;
                }
            }
        }
    }

    /* Update Course */
    if (isset($_POST['update_course_cat']) && ($role === 'admin' || $role === 'instructor'))
    {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['c_name']) && !empty($_POST['c_name'])) {
            $c_name = mysqli_real_escape_string($mysqli, trim($_POST['c_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }
        if (isset($_POST['c_code']) && !empty($_POST['c_code'])) {
            $c_code = mysqli_real_escape_string($mysqli, trim($_POST['c_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['c_ins']) && !empty($_POST['c_ins'])) {
            $c_ins = mysqli_real_escape_string($mysqli, trim($_POST['c_ins']));
            if ($c_ins == 'N/A')
            {
                $error = 1;
                $err = "SELECT An Instructor From The Dropdown";
            }
        } else {
            $error = 1;
            $err = "Course Instructor Cannot Be Empty";
        }
        if (isset($_POST['c_desc']) && !empty($_POST['c_desc'])) {
            $c_desc = mysqli_real_escape_string($mysqli, trim($_POST['c_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }
        if (isset($_POST['c_id']) && !empty($_POST['c_id'])) {
            $c_id = mysqli_real_escape_string($mysqli, trim($_POST['c_id']));
        } else {
            $error = 1;
            $err = "ID Cannot Be Empty";
        }

        if (!$error) {
            if (isset($_FILES["c_dpic"]["name"]) && !empty($_FILES["c_dpic"]["name"])) {
                $uploadedCourseImage = $_FILES["c_dpic"]["name"];
                $imageExtension = pathinfo($uploadedCourseImage, PATHINFO_EXTENSION);
                $c_dpic = $c_code . '.' . $imageExtension;
                move_uploaded_file($_FILES["c_dpic"]["tmp_name"], "../public/sys_data/uploads/courses/" . $c_dpic);

                if (isset($_POST['current_c_dpic']) && !empty($_POST['current_c_dpic'])) {
                    $oldImagePath = "../public/sys_data/uploads/courses/" . basename($_POST['current_c_dpic']);
                    if (file_exists($oldImagePath) && basename($_POST['current_c_dpic']) !== $c_dpic) {
                        unlink($oldImagePath);
                    }
                }
            } else {
                $c_dpic = mysqli_real_escape_string($mysqli, $_POST['current_c_dpic']);
            }
            $query = "UPDATE courses SET c_name =?, c_code =?, i_id =?, c_desc =?, c_dpic =? WHERE c_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssssss', $c_name, $c_code, $c_ins, $c_desc, $c_dpic, $c_id);
            $stmt->execute();
            if ($stmt) {
                $_SESSION['flash_success'] = "Course updated successfully.";
                header("Location: courses.php");
                exit;
            } else {
                $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                header("Location: courses.php");
                exit;
            }
        }
    }

    /* Delete Course */
    if (isset($_GET['delete']) && $role === 'admin')
    {
        $id = $_GET['delete'];
        $imageQuery = "SELECT c_dpic FROM courses WHERE c_id = ?";
        $imageStmt = $mysqli->prepare($imageQuery);
        $imageStmt->bind_param('i', $id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $imageRow = $imageResult->fetch_object();

        

        $adn = "DELETE FROM courses WHERE c_id= '$id' ";
        $stmt = $mysqli->prepare($adn);
        $stmt->execute();
        $stmt->close();
        if ($stmt) {
            if (!empty($imageRow) && !empty($imageRow->c_dpic)) {
                $imagePath = "../public/sys_data/uploads/courses/" . basename($imageRow->c_dpic);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $_SESSION['flash_success'] = "Course deleted successfully.";
            header("Location: courses.php");
            exit;
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: courses.php");
            exit;
        }
    }

    /* Import Courses Via PhpOffice - Excel Sheet */
    if (isset($_POST["upload"]) && ($role === 'admin' || $role === 'instructor'))
    {

        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        /* Where Magic Happens */
        if (in_array($_FILES["file"]["type"], $allowedFileType))
        {

            $fileNameWithoutExt = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            $fileExt = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $renamedFileName = $fileNameWithoutExt . '_' . date('Y-m-d_His') . '.' . $fileExt;
            $targetPath = '../public/sys_data/uploads/xls/' . $renamedFileName;
            move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
            $importedAny = false;
            $zipTargetPath = '';

            $uploadedImageDir = '../public/sys_data/uploads/courses/';
            if (!is_dir($uploadedImageDir)) { mkdir($uploadedImageDir, 0777, true); }
            $zipExtractDir = dirname($targetPath) . DIRECTORY_SEPARATOR . pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            if (!is_dir($zipExtractDir)) { mkdir($zipExtractDir, 0777, true); }

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

            for ($i = 1; $i < $sheetCount; $i++)
            {

                $courseImage = "";
                $sourceImagePath = "";
                $fileExtension = "";
                $c_dpic = "";
                $c_name = "";
                
                if (isset($spreadSheetAry[$i][0])) { $c_name = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][0]);}
                
                $code_a = substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, 5);
                $code_b = substr(str_shuffle("1234567890"), 1, 5);
                $c_code = $code_a . '-' . $code_b;

                $c_ins_raw = '';
                $user_code = '';
                $c_ins = 0;

                if (isset($spreadSheetAry[$i][1])) { $c_ins_raw = trim($spreadSheetAry[$i][1]);}
                if (!empty($c_ins_raw))
                {
                    $parts = explode(' - ', $c_ins_raw, 2);
                    $user_code = trim($parts[0]);

                    $insQuery = "SELECT user_id FROM users WHERE role = 'instructor' AND user_code = ? LIMIT 1";
                    $insStmt = $mysqli->prepare($insQuery);
                    $insStmt->bind_param("s", $user_code);
                    $insStmt->execute();
                    $insRes = $insStmt->get_result();

                    if ($insRow = $insRes->fetch_assoc()) { $c_ins = (int)$insRow['user_id']; }
                    else
                    {
                        $err = "Instructor not found " . ($i + 1) . ": " . $c_ins_raw;
                        continue;
                    }
                    $insStmt->close();
                }

                $c_desc = "";
                if (isset($spreadSheetAry[$i][2])) { $c_desc = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][2]); }

                if (isset($spreadSheetAry[$i][3]) && trim($spreadSheetAry[$i][3]) !== "")
                {
                    $courseImage = trim($spreadSheetAry[$i][3]);
                    $imageName = basename($courseImage);
                    $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);

                    $sourceCandidates = [
                        $courseImage,
                        $zipExtractDir . DIRECTORY_SEPARATOR . $imageName,
                        dirname($targetPath) . DIRECTORY_SEPARATOR . $imageName,
                        '../public/sys_data/uploads/xls/' . $imageName,
                        '../public/sys_data/uploads/courses/' . $imageName,
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

                if (!empty($c_name) || !empty($c_ins))
                {
                    $query = "INSERT INTO courses (c_name, c_code, i_id, c_desc, c_dpic) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);

                    if ($stmt)
                    {
                        $stmt->bind_param("ssiss", $c_name, $c_code, $c_ins, $c_desc, $c_dpic);
                        if ($stmt->execute())
                        {
                            $insertId = $stmt->insert_id;
                            if (!empty($sourceImagePath) && !empty($fileExtension))
                            {
                                $c_dpic = $c_code . '.' . $fileExtension;
                                $destinationImage = $uploadedImageDir . $c_dpic;
                                if (file_exists($sourceImagePath))
                                {
                                    copy($sourceImagePath, $destinationImage);
                                    $updateQuery = "UPDATE courses SET c_dpic = ? WHERE c_id = ?";
                                    $updateStmt = $mysqli->prepare($updateQuery);
                                    if ($updateStmt)
                                    {
                                        $updateStmt->bind_param('si', $c_dpic, $insertId);
                                        $updateStmt->execute();
                                        $updateStmt->close();
                                    }
                                }
                            }
                            $_SESSION['flash_success'] = "Courses Data Imported";
                            $importedAny = true;
                        }
                    }
                } else { $_SESSION['flash_error'] = "Data Import Failed"; }
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
        else { $info = "Invalid File Type. Upload Excel File."; }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
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
                <div class="content-wrapper"><?php if($role === 'admin'){?> <?php } ?>
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <?php
                                    if($role === 'admin')
                                    {
                            ?>
                            <div class="container">
                                <div class="text-right text-dark" style="padding-top: 10px !important;">
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Courses Records </button>
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Course</button>
                                </div>
                            </div>
                            <?php
                                }
                            ?>
                            <hr>
                            <div class="card">
                                <div class="col-md-12">
                                <?php
                                    if($role === 'admin')
                                    {
                                ?>
                                    <!-- Import  Modal -->
                                    <div class="modal fade" id="import-modal">
                                        <div class="modal-dialog  modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">
                                                    Please, Use The Format Below<br><a href="courses.php?download_template=1">Download The Format</a>
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
                                                                    <label for="exampleInputFile">Select Excel File</label>
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
                                                                    <label for="exampleZipFile">Select Course Images ZIP File</label>
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
                                                            <div class=" col-md-6">
                                                                <label for="c_name">Course Name</label>
                                                                <input type="text" name="c_name" required class="form-control" id="c_name" autocomplete="name">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="c_code">Course Code</label>
                                                                <?php
                                                                // Ensure variables exist to avoid undefined variable notice
                                                                $code_a = isset($a) && $a !== '' ? $a : '';
                                                                $code_b = isset($b) && $b !== '' ? $b : '';
                                                                $c_code = $code_a !== '' && $code_b !== '' ? $code_a . '-' . $code_b : ($code_a !== '' ? $code_a : $code_b);
                                                                ?>
                                                                <input type="text" value="<?php echo $c_code; ?>" name="c_code" readonly required class="form-control" id="c_code" autocomplete="on">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="exampleInputFile">Course Logo</label>
                                                                <div class="input-group">
                                                                    <div class="custom-file">
                                                                        <input required name="c_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="exampleInputFile">
                                                                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="c_ins">Instructor</label>
                                                                <div class="form-group">

                                                                    <select name="c_ins" class="form-control select2bs4" style="width: 100%;" id="c_ins">
                                                                        <?php
                                                                        $ret = "SELECT * FROM users WHERE role = 'instructor'";
                                                                        $stmt = $mysqli->prepare($ret);
                                                                        $stmt->execute(); //ok
                                                                        $res = $stmt->get_result();
                                                                        while ($instructor = $res->fetch_object()) {
                                                                        ?>
                                                                            <option value="<?php echo htmlspecialchars($instructor->user_id); ?>">
                                                                                <?php echo $instructor->name; ?>
                                                                            </option>
                                                                        <?php
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <label for="editor">Course Description</label>
                                                                <textarea type="text" name="c_desc" class="form-control" id="editor"></textarea autocomplete="on">
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="text-right">
                                                            <button type="submit" name="add_course_cat" class="btn btn-outline-warning">Add Course</button>
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
                                <?php
                                    }
                                ?>
                                    <div class="card-body">
                                        <table id="dash-2" class="table table-striped table-bordered display " style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Course Name</th>
                                                    <?php
                                                        if ($role === 'student')
                                                        {
                                                    ?>
                                                            <th>Instructor</th>
                                                            <th>Date Enrolled</th>
                                                    <?php
                                                        }
                                                        else if ($role === 'instructor')
                                                        {  
                                                    ?>
                                                            <th>Date Allocated</th>
                                                            <th>Manage Course</th>
                                                    <?php
                                                        }
                                                        else if ($role === 'admin')
                                                        {
                                                    ?>
                                                            <th>Instructor</th>
                                                            <th>Manage Course</th>
                                                    <?php
                                                        }
                                                    ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if ($role === 'admin')
                                                {
                                                    $ret = "SELECT * FROM courses";
                                                    $stmt = $mysqli->prepare($ret);
                                                }
                                                else if($role === 'instructor')
                                                {
                                                    $ret = "
                                                        SELECT c.*, u.name AS instructor_name, c.al_date
                                                        FROM courses c
                                                        INNER JOIN users u ON c.i_id = u.user_id
                                                        WHERE u.role = 'instructor' AND c.i_id = ?
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("i", $user_id);
                                                }
                                                else if ($role === 'student')
                                                {
                                                    $ret = "
                                                        SELECT
                                                            e.en_id,
                                                            e.en_date,
                                                            c.c_code,
                                                            c.c_name,
                                                            u1.name AS student_name,
                                                            u1.user_code AS student_code,
                                                            u2.name AS instructor_name,
                                                            c.i_id
                                                        FROM enrollments e
                                                        JOIN courses c ON e.c_id = c.c_id
                                                        LEFT JOIN users u1 ON e.s_id = u1.user_id
                                                        LEFT JOIN users u2 ON c.i_id = u2.user_id
                                                        WHERE e.s_id = ?
                                                        ORDER BY e.en_id DESC
                                                    ";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->bind_param("i", $user_id);
                                                }
                                                $stmt->execute(); 
                                                $res = $stmt->get_result();
                                                while ($courses = $res->fetch_object())
                                                {
                                                    if($courses->i_id != null)
                                                    {
                                                        $inc= "SELECT * FROM users WHERE user_id = '$courses->i_id'";
                                                        $ins_stmt = $mysqli->prepare($inc);
                                                        $ins_stmt->execute();
                                                        $ins_res = $ins_stmt->get_result();
                                                        while ($instructor = $ins_res->fetch_object()) {
                                                            $courses->instructor_name = $instructor->name;
                                                        }
                                                    }
                                                    else {
                                                        $courses->instructor_name = "N/A";
                                                    }
                                            ?>
                                                    <tr>
                                                        <td><?php echo $courses->c_code; ?></td>
                                                        <td><?php echo $courses->c_name; ?></td>
                                                        <td><?php echo $courses->instructor_name; ?></td>
                                                        <td>
                                                    <?php
                                                        if ($role === 'student'){echo date('d M Y', strtotime($courses->en_date));}
                                                        else if ($role === 'admin' || $role === 'instructor')
                                                        {
                                                    ?>
                                                            <a class="badge badge-outline-warning" href="view_course.php?view=<?php echo $courses->c_id; ?>">
                                                                <i class="fas fa-eye"></i>
                                                                View
                                                            </a>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $courses->c_id; ?>">
                                                                <i class="fas fa-pencil-alt"></i>
                                                                Update
                                                            </a>
                                                            <!-- Update Modal -->
                                                            <div class="modal fade" id="update-<?php echo $courses->c_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Update <?php echo $courses->c_name; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class=" col-md-6">
                                                                                        <label for="c_name">Course Name</label>
                                                                                        <input type="text" name="c_name" value="<?php echo $courses->c_name; ?>" required class="form-control" id="c_name" autocomplete="name">
                                                                                        <input type="hidden" name="c_id" value="<?php echo $courses->c_id; ?>" required class="form-control" id="c_id">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="c_code">Course Code</label>
                                                                                        <input type="text" value="<?php echo $courses->c_code; ?>" name="c_code"  readonly required class="form-control" id="c_code" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="exampleInputFile">Course Logo</label><br>
                                                                                        <img
                                                                                            id="preview-<?php echo $courses->c_id; ?>"
                                                                                            src="../public/sys_data/uploads/courses/<?php echo htmlspecialchars($courses->c_dpic); ?>"
                                                                                            alt="Course Logo"
                                                                                            style="max-width: 120px; max-height: 120px;margin-bottom: 10px;"
                                                                                        >
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input
                                                                                                    name="c_dpic"
                                                                                                    type="file"
                                                                                                    class="custom-file-input"
                                                                                                    id="exampleInputFile-<?php echo $courses->c_id; ?>"
                                                                                                    accept=".png,.jpg,.jpeg"
                                                                                                    onchange="previewCourseImage(this, '<?php echo $courses->c_id; ?>')"
                                                                                                >
                                                                                                <label class="custom-file-label" for="exampleInputFile-<?php echo $courses->c_id; ?>">
                                                                                                    <?php echo !empty($courses->c_dpic) ? htmlspecialchars($courses->c_dpic) : 'Choose file'; ?>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <?php if (!empty($courses->c_dpic)) : ?>
                                                                                            <input type="hidden" name="current_c_dpic" value="<?php echo htmlspecialchars($courses->c_dpic); ?>" id="current_c_dpic">
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                    <div class="col-md-6">
                                                                                        <label for="c_ins">Instructor</label>
                                                                                        <div class="form-group">
                                                                                            <select name="c_ins" class="form-control select2bs4" style="width: 100%;" required id="c_ins">
                                                                                                <?php
                                                                                                $ins_ret = "SELECT  * FROM  users WHERE role = 'instructor'";
                                                                                                $ins_stmt = $mysqli->prepare($ins_ret);
                                                                                                $ins_stmt->execute();
                                                                                                $ins_res = $ins_stmt->get_result();
                                                                                                while ($instructor = $ins_res->fetch_object()) {
                                                                                                    if($courses->i_id == null){ ?> <option value="N/A" selected>N/A</option> <?php }
                                                                                                    $selected = trim($courses->instructor_name) == trim($instructor->name) ? 'selected' : '';
                                                                                                    ?>
                                                                                                        <option value="<?php echo htmlspecialchars($instructor->user_id); ?>" <?php echo $selected; ?>>
                                                                                                        <?php echo htmlspecialchars($instructor->name); ?>
                                                                                                    </option>                                             
                                                                                                <?php
                                                                                                }
                                                                                                $ins_stmt->close(); ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-12">
                                                                                        <label for="<?php echo $courses->c_id; ?>">Course Description</label>
                                                                                        <textarea type="text" name="c_desc" rows='10' class="form-control" id="<?php echo $courses->c_id; ?>"><?php echo $courses->c_desc; ?></textarea autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <hr>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="update_course_cat" class="btn btn-outline-warning">Update Course</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php
                                                            if($role === 'admin')
                                                            {
                                                        ?>
                                                                <!-- End Update Modal -->
                                                                <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $courses->c_id; ?>">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                    Delete
                                                                </a>
                                                                <!-- Delete Modal -->
                                                                <div class="modal fade" id="delete-<?php echo $courses->c_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center text-danger">
                                                                                <h4>Delete <?php echo $courses->c_code; ?> - <?php echo $courses->c_name; ?> Record ?</h4>
                                                                                <br>
                                                                                <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                                <a href="courses.php?delete=<?php echo $courses->c_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- End Delete Modal -->
                                                    <?php
                                                            }
                                                        }
                                                    ?>
                                                        </td>
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
            <!-- ./wrapper -->
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
            <script>
                function previewCourseImage(input, courseId) {
                    const preview = document.getElementById('preview-' + courseId);

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            preview.src = e.target.result;
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            </script>
        </body>
        </html>
    <?php
        } 
?>