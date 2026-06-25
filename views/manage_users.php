<?php
session_start();

include('../config/config.php');
include('../config/checklogin.php');
userRoles(['admin']);

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

/* Approve User */
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $verify_user_id = (int) $_GET['approve'];
    $verified_by     = (int) $_SESSION['user_id'];

    $stmt = $mysqli->prepare("
        UPDATE users
        SET status = 'active',
            verified_at = NOW(),
            verified_by = ?
        WHERE user_id = ? AND status = 'pending'
    ");
    $stmt->bind_param('ii', $verified_by, $verify_user_id);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "User approved successfully.";
        header("Location: manage_users.php");
        exit;
    } else {
        $_SESSION['flash_error'] = "Unable to approve user.";
        header("Location: manage_users.php");
        exit;
    }
}

/* Reject / Deactivate User */
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $reject_user_id = (int) $_GET['reject'];

    $stmt = $mysqli->prepare("
        UPDATE users
        SET status = 'inactive',
            verified_at = NOW(),
            verified_by = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param('ii', $_SESSION['user_id'], $reject_user_id);

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "User rejected successfully.";
        header("Location: manage_users.php");
        exit;
    } else {
        $_SESSION['flash_error'] = "Unable to reject user.";
        header("Location: manage_users.php");
        exit;
    }

    $stmt->close();
}

/* Load system settings */
$ret = "SELECT * FROM system";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();

while ($sys = $res->fetch_object()) {
    require_once('../partials/head.php');
?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

    <?php require_once('../partials/navbar.php'); ?>
    <?php require_once('../partials/sidebar.php'); ?>

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dash-1" class="table table-striped table-bordered display no-wrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>User Code</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Approved Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $query = "
                                    SELECT
                                        u.user_id,
                                        u.user_code,
                                        u.name,
                                        u.email,
                                        u.phone,
                                        u.role,
                                        u.status,
                                        u.verified_at,
                                        u.verified_by
                                    FROM users u
                                    ORDER BY u.user_id DESC
                                ";

                                $result = $mysqli->query($query);

                                while ($row = $result->fetch_assoc()) {
                                    $statusBadge = 'badge-secondary';

                                    if ($row['status'] === 'pending') {
                                        $statusBadge = 'badge-warning text-white';
                                    } elseif ($row['status'] === 'active') {
                                        $statusBadge = 'badge-success';
                                    } elseif ($row['status'] === 'inactive') {
                                        $statusBadge = 'badge-danger';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_code']); ?></td>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $statusBadge; ?>">
                                                <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (empty($row['verified_by'])) { ?>
                                                -
                                            <?php } else { ?>
                                                <?php
                                                    $approvedByQuery = "SELECT name FROM users WHERE user_id = " . $row['verified_by'];
                                                    $approvedByResult = $mysqli->query($approvedByQuery);
                                                    $approvedByName = $approvedByResult->fetch_assoc()['name'];
                                                    echo htmlspecialchars($approvedByName);
                                                ?>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php
                                            echo !empty($row['verified_at'])
                                                ? date('d M Y H:i', strtotime($row['verified_at']))
                                                : '-';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'pending' || $row['status'] === 'inactive') { ?>

                                                <a href="?approve=<?php echo $row['user_id']; ?>"
                                                   class="badge badge-outline-success"
                                                   onclick="return confirm('Approve this user?');">
                                                    <i class="fas fa-user-check"></i>Activate
                                                </a>
                                            <?php } elseif ($row['status'] === 'active' || $row['status'] === 'pending') { ?>
                                                <a href="?reject=<?php echo $row['user_id']; ?>"
                                                   class="badge badge-outline-warning"
                                                   onclick="return confirm('Deactivate this user?');">
                                                   <i class="fas fa-user-slash"></i>
                                                    Deactivate
                                                </a>
                                            <?php } ?>
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

<!-- ./wrapper -->
<?php require_once('../partials/scripts.php'); ?>
</body>
</html>
<?php } ?>