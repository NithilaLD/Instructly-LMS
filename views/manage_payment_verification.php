<?php
session_start();
include('../config/config.php');
include('../config/checklogin.php');
admin();
require_once('../config/codeGen.php');

/* Handle Verification Actions */
if (isset($_POST['verify_payment'])) {
    $psm_id = mysqli_real_escape_string($mysqli, $_POST['psm_id']);
    $verified_by_id = $_SESSION['ad_id'];
    
    $query = "UPDATE lms_paid_study_materials 
              SET p_verification_status = 'verified', p_verified_date = NOW(), verified_by_id = ? 
              WHERE psm_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $verified_by_id, $psm_id);
    
    if ($stmt->execute()) {
        // Get the material ID to update study material status
        $get_query = "SELECT ls_id FROM lms_paid_study_materials WHERE psm_id = ?";
        $get_stmt = $mysqli->prepare($get_query);
        $get_stmt->bind_param("i", $psm_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $payment = $result->fetch_assoc();
        
        // Update study material payment status
        if ($payment && isset($payment['ls_id'])) {
            $update_query = "UPDATE lms_study_material SET payment_status = 'Paid' WHERE ls_id = ?";
            $update_stmt = $mysqli->prepare($update_query);
            $update_stmt->bind_param("i", $payment['ls_id']);
            $update_stmt->execute();
        }
        
        $success = "Payment verified! Student now has access to the material.";
    } else {
        $err = "Error verifying payment. Please try again.";
    }
}

if (isset($_POST['reject_payment'])) {
    $psm_id = mysqli_real_escape_string($mysqli, $_POST['psm_id']);
    $rejection_reason = mysqli_real_escape_string($mysqli, $_POST['rejection_reason']);
    $verified_by_id = $_SESSION['ad_id'];
    
    // Mark the payment record as rejected
    $query = "UPDATE lms_paid_study_materials 
              SET p_verification_status = 'rejected', p_rejection_reason = ?, p_verified_date = NOW(), verified_by_id = ? 
              WHERE psm_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("sii", $rejection_reason, $verified_by_id, $psm_id);
    
    if ($stmt->execute()) {
        // Ensure the study material is set back to 'unpaid' so the student can try again
        $get_query = "SELECT ls_id FROM lms_paid_study_materials WHERE psm_id = ?";
        $get_stmt = $mysqli->prepare($get_query);
        $get_stmt->bind_param("i", $psm_id);
        $get_stmt->execute();
        $result = $get_stmt->get_result();
        $payment = $result->fetch_assoc();
        
        if ($payment && isset($payment['ls_id'])) {
            $update_sm = "UPDATE lms_study_material SET payment_status = 'Unpaid' WHERE ls_id = ?";
            $update_sm_stmt = $mysqli->prepare($update_sm);
            $update_sm_stmt->bind_param('i', $payment['ls_id']);
            $update_sm_stmt->execute();
        }
        
        $success = "Payment rejected. Student can resubmit payment.";
    } else {
        $err = "Error rejecting payment. Please try again.";
    }
}

