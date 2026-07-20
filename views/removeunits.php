<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin','instructor']);
    require_once('../vendor/autoload.php');
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $message = '';

    if(isset($_POST['bulk_delete_units'])){
        $course_id=(int)($_POST['course_id']??0);
        $units=$_POST['unit_ids']??[];

        if($course_id<=0 || empty($units)){
            $message="<div class='alert alert-warning alert-dismissible fade show'>Select a course and at least one unit.<button type='button' class='close' data-dismiss='alert'><span>&times;</span></button></div>";
        }else{
            $del=$mysqli->prepare("DELETE FROM units WHERE u_id=? AND c_id=?");
            $ok=0;$skip=0;
            foreach($units as $uid){
                $uid=(int)$uid;
                $del->bind_param("ii",$uid,$course_id);
                $del->execute();
                if($del->affected_rows>0){$ok++;}else{$skip++;}
            }
            $del->close();
            $message="<div class='alert alert-success alert-dismissible fade show'><strong>Success!</strong> Deleted {$ok} units. Skipped {$skip}.<button type='button' class='close' data-dismiss='alert'><span>&times;</span></button></div>";
        }
    }

    $courses=[];
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
    $units=[];
    if(isset($_GET['course_id']) && is_numeric($_GET['course_id'])){
        $cid=(int)$_GET['course_id'];
        $st=$mysqli->prepare("SELECT u_id, u_name FROM units WHERE c_id=?");
        $st->bind_param("i",$cid);
        $st->execute();
        $res=$st->get_result();
        while($row=$res->fetch_assoc()) $units[]=$row;
        $st->close();
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
                    <section class="content pt-3">
                        <div class="container-fluid">
                            <?= $message ?>
                            <div class="card">
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Course</label>
                                            <select class="form-control" name="course_id" onchange="location='?course_id='+this.value;">
                                                <option value="">Select Course</option>
                                                <?php foreach($courses as $c): ?>
                                                    <option value="<?=$c['c_id']?>" <?=isset($_GET['course_id'])&&$_GET['course_id']==$c['c_id']?'selected':''?>><?=htmlspecialchars($c['c_code'].' - '.$c['c_name'])?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Units</label>
                                            <select class="form-control select2" multiple name="unit_ids[]" style="width:100%">
                                                <?php foreach($units as $u): ?>
                                                    <option value="<?=$u['u_id']?>"><?=htmlspecialchars($u['u_name'])?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                        <button class="btn btn-danger" name="bulk_delete_units"><i class="fas fa-trash"></i> Delete Units</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <?php require_once('../partials/scripts.php'); ?>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>$('.select2').select2({placeholder:'Select units'});</script>
        </body>
    </html>
<?php } ?>