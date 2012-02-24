<?php

require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Config/Xml.php';

$config = new Zend_Config_Xml('config.xml', 'production');
$uploaded = false;

function getCsvFile() {
	if (!empty($_POST['collId'])) {
		if (startsWith($_POST['collId'], '/')) {
			return substr($_POST['collId'], 1) . '.csv';
		}
		else {
			return $_POST['collId'] . '.csv';
		}
	}
	else if (!empty($config->defColl)) {
		if (startsWith($config->defColl, '/')) {
			return substr($config->defColl, 1) . '.csv';
		}
		else {
			return $config->defColl . '.csv';
		}
	}
	else {
		throw new Zend_Exception("Couldn't find a collection to use");
	}
}

function startsWith($haystack, $needle) {
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function getErrorMsg() {
	$error = '<div class="error"><span>Warning: &nbsp;</span>File "%FILE%"';
	$error = $error . ' already exists; please process or delete it before';
	return $error . ' uploading your file.</div>';
}

?>

<html>
<head>
<title>Results: Batch Update File Upload</title>
<style type="text/css">
body {
	padding: 20px;
}

div {
	width: 700px;
	margin-left: auto;
	margin-right: auto;
}

span {
	font-weight: bold;
}

.centered {
	text-align: center;
}

.error {
	padding-top: 8px;
	color: #990000;
}
</style>
</head>
<body>
	<h3 class="centered">Result of Batch File Upload</h3>
	<div style="border: 1px solid #ccc; padding: 8px;">

	<?php
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'POST':
			if (($_FILES["batchfile"]["type"] == "text/csv")
			&& ($_FILES["batchfile"]["size"] < $config->maxBatchFileSize)) {
				if ($_FILES["batchfile"]["error"] > 0) {
	?>
		<div>
			<span class='bold'>Return Code</span>:
			<?php $_FILES["batchfile"]["error"]?>
		</div>

			<?php
				}
				else {
					$uploaded = true;
					$csvFile = "batch_files/" . getCsvFile();

					echo "<div><span>Uploaded:</span> " . $_FILES["batchfile"]["name"] . "</div>";
					echo "<div><span>Type:</span> " . $_FILES["batchfile"]["type"] . "</div>";
					echo "<div><span>Size:</span> " . ($_FILES["batchfile"]["size"] / 1024) . " Kb</div>";
					echo "<div><span>Temp file:</span> " . $_FILES["batchfile"]["tmp_name"] . "</div>";

					if (file_exists($csvFile)) {
						echo str_replace('%FILE%', getCsvFile(), getErrorMsg());
					}
					else {
						$tmpFile = $_FILES["batchfile"]["tmp_name"];
						move_uploaded_file($tmpFile, $csvFile);
						echo '<div><span>Stored in:</span> ' . $csvFile . '</div>';
					}
				}
			}
			else {
				echo '<div>Invalid file.</div>';
			}

			break;
		case 'GET':
			$fName = 'batch_files/' . $_GET['collId'] . '.csv';

			if (file_exists($fName)) {
				$uploaded = true;
	?>
				<div><span>File:</span> <?php echo $fName; ?></div>
				<div><span>Size:</span>
					<?php echo round(filesize($fName) / 1024, 2);?> Kb</div>
	<?php
			}
	}
	?>

	</div>

	<?php
	if ($uploaded) {
		$csvFile = ''; // depends on how file was created

		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				$csvFile = $_GET["collId"] . '.csv';
				break;
			case 'POST':
				$csvFile = getCsvFile();
		}
	?>
	<div class="centered" style="padding-top: 8px;">
		<form style="display: inline;" action="update.php" method="POST">
			<input type="hidden" name="file"
				value="<?php echo "batch_files/" . $csvFile; ?>" />
			<input type="submit" value="Process" />
		</form>
		<form style="display: inline;" action="delete.php" method="POST">
			<input type="hidden" name="file"
				value="<?php echo "batch_files/" . $csvFile; ?>" /> <input
				type="submit" value="Delete" />
		</form>
	</div>
	<div style="padding-top: 20px;"><span>Instructions</span></div>
	<div style="padding-top: 8px;">Successfully uploaded files can be processed
	or deleted. If the file couldn't be successfully uploaded, it may be that
	there is another one waiting to be processed.  You must delete or process
	the queued file before uploading a new one.</div>
	<div style="padding-top: 8px;">When a file is successfully uploaded it is
	renamed to match the collection it is intended to update. Successfully
	processing a file moves it into a different directory where it is saved for
	historical purposes.  These saved batch files can be
	<a href="review.php">reviewed</a> as needed.</div>
	<?php } else { echo 'No file uploaded.'; } ?>
</body>
</html>
