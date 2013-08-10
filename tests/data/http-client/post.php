<?php

if (isset($_POST['action'])) {
	switch ($_POST['action']) {
		case 'connect':
			session_start();
			$_SESSION['count'] = 1;
			$_SESSION['jedna'] = 'Lorem ipsum doler ist.';
			break;
		default:
			echo '<error>Unknow command</error>' . PHP_EOL;
	}
}

?>postak
GET:<?php print_r($_GET);?>
POST:<?php print_r($_POST);?>
COOKIE:<?php print_r($_COOKIE);?>
