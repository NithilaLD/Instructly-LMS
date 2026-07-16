<?php
    require_once('../config/config.php');

    if (!isset($_GET['token']) && !isset($_POST['token'])) {
        header("Location: reset_password.php");
        exit();
    }

    $token = trim($_POST['token'] ?? $_GET['token'] ?? '');
    $tokenHash = hash('sha256', $token);

    $stmt = $mysqli->prepare("
        SELECT user_id, email
        FROM users
        WHERE reset_token_hash = ?
        AND reset_token_expiry > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();

    $user = $res->fetch_assoc();

    if (!$user) {
        die("Invalid or expired reset link");
    }

    if (isset($_POST['ConfirmPassword'])) {
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($new_password)) {
            $err = "New Password Cannot Be Empty";
        } elseif (empty($confirm_password)) {
            $err = "Confirmation Password Cannot Be Empty";
        } elseif ($new_password !== $confirm_password) {
            $err = "Passwords Do Not Match";
        } else {
            $hashed_password = sha1(md5($new_password));
            // Prevent reusing the current password
            $check = $mysqli->prepare("SELECT password FROM users WHERE user_id = ?");
            $check->bind_param("i", $user['user_id']);
            $check->execute();
            $result = $check->get_result();
            $current = $result->fetch_assoc();

            if ($hashed_password === $current['password']) {
                $err = "Your new password cannot be the same as your current password.";
            } else {

                $upd = $mysqli->prepare("
                    UPDATE users
                    SET password = ?,
                        reset_token_hash = NULL,
                        reset_token_expiry = NULL
                    WHERE user_id = ?
                ");
                $upd->bind_param("si", $hashed_password, $user['user_id']);

                if ($upd->execute()) {
                    header("Location: login.php?reset=1");
                    exit();
                } else {
                    $err = "Failed To Change Password";
                }
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
            <div class="card" style="padding: 1rem !important; margin: 0 !important;">
                <div class="login-logo">
                    <a href="../index.php">
                        <img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" class="img-fluid" height="50" width="100">
                        <br>
                        <?php echo $sys->sys_name; ?>
                    </a>
                </div>
                <div class="card-body">
                    <p class="login-box-msg">
                        <span class="badge badge-success">Verified</span><br>
                        Confirm Your Password
                    </p>

                    <?php if (isset($err)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $err; ?>
                        </div>
                    <?php } ?>

                    <div class="alert alert-danger">
                        This token will expire in 5 minutes.
                    </div>

                    <form method="post">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="input-group mb-3">
                            <input type="password" name="new_password" class="form-control" placeholder="New Password" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" autocomplete="new-password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="ConfirmPassword" class="btn btn-primary btn-block">Change Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php require_once('../partials/scripts.php'); ?>
    </body>
    </html>
    <?php } ?>