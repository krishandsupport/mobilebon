<!-- pages/logout.php -->
<section id="logout">
	<?php
		// logout.php
		session_start();
		session_destroy();
		header("Location: ?page=login");
		//echo "logout"
		exit();
	?>
</section>
