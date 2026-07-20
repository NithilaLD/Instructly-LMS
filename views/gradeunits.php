<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../vendor/autoload.php');
    
    $message = '';

    // Handle form submission
    if (isset($_POST['bulk_add_marks'])) {
        $student_id = $_POST['student_id']; // Now receiving ID directly from dropdown
        $raw_marks = $_POST['marks_data'];
        
        if (empty($student_id) || empty(trim($raw_marks))) {
            $message = "<div class='alert alert-warning'>Please select a student and enter the marks list.</div>";
        } else {
            try {
                $marks_array = preg_split('/\r\n|\r|\n/', trim($raw_marks), -1, PREG_SPLIT_NO_EMPTY);
                $successCount = 0; $skippedCount = 0;

                // Prepare queries
                $getUnit = $mysqli->prepare("SELECT u_id, c_id FROM units WHERE u_code = ?");
                $insertMark = $mysqli->prepare("INSERT INTO marks (r_code, u_id, s_id, u_cat1_marks, u_cat2_marks, u_eos_marks) VALUES (?, ?, ?, ?, ?, ?)");

                foreach ($marks_array as $line) {
                    $parts = explode(',', trim($line));
                    if(count($parts) == 4){
                        $u_code = trim($parts[0]);
                        $ca1 = (float)trim($parts[1]);
                        $ca2 = (float)trim($parts[2]);
                        $exam = (float)trim($parts[3]);

                        $getUnit->bind_param('s', $u_code);
                        $getUnit->execute();
                        $u_res = $getUnit->get_result();
                        
                        if ($unit = $u_res->fetch_assoc()) {
                            $rcode = 'RS' . substr(md5(uniqid(rand(), true)), 0, 13);
                            $insertMark->bind_param('siiddd', $rcode, $unit['u_id'], $student_id, $ca1, $ca2, $exam);
                            $insertMark->execute();
                            logAuditAction($mysqli, 'enter_marks', 'Marks added', 'marks', 'mark', $rcode);
                            $successCount++;
                        } else {
                            $skippedCount++;
                        }
                    } else {
                        $skippedCount++;
                    }
                }
                $message = "<div class='alert alert-success'>Processed marks. Uploaded {$successCount}. Skipped {$skippedCount} (Invalid Unit Code).</div>";
            } catch (Exception $e) {
                $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

    // Fetch students for the dropdown
    $students = $mysqli->query("SELECT user_id, user_code, name FROM users WHERE role = 'student'");

    require_once('../partials/head.php'); 
?>
<body>
    <div class="wrapper">
        <?php require_once('../partials/navbar.php'); ?>
        <?php require_once('../partials/sidebar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid mt-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <?php if (!empty($message)) echo $message; ?>
                        <div class="card card-body">
                            <form action="" method="POST">
                                <div class="form-group mb-3">
                                    <label><strong>Select Student</strong></label>
                                    <!-- Changed input to select for better usability -->
                                    <select name="student_id" class="form-control" required>
                                        <option value="">-- Search or Select Student --</option>
                                        <?php while ($row = $students->fetch_assoc()) { ?>
                                            <option value="<?= $row['user_id'] ?>">
                                                <?= $row['user_code'] ?> - <?= $row['name'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label><strong>Insert Marks (StudentCode, CA1(Out Of 30), CA2(Out Of 30), Exam(Out Of 100))</strong></label>
                                    <textarea name="marks_data" class="form-control" rows="8" placeholder="MATH101, 20, 25, 80&#10;HIS202, 15, 20, 75" required></textarea>
                                </div>
                                <button type="submit" name="bulk_add_marks" class="btn btn-primary">Grade Student</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once('../partials/scripts.php'); ?>
    <!-- If you have Select2 installed, initialize it here -->
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>