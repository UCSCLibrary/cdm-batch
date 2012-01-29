<?php

if (unlink($_POST['file'])) {
	header('Location: index.php');
}
else {
	echo "<div>Error: Unable to delete batch file! Delete manually.</div>";
}