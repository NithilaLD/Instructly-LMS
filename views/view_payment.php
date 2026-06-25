<?php
    include('../config/config.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    require_once('../partials/analytics.php');

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute(); //ok
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php'); 
?>
        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <?php
                    require_once('../partials/navbar.php'); 
                    require_once('../partials/sidebar.php');
                    $view = (int)$_GET['view'];
                    $ret = "
                        SELECT
                            p.psm_id,
                            p.m_id,
                            p.p_method,
                            p.p_code,
                            p.p_amt,
                            p.p_date_paid,
                            p.s_id,
                            p.p_status,
                            p.p_rejection_reason,
                            p.rejected_by_id,
                            p.p_rejected_date,
                            m.m_number AS sm_number,
                            m.m_name,
                            m.m_price,
                            u.u_code,
                            u.u_name,
                            c.c_id,
                            c.c_code,
                            c.c_name,
                            c.c_dpic,
                            s.user_code,
                            s.name
                        FROM payments p
                        INNER JOIN materials m ON p.m_id = m.m_id
                        INNER JOIN units u ON m.u_id = u.u_id
                        INNER JOIN courses c ON u.c_id = c.c_id
                        INNER JOIN users s ON p.s_id = s.user_id
                        WHERE p.psm_id = ?
                    ";
                    $stmt = $mysqli->prepare($ret);
                    $stmt->bind_param("i", $view);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($payment = $res->fetch_object())
                    {
                        $course_id = $payment->c_id;
                ?>
                            <div class="content-wrapper" style="margin-bottom: 0 !important;"><br>
                                <section class="content">
                                    <div class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-warning card-outline" id="Print">
                                                    <div class="card-header p-2">
                                                        <h3 class="text-center">
                                                            Payment Record
                                                        </h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="tab-content">
                                                            <div class="active tab-pane">
                                                                <div class="" >
                                                                    <div class="col-md-12">
                                                                        <div class="row">
                                                                            <div class="col-md-6">
                                                                                <!-- Course Details -->
                                                                                <div class="card card-warning card-outline">
                                                                                    <div class="card-body box-profile">
                                                                                        <div class="text-center">
                                                                                            <?php
                                                                                                if ($payment->c_dpic == '') {$logo = 'Default.png';}
                                                                                                else {$logo = $payment->c_dpic;} 
                                                                                            ?>
                                                                                            <img class="img-fluid  img-rectangle" src="../public/sys_data/uploads/courses/<?php echo $logo; ?>" alt="Course Logo">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <!-- Course Details -->
                                                                                <div class="card card-warning card-outline">
                                                                                    <div class="card-body box-profile">
                                                                                        <ul class="list-group list-group-unbordered mb-3">
                                                                                            <li class="list-group-item">
                                                                                                <b>Course Code</b> <a class="float-right"> <?php echo $payment->c_code; ?></a>
                                                                                            </li>
                                                                                            <li class="list-group-item">
                                                                                                <b>Course Name </b> <a class="float-right"> <?php echo $payment->c_name; ?></a>
                                                                                            </li>
                                                                                            <li class="list-group-item">
                                                                                                <b>Unit Code </b> <a class="float-right"> <?php echo $payment->u_code; ?></a>
                                                                                            </li>
                                                                                            <li class="list-group-item">
                                                                                                <b>Unit Name </b> <a class="float-right"> <?php echo $payment->u_name; ?></a>
                                                                                            </li>
                                                                                        </ul>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <table class="table table-striped table-bordered display no-wrap" style="width:100%">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Material</th>
                                                                                    <th>Payment Method</th>
                                                                                    <th>Payment Code</th>
                                                                                    <th>Amount Paid</th>
                                                                                    <th>Date Paid</th>
                                                                                    <th>Student</th>
                                                                                    <th>Payment Status</th>
                                                                    <?php
                                                                        $verification_status = 'pending';
                                                                        $status_text = 'PAYMENT PENDING';
                                                                        $status_html = '<span class="badge badge-warning text-white payment-status">'. $status_text .'</span>';
                                                                        if (!empty($payment->psm_id)){$verification_status = strtolower(trim($payment->p_status));
                                                                            if ($verification_status === 'verified'){$status_text = 'PAYMENT VERIFIED';$status_html = '<span class="badge badge-success payment-status">'. $status_text .'</span>';} 
                                                                            else if ($verification_status === 'rejected'){$status_text = 'PAYMENT REJECTED';$status_html = '<span class="badge badge-danger payment-status">'. $status_text .'</span>';}
                                                                            else if ($verification_status === 'pending') {$status_text = 'PAYMENT PENDING';$status_html = '<span class="badge badge-warning payment-status">'. $status_text .'</span>';}
                                                                        }
                                                                        
                                                                            if($verification_status === 'rejected')
                                                                            {
                                                                    ?>              <th>Rejection Reason</th>
                                                                                    <th>Rejected Date</th>
                                                                    <?php   
                                                                            }
                                                                                  
                                                                    ?>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>
                                                                                        <a class="material-link" href="../public/sys_data/uploads/materials/<?php echo $payment->m_name; ?>" target="_blank">
                                                                                            <?php echo $payment->m_name; ?>
                                                                                        </a>
                                                                                    </td>
                                                                                    <td><?php echo $payment->p_method; ?></td>
                                                                                    <td><?php echo $payment->p_code; ?></td>
                                                                                    <td>Rs. <?php echo $payment->p_amt; ?></td>
                                                                                    <td><?php echo date("d M Y g:ia", strtotime($payment->p_date_paid)); ?></td>
                                                                                    <td><?php echo $payment->user_code; ?> - <?php echo $payment->name; ?></td>
                                                                                    <td><?php echo $status_html; ?></td>
                                                                <?php
                                                                            if($verification_status === 'rejected')
                                                                            {
                                                                    ?>              <td><?php echo htmlspecialchars($payment->p_rejection_reason); ?></td>
                                                                                    <td><?php echo htmlspecialchars($payment->p_rejected_date); ?></td>
                                                                    <?php
                                                                                
                                                                            }
                                                                    ?>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="text-align: right; padding: 20px 0;">
                                            <button id="print" onclick="printContent('Print');" type="button" class="btn btn-outline-warning">
                                                <i class="fas fa-print"></i>
                                                Print
                                            </button>
                                        </div>
                                    </div>
                                </section>
                            </div>
                <?php 
                    } 
                ?>
            </div>
            <?php require_once('../partials/scripts.php'); ?>
        </body>
    </html>
<?php
    } 
?>