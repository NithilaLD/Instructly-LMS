<?php
session_start();
require_once('../config/config.php'); //get configuration file
if (isset($_POST['Register'])) {
	$a_name = isset($_POST['a_name']) ? trim($_POST['a_name']) : '';
	$a_email = isset($_POST['a_email']) ? trim($_POST['a_email']) : '';
	$a_pwd = isset($_POST['a_pwd']) ? $_POST['a_pwd'] : '';
	$a_cpwd = isset($_POST['a_cpwd']) ? $_POST['a_cpwd'] : '';

	// Require all fields server-side to avoid DB failures
	if (empty($a_name) || empty($a_email) || empty($a_pwd) || empty($a_cpwd)) {
		$err = "All fields are required";
	} elseif ($a_pwd !== $a_cpwd) {
		$err = "Passwords do not match";
	} else {
		/* Check if email already exists */
		$stmt = $mysqli->prepare("SELECT a_email FROM lms_admin WHERE a_email=?");
		$stmt->bind_param('s', $a_email);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			$err = "Email is already registered";
		} else {
			$a_hashed = sha1(md5($a_pwd)); // match existing hashing scheme

			/* Determine next a_id for naming image */
			$next_id = 1;
			$stmt = $mysqli->prepare("SELECT a_id FROM lms_admin ORDER BY a_id DESC LIMIT 1");
			$stmt->execute();
			$resid = $stmt->get_result();
			if ($resid && $resid->num_rows > 0) {
				$row = $resid->fetch_assoc();
				$next_id = intval($row['a_id']) + 1;
			}

			/* Handle uploaded image: require it and move it named as next_id + ".png" */
			$a_dpic = '';
			if (isset($_FILES['a_dpic']) && isset($_FILES['a_dpic']['tmp_name']) && $_FILES['a_dpic']['error'] === UPLOAD_ERR_OK) {
				$tmp = $_FILES['a_dpic']['tmp_name'];
				$imginfo = @getimagesize($tmp);
				if ($imginfo === false) {
					$err = "Uploaded file is not a valid image";
				} else {
					$imageName = $next_id . '.png';
					$destDir = dirname(__FILE__) . '/../public/sys_data/uploads/users/';
					if (!is_dir($destDir)) {
						@mkdir($destDir, 0755, true);
					}
					if (!@move_uploaded_file($tmp, $destDir . $imageName)) {
						$err = "Failed to move uploaded image";
					} else {
						$a_dpic = $imageName;
					}
				}
			} else {
				$err = "Please upload an image";
			}

			if (empty($err)) {
				$ins = $mysqli->prepare("INSERT INTO lms_admin (a_name, a_email, a_pwd, a_dpic) VALUES (?, ?, ?, ?)");
				$ins->bind_param('ssss', $a_name, $a_email, $a_hashed, $a_dpic);

				if ($ins && $ins->execute()) {
					header("location:login.php?registered=1");
					exit;
				} else {
					// Remove uploaded image if insert failed
					if (!empty($a_dpic)) {
						$uploadedPath = dirname(__FILE__) . '/../public/sys_data/uploads/users/' . $a_dpic;
						if (file_exists($uploadedPath)) unlink($uploadedPath);
					}
					$err = "Registration failed. Please try again.";
				}
			}
		}
	}
}
/* Persist System Settings  */
$ret = "SELECT * FROM `lms_sys_setttings` ";
$stmt = $mysqli->prepare($ret);
$stmt->execute(); //ok
$res = $stmt->get_result();
while ($sys = $res->fetch_object()) {
	require_once('../partials/head.php')
?>

	<body class="hold-transition login-page">
		<div class="login-box">
			<div class="login-logo">
				<a href="../index.php">
					<img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" class="img-fluid" height="50" width="100">
					<br>
					<?php echo $sys->sys_name; ?>
				</a>
			</div>
			<!-- /.login-logo -->
			<div class="card card-success">
				<div class="card-body">
					<p class="login-box-msg">Register Admin</p>
					<?php if (isset($err)) { ?>
						<div class="alert alert-danger"><?php echo $err; ?></div>
					<?php } ?>
					<form method="post" enctype="multipart/form-data">
						<div class="input-group mb-3">
							<input type="text" required name="a_name" class="form-control" placeholder="Full Name" autocomplete="name" id="a_name">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-user"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="email" required name="a_email" class="form-control" placeholder="Email" autocomplete="username" id="a_email">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-envelope"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" required name="a_pwd" class="form-control" placeholder="Password" autocomplete="new-password" id="a_pwd">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-lock"></span>
								</div>
							</div>
						</div>
						<div class="input-group mb-3">
							<input type="password" required name="a_cpwd" class="form-control" placeholder="Confirm Password" autocomplete="new-password" id="a_cpwd">
							<div class="input-group-append">
								<div class="input-group-text">
									<span class="fas fa-lock"></span>
								</div>
							</div>
						</div>

						<div class="form-group mb-3">
							<label for="a_dpic">Profile Picture</label>
							<input type="file" required name="a_dpic" accept="image/*" id="a_dpic" class="form-control-file">
						</div>
						<div class="row">
							<div class="col-8">
								<div class="icheck-primary">
									<input type="checkbox" id="agree">
									<label for="agree">
										I agree to the terms
									</label>
								</div>
							</div>
							<!-- /.col -->
							<div class="col-4">
								<button id="registerBtn" type="submit" name="Register" class="btn btn-primary btn-block" disabled>Register</button>
							</div>
							<!-- /.col -->
						</div>
					</form>

					<p class="mb-1">
						<a href="login.php">I already have an account</a>
					</p>
				</div>
				<!-- /.login-card-body -->
			</div>
		</div>
		<!-- /.login-box -->

				<script>
					document.addEventListener('DOMContentLoaded', function() {
						const nameEl = document.getElementById('a_name');
						const emailEl = document.getElementById('a_email');
						const pwdEl = document.getElementById('a_pwd');
						const cpwdEl = document.getElementById('a_cpwd');
						const dpicEl = document.getElementById('a_dpic');
						const agreeEl = document.getElementById('agree');
						const btn = document.getElementById('registerBtn');

						function updateBtn() {
							const ok = nameEl.value.trim() && emailEl.value.trim() && pwdEl.value && cpwdEl.value && dpicEl.files && dpicEl.files.length > 0 && agreeEl.checked;
							btn.disabled = !ok;
						}

						[nameEl, emailEl, pwdEl, cpwdEl].forEach(el => el.addEventListener('input', updateBtn));
						dpicEl.addEventListener('change', updateBtn);
						agreeEl.addEventListener('change', updateBtn);
						updateBtn();
					});
				</script>

				<?php require_once('../partials/scripts.php'); ?>

	</body>

	</html>
<?php } ?>
