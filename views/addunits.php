<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor']);
    require_once('../vendor/autoload.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $message = '';

    if (isset($_POST['bulk_add_units'])) {
        $course_id = $_POST['course_id'];
        $raw_units = $_POST['units_data'];
        
        if (empty($course_id)) {
            $message = "<div class='alert alert-warning alert-dismissible fade show'>Please select a course.<button type='button' class='close' data-dismiss='alert'><span>&times;</span></button></div>";
        } elseif (empty(trim($raw_units))) {
            $message = "<div class='alert alert-warning alert-dismissible fade show'>Please enter unit data.<button type='button' class='close' data-dismiss='alert'><span>&times;</span></button></div>";
        } else {
            try {
                // Split by new line
                $lines = preg_split('/\r\n|\r|\n/', trim($raw_units), -1, PREG_SPLIT_NO_EMPTY);
                $successCount = 0;
                $errorCount = 0;
                
                // Adjusted SQL: Ensure your table has a_id, u_code and u_desc columns
                $insertUnit = $mysqli->prepare("INSERT INTO units (c_id, a_id, u_code, u_name, u_desc) VALUES (?, ?, ?, ?, ?)");

                foreach ($lines as $line) {
                    // Split line by comma
                    $parts = explode(',', $line);
                    
                    // Check if we have exactly 3 parts (Code, Name, Description)
                    if (count($parts) >= 3) {
                        $u_code = trim($parts[0]);
                        $u_name = trim($parts[1]);
                        $u_desc = trim($parts[2]);

                        if(!empty($u_code) && !empty($u_name)){
                            $insertUnit->bind_param('iisss', $course_id, $user_id, $u_code, $u_name, $u_desc);
                            $insertUnit->execute();
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    } else {
                        $errorCount++;
                    }
                }
                $insertUnit->close();
                $message = "<div class='alert alert-success alert-dismissible fade show'><strong>Success!</strong> Created {$successCount} units. Skipped {$errorCount} invalid rows.<button type='button' class='close' data-dismiss='alert'><span>&times;</span></button></div>";
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

    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); 
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object()) {
        require_once('../partials/head.php'); 
?>
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
                                            <label><strong>1. Select Course</strong></label>
                                            <select name="course_id" class="form-control" required>
                                                <option value="">-- Choose a Course --</option>
                                                <?php foreach ($courses as $course): ?>
                                                    <option value="<?= htmlspecialchars($course['c_id']) ?>"><?= htmlspecialchars($course['c_code'] . ' - ' . $course['c_name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label><strong>2. Paste Unit Details</strong></label>
                                            <textarea name="units_data" class="form-control" rows="8" placeholder="Unit 01, Introduction to Math, Basic algebra concepts&#10;Unit 02, Calculus I, Limits and derivatives" required></textarea>
                                            <small class="text-muted">Format: <b>Unit Code, Name, Description</b>. One unit per line. Do not use commas inside your description and put a brief short sentence/line as description..</small>
                                        </div>
                                        <button type="submit" name="bulk_add_units" class="btn btn-primary">Create Units</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php } ?>