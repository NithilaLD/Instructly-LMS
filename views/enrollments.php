<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../config/codeGen.php');
    require_once('../vendor/autoload.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $message = '';

    // Process the Bulk Enrollment Form
    if (isset($_POST['bulk_enroll']))
    {
        $course_id = $_POST['course_id'];
        
        // A multiple select dropdown sends an array of values
        $student_ids_array = isset($_POST['student_ids']) ? $_POST['student_ids'] : [];
        
        // Basic validation
        if (empty($course_id)) {
            $message = "<div class='alert alert-warning'>Please select a course.</div>";
        } elseif (empty($student_ids_array) || !is_array($student_ids_array)) {
            $message = "<div class='alert alert-warning'>Please select at least one student.</div>";
        } else {
            try {
                $successCount = 0;
                $skippedCount = 0;
                
                // Prepare statements outside the loop for performance
                $checkUser = $mysqli->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'student'");
                $checkEnrollment = $mysqli->prepare("SELECT en_id FROM enrollments WHERE s_id = ? AND c_id = ?");
                $insertEnrollment = $mysqli->prepare("INSERT INTO enrollments (s_id, c_id, status) VALUES (?, ?, 'active')");

                // Loop through the selected IDs array directly
                foreach ($student_ids_array as $user_id) {
                    
                    // 1. Verify the student exists in the system
                    $checkUser->bind_param('i', $user_id);
                    $checkUser->execute();
                    $checkUser->store_result(); 
                    
                    if ($checkUser->num_rows > 0) {
                        
                        // 2. Verify they aren't already enrolled
                        $checkEnrollment->bind_param('ii', $user_id, $course_id); 
                        $checkEnrollment->execute();
                        $checkEnrollment->store_result();
                        
                        if ($checkEnrollment->num_rows == 0) {
                            // 3. Enroll the student
                            $insertEnrollment->bind_param('ii', $user_id, $course_id);
                            $insertEnrollment->execute();
                            logAuditAction($mysqli, 'enroll_student', 'Student enrolled in a course', 'students', 'enrollment', $course_id . ':' . $user_id);
                            $successCount++;
                        } else {
                            $skippedCount++; // Already enrolled
                        }
                    } else {
                        $skippedCount++; // Student ID not found
                    }
                    
                    $checkUser->free_result();
                    $checkEnrollment->free_result();
                }

                $checkUser->close();
                $checkEnrollment->close();
                $insertEnrollment->close();

                $message = "
                    <div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong>Success!</strong> Successfully enrolled {$successCount} students. Skipped {$skippedCount} (already enrolled).
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true' style='color: #fff;'>&times;</span>
                        </button>
                    </div>";

            } catch (Exception $e) {
                $message = "<div class='alert alert-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

    // Fetch active courses for the dropdown menu
    $courses = [];
    $res_courses = '';
    if($role === 'admin') {$res_courses = $mysqli->query("SELECT c_id, c_code, c_name FROM courses WHERE status = 'active'");}
    elseif($role === 'instructor')
    {
        // Assuming instructors have a relation to courses they teach
        $stmt = $mysqli->prepare("SELECT c.c_id, c.c_code, c.c_name 
                                  FROM courses c 
                                  INNER JOIN users u ON u.user_id = c.i_id 
                                  WHERE c.status = 'active' && c.i_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res_courses = $stmt->get_result();
    }
    if ($res_courses) {
        while ($row = $res_courses->fetch_assoc()) { $courses[] = $row; }
        $res_courses->free();
    }

    // Fetch active students for the multiple select dropdown
    $students = [];
    $res_students = $mysqli->query("SELECT user_id, name FROM users WHERE role = 'student' AND status = 'active'");
    if ($res_students) {
        while ($row = $res_students->fetch_assoc()) { $students[] = $row; }
        $res_students->free();
    }

    // Fetch recent enrollments
    $recentEnrollments = [];

    $sql = "
    SELECT
        e.en_id,
        u.user_id,
        u.user_code,
        u.name AS student_name,
        c.c_name,
        e.status,
        e.en_date
    FROM enrollments e
    INNER JOIN users u ON u.user_id = e.s_id
    INNER JOIN courses c ON c.c_id = e.c_id
    ORDER BY e.en_id DESC
    LIMIT 20
    ";

    $result = $mysqli->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentEnrollments[] = $row;
        }
        $result->free();
    }
    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); 
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php'); ?>
            <body>
                <div class="wrapper">
                    <?php require_once('../partials/navbar.php'); ?>
                    <?php require_once('../partials/sidebar.php'); ?>
                    
                    <div class="content-wrapper">
                        <div class="container-fluid mt-5">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 col-xl-7">
                                    <?php if (!empty($message)) echo $message; ?>

                                    <div class="card card-body mb-4">
                                        <form action="" method="POST">
                                            
                                            <div class="form-group mb-3">
                                                <label for="course_id"><strong>1. Select Course</strong></label>
                                                <select name="course_id" id="course_id" class="form-control" required>
                                                    <option value="">-- Choose a Course --</option>
                                                    <?php foreach ($courses as $course): ?>
                                                        <option value="<?= htmlspecialchars($course['c_id']) ?>">
                                                            <?= htmlspecialchars($course['c_code'] . ' - ' . $course['c_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="student_ids"><strong>2. Select Students</strong></label>
                                                <!-- Notice the name="student_ids[]" with brackets and multiple attribute -->
                                                <select name="student_ids[]" id="student_ids" class="form-control select2" multiple="multiple" required style="width: 100%;">
                                                    <?php foreach ($students as $student): ?>
                                                        <option value="<?= htmlspecialchars($student['user_id']) ?>">
                                                            <?= htmlspecialchars($student['user_id'] . ' - ' . $student['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="form-text text-muted">Click and type to search. You can select as many students as you want.</small>
                                            </div>

                                            <button type="submit" name="bulk_enroll" class="btn btn-primary">Enroll Students</button>
                                        </form>
                                    </div>
                                    <hr>
                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                Recent Enrollments
                                            </h3>
                                        </div>

                                        <div class="card-body table-responsive p-0">

                                            <table class="table table-bordered table-hover table-striped">

                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Student ID</th>
                                                        <th>Student Name</th>
                                                        <th>Course</th>
                                                        <th>Status</th>
                                                        <th>Enrolled On</th>
                                                    </tr>
                                                </thead>

                                                <tbody>

                                                <?php if(count($recentEnrollments) > 0){ ?>

                                                    <?php $i=1; foreach($recentEnrollments as $row){ ?>

                                                    <tr>

                                                        <td><?= $i++; ?></td>

                                                        <td><?= htmlspecialchars($row['user_code']); ?></td>

                                                        <td><?= htmlspecialchars($row['student_name']); ?></td>

                                                        <td><?= htmlspecialchars($row['c_name']); ?></td>

                                                        <td>

                                                            <?php if($row['status']=="active"){ ?>

                                                                <span class="badge badge-success">Active</span>

                                                            <?php }else{ ?>

                                                                <span class="badge badge-secondary">
                                                                    <?= htmlspecialchars($row['status']); ?>
                                                                </span>

                                                            <?php } ?>

                                                        </td>

                                                        <td><?= date("d M Y h:i A", strtotime($row['en_date'])); ?></td>

                                                    </tr>

                                                    <?php } ?>

                                                <?php } else { ?>

                                                    <tr>

                                                        <td colspan="6" class="text-center">
                                                            No enrollments found.
                                                        </td>

                                                    </tr>

                                                <?php } ?>

                                                </tbody>

                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php require_once('../partials/scripts.php'); ?>
                
                <!-- Add Select2 JS and Initialize it -->
                <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
                <script>
                    $(document).ready(function() {
                        // Initialize Select2 on the student dropdown
                        $('.select2').select2({
                            placeholder: "Search and select students...",
                            allowClear: true
                        });
                    });
                </script>
            </body>
        </html>
<?php
    } 
?>
