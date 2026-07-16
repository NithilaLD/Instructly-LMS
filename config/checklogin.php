<?php	
	if (session_status() == PHP_SESSION_NONE){session_start();}
	$timeout = 30 * 60;
	if (isset($_SESSION['last_activity']))
	{
		if ((time() - $_SESSION['last_activity']) > $timeout)
		{
			session_unset();
			session_destroy();
			header("Location: logout.php");
			exit();
		}
	}
	$_SESSION['last_activity'] = time();
	if (!isset($_SESSION['role']))
	{
        header("Location: logout.php");
        exit();
    }
	if (!isset($_SESSION['user_id']))
    {
        header("Location: logout.php");
        exit();
    }
	$currentPage = basename($_SERVER['PHP_SELF']);
	if (isset($_SESSION['must_change_password']) && (int)$_SESSION['must_change_password'] === 1 && $currentPage !== 'force_change_password.php' && $currentPage !== 'logout.php')
	{
		header("Location: force_change_password.php");
		exit();
	}
	function userRole(string $role)
	{
		if ($_SESSION['role'] !== $role)
		{
			header("Location: logout.php");
			exit();
		}
	}
	function userRoles(array|string $roles)
	{
		if (is_string($roles)) {$roles = [$roles];}
		if (!in_array($_SESSION['role'], $roles, true))
		{
			header("Location: logout.php");
			exit();
		}
	}
?>
<script>
	let timeout = 30 * 60 * 1000; // 30 minutes
	setTimeout(function (){window.location.href = "logout.php";}, timeout);
</script>