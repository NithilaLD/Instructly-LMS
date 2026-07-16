<?php
    include('../config/config.php');
    require_once('../config/audit.php');
    include('../config/checklogin.php');
    userRoles(['admin', 'instructor', 'student']);
    require_once('../config/codeGen.php');

    $role = $_SESSION['role'];
    $user_id = $_SESSION['user_id'];
    $userSql = "SELECT user_id, user_code, name, email, phone
            FROM users
            WHERE user_id = ? AND role = 'student'";
    $userStmt = $mysqli->prepare($userSql);
    $userStmt->bind_param('s', $user_id);
    $userStmt->execute();
    $userRes = $userStmt->get_result();
    $loggedInUser = $userRes->fetch_object();
    if (isset($_POST['reject_verified_payment']))
    {
        $psm_id = (int) $_POST['psm_id'];
        $reason = trim($_POST['rejection_reason']);
        $rejected_by_id = $_SESSION['user_id']; // or $_SESSION['user_id'] if that is your admin id
        $query = "UPDATE payments
                SET p_status = 'rejected',
                    p_rejection_reason = ?,
                    p_rejected_date = NOW(),
                    rejected_by_id = ?
                WHERE psm_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sii", $reason, $rejected_by_id, $psm_id);
        if ($stmt->execute()) {
            logAuditAction($mysqli, 'reject_verified_payment', 'Payment rejected', 'billings', 'payment', (string) $psm_id);
            $_SESSION['flash_success'] = "Payment Rejected Successfully.";
        } else {
            $_SESSION['flash_error'] = "Error Rejecting Payment. Please Try Again.";
        }
    }

    /* Persist System Settings  */
    $ret = "SELECT * FROM system";
    $stmt = $mysqli->prepare($ret);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($sys = $res->fetch_object())
    {
        require_once('../partials/head.php'); ?>

        <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
            <div class="wrapper">
                <!-- Navbar -->
                <?php require_once('../partials/navbar.php'); ?>

                <!-- Main Sidebar Container -->
                <?php require_once('../partials/sidebar.php'); ?>

                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <!-- Main content -->
                    <section class="content pt-3">
                        <div class="container-fluid">
                            <div class="card">
                                <div class="col-md-12">
                                    <div class="card-body">
                                        <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                        <th>Course</th>
                                                        <th>Unit</th>
                                                        <th>Instructor</th>
                                                        <th>Material Code</th>
                                                        <th>Material</th>
                                                        <th>Amount</th>
                                                        <th>Payment Status</th>
                                                        <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                if ($role === 'admin') {
                                                $ret = "
                                                    SELECT
                                                        p.psm_id,
                                                        p.m_id,
                                                        p.p_method,
                                                        p.p_code,
                                                        p.p_amt,
                                                        p.p_date_paid,
                                                        p.email_flag,
                                                        p.order_id,
                                                        p.currency,
                                                        p.s_id,
                                                        p.p_status,
                                                        p.p_rejection_reason,
                                                        p.p_rejected_date,
                                                        p.rejected_by_id,
                                                        m.m_number AS sm_number,
                                                        m.m_name AS m_name,
                                                        m.m_price AS m_price,
                                                        u.u_code AS u_code,
                                                        u.u_name AS u_name,
                                                        c.c_name AS c_name,
                                                        s.user_code,
                                                        s.name AS s_name,
                                                        usr.name AS i_name
                                                    FROM payments p
                                                    INNER JOIN materials m ON p.m_id = m.m_id
                                                    INNER JOIN units u ON m.u_id = u.u_id
                                                    INNER JOIN courses c ON u.c_id = c.c_id
                                                    INNER JOIN users usr ON c.i_id = usr.user_id
                                                    INNER JOIN users s ON p.s_id = s.user_id
                                                    ORDER BY GREATEST(
                                                            COALESCE(m.created_at, '1970-01-01 00:00:00'),
                                                            COALESCE(p.p_date_paid, '1970-01-01 00:00:00')
                                                        ) DESC
                                                ";
                                                } elseif ($role === 'instructor') {
                                                $ret = "
                                                    SELECT
                                                        p.psm_id,
                                                        p.m_id,
                                                        p.p_method,
                                                        p.p_code,
                                                        p.p_amt,
                                                        p.p_date_paid,
                                                        p.email_flag,
                                                        p.order_id,
                                                        p.currency,
                                                        p.s_id,
                                                        p.p_status,
                                                        p.p_rejection_reason,
                                                        p.p_rejected_date,
                                                        p.rejected_by_id,
                                                        c.c_name AS c_name,
                                                        m.m_number AS sm_number,
                                                        m.m_name AS m_name,
                                                        m.m_price AS m_price,
                                                        u.u_code AS u_code,
                                                        u.u_name AS u_name,
                                                        usr.name AS i_name
                                                    FROM payments p
                                                    INNER JOIN materials m ON p.m_id = m.m_id
                                                    INNER JOIN units u ON m.u_id = u.u_id
                                                    INNER JOIN courses c ON u.c_id = c.c_id
                                                    INNER JOIN users usr ON c.i_id = usr.user_id
                                                    WHERE usr.user_id = ?
                                                    ORDER BY GREATEST(
                                                            COALESCE(m.created_at, '1970-01-01 00:00:00'),
                                                            COALESCE(p.p_date_paid, '1970-01-01 00:00:00')
                                                        ) DESC
                                                    ";
                                                }
                                                if ($role === 'student') {
                                                    $ret = "
                                                        SELECT DISTINCT
                                                            e.en_id,
                                                            e.en_date,
                                                            c.c_id,
                                                            c.c_code,
                                                            c.c_name,
                                                            u.u_id,
                                                            u.u_code,
                                                            u.u_name,
                                                            m.m_id,
                                                            m.m_number AS sm_number,
                                                            m.m_name AS m_name,
                                                            m.m_price,
                                                            p.psm_id,
                                                            p.p_status,
                                                            p.p_rejection_reason,
                                                            p.p_rejected_date,
                                                            usr.name AS i_name
                                                        FROM enrollments e
                                                        INNER JOIN courses c ON e.c_id = c.c_id
                                                        INNER JOIN units u ON u.c_id = c.c_id
                                                        INNER JOIN materials m ON m.u_id = u.u_id
                                                        INNER JOIN users usr ON c.i_id = usr.user_id
                                                        LEFT JOIN (
                                                            SELECT p1.*
                                                            FROM payments p1
                                                            INNER JOIN (
                                                                SELECT m_id, s_id, MAX(psm_id) AS max_psm_id
                                                                FROM payments
                                                                GROUP BY m_id, s_id
                                                            ) latest ON latest.max_psm_id = p1.psm_id
                                                        ) p ON p.m_id = m.m_id AND p.s_id = e.s_id
                                                        WHERE e.s_id = ?
                                                        ORDER BY GREATEST(
                                                            COALESCE(m.created_at, '1970-01-01 00:00:00'),
                                                            COALESCE(p.p_date_paid, '1970-01-01 00:00:00')
                                                        ) DESC
                                                    ";
                                                }
                                                $stmt = $mysqli->prepare($ret);
                                                if($role === 'instructor' || $role === 'student') {$stmt->bind_param('i', $user_id);}
                                                $stmt->execute();
                                                $res = $stmt->get_result();
                                                while ($pdetails = $res->fetch_object())
                                                { 
                                            ?>
                                                    <tr>
                                                        <td><?php echo $pdetails->c_name; ?></td>
                                                        <td><?php echo $pdetails->u_name; ?></td>
                                                        <td><?php echo $pdetails->i_name; ?></td>
                                                        <td><?php echo $pdetails->sm_number; ?></td>
                                                        <td><?php echo $pdetails->m_name; ?></td>
                                                        <td>Rs. <?php echo $pdetails->m_price; ?></td>
                                                        <?php
                                                            $verification_status = 'pending';
                                                            $status_html = '<span class="badge badge-warning" style="background-color: #a79628 !important;color: #fff !important;">PAYMENT PENDING</span>';
                                                            if (!empty($pdetails->psm_id)){$verification_status = strtolower(trim($pdetails->p_status));
                                                                if ($verification_status === 'verified'){$status_html = '<span class="badge badge-success">PAYMENT VERIFIED</span>';} 
                                                                else if ($verification_status === 'rejected'){$status_html = '<span class="badge badge-danger">PAYMENT REJECTED</span>';}
                                                                else if ($verification_status === 'pending') {$status_html = '<span class="badge badge-warning">PAYMENT PENDING</span>';}
                                                            }
                                                        ?>
                                                            <td><?php echo $status_html; ?></td>
                                                        <?php
                                                            if($role ==="admin" || $role ==="instructor")
                                                            {
                                                                if($verification_status === 'rejected' || $verification_status === 'verified')
                                                                {
                                                        ?>
                                                                    <td>
                                                                        <a class='badge badge-outline-warning' href='view_payment.php?view=<?php echo $pdetails->psm_id; ?>'>
                                                                            <i class='fas fa-eye'></i>
                                                                            View
                                                                        </a>
                                                        <?php   
                                                                    if ($verification_status === 'verified' && $role === 'admin' )
                                                                    {
                                                                        
                                                        ?>
                                                                        <a class="badge badge-outline-danger" data-toggle="modal" href="#reject-<?php echo $pdetails->psm_id; ?>">
                                                                            <i class="fas fa-times"></i>
                                                                            Reject
                                                                        </a>
                                                                        <div class="modal fade" id="reject-<?php echo $pdetails->psm_id; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                                <div class="modal-content">
                                                                                    <form method="POST">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title">Reject Verified Payment</h5>
                                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                                        </div>
                                                                                        <div class="modal-body">
                                                                                            <p><strong>Student:</strong> <?php echo htmlspecialchars($pdetails->s_name ?? ''); ?></p>
                                                                                            <p><strong>Material:</strong> <?php echo htmlspecialchars($pdetails->m_name); ?></p>
                                                                                            <p><strong>Payment Code:</strong> <?php echo htmlspecialchars($pdetails->p_code); ?></p>
                                                                                            <div class="form-group">
                                                                                                <label>Reason for rejection</label>
                                                                                                <textarea name="rejection_reason" class="form-control" required></textarea>
                                                                                            </div>
                                                                                            <input type="hidden" name="psm_id" value="<?php echo $pdetails->psm_id; ?>">
                                                                                        </div>
                                                                                        <div class="modal-footer">
                                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                                            <button type="submit" name="reject_verified_payment" class="btn btn-danger">Reject Payment</button>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                        <?php 
                                                                    } 
                                                        ?>
                                                                    </td> 
                                                        <?php
                                                                }  
                                                            }
                                                            elseif($role ==="student")
                                                            {
                                                                if($verification_status === 'rejected' || $verification_status === 'pending')
                                                                {
                                                        ?> 
                                                                <td>
                                                                    <a class="badge badge-outline-warning"
                                                                        data-toggle="modal"
                                                                        href="#pay-<?php echo htmlspecialchars($pdetails->m_id); ?>">
                                                                            <i class="fas fa-external-link-alt"></i>
                                                        <?php
                                                                if($verification_status === 'rejected')
                                                                {
                                                                    echo "Repay";
                                                                }
                                                                else if($verification_status === 'pending')
                                                                {
                                                                    echo "Pay";
                                                                }
                                                        ?>
                                                                        </a>
                                                                    <div class="modal fade" id="pay-<?php echo htmlspecialchars($pdetails->m_id); ?>">
                                                                        <div class="modal-dialog modal-lg">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header text-center">
                                                                                    <h4 class="modal-title">
                                                                                        Pay for <?php echo htmlspecialchars($pdetails->c_name); ?> - <?php echo htmlspecialchars($pdetails->u_name); ?>
                                                                                        (Material : <?php echo htmlspecialchars($pdetails->m_name); ?>)
                                                                                    </h4>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <form action="../payment/checkout.php" method="post" enctype="multipart/form-data">
                                                                                        <div class="row">
                                                                                            <div class="form-group col-md-6">
                                                                                                <label>Course</label>
                                                                                                <input type="text" name="c_name" class="form-control" value="<?php echo htmlspecialchars($pdetails->c_name); ?>" readonly>
                                                                                            </div>

                                                                                            <div class="form-group col-md-6">
                                                                                                <label>Unit</label>
                                                                                                <input type="text" name="u_name" class="form-control" value="<?php echo htmlspecialchars($pdetails->u_name); ?>" readonly>
                                                                                            </div>

                                                                                            <div class="form-group col-md-6">
                                                                                                <label>Material</label>
                                                                                                <input type="text" name="m_name" class="form-control" value="<?php echo htmlspecialchars($pdetails->m_name); ?>" readonly>
                                                                                                <input type="hidden" name="m_id" value="<?php echo htmlspecialchars($pdetails->m_id); ?>">
                                                                                            </div>

                                                                                            <div class="form-group col-md-6">
                                                                                                <label>Amount</label>
                                                                                                <input type="text" name="m_price" class="form-control" value="<?php echo htmlspecialchars("Rs.".$pdetails->m_price); ?>" readonly>
                                                                                            </div>
                                                        <?php
                                                                if($verification_status === 'rejected')
                                                                {
                                                        ?>              <div class="form-group col-md-6">
                                                                            <label>Rejection Reason</label>
                                                                            <input type="text" name="p_rejection_reason" class="form-control" value="<?php echo htmlspecialchars($pdetails->p_rejection_reason); ?>" readonly>
                                                                        </div>
                                                                        <div class="form-group col-md-6">
                                                                            <label>Rejected Date</label>
                                                                            <input type="text" name="p_rejected_date" class="form-control" value="<?php echo htmlspecialchars($pdetails->p_rejected_date); ?>" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <button type="submit" name="pay_for_reading_material" class="btn btn-outline-warning">
                                                                            Repay
                                                                        </button>
                                                                    </div>
                                                        <?php
                                                                    
                                                                }
                                                                else if($verification_status === 'pending')
                                                                {
                                                        ?>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <button type="submit" name="pay_for_reading_material" class="btn btn-outline-warning">
                                                                            Pay
                                                                        </button>
                                                                    </div>
                                                        <?php
                                                                }
                                                        ?>
                                                                                    </form>
                                                                                </div>

                                                                                <div class="modal-footer justify-content-between">
                                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                        <?php
                                                                }
                                                                else if($verification_status === 'verified')
                                                                {
                                                        ?>
                                                                    <td>
                                                                        <a class='badge badge-outline-warning' href='view_payment.php?view=<?php echo $pdetails->psm_id; ?>'>
                                                                            <i class='fas fa-eye'></i>
                                                                                View
                                                                        </a>
                                                                    </td>
                                                        <?php
                                                                }
                                                            }
                                                        ?>
                                                        
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
    } 
?>