<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
admin();
require_once('../config/codeGen.php');

if (!function_exists('generateInstructorNumber')) {
    function generateInstructorNumber()
    {
        return 'INS' . substr(str_shuffle("QWERTYUIOPLKJHGFDSAZXCVBNM1234567890"), 1, 6) . substr(str_shuffle("1234567890"), 1, 4);
    }
}

/* Add Instructor */
if (isset($_POST['add_ins'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;
    
    // Auto-generate instructor number
    $i_number = generateInstructorNumber();

    if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
        $i_name = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
    } else {
        $error = 1;
        $err = "Name Cannot Be Empty";
    }

    if (isset($_POST['i_email']) && !empty($_POST['i_email'])) {
        $i_email = mysqli_real_escape_string($mysqli, trim($_POST['i_email']));
    } else {
        $error = 1;
        $err = "Email Cannot Be Empty";
    }

    if (isset($_POST['i_phone']) && !empty($_POST['i_phone'])) {
        $i_phone = mysqli_real_escape_string($mysqli, trim($_POST['i_phone']));
    } else {
        $error = 1;
        $err = "Phone Cannot Be Empty";
    }

    if (isset($_POST['i_pwd']) && !empty($_POST['i_pwd'])) {
        $i_pwd = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['i_pwd']))));
    } else {
        $error = 1;
        $err = "Password Cannot Be Empty";
    }

    $uploadedFile = $_FILES["i_dpic"]["tmp_name"];
    $fileExtension = pathinfo($_FILES["i_dpic"]["name"], PATHINFO_EXTENSION);
    $tempImageName = $_FILES["i_dpic"]["name"];

    if (!$error) {
        //prevent Double entries
        $sql = "SELECT * FROM  lms_instructor WHERE  i_number='$i_number'  ";
        $res = mysqli_query($mysqli, $sql);
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($i_number == $row['i_number']) {
                $err =  "An INstructor  With $i_number Already Exists";
            }
        } else {
            $query = "INSERT INTO lms_instructor (i_number, i_name, i_phone, i_email, i_pwd, i_dpic) VALUES (?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $tempI_dpic = "";
            $rc = $stmt->bind_param('ssssss', $i_number, $i_name, $i_phone, $i_email, $i_pwd, $tempI_dpic);
            $stmt->execute();
            
            if ($stmt) {
                $insertId = $mysqli->insert_id;
                // Rename image file using instructor number
                $newImageName = $i_number . '.' . $fileExtension;
                $uploadDestination = "../public/sys_data/uploads/users/" . $newImageName;
                move_uploaded_file($uploadedFile, $uploadDestination);
                
                // Update record with new image filename
                $updateQuery = "UPDATE lms_instructor SET i_dpic = ? WHERE i_id = ?";
                $updateStmt = $mysqli->prepare($updateQuery);
                $updateStmt->bind_param('si', $newImageName, $insertId);
                $updateStmt->execute();
                
                $success = "Added" && header("refresh:1; url=instructors.php");
            } else {
                $info = "Please Try Again Or Try Later";
            }
        }
    }
}

