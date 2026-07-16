<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin','instructor','student']);
    require_once('../config/codeGen.php');
    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];

    $u_id = isset($_GET['u_id']) ? trim($_GET['u_id']) : '';
    if ($u_id === '')
    {
        header("Location: materials.php");
        exit();
    }

    /* Delete Study Materials */
    if (isset($_GET['delete']) && ($role == 'instructor' || $role == 'admin'))
    {
        $id = intval($_GET['delete']);

        $adn = "UPDATE materials SET status = 'deleted' WHERE m_id = ?";
        $stmt = $mysqli->prepare($adn);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            logAuditAction($mysqli, 'delete_material', 'Material deleted', 'materials', 'material', (string) $id);
            $_SESSION['flash_success'] = "Material deleted successfully.";
            header("Location: manage_materials.php?u_id=" . urlencode($u_id));
            exit;
        } else {
            $_SESSION['flash_error'] = "Please Try Again Or Try Later";
            header("Location: manage_materials.php?u_id=" . urlencode($u_id));
            exit;
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php');
?>
        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>
                
                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Material Number</th>
                                                    <th>Material</th>
                                                    <th>Course</th>
                                                    <th>Unit</th>
                                                <?php
                                                    if($role === 'admin' || $role === 'student')
                                                    {
                                                ?>
                                                        <th>Instructor</th>
                                                <?php
                                                    }
                                                    if($role === 'student')
                                                    {
                                                ?>
                                                        <th>Action</th>
                                                <?php
                                                    }
                                                    else if($role === 'admin' || $role === 'instructor')
                                                    {
                                                ?>
                                                    <th>Actions</th>
                                                <?php
                                                    }
                                                ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $ret = "
                                                    SELECT 
                                                        m.m_id,
                                                        m.m_number,
                                                        m.m_name,
                                                        m.m_price,
                                                        m.m_title,
                                                        u.u_code,
                                                        u.u_name,
                                                        c.c_name,
                                                        usr.name AS i_name
                                                    FROM materials m
                                                    INNER JOIN units u ON m.u_id = u.u_id
                                                    INNER JOIN courses c ON u.c_id = c.c_id
                                                    INNER JOIN users usr ON c.i_id = usr.user_id
                                                    WHERE m.u_id = ? AND m.status <> 'deleted' AND u.status <> 'deleted' AND c.status <> 'deleted' AND usr.status = 'active'
                                                ";
                                                $stmt = $mysqli->prepare($ret);
                                                $stmt->bind_param("s", $u_id);
                                                $stmt->execute();   
                                                $res = $stmt->get_result();
                                                while ($materials = $res->fetch_object()) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $materials->m_number; ?></td>
                                                        <td><?php echo $materials->m_title; ?></td>
                                                        <td><?php echo $materials->c_name; ?></td>
                                                        <td><?php echo $materials->u_name; ?></td>
                                                        <td><?php echo $materials->i_name; ?></td>
                                                        <td>
                                                    <?php 
                                                        $fileExt = strtolower(pathinfo($materials->m_name, PATHINFO_EXTENSION));
                                                        if ($fileExt === 'docx') 
                                                        { 
                                                    ?>
                                                            <a class="badge badge-outline-warning" href="../public/sys_data/uploads/materials/<?php echo $materials->m_name; ?>">
                                                    <?php 
                                                        }
                                                        else if ($fileExt === 'pdf')
                                                        { 
                                                    ?>
                                                            <a class="badge badge-outline-warning" target="_blank" href="view_material.php?m_id=<?php echo $materials->m_id; ?>">
                                                    <?php 
                                                        } 
                                                    ?>
                                                                <i class="fas fa-file-download"></i>
                                                                View
                                                            </a>
                                                    <?php
                                                        if($role === 'admin' || $role === 'instructor')
                                                        {
                                                    ?>
                                                            <a class="badge badge-outline-warning" data-toggle="modal" href="#delete-<?php echo $materials->m_id; ?>">
                                                                <i class="fas fa-trash-alt"></i>
                                                                Delete
                                                            </a>
                                                            <!-- Delete Modal -->
                                                            <div class="modal fade" id="delete-<?php echo $materials->m_id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body text-center text-danger">
                                                                            <h4>Delete <?php echo $materials->m_number; ?>?</h4>
                                                                            <br>
                                                                            <button type="button" class="text-center btn btn-outline-warning" data-dismiss="modal">No</button>
                                                                            <a href="manage_materials.php?u_id=<?php echo $u_id; ?>&delete=<?php echo $materials->m_id; ?>" class="text-center btn btn-outline-warning">Delete</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- End Delete Modal -->
                                                    <?php
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
            <!-- Scripts -->
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php
    } 
?>