<?php
require_once('../config/config.php');

if (isset($_POST['Reset'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $err = "Enter Your Email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid Email Address";
    } else {
        $stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);

            $upd = $mysqli->prepare("
                UPDATE users
                SET reset_token_hash = ?,
                    reset_token_expiry = DATE_ADD(NOW(), INTERVAL 5 Minute)
                WHERE email = ?
            ");
            $upd->bind_param("ss", $tokenHash, $email);

            if ($upd->execute()) {
                header("Location: confirm_password.php?token=" . urlencode($token));
                exit();
            } else {
                $err = "Failed to create reset link";
            }
        } else {
            $err = "Email Does Not Exist";
        }
    }
}

/* Persist System Settings  */
$ret = "SELECT * FROM system ";
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
                <p class="login-box-msg">Reset Password</p>

                <?php if (isset($err)) { ?>
                    <div class="alert alert-danger">
                        <?php echo $err; ?>
                    </div>
                <?php } ?>

                <form method="post">
                    <div class="input-group mb-3">
                        <input type="email" required name="email" class="form-control" placeholder="Email" autocomplete="email" id="email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="Reset" class="btn btn-primary btn-block">Reset Password</button>
                        </div>
                    </div>
                </form>

                <p class="mb-1 pt-2">
                    <a href="login.php">I Remembered My Password</a>
                </p>
            </div>
        </div>
    </div>

    <?php require_once('../partials/scripts.php'); ?>
</body>
</html>
<?php } ?>