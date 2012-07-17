<?php

require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Exception.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';

$config = new Zend_Config_Xml('../config.xml', 'production');
$shoulder = $config->ezid->shoulder;

?>

<html>
<head>
<title>EZID ARK Assigner</title>
<style type="text/css">
body {
	padding: 20px;
}

div {
	width: 700px;
	margin-left: auto;
	margin-right: auto;
	padding: 10px;
}

td {
	padding: 5px;
}

.centered {
	text-align: center;
}

.bold {
	font-weight: bold;
}

.condensed {
	padding: 0px;
}
</style>
</head>
<body>
	<h3 class="centered">EZID ARK Assigner</h3>
	<form enctype="multipart/form-data" action="upload.php" method="POST">
		<div style="border: 1px solid #ccc;">
			<table>
				<tr>
					<td>EZID ARK Shoulder:</td>
					<td><input
						onfocus="if (this.value=='<?php echo $shoulder; ?>') this.value = ''"
						type="text" name="arkShoulder" value="<?php echo $shoulder; ?>" size="100" /></td>
				</tr>
				<tr>
					<td><input type="hidden" name="MAX_FILE_SIZE" value="100000" />
						Choose a CDM export file to process:</td>
					<td><input name="cdmfile" type="file" /></td>
				</tr>
			</table>
		</div>
		<div style="text-align: center; padding-top: 20px;">
			<input type="submit" value="Upload File" />
		</div>
	</form>
</body>
</html>
