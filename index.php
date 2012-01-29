<?php

require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Exception.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log.php';
require_once 'Zend/Registry.php';

$config = new Zend_Config_Xml('config.xml', 'production');
$coll = $config->defColl;

?>

<html>
<head>
<title>Batch Update File Upload</title>
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
	<h3 class="centered">Simple ContentDM Batch File Uploader</h3>
	<form enctype="multipart/form-data" action="upload.php" method="POST">
		<div style="border: 1px solid #ccc;">
			<table>
				<tr>
					<td>ContentDM Collection ID:</td>
					<td><input
						onfocus="if (this.value=='<?php echo $coll; ?>') this.value = ''"
						type="text" name="collId" value="<?php echo $coll; ?>" /></td>
				</tr>
				<tr>
					<td><input type="hidden" name="MAX_FILE_SIZE" value="100000" />
						Choose a file to upload:</td>
					<td><input name="batchfile" type="file" /></td>
				</tr>
			</table>
		</div>
		<div style="text-align: center; padding-top: 20px;">
			<input type="submit" value="Upload File" />
		</div>
	</form>
	<div>
		<div class="bold">Instructions</div>
		<div>The batch file you upload must be in CSV format (e.g.,
			filename.csv) and have three columns:</div>
		<ul>
			<li>ContentDM record ID</li>
			<li>ContentDM field name</li>
			<li>Replacement value</li>
		</ul>
		<div>Do not include the column names as the first row in the file. So,
			for instance, your CSV file should look like this (assuming you've
			already created an ARK field in ContentDM):</div>
		<table style="padding-left: 10px; color: #333; width: 100%">
			<tr>
				<td class="condensed">0,</td>
				<td class="condensed">ARK,</td>
				<td class="condensed">ark:/99999/f1wd3xhg</td>
			</tr>
			<tr>
				<td class="condensed">1,</td>
				<td class="condensed">ARK,</td>
				<td class="condensed">ark:/99999/f1rd3xpk</td>
			</tr>
			<tr>
				<td class="condensed">2,</td>
				<td class="condensed">ARK,</td>
				<td class="condensed">ark:/99999/f1hg3xwd</td>
			</tr>
			<tr>
				<td class="condensed">3,</td>
				<td class="condensed">ARK,</td>
				<td class="condensed">ark:/99999/f1wd6xrt</td>
			</tr>
		</table>
		<div style="margin-top: 20px; color: #990000;"
			class="centered; padding-left: 0px; padding-right: 0px;">
			<span class="bold">Warning:</span> You can overwrite your CDM
			metadata with this script! Password protect this page!
		</div>
	</div>
</body>
</html>