/* Persist System Settings */
$ret = "SELECT * FROM `lms_sys_setttings`";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();
while ($sys = $res->fetch_object()) {
    require_once('../partials/head.php');
?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <?php require_once('../partials/navbar.php'); ?>
        <!-- Main Sidebar Container -->
        <?php require_once('../partials/sidebar.php'); ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper"><br>
            <section class="content">
                <div class="container-fluid">

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php if (isset($err)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $err; ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Pending Payment Verifications</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                        $table_id = "reports2";

                                        $ret = "SELECT * FROM lms_paid_study_materials 
                                                WHERE p_verification_status = 'pending'
                                                ORDER BY p_date_paid DESC";

                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        if ($res->num_rows == 0) {
                                            $table_id = "emptyReports";
                                        }
                                        ?>
                                    <table id="<?php echo $table_id; ?>" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Student RegNo</th>
                                                <th>Unit</th>
                                                <th>Instructor</th>
                                                <th>Payment Method</th>
                                                <th>Payment Code</th>
                                                <th>Amount</th>
                                                <th>Date Submitted</th>
                                                <th class="no-export">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $ret = "SELECT * FROM lms_paid_study_materials 
                                                    WHERE p_verification_status = 'pending'
                                                    ORDER BY p_date_paid DESC";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            
                                            if ($res->num_rows > 0) {
                                                while ($payment = $res->fetch_object()) {
                                                    $status_badge = ($payment->p_verification_status == 'pending') 
                                                        ? '<span class="badge badge-warning text-white">PENDING</span>'
                                                        : '<span class="badge badge-danger">REJECTED</span>';
                                            ?>
                                                <tr>
                                                    <td><?php echo $payment->s_name; ?></td>
                                                    <td><?php echo $payment->s_regno; ?></td>
                                                    <td><?php echo $payment->c_code." - ".$payment->c_name; ?></td>
                                                    <td><?php echo $payment->i_name; ?></td>
                                                    <td><?php echo $payment->p_method; ?></td>
                                                    <td><?php echo $payment->p_code; ?></td>
                                                    <td>Rs. <?php echo $payment->p_amt; ?></td>
                                                    <td><?php echo date('d M Y H:i', strtotime($payment->p_date_paid)); ?></td>
                                                    <td>
                                                            <!-- Verify Button -->
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="psm_id" value="<?php echo $payment->psm_id; ?>" id="psm_id">
                                                                <button type="submit" name="verify_payment" class="btn btn-sm btn-success" 
                                                                        onclick="return confirm('Verify this payment and grant student access?')">
                                                                    <i class="fas fa-check"></i> Verify
                                                                </button>
                                                            </form>
                                                            
                                                            <!-- Reject Button -->
                                                            <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" 
                                                                    data-target="#rejectModal<?php echo $payment->psm_id; ?>">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>

                                                        <!-- Reject Modal -->
                                                        <div class="modal fade" id="rejectModal<?php echo $payment->psm_id; ?>" tabindex="-1" role="dialog">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <form method="POST">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Reject Payment</h5>
                                                                            <button type="button" class="close" data-dismiss="modal">
                                                                                <span>&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <p><strong>Payment Code:</strong> <?php echo $payment->p_code; ?></p>
                                                                            <p><strong>Student:</strong> <?php echo $payment->s_name; ?> (<?php echo $payment->s_regno; ?>)</p>
                                                                            
                                                                            <div class="form-group">
                                                                                <label for="rejection_reason">Reason for Rejection:</label>
                                                                                <textarea name="rejection_reason" class="form-control" required placeholder="e.g., Payment code not found, invalid transaction, duplicate payment" id="rejection_reason" autocomplete="on"></textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                            <input type="hidden" name="psm_id" value="<?php echo $payment->psm_id; ?>" id="psm_id">
                                                                            <button type="submit" name="reject_payment" class="btn btn-danger">Reject & Notify Student</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php
                                                }
                                            } else {
                                                echo '<tr><td colspan="11" class="text-center text-muted">No pending verifications</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verified Payments Section -->
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Verification History (Last 30 Days)</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                        $table_id = "reports3";

                                        $ret = "SELECT * FROM lms_paid_study_materials 
                                                WHERE p_verification_status != 'pending'
                                                ORDER BY p_date_paid DESC";

                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->execute();
                                        $res = $stmt->get_result();

                                        if ($res->num_rows == 0) {
                                            $table_id = "emptyReports";
                                        }
                                        ?>
                                    <table id="<?php echo $table_id; ?>" class="table table-sm table-striped table-bordered display no-wrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Instructor</th>
                                                <th>Payment Code</th>
                                                <th>Status</th>
                                                <th>Verified Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $ret = "SELECT * FROM lms_paid_study_materials 
                                                    WHERE p_verification_status IN ('verified', 'rejected')
                                                    AND p_verified_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                                    ORDER BY p_verified_date DESC LIMIT 50";
                                            $stmt = $mysqli->prepare($ret);
                                            $stmt->execute();
                                            $res = $stmt->get_result();
                                            
                                            while ($payment = $res->fetch_object()) {
                                                $status_badge = ($payment->p_verification_status == 'verified') 
                                                    ? '<span class="badge badge-success">VERIFIED</span>'
                                                    : '<span class="badge badge-danger">REJECTED</span>';
                                            ?>
                                                <tr>
                                                    <td><?php echo $payment->s_name; ?> (<?php echo $payment->s_regno; ?>)</td>
                                                    <td><?php echo $payment->i_name; ?></td>
                                                    <td><?php echo $payment->p_code; ?></td>
                                                    <td><?php echo $status_badge; ?></td>
                                                    <td><?php echo date('d M Y H:i', strtotime($payment->p_verified_date)); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
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
