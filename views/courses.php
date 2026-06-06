<?php
    session_start();
    include('../config/config.php');
    include('../config/checklogin.php');
    admin();
    require_once('../config/codeGen.php');

    // ðŸ”¹ FUNCTION TO GENERATE AND DOWNLOAD COURSES TEMPLATE
    function downloadCoursesTemplate() {
        global $mysqli;
        
        require '../vendor/autoload.php';

        // PREPARED STATEMENT - Fetch instructor names
        $stmt = $mysqli->prepare("SELECT i_name FROM lms_instructor");
        $stmt->execute();
        $result = $stmt->get_result();
        $dropdownValues = [];

        while ($row = $result->fetch_assoc()) {
            $dropdownValues[] = $row['i_name'];
        }
        $stmt->close();

        // LOAD EXISTING TEMPLATE
        $templatePath = "../public/sys_data/uploads/xls/Courses_Template.xlsx";
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
        $validation->setFormula1('$Z$1:$Z$' . ($rowIndex - 1));

        // APPLY TO MULTIPLE ROWS
        for ($i = 2; $i <= 200; $i++) {
            $sheet->getCell("B$i")->setDataValidation(clone $validation);
        }

        // HIDE COLUMN Z
        $sheet->getColumnDimension('Z')->setVisible(false);

        // OUTPUT FILE
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Courses_Template.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ðŸ”¹ CHECK IF DOWNLOAD TEMPLATE IS REQUESTED
    if (isset($_GET['download_template']) && $_GET['download_template'] == 1) {
        downloadCoursesTemplate();
    }

    /* Add Course */
    if (isset($_POST['add_course_cat'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $cc_code = '';

        if (isset($_POST['cc_name']) && !empty($_POST['cc_name'])) {
            $cc_name = mysqli_real_escape_string($mysqli, trim($_POST['cc_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }
        if (isset($_POST['cc_code']) && !empty($_POST['cc_code'])) {
            $cc_code = mysqli_real_escape_string($mysqli, trim($_POST['cc_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['cc_dept_head']) && !empty($_POST['cc_dept_head'])) {
            $cc_dept_head = mysqli_real_escape_string($mysqli, trim($_POST['cc_dept_head']));
        } else {
            $error = 1;
            $err = "Course HOD Cannot Be Empty";
        }
        if (isset($_POST['cc_desc']) && !empty($_POST['cc_desc'])) {
            $cc_desc = mysqli_real_escape_string($mysqli, trim($_POST['cc_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }

        $cc_dpic = '';
        $uploadedCourseImage = isset($_FILES["cc_dpic"]["name"]) ? $_FILES["cc_dpic"]["name"] : '';
        if (!empty($uploadedCourseImage)) {
            $imageExtension = pathinfo($uploadedCourseImage, PATHINFO_EXTENSION);
            $cc_dpic = $cc_code . '.' . $imageExtension;
        }
        else {
            $error = 1;
            $err = "Image is not Found";
        }

        if (!$error) {
            //prevent Double entries
            $sql = "SELECT * FROM  lms_course_categories WHERE  cc_code='$cc_code'  ";
            $res = mysqli_query($mysqli, $sql);
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($cc_code == $row['cc_code']) {
                    $err =  "A Course With $cc_code Exists";
                }
            } else {
                $query = "INSERT INTO lms_course_categories (cc_name, cc_code, cc_dept_head, cc_desc, cc_dpic) VALUES (?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $rc = $stmt->bind_param('sssss', $cc_name, $cc_code, $cc_dept_head, $cc_desc, $cc_dpic);
                $stmt->execute();
                if ($stmt) {
                    move_uploaded_file($_FILES["cc_dpic"]["tmp_name"], "../public/sys_data/uploads/courses/" . $cc_dpic);
                    $success = "Added" && header("refresh:1; url=courses.php");
                } else {
                    $info = "Please Try Again Or Try Later";
                }
            }
        }
    }

    /* Update Course */
    if (isset($_POST['update_course_cat'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;

        if (isset($_POST['cc_name']) && !empty($_POST['cc_name'])) {
            $cc_name = mysqli_real_escape_string($mysqli, trim($_POST['cc_name']));
        } else {
            $error = 1;
            $err = "Name Cannot Be Empty";
        }
        if (isset($_POST['cc_code']) && !empty($_POST['cc_code'])) {
            $cc_code = mysqli_real_escape_string($mysqli, trim($_POST['cc_code']));
        } else {
            $error = 1;
            $err = "Code Cannot Be Empty";
        }
        if (isset($_POST['cc_dept_head']) && !empty($_POST['cc_dept_head'])) {
            $cc_dept_head = mysqli_real_escape_string($mysqli, trim($_POST['cc_dept_head']));
        } else {
            $error = 1;
            $err = "Course HOD Cannot Be Empty";
        }
        if (isset($_POST['cc_desc']) && !empty($_POST['cc_desc'])) {
            $cc_desc = mysqli_real_escape_string($mysqli, trim($_POST['cc_desc']));
        } else {
            $error = 1;
            $err = "Description Cannot Be Empty";
        }
        if (isset($_POST['cc_id']) && !empty($_POST['cc_id'])) {
            $cc_id = mysqli_real_escape_string($mysqli, trim($_POST['cc_id']));
        } else {
            $error = 1;
            $err = "ID Cannot Be Empty";
        }

        

        if (!$error) {
            if (isset($_FILES["cc_dpic"]["name"]) && !empty($_FILES["cc_dpic"]["name"])) {
                $uploadedCourseImage = $_FILES["cc_dpic"]["name"];
                $imageExtension = pathinfo($uploadedCourseImage, PATHINFO_EXTENSION);
                $cc_dpic = $cc_code . '.' . $imageExtension;
                move_uploaded_file($_FILES["cc_dpic"]["tmp_name"], "../public/sys_data/uploads/courses/" . $cc_dpic);

                if (isset($_POST['current_cc_dpic']) && !empty($_POST['current_cc_dpic'])) {
                    $oldImagePath = "../public/sys_data/uploads/courses/" . basename($_POST['current_cc_dpic']);
                    if (file_exists($oldImagePath) && basename($_POST['current_cc_dpic']) !== $cc_dpic) {
                        unlink($oldImagePath);
                    }
                }
            } else {
                $cc_dpic = mysqli_real_escape_string($mysqli, $_POST['current_cc_dpic']);
            }
            $query = "UPDATE lms_course_categories SET cc_name =?, cc_code =?, cc_dept_head =?, cc_desc =?, cc_dpic =? WHERE cc_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssssss', $cc_name, $cc_code, $cc_dept_head, $cc_desc, $cc_dpic, $cc_id);
            $stmt->execute();
            if ($stmt) {
                $success = "Updated" && header("refresh:1; url=courses.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
    }

    /* Delete Course */
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $imageQuery = "SELECT cc_dpic FROM lms_course_categories WHERE cc_id = ?";
        $imageStmt = $mysqli->prepare($imageQuery);
        $imageStmt->bind_param('i', $id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $imageRow = $imageResult->fetch_object();

        

        $adn = "DELETE FROM lms_course_categories WHERE cc_id= '$id' ";
        $stmt = $mysqli->prepare($adn);
        $stmt->execute();
        $stmt->close();
        if ($stmt) {
            if (!empty($imageRow) && !empty($imageRow->cc_dpic)) {
                $imagePath = "../public/sys_data/uploads/courses/" . basename($imageRow->cc_dpic);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $success = "Deleted" && header("refresh:1; url=courses.php");
        } else {
            $info = "Please Try Again Or Try Later";
        }
    }

    /* Import Courses Via PhpOffice - Excel Sheet */

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
            $importedAny = false;
            $zipTargetPath = '';

            $uploadedImageDir = '../public/sys_data/uploads/courses/';
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

                $courseImage = "";
                $sourceImagePath = "";
                $fileExtension = "";
                $cc_dpic = "";

                $cc_name = "";
                if (isset($spreadSheetAry[$i][0])) {
                    $cc_name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
                }

                $code_a = substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, 5);
                $code_b = substr(str_shuffle("1234567890"), 1, 5);
                $cc_code = $code_a . '-' . $code_b;

                $cc_dept_head = "";
                if (isset($spreadSheetAry[$i][1])) {
                    $cc_dept_head = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
                }

                $cc_desc = "";
                if (isset($spreadSheetAry[$i][2])) {
                    $cc_desc = mysqli_real_escape_string($conn, $spreadSheetAry[$i][2]);
                }

                if (isset($spreadSheetAry[$i][3]) && trim($spreadSheetAry[$i][3]) !== "") {
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

                    foreach ($sourceCandidates as $sourceImage) {
                        if (is_string($sourceImage) && file_exists($sourceImage) && is_file($sourceImage)) {
                            $sourceImagePath = $sourceImage;
                            break;
                        }
                    }
                }

                if (!empty($cc_name) || !empty($cc_dept_head)) {
                    $query = "INSERT INTO lms_course_categories (cc_name, cc_code, cc_dept_head, cc_desc, cc_dpic) VALUES(?,?,?,?,?)";
                    $paramType = "sssss";
                    $paramArray = array(
                        $cc_name,
                        $cc_code,
                        $cc_dept_head,
                        $cc_desc,
                        $cc_dpic
                    );
                    $insertId = $db->insert($query, $paramType, $paramArray);
                    if (!empty($insertId)) {
                        if (!empty($sourceImagePath) && !empty($fileExtension)) {
                            $cc_dpic = $cc_code . '.' . $fileExtension;
                            $destinationImage = $uploadedImageDir . $cc_dpic;
                            if (file_exists($sourceImagePath)) {
                                copy($sourceImagePath, $destinationImage);
                                $updateQuery = "UPDATE lms_course_categories SET cc_dpic = ? WHERE cc_id = ?";
                                $updateStmt = $mysqli->prepare($updateQuery);
                                $updateStmt->bind_param('si', $cc_dpic, $insertId);
                                $updateStmt->execute();
                            }
                        }
                        $success = "Courses Data Imported";
                        $importedAny = true;
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
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Courses Records </button>
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Course</button>
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
                                                                <label for="cc_name">Course Name</label>
                                                                <input type="text" name="cc_name" required class="form-control" id="cc_name" autocomplete="name">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label for="cc_code">Course Code</label>
                                                                <?php
                                                                // Ensure variables exist to avoid undefined variable notice
                                                                $code_a = isset($a) && $a !== '' ? $a : '';
                                                                $code_b = isset($b) && $b !== '' ? $b : '';
                                                                $cc_code = $code_a !== '' && $code_b !== '' ? $code_a . '-' . $code_b : ($code_a !== '' ? $code_a : $code_b);
                                                                ?>
                                                                <input type="text" value="<?php echo $cc_code; ?>" name="cc_code" readonly required class="form-control" id="cc_code" autocomplete="on">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-6">
                                                                <label for="exampleInputFile">Course Logo</label>
                                                                <div class="input-group">
                                                                    <div class="custom-file">
                                                                        <input required name="cc_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="exampleInputFile">
                                                                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label for="cc_dept_head">Course Dept. Head Instructor</label>
                                                                <div class="form-group">

                                                                    <select name="cc_dept_head" class="form-control select2bs4" style="width: 100%;" id="cc_dept_head">
                                                                        <?php
                                                                        $ret = "SELECT  * FROM  lms_instructor";
                                                                        $stmt = $mysqli->prepare($ret);
                                                                        $stmt->execute(); //ok
                                                                        $res = $stmt->get_result();
                                                                        while ($instructor = $res->fetch_object()) {
                                                                        ?>
                                                                            <option><?php echo $instructor->i_name; ?></option>
                                                                        <?php
                                                                        } ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <label for="editor">Couse Description</label>
                                                                <textarea type="text" name="cc_desc" class="form-control" id="editor"></textarea autocomplete="on">
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
                                    <div class="card-body">
                                        <table id="dash-2" class="table table-striped table-bordered display " style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Course Name</th>
                                                    <th>Head Of Department</th>
                                                    <th>Manage Course</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ret = "SELECT  * FROM  lms_course_categories ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();
                                                while ($courses = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $courses->cc_code; ?></td>
                                                        <td><?php echo $courses->cc_name; ?></td>
                                                        <td><?php echo $courses->cc_dept_head; ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" href="view_course.php?view=<?php echo $courses->cc_id; ?>">
                                                                <i class="fas fa-external-link-alt"></i>
                                                                View
                                                            </a>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $courses->cc_id; ?>">
                                                                <i class="fas fa-pencil-alt"></i>
                                                                Update
                                                            </a>
                                                            <!-- Update Modal -->
                                                            <div class="modal fade" id="update-<?php echo $courses->cc_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Update <?php echo $courses->cc_name; ?></h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class=" col-md-6">
                                                                                        <label for="cc_name">Course Name</label>
                                                                                        <input type="text" name="cc_name" value="<?php echo $courses->cc_name; ?>" required class="form-control" id="cc_name" autocomplete="name">
                                                                                        <input type="hidden" name="cc_id" value="<?php echo $courses->cc_id; ?>" required class="form-control" id="cc_id">
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="cc_code">Course Code</label>
                                                                                        <input type="text" value="<?php echo $courses->cc_code; ?>" name="cc_code"  readonly required class="form-control" id="cc_code" autocomplete="on">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="exampleInputFile">Course Logo</label>
                                                                                        <?php if (!empty($courses->cc_dpic)) : ?>
                                                                                            <div class="mb-2">
                                                                                                <img src="../public/sys_data/uploads/courses/<?php echo htmlspecialchars($courses->cc_dpic); ?>" alt="Course Logo" style="max-width: 120px; max-height: 120px;">
                                                                                            </div>
                                                                                        <?php endif; ?>
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input name="cc_dpic" type="file" class="custom-file-input" id="exampleInputFile-<?php echo $courses->cc_id; ?>">
                                                                                                <label class="custom-file-label" for="exampleInputFile-<?php echo $courses->cc_id; ?>">
                                                                                                    <?php echo !empty($courses->cc_dpic) ? htmlspecialchars($courses->cc_dpic) : 'Choose file'; ?>
                                                                                                </label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <?php if (!empty($courses->cc_dpic)) : ?>
                                                                                            <input type="hidden" name="current_cc_dpic" value="<?php echo htmlspecialchars($courses->cc_dpic); ?>" id="current_cc_dpic">
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                    <div class="col-md-6">
                                                                                        <label for="cc_dept_head">Course Dept. Head Instructor</label>
                                                                                        <div class="form-group">
                                                                                            <select name="cc_dept_head" class="form-control select2bs4" style="width: 100%;" required id="cc_dept_head">
                                                                                                <?php
                                                                                                $ins_ret = "SELECT  * FROM  lms_instructor";
                                                                                                $ins_stmt = $mysqli->prepare($ins_ret);
                                                                                                $ins_stmt->execute();
                                                                                                $ins_res = $ins_stmt->get_result();
                                                                                                while ($instructor = $ins_res->fetch_object()) {
                                                                                                    $selected = trim($courses->cc_dept_head) == trim($instructor->i_name) ? 'selected' : '';
                                                                                                ?>
                                                                                                    <option value="<?php echo htmlspecialchars($instructor->i_name); ?>" <?php echo $selected; ?>>
                                                                                                        <?php echo htmlspecialchars($instructor->i_name); ?>
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
                                                                                        <label for="<?php echo $courses->cc_id; ?>">Couse Description</label>
                                                                                        <textarea type="text" name="cc_desc" rows='20' class="form-control" id="<?php echo $courses->cc_id; ?>"><?php echo $courses->cc_desc; ?></textarea autocomplete="on">
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
                                                            <!-- End Update Modal -->
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $courses->cc_id; ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </a>
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="delete-<?php echo $courses->cc_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center text-danger">
                                                                            <h4>Delete <?php echo $courses->cc_code; ?> - <?php echo $courses->cc_name; ?> Record ?</h4>
                                                                            <br>
                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                            <a href="courses.php?delete=<?php echo $courses->cc_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Delete Modal -->
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
        </body>
        </html>
    <?php
        } 
?>
