<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRole('admin');
    require_once('../config/codeGen.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    
    $prefix= 'INS';
    require_once('../vendor/autoload.php');

    $uploadedImageDir = '../public/sys_data/uploads/users/';
    $excelUploadDir = '../public/sys_data/uploads/xls/';
    if (!is_dir($uploadedImageDir)) {mkdir($uploadedImageDir, 0777, true);}
    if (!is_dir($excelUploadDir)) {mkdir($excelUploadDir, 0777, true);}

    /* Add Instructor */
    if (isset($_POST['add_ins'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        
        // Auto-generate instructor number
        $i_number = getNextCode($mysqli, $prefix);

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
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_code = ?");
            $stmt->bind_param("s", $i_number);
            $stmt->execute();
            $res = $stmt->get_result();
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                if ($i_number == $row['user_code']) {
                    $err =  "An Instructor  With $i_number Already Exists";
                }
            } else {
                $status="active";
                $must_change_password = 1;
                $inrole = "instructor";
                $query = "INSERT INTO users (user_code, name, phone, email, password, dpic,status, role, must_change_password) VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt = $mysqli->prepare($query);
                $tempI_dpic = "";
                $rc = $stmt->bind_param('sssssssss', $i_number, $i_name, $i_phone, $i_email, $i_pwd, $tempI_dpic, $status, $inrole, $must_change_password);
                $stmt->execute();
                
                if ($stmt) {
                    $insertId = $mysqli->insert_id;
                    // Rename image file using instructor number
                    $newImageName = $i_number . '.' . $fileExtension;
                    $uploadDestination = "../public/sys_data/uploads/users/" . $newImageName;
                    move_uploaded_file($uploadedFile, $uploadDestination);
                    
                    // Update record with new image filename
                    $updateQuery = "UPDATE users SET dpic = ? WHERE user_id = ?";
                    $updateStmt = $mysqli->prepare($updateQuery);
                    $updateStmt->bind_param('si', $newImageName, $insertId);
                    $updateStmt->execute();
                    
                    $_SESSION['flash_success'] = "Instructor added successfully.";
                    header("Location: instructors.php");
                    exit;
                } else {
                    $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                    header("Location: instructors.php");
                    exit;
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

            $query = "UPDATE users SET user_code =?, name =?, phone =?, email =?, dpic =? WHERE user_id = ?";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('ssssss', $i_number, $i_name, $i_phone, $i_email, $newImageName, $i_id);
            $stmt->execute();
            if ($stmt) {
                $_SESSION['flash_success'] = "Instructor updated successfully.";
                header("Location: instructors.php");
                exit;
            } else {
                $_SESSION['flash_error'] = "Please Try Again Or Try Later";
                header("Location: instructors.php");
                exit;
            }
        }
    }

    /* Delete Instructor */
    if (isset($_GET['delete'])) {
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
            $_SESSION['flash_success'] = "Instructor deleted successfully.";
            header("Location: instructors.php");
            exit;
        } else {
            $_SESSION['flash_error'] = "Try Again Later";
            header("Location: instructors.php");
            exit;
        }
    }

    /* Bulk Import Instructors */
    if (isset($_POST["upload"])) {
        $allowedFileType = [
            'application/vnd.ms-excel',
            'text/xls',
            'text/xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

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

            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if ($ext === 'xls') {
                $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } else {
                $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }

            $spreadSheet = $Reader->load($targetPath);
            $excelSheet = $spreadSheet->getActiveSheet();
            $spreadSheetAry = $excelSheet->toArray();
            $sheetCount = count($spreadSheetAry);

            for ($i = 1; $i < $sheetCount; $i++) {

                // Auto-generate instructor number for each row
                $i_number = getNextCode($mysqli, $prefix);

                $i_name = "";
                if (isset($spreadSheetAry[$i][0])) {
                    $i_name = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][0]);
                }

                $i_email = "";
                if (isset($spreadSheetAry[$i][1])) {
                    $i_email = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][1]);
                }

                $i_phone = "";
                if (isset($spreadSheetAry[$i][2])) {
                    $i_phone = mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][2]);
                }

                $i_pwd = "";
                if (isset($spreadSheetAry[$i][3])) {
                    $i_pwd = sha1(md5(mysqli_real_escape_string($mysqli, $spreadSheetAry[$i][3])));
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
                    $status="active";
                    $must_change_password = 1;
                    $unrole = "instructor";
                    $query = "INSERT INTO users (user_code, role, name, email, password, phone, dpic, status, must_change_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $mysqli->prepare($query);
                    if ($stmt)
                    {
                        $stmt->bind_param(
                            "sssssssss",
                            $i_number,
                            $unrole,
                            $i_name,
                            $i_email,
                            $i_pwd,
                            $i_phone,
                            $i_dpic,
                            $status,
                            $must_change_password
                        );

                        if ($stmt->execute()) {$insertId = $stmt->insert_id;}
                    }
                    
                    if (!empty($insertId)) {
                        // If image source was found, copy it with the instructor number as filename
                        if (!empty($sourceImagePath) && !empty($fileExtension)) {
                            $newImageName = $i_number . '.' . $fileExtension;
                            $destinationImage = $uploadedImageDir . $newImageName;
                            if (file_exists($sourceImagePath)) {
                                copy($sourceImagePath, $destinationImage);
                                // Update the record with the new image filename
                                $updateQuery = "UPDATE users SET dpic = ? WHERE user_id = ?";
                                $updateStmt = $mysqli->prepare($updateQuery);
                                $updateStmt->bind_param('si', $newImageName, $insertId);
                                $updateStmt->execute();
                            }
                        }
                        $_SESSION['flash_success'] = "Instructors Data Imported";
                    } else {
                        $_SESSION['flash_error'] = "Data Import Failed";
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

    /* Add Allocation */
    if (isset($_POST['add_teaching_allocation'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $c_id = '';

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
            $err = "Instructor Id Cannot Be Empty";
        }

        if (!$error && isset($i_id))
        {
            // Check if this course is already allocated
            $stmt = $mysqli->prepare("SELECT c_id FROM courses WHERE i_id = ?");
            $stmt->bind_param("i", $i_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0 && $res->fetch_object()->c_id == $c_id) {$err = "This Lecturer is Already Allocated to the Course";}
            else
            {
                $up = $mysqli->prepare("UPDATE courses SET i_id = ?, al_date = NOW() WHERE c_id = ?");
                $up->bind_param("ii", $i_id, $c_id);
                $up->execute();

                $_SESSION['flash_success'] = "Teaching allocation added successfully.";
                header("Location: instructors.php");
                exit;
            }
        }
    }

    /* Delete Allocation */
    if (isset($_POST['remove_teaching_allocation'])) {
        //Error Handling and prevention of posting double entries
        $error = 0;
        $c_id = '';

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
            $err = "Instructor Id Cannot Be Empty";
        }

        // Remove instructor assignment from course
        $stmt2 = $mysqli->prepare("UPDATE courses SET i_id = null, al_date = NULL WHERE c_id = ?");
        $stmt2->bind_param("i", $c_id);
        $stmt2->execute();

        if ($stmt2->execute())
        {
            $_SESSION['flash_success'] = "Teaching allocation removed successfully.";
            header("Location: instructors.php");
            exit;
        }
        else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: instructors.php");
            exit;
        }
        
    }
    /* Persist System Settings  */
    $ret = "SELECT * FROM system ";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
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
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <div class="container">
                                <div class="text-right text-dark" style="padding-top: 10px !important;">
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#import-modal">Import Instructors Records </button>
                                    <button type="button" class="btn btn-outline-warning" data-toggle="modal" data-target="#add-modal">Add Instructor</button>
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
                                                    <h4 class="modal-title">
                                                        Please, Use The Format Below<br><a href="../public/sys_data/uploads/xls/Instructors_Template.xlsx">Download The Format</a>
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
                                                                <label for="instructorNumberInput">Instructor Number</label>
                                                                <input type="text" name="i_number" value="<?php echo getNextCode($mysqli, 'INS'); ?>" readonly required class="form-control" id="instructorNumberInput" aria-describedby="numberHelp" autocomplete="instructor-number">
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
                                                    <th>Instructor</th>
                                                    <th>Instructor Number</th>
                                                    <th>Contact</th>
                                                    <th>Email</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ret = "SELECT  * FROM  users WHERE role = 'instructor' ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->execute(); //ok
                                                $res = $stmt->get_result();
                                                while ($ins = $res->fetch_object()) {
                                                ?>

                                                    <tr>
                                                        <td><?php echo $ins->name; ?></td>
                                                        <td><?php echo $ins->user_code; ?></td>
                                                        <td><?php echo $ins->phone; ?></td>
                                                        <td><?php echo $ins->email; ?></td>
                                                        <td>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#allocate-<?php echo $ins->user_id; ?>">
                                                                <i class="fas fa-user-plus"></i>
                                                                Allocate
                                                            </a>
                                                            <!-- Allocate   Modal -->
                                                            <div class="modal fade" id="allocate-<?php echo $ins->user_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Allocate Instructor</h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="Course">Course</label>
                                                                                        <select name="c_id" style="width: 100%;" id="Course" required class="form-control select2bs4">
                                                                                            <option>Select Course</option>
                                                                                            <?php
                                                                                                $stmt = $mysqli->prepare("SELECT * FROM courses WHERE (i_id != ? OR i_id IS NULL)");
                                                                                                $stmt->bind_param("s", $ins->user_id);
                                                                                                $stmt->execute();
                                                                                                $res = $stmt->get_result();
                                                                                                while ($course = $res->fetch_object())
                                                                                                {
                                                                                            ?>
                                                                                                <option value="<?php echo $course->c_id; ?>">
                                                                                                    <?php echo $course->c_code; ?> - <?php echo $course->c_name; ?>
                                                                                                </option>
                                                                                            <?php 
                                                                                                } 
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="i_name">Instructor</label>
                                                                                        <input type="text" name="i_name" value="<?php echo $ins->name; ?>" readonly required class="form-control" id="INS_Name" autocomplete="on">
                                                                                            <input type="hidden" name="i_id" value="<?php echo $ins->user_id; ?>" id="INS_Id" required class="form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="add_teaching_allocation" class="btn btn-outline-warning">Allocate</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Allocate  Modal -->
                                                            <a class="badge badge-outline-warning" href="view_instructor.php?view=<?php echo $ins->user_id; ?>">
                                                                <i class="fas fa-eye"></i>
                                                                View
                                                            </a>

                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#update-<?php echo $ins->user_id; ?>">
                                                                <i class="fas fa-pencil-alt"></i>
                                                                Update
                                                            </a>
                                                            <!-- Update Modal -->
                                                            <div class="modal fade" id="update-<?php echo $ins->user_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Update <?php echo $ins->name; ?> Details</h4>
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
                                                                                        <input type="text" name="i_number" value="<?php echo $ins->user_code ?>" readonly required class="form-control" id="i_number" aria-describedby="emailHelp" autocomplete="on">
                                                                                        <input type="hidden" name="i_id" value="<?php echo $ins->user_id ?>" required class="form-control" id="i_id" aria-describedby="emailHelp">

                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="i_name">Instructor Full Name</label>
                                                                                        <input type="text" name="i_name" value="<?php echo $ins->name; ?>" required class="form-control" id="i_name" aria-describedby="emailHelp" autocomplete="name">
                                                                                    </div>

                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="i_email">Email Address</label>
                                                                                        <input type="email" name="i_email" value="<?php echo $ins->email; ?>" class="form-control" id="i_email" aria-describedby="emailHelp" autocomplete="email">
                                                                                    </div>

                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="i_phone">Phone Number</label>
                                                                                        <input type="text" name="i_phone" value="<?php echo $ins->phone; ?>" class="form-control" id="i_phone" aria-describedby="emailHelp" autocomplete="tel">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-12">
                                                                                        <b>Instructor Passport</b><br><br>
                                                                                        <?php if (!empty($ins->dpic)) { ?>
                                                                                            <div class="mb-2">
                                                                                                <img src="../public/sys_data/uploads/users/<?php echo $ins->dpic; ?>" alt="Instructor Passport" style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                                                                            </div>
                                                                                        <?php } ?>
                                                                                        <div class="input-group">
                                                                                            <div class="custom-file">
                                                                                                <input name="i_dpic" accept=".png, .jpg" type="file" class="custom-file-input" id="i_dpic">
                                                                                                <label class="custom-file-label" for="i_dpic">Choose file</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <input type="hidden" name="current_i_dpic" value="<?php echo $ins->dpic; ?>" id="current_i_dpic">
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

                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $ins->user_id; ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </a>
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="delete-<?php echo $ins->user_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center text-danger">
                                                                            <h4>Delete <?php echo $ins->name; ?> Details</h4>
                                                                            <br>
                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                            <a href="instructors.php?delete=<?php echo $ins->user_id; ?>" class="text-center btn btn-outline-warning"> Delete </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Delete Modal -->
                                                        <?php
                                                                $stmt = $mysqli->prepare("SELECT * FROM courses WHERE i_id = ?");
                                                                $stmt->bind_param("s", $ins->user_id);
                                                                $stmt->execute();
                                                                $res = $stmt->get_result();
                                                                if (mysqli_num_rows($res) > 0)
                                                                {
                                                        ?>
                                                             <a class="badge badge-outline-warning" data-toggle="modal" href="#unallocate-<?php echo $ins->user_id; ?>">
                                                                <i class="fas fa-user-minus"></i>
                                                                Unallocate
                                                            </a>
                                                        <?php
                                                            $alcid = [];
                                                            $alcname = [];
                                                            $alccode = [];
                                                            while ($unallocations = $res->fetch_object())
                                                            {
                                                                $alcid  [] = $unallocations->c_id;
                                                                $alcname [] = $unallocations->c_name;
                                                                $alccode [] = $unallocations->c_code;

                                                            }
                                                        ?>
                                                            <!-- Unallocate   Modal -->
                                                            <div class="modal fade" id="unallocate-<?php echo $ins->user_id; ?>">
                                                                <div class="modal-dialog  modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">Unallocate Instrcutor</h4>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <!-- Form -->
                                                                            <form method="post" enctype="multipart/form-data">
                                                                                <div class="row">
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="Course">Course</label>
                                                                                        <select name="c_id" style="width: 100%;" id="Course" required class="form-control select2bs4">
                                                                                            <option>Select Course</option>
                                                                                            <?php for ($i = 0; $i < count($alcid); $i++) { ?>
                                                                                                <option value="<?php echo $alcid[$i]; ?>">
                                                                                                    <?php echo $alccode[$i] . " - " . $alcname[$i]; ?>
                                                                                                </option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="form-group col-md-6">
                                                                                        <label for="i_name">Instructor</label>
                                                                                        <input type="text" name="i_name" value="<?php echo $ins->name; ?>" readonly required class="form-control" id="INS_Name" autocomplete="on">
                                                                                            <input type="hidden" name="i_id" value="<?php echo $ins->user_id; ?>" id="INS_Id" required class="form-control">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-right">
                                                                                    <button type="submit" name="remove_teaching_allocation" class="btn btn-outline-warning">Unallocate</button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="modal-footer justify-content-between">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Unallocate  Modal -->
                                                        <?php  } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
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