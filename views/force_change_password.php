<?php
session_start();
require_once('../config/config.php');
include('../config/checklogin.php');

if (!isset($_SESSION['must_change_password']) || (int)$_SESSION['must_change_password'] !== 1) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['change_password'])) {
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($new_password === '' || $confirm_password === '') {
        $err = "All fields are required";
    } elseif ($new_password !== $confirm_password) {
        $err = "Passwords do not match";
    } else {
        $hashed = sha1(md5($new_password));
        $user_id = (int)$_SESSION['user_id'];

        $stmt = $mysqli->prepare("
            UPDATE users
            SET password = ?,
                must_change_password = 0,
                password_changed_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->bind_param('si', $hashed, $user_id);

        if ($stmt->execute()) {
            $_SESSION['must_change_password'] = 0;
            header("Location: dashboard.php?changed=1");
            exit();
        } else {
            $err = "Failed to update password";
        }
        $stmt->close();
    }
}

$ret = "SELECT * FROM system";
$stmt = $mysqli->prepare($ret);
$stmt->execute();
$res = $stmt->get_result();

while ($sys = $res->fetch_object()) {
    require_once('../partials/head.php');
?>
<body class="hold-transition login-page login-bg">
    <div class="login-box" style="width: 500px !important;">
        <div class="card card-success" style="padding: 1rem !important; margin: 0 !important;">
            <div class="login-logo">
                <a href="../index.php">
                    <img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" class="img-fluid" height="50" width="100">
                    <br>
                    <?php echo $sys->sys_name; ?>
                </a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">
                    <b>Change Your Password</b><br>
                    You must change your temporary password before using the system.
                </p>

                <?php if (isset($err)) { ?>
                    <div class="alert alert-danger"><?php echo $err; ?></div>
                <?php } ?>

                <form method="post">
                    <div class="input-group mb-3">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary btn-block">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php require_once('../partials/scripts.php'); ?>
</body>
</html>
<?php } ?>