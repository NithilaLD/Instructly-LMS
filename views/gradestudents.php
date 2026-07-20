<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../vendor/autoload.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $message = '';

    if (isset($_POST['bulk_add_marks'])) {
        $course_id = $_POST['course_id'];
        $unit_id = $_POST['unit_id'];
        $raw_marks = $_POST['marks_data'];
        
        if (empty($course_id) || empty($unit_id)) {
            $message = "<div class='alert alert-warning'>Please select both a Course and a Unit.</div>";
        } elseif (empty(trim($raw_marks))) {
            $message = "<div class='alert alert-warning'>Please enter marks.</div>";
        } else {
            try {
                $marks_array = preg_split('/\r\n|\r|\n/', trim($raw_marks), -1, PREG_SPLIT_NO_EMPTY);
                $successCount = 0; $skippedCount = 0;
                
                $checkUser = $mysqli->prepare("SELECT user_id FROM users WHERE user_code = ? AND role = 'student'");
                // Updated query to include ca1, ca2, and exam fields[cite: 5]
                $insertMark = $mysqli->prepare("INSERT INTO marks (r_code, u_id, s_id, u_cat1_marks, u_cat2_marks, u_eos_marks) VALUES (?, ?, ?, ?, ?, ?)");

                foreach ($marks_array as $line) {
                    $parts = explode(',', trim($line));
                    // Expecting: StudentCode, CA1, CA2, Exam
                    if(count($parts) == 4){ 
                        $student_code = trim($parts[0]);
                        $ca1 = (float)trim($parts[1]);
                        $ca2 = (float)trim($parts[2]);
                        $exam = (float)trim($parts[3]);

                        $checkUser->bind_param('s', $student_code);
                        $checkUser->execute();
                        $res = $checkUser->get_result();
                        if ($row = $res->fetch_assoc()) {
                            $student_id = $row['user_id'];
                            $rcode = 'RS' . substr(md5(uniqid(rand(), true)), 0, 13);
                            // 'iisddd' format matches: rcode, u_id, s_id, ca1, ca2, exam[cite: 5]
                            $insertMark->bind_param('siiddd', $rcode, $unit_id, $student_id, $ca1, $ca2, $exam);
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
                $insertMark->close();
                $message = "<div class='alert alert-success'>Successfully uploaded {$successCount} records. Skipped {$skippedCount} invalid entries.</div>";
            } catch (Exception $e) {
                $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

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
                                    <label><strong>Select Course</strong></label>
                                    <select name="course_id" id="course_id" class="form-control" required>
                                        <option value="">-- Choose a Course --</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= htmlspecialchars($course['c_id']) ?>"><?= htmlspecialchars($course['c_code'] . ' - ' . $course['c_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label><strong>Select Unit</strong></label>
                                    <select name="unit_id" id="unit_id" class="form-control" required disabled>
                                        <option value="">-- Select Course First --</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label><strong>Insert Marks (StudentCode, CA1(Out Of 30), CA2(Out Of 30), Exam(Out Of 100))</strong></label>
                                    <textarea name="marks_data" class="form-control" rows="8" placeholder="STD001, 20, 25, 80&#10;STD002, 15, 20, 75" required></textarea>
                                </div>
                                <button type="submit" name="bulk_add_marks" class="btn btn-primary">Grade</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once('../partials/scripts.php'); ?>
    <script>
    $(document).ready(function(){
        $('#course_id').change(function(){
            var c_id = $(this).val();
            if(c_id){
                $.ajax({
                    url: '../partials/ajax.php',
                    method: 'POST',
                    data: {course_id: c_id},
                    success: function(data){
                        $('#unit_id').html(data);
                        $('#unit_id').prop('disabled', false);
                    }
                });
            } else {
                $('#unit_id').html('<option value="">-- Select Course First --</option>');
                $('#unit_id').prop('disabled', true);
            }
        });
    });
    </script>
</body>
</html>