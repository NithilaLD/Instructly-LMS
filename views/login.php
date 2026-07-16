<?php
    require_once('../config/config.php');
    require_once('../config/audit.php');
    if (isset($_POST['login'])) {
        $email = trim($_POST['email']);
        $password = $_POST['pwd'];

        // Keep the same hash format used in register.php
        $hashed = sha1(md5($password));

        $stmt = $mysqli->prepare("
            SELECT user_id, user_code, name, role, must_change_password
            FROM users
            WHERE email = ? AND password = ? AND status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param("ss", $email, $hashed);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc())
        {
            $lifetime = 1800; // 30 minutes
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']),
                'samesite' => 'Lax'
            ]);

            session_start();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_code'] = $row['user_code'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['must_change_password'] = (int)$row['must_change_password'];
            logAuditAction($mysqli, 'login', 'User signed in successfully', 'auth');

            if ((int)$row['must_change_password'] === 1)
            {
                header("Location: force_change_password.php");
                exit();
            }
            else if (isset($row['role']))
            {
                header("Location: dashboard.php");
                exit();
            }
            else {
                $err = "Invalid user role";
            }
        } else {
            $err = "Invalid email or password";
        }
    }

    /* Persist System Settings  */
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
                    <p class="login-box-msg">Sign In</p>

                    <?php if (isset($_GET['registered']) && $_GET['registered'] == 1) { ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Success!</h5>
                            Successfully registered. Please log in with your credentials.
                        </div>
                        <script>
                            if (window.history.replaceState) {
                                window.history.replaceState({}, document.title, "login.php");
                            }
                        </script>
                    <?php } ?>

                    <?php if (isset($_GET['reset']) && $_GET['reset'] == 1) { ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="color: #fff !important;opacity: 100% !important;">&times;</button>
                            <h5><i class="icon fas fa-check"></i> Success!</h5>
                            Password changed successfully. Please login.
                        </div>
                        <script>
                            if (window.history.replaceState) {
                                window.history.replaceState({}, document.title, "login.php");
                            }
                        </script>
                        <?php } ?>

                    <?php if (isset($err)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $err; ?>
                        </div>
                    <?php } ?>

                    <form method="post">
                        <div class="input-group mb-3">
                            <input type="email" required name="email" class="form-control" placeholder="Email" autocomplete="username" id="email">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>

                        <div class="input-group mb-3">
                            <input type="password" required name="pwd" class="form-control" placeholder="Password" autocomplete="current-password" id="pwd">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" name="login" class="btn btn-primary btn-block">Sign In</button>
                            </div>
                        </div>
                    </form>

                    <p class="mb-1 mt-1">
                        <a href="reset_password.php">Forgot Password</a>
                    </p>
                </div>
            </div>
        </div>

        <?php require_once('../partials/scripts.php'); ?>
    </body>
    </html>
    <?php } ?>