/* Update Instructor */
if (isset($_POST['update_ins'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;
    $i_id = '';

    if (isset($_POST['i_id']) && !empty($_POST['i_id'])) {
        $i_id = mysqli_real_escape_string($mysqli, trim($_POST['i_id']));
    } else {
        $error = 1;
        $err = "Instructor ID Cannot Be Empty";
    }

    if (isset($_POST['i_number']) && !empty($_POST['i_number'])) {
        $i_number = mysqli_real_escape_string($mysqli, trim($_POST['i_number']));
    } else {
        $error = 1;
        $err = "Number Cannot Be Empty";
    }

    if (isset($_POST['i_name']) && !empty($_POST['i_name'])) {
        $i_name = mysqli_real_escape_string($mysqli, trim($_POST['i_name']));
    } else {
        $error = 1;
        $err = "Name Cannot Be Empty";
    }

    if (isset($_POST['i_email']) && !empty($_POST['i_email'])) {
        $i_email = mysqli_real_escape_string($mysqli, trim($_POST['i_email']));
    } else {
        $error = 1;
        $err = "Email Cannot Be Empty";
    }

    if (isset($_POST['i_phone']) && !empty($_POST['i_phone'])) {
        $i_phone = mysqli_real_escape_string($mysqli, trim($_POST['i_phone']));
    } else {
        $error = 1;
        $err = "Phone Cannot Be Empty";
    }



    $currentImage = '';
    if (isset($_POST['current_i_dpic']) && !empty($_POST['current_i_dpic'])) {
        $currentImage = mysqli_real_escape_string($mysqli, trim($_POST['current_i_dpic']));
    }

    $uploadedFile = isset($_FILES["i_dpic"]) ? $_FILES["i_dpic"]["tmp_name"] : '';
    $fileName = isset($_FILES["i_dpic"]["name"]) ? $_FILES["i_dpic"]["name"] : '';
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    if (!$error) {
        $newImageName = $currentImage;
        if (!empty($fileName)) {
            $newImageName = $i_number . '.' . $fileExtension;
            $uploadDestination = "../public/sys_data/uploads/users/" . $newImageName;
            move_uploaded_file($uploadedFile, $uploadDestination);

            if (!empty($currentImage) && $currentImage !== $newImageName) {
                $oldImagePath = "../public/sys_data/uploads/users/" . basename($currentImage);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        $query = "UPDATE lms_instructor SET i_number =?, i_name =?, i_phone =?, i_email =?, i_dpic =? WHERE i_id = ?";
        $stmt = $mysqli->prepare($query);
        $rc = $stmt->bind_param('ssssss', $i_number, $i_name, $i_phone, $i_email, $newImageName, $i_id);
        $stmt->execute();
        if ($stmt) {
            $success = "Updated" && header("refresh:1; url=instructors.php");
        } else {
            $info = "Please Try Again Or Try Later";
        }
    }
}


/* Delete Instructor */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $imageQuery = "SELECT i_dpic FROM lms_instructor WHERE i_id = ?";
    $imageStmt = $mysqli->prepare($imageQuery);
    $imageStmt->bind_param('i', $id);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();
    $imageRow = $imageResult->fetch_object();

    

    $adn = "DELETE FROM lms_instructor WHERE i_id = '$id'";
    $stmt = $mysqli->prepare($adn);
    $stmt->execute();
    $stmt->close();

    if ($stmt) {
        if (!empty($imageRow) && !empty($imageRow->i_dpic)) {
            $imagePath = "../public/sys_data/uploads/users/" . basename($imageRow->i_dpic);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $success = "Deleted" && header("refresh:1; url=instructors.php");
    } else {
        $err = "Try Again Later";
    }
}


/* Bulk Import Instructors */

use DSAPI\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once('../config/DataSource.php');
$db = new DataSource();
$conn = $db->getConnection();
require_once('../vendor/autoload.php');

$uploadedImageDir = '../public/sys_data/uploads/users/';
if (!is_dir($uploadedImageDir)) {
    mkdir($uploadedImageDir, 0777, true);
}

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

            // Auto-generate instructor number for each row
            $i_number = generateInstructorNumber();

            $i_name = "";
            if (isset($spreadSheetAry[$i][0])) {
                $i_name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
            }

            $i_email = "";
            if (isset($spreadSheetAry[$i][1])) {
                $i_email = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
            }

            $i_phone = "";
            if (isset($spreadSheetAry[$i][2])) {
                $i_phone = mysqli_real_escape_string($conn, $spreadSheetAry[$i][2]);
            }

            $i_pwd = "";
            if (isset($spreadSheetAry[$i][3])) {
                $i_pwd = sha1(md5(mysqli_real_escape_string($conn, $spreadSheetAry[$i][3])));
            }

            $i_dpic = "";
            $sourceImagePath = "";
            $fileExtension = "";
            
            // Image filename is now in column 4 (previously column 5)
            if (isset($spreadSheetAry[$i][4]) && trim($spreadSheetAry[$i][4]) !== "") {
                $imageReference = trim($spreadSheetAry[$i][4]);
                $imageName = basename($imageReference);
                $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);
                
                $sourceCandidates = [
                    $imageReference,
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


            if (!empty(trim($i_name))) {
                $query = "INSERT INTO lms_instructor (i_number, i_name, i_phone, i_email, i_pwd, i_dpic) VALUES (?,?,?,?,?,?)";
                $paramType = "ssssss";
                $paramArray = array(
                    $i_number,
                    $i_name,
                    $i_phone,
                    $i_email,
                    $i_pwd,
                    $i_dpic
                );
                $insertId = $db->insert($query, $paramType, $paramArray);
                
                if (!empty($insertId)) {
                    // If image source was found, copy it with the instructor number as filename
                    if (!empty($sourceImagePath) && !empty($fileExtension)) {
                        $newImageName = $i_number . '.' . $fileExtension;
                        $destinationImage = $uploadedImageDir . $newImageName;
                        if (file_exists($sourceImagePath)) {
                            copy($sourceImagePath, $destinationImage);
                            // Update the record with the new image filename
                            $updateQuery = "UPDATE lms_instructor SET i_dpic = ? WHERE i_id = ?";
                            $updateStmt = $mysqli->prepare($updateQuery);
                            $updateStmt->bind_param('si', $newImageName, $insertId);
                            $updateStmt->execute();
                        }
                    }
                    $success = "Instructors Data Imported";
                } else {
                    $err = "Data Import Failed";
                }
            }
        }
        
        // Delete uploaded files after import completes
        if (file_exists($targetPath)) {
            unlink($targetPath);
        }
        if (isset($zipTargetPath) && file_exists($zipTargetPath)) {
            unlink($zipTargetPath);
        }
        
        // Delete extraction folder recursively
        if (is_dir($zipExtractDir)) {
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
                                <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Instructors Records </button>
                                <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Instructor</button>
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
                                                    Allowed file types: XLS, XLSX. Please, <a href="../public/sys_data/uploads/xls/Instructors_Template.xlsx">Download</a> The Sample File.
                                                </h4> -->
                                                <h4 class="modal-title">
                                                    Please, Use The Format Below<br><a href="../public/sys_data/uploads/xls/Instructors_Template.xlsx">Download The Format</a><br>
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
                                                            <label for="instructorNumberInput">Instructor Number</label>
                                                            <input type="text" name="i_number" value="<?php echo generateInstructorNumber(); ?>" readonly required class="form-control" id="instructorNumberInput" aria-describedby="numberHelp" autocomplete="instructor-number">
                                                        </div>

                                                        <div class="form-group col-md-6">
                                                            <label for="instructorNameInput">Instructor Full Name</label>
                                                            <input type="text" name="i_name" required class="form-control" id="instructorNameInput" aria-describedby="nameHelp" autocomplete="name">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label for="instructorEmailInput">Email Address</label>
                                                            <input type="email" name="i_email" class="form-control" id="instructorEmailInput" aria-describedby="emailHelp" autocomplete="email">
                                                        </div>

                                                        <div class="form-group col-md-6">
                                                            <label for="instructorPhoneInput">Phone Number</label>
                                                            <input type="text" name="i_phone" class="form-control" id="instructorPhoneInput" aria-describedby="phoneHelp" autocomplete="phone">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label for="instructorPasswordInput">Password</label>
                                                            <input type="password" name="i_pwd" class="form-control" id="instructorPasswordInput" aria-describedby="emailHelp" autocomplete="current-password">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <b>Instructor Passport</b><br><br>
                                                            <div class="input-group">
                                                                <div class="custom-file">
                                                                    <input required name="i_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="instructorPassportInput">
                                                                    <label class="custom-file-label" for="instructorPassportInput">Choose file</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="text-right">
                                                        <button type="submit" name="add_ins" class="btn btn-outline-warning">Add Instructor</button>
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
                                                <th>Number</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ret = "SELECT  * FROM  lms_instructor";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute(); //ok
                                            $res = $stmt->get_result();
                                            while ($ins = $res->fetch_object()) {
                                            ?>

                                                <tr>
                                                    <td><?php echo $ins->i_name; ?></td>
                                                    <td><?php echo $ins->i_number; ?></td>
                                                    <td><?php echo $ins->i_phone; ?></td>
                                                    <td><?php echo $ins->i_email; ?></td>
                                                    <td>
                                                        <a class="badge badge-outline-warning" href="view_instructor.php?view=<?php echo $ins->i_id; ?>">
                                                            <i class="fas fa-external-link-alt"></i>
                                                            View
                                                        </a>

                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $ins->i_id; ?>">
                                                            <i class="fas fa-pencil-alt"></i>
                                                            Update
                                                        </a>
                                                        <!-- Update Modal -->
                                                        <div class="modal fade" id="update-<?php echo $ins->i_id; ?>">
                                                            <div class="modal-dialog  modal-lg">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title">Update <?php echo $ins->i_name; ?> Details</h4>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <!-- Form -->
                                                                        <form method="post" enctype="multipart/form-data">
                                                                            <div class="row">
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="i_number">Instructor Number</label>
                                                                                    <input type="text" name="i_number" value="<?php echo $ins->i_number ?>" readonly required class="form-control" id="i_number" aria-describedby="emailHelp" autocomplete="on">
                                                                                    <input type="hidden" name="i_id" value="<?php echo $ins->i_id ?>" required class="form-control" id="i_id" aria-describedby="emailHelp">

                                                                                </div>
                                                                                <div class="form-group col-md-6">
                                                                                    <label for="i_name">Instructor Full Name</label>
                                                                                    <input type="text" name="i_name" value="<?php echo $ins->i_name; ?>" required class="form-control" id="i_name" aria-describedby="emailHelp" autocomplete="name">
                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="i_email">Email Address</label>
                                                                                    <input type="email" name="i_email" value="<?php echo $ins->i_email; ?>" class="form-control" id="i_email" aria-describedby="emailHelp" autocomplete="email">
                                                                                </div>

                                                                                <div class="form-group col-md-6">
                                                                                    <label for="i_phone">Phone Number</label>
                                                                                    <input type="text" name="i_phone" value="<?php echo $ins->i_phone; ?>" class="form-control" id="i_phone" aria-describedby="emailHelp" autocomplete="tel">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="form-group col-md-12">
                                                                                    <b>Instructor Passport</b><br><br>
                                                                                    <?php if (!empty($ins->i_dpic)) { ?>
                                                                                        <div class="mb-2">
                                                                                            <img src="../public/sys_data/uploads/users/<?php echo $ins->i_dpic; ?>" alt="Instructor Passport" style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                                                                        </div>
                                                                                    <?php } ?>
                                                                                    <div class="input-group">
                                                                                        <div class="custom-file">
                                                                                            <input name="i_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="i_dpic">
                                                                                            <label class="custom-file-label" for="i_dpic">Choose file</label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <input type="hidden" name="current_i_dpic" value="<?php echo $ins->i_dpic; ?>" id="current_i_dpic">
                                                                                </div>
                                                                            </div>
                                                                            <hr>
                                                                            <div class="text-right">
                                                                                <button type="submit" name="update_ins" class="btn btn-outline-primary">Update Instructor</button>
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

                                                        <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $ins->i_id; ?>">
                                                            <i class="fas fa-trash-alt"></i>
                                                            Delete
                                                        </a>
                                                        <!-- Delete Modal -->
                                                        <div class="modal fade" id="delete-<?php echo $ins->i_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <div class="modal-body text-center text-danger">
                                                                        <h4>Delete <?php echo $ins->i_name; ?> Details</h4>
                                                                        <br>
                                                                        <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                        <a href="instructors.php?delete=<?php echo $ins->i_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
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
} ?>
