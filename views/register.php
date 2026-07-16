<?php
	require_once('../config/config.php');
	require_once('../config/audit.php');
	require_once('../config/codeGen.php');

	if (isset($_POST['Register'])) {
		$name  = isset($_POST['name']) ? trim($_POST['name']) : '';
		$email = isset($_POST['email']) ? trim($_POST['email']) : '';
		$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
		$pwd   = isset($_POST['pwd']) ? $_POST['pwd'] : '';
		$cpwd  = isset($_POST['cpwd']) ? $_POST['cpwd'] : '';
		$role  = isset($_POST['role']) ? trim($_POST['role']) : '';

		$phone  = isset($_POST['phone']) ? trim($_POST['phone']) : null;

		if (empty($name) || empty($email) || empty($phone) || empty($pwd) || empty($cpwd) || empty($role)) {
			$err = "All fields are required";
		} elseif ($pwd !== $cpwd) {
			$err = "Passwords do not match";
		} elseif (!in_array($role, ['admin', 'instructor', 'student'])) {
			$err = "Invalid role selected";
		} else {
			/* Check email in users table */
			$stmt = $mysqli->prepare("SELECT user_id FROM users WHERE email=?");
			$stmt->bind_param('s', $email);
			$stmt->execute();
			$stmt->store_result();

			if ($stmt->num_rows > 0) {
				$err = "Email already registered";
			} else {
				$hashed = sha1(md5($pwd)); // keep your existing hash scheme
				$prefix= '';
				/* Generate role-based user code */
				if ($role === 'instructor') {
					$prefix = 'INS';
				} else if ($role === 'student') {
					$prefix = 'STD';
				}

				$userCode = getNextCode($mysqli, $prefix);

				/* Image name based on generated user code */
				$dpic = '';
				if (isset($_FILES['dpic']) && $_FILES['dpic']['error'] === UPLOAD_ERR_OK) {
					$tmp = $_FILES['dpic']['tmp_name'];
					$imginfo = @getimagesize($tmp);

					if ($imginfo === false) {
						$err = "Uploaded file is not a valid image";
					} else {
						$imageName = $userCode . '.png';
						$destDir = dirname(__FILE__) . '/../public/sys_data/uploads/users/';

						if (!is_dir($destDir)) {
							@mkdir($destDir, 0755, true);
						}

						if (!@move_uploaded_file($tmp, $destDir . $imageName)) {
							$err = "Failed to move uploaded image";
						} else {
							$dpic = $imageName;
						}
					}
				} else {
					$err = "Please upload an image";
				}

				if (empty($err)) {
					$ins = $mysqli->prepare(
						"INSERT INTO users
						(user_code, name, email, password, role, phone, dpic, status)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
					);

					$status = "pending";

					$ins->bind_param(
						'ssssssss',
						$userCode,
						$name,
						$email,
						$hashed,
						$role,
						$phone,
						$dpic,
						$status
					);
					if ($ins->execute()) {
							logAuditAction($mysqli, 'register', 'New user account created', 'auth', 'user', (string) $mysqli->insert_id);
						header("Location: login.php?registered=1");
						exit;
					} else {
						if (!empty($dpic)) {
							$uploadedPath = dirname(__FILE__) . '/../public/sys_data/uploads/users/' . $dpic;
							if (file_exists($uploadedPath)) {
								unlink($uploadedPath);
							}
						}
						$err = "Registration failed. Please try again.";
					}
				}
			}
		}
	}
	/* Persist System Settings  */
	$ret = "SELECT * FROM system ";
	$stmt = $mysqli->prepare($ret);
	$stmt->execute(); //ok
	$res = $stmt->get_result();
	while ($sys = $res->fetch_object()) {
		require_once('../partials/head.php')
	?>

		<body class="hold-transition login-page login-bg">
			<div class="login-box" style="width: 700px !important;">
				<div class="card card-success" style="padding: 1rem !important; margin: 0 !important;">
					<div class="login-logo">
						<a href="../index.php">
							<img src="../public/sys_data/logo/<?php echo $sys->sys_logo; ?>" class="img-fluid" height="50" width="100">
							<br>
							<?php echo $sys->sys_name; ?>
						</a>
					</div>
					<!-- /.login-logo -->
					<div class="card-body">
						<p class="login-box-msg">Register User</p>
						<?php if (isset($err)) { ?>
							<div class="alert alert-danger"><?php echo $err; ?></div>
						<?php } ?>
						<form method="post" enctype="multipart/form-data">
							<div class="input-group mb-3">
								<input type="text" required name="name" class="form-control" placeholder="Full Name" autocomplete="name" id="name">
								<div class="input-group-append">
									<div class="input-group-text">
										<span class="fas fa-user"></span>
									</div>
								</div>
							</div>
							<div class="input-group mb-3">
								<input type="email" required name="email" class="form-control" placeholder="Email" autocomplete="username" id="email">
								<div class="input-group-append">
									<div class="input-group-text">
										<span class="fas fa-envelope"></span>
									</div>
								</div>
							</div>
							<div class="input-group mb-3">
								<input type="text" required name="phone" class="form-control" placeholder="Phone Number" autocomplete="tel" id="phone">
								<div class="input-group-append">
									<div class="input-group-text">
										<span class="fas fa-phone"></span>
									</div>
								</div>
							</div>
							<div class="input-group mb-3">
								<input type="password" required name="pwd" class="form-control" placeholder="Password" autocomplete="new-password" id="pwd">
								<div class="input-group-append">
									<div class="input-group-text">
										<span class="fas fa-lock"></span>
									</div>
								</div>
							</div>
							<div class="input-group mb-3">
								<input type="password" required name="cpwd" class="form-control" placeholder="Confirm Password" autocomplete="new-password" id="cpwd">
								<div class="input-group-append">
									<div class="input-group-text">
										<span class="fas fa-lock"></span>
									</div>
								</div>
							</div>
							<div class="input-group mb-3">
								<select name="role" class="form-control" placeholder="User Role" id="role" required>
									<option value="">Select Role</option>
									<option value="instructor">Instructor</option>
									<option value="student">Student</option>
								</select>
								<div class="input-group-text">
									<span class="fas fa-user"></span>
								</div>
							</div>
							<div class="form-group mb-3">
								<label for="dpic">Profile Picture</label>
								<input type="file" required name="dpic" accept="image/*" id="dpic" class="form-control-file">
							</div>
							<br>
							<div class="icheck-primary">
								<input type="checkbox" id="agree">
								<label for="agree">
									The details provided is accurate and can use and store for management purposes.
								</label>
							</div>
							<br>
							<div class="row">
								<div class="col-12">
									<button id="registerBtn" type="submit" name="Register" class="btn btn-primary btn-block" disabled>Register</button>
								</div>
							</div>
						</form>
						<p class="mb-1 mt-1">
							<a href="login.php">I already have an account</a>
						</p>
					</div>
					<!-- /.login-card-body -->
				</div>
			</div>
			<!-- /.login-box -->

					<script>
						document.addEventListener('DOMContentLoaded', function() {
							const nameEl = document.getElementById('name');
							const emailEl = document.getElementById('email');
							const phoneEl = document.querySelector('[name="phone"]');
							const pwdEl = document.getElementById('pwd');
							const cpwdEl = document.getElementById('cpwd');
							const dpicEl = document.getElementById('dpic');
							const agreeEl = document.getElementById('agree');
							const btn = document.getElementById('registerBtn');

							function updateBtn() {
								const ok = nameEl.value.trim() && emailEl.value.trim() && phoneEl.value.trim() && pwdEl.value && cpwdEl.value && dpicEl.files && dpicEl.files.length > 0 && agreeEl.checked;
								btn.disabled = !ok;
							}

							[nameEl, emailEl, phoneEl, pwdEl, cpwdEl].forEach(el => el.addEventListener('input', updateBtn));
							dpicEl.addEventListener('change', updateBtn);
							agreeEl.addEventListener('change', updateBtn);
							updateBtn();
						});
					</script>

					<?php require_once('../partials/scripts.php'); ?>

		</body>
		</html>
	<?php 
		} 
?>