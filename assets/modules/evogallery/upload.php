<?php
$path_to_modx_config = '../../../manager/includes/config.inc.php';

include_once($path_to_modx_config);
startCMSSession();

include_once "../../../manager/includes/document.parser.class.inc.php";
$modx = new DocumentParser;
$modx->loadExtension("ManagerAPI");
$modx->getSettings();

// get module data
$rs = $modx->db->select('properties', $modx->getFullTableName('site_modules'), 'id = '.$_REQUEST['id'], '', '1');
if ($modx->db->getRecordCount($rs) > 0){
	$properties = $modx->db->getValue($rs);
}

// load module configuration
$parameters = array();
if(!empty($properties)){
	$tmpParams = explode("&",$properties);
	for($x=0; $x<count($tmpParams); $x++) {
		$pTmp = explode("=", $tmpParams[$x]);
		$pvTmp = explode(";", trim($pTmp[1]));
		if ($pvTmp[1]=='list' && $pvTmp[3]!="") $parameters[$pTmp[0]] = $pvTmp[3]; //list default
		else if($pvTmp[1]!='list' && $pvTmp[2]!="") $parameters[$pTmp[0]] = $pvTmp[2];
	}
}

if(is_array($parameters)) {
	extract($parameters, EXTR_SKIP);
}

include_once('config.inc.php');

if (is_uploaded_file($_FILES['Filedata']['tmp_name'])){
    $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : $params['docId'];  // Get document id3_get_frame_long_name(string frameId)
    $target_dir = $params['savePath'] . '/' . $content_id . '/';
	$target_fname = $_FILES['Filedata']['name'];
	if($modx->config['clean_uploaded_filename']) {
		$nameparts = explode('.', $target_fname);
		$nameparts = array_map(array($modx, 'stripAlias'), $nameparts);
		$target_fname = implode('.', $nameparts);
	}
	
	$target_file = $target_dir . $target_fname;
	$target_thumb = $target_dir . 'thumbs/' . $target_fname;
	
    // Check for existence of document/gallery directories
	if (!file_exists($target_dir))
	{
		mkdir($target_dir, 0777);
		mkdir($target_dir . 'thumbs/', 0777);
	}

	// Copy uploaded image to final destination
	if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $target_file))
	{
		resizeImage($target_file, $target_file, $params['imageSize'], $params['imageQuality']);  // Create and save main image
		resizeImage($target_file, $target_thumb, $params['thumbSize'], $params['thumbQuality']);  // Create and save thumb
		chmod($target_file, 0666);
		chmod($target_thumb, 0666);
	}

	// Find the last order position
	$rs = $modx->db->select('sortorder', $modx->getFullTableName('portfolio_galleries'), '', 'sortorder DESC', '1');
	if ($modx->db->getRecordCount($rs) > 0)
		$pos = $modx->db->getValue($rs) + 1;
	else
		$pos = 1; 

	// Create record in the database
	$fields = array(
		'content_id' => $content_id,
		'filename' => $modx->db->escape($target_fname),
		'sortorder' => $pos
	);
	$modx->db->insert($fields, $modx->getFullTableName('portfolio_galleries'));
	
    //return new filename
	echo $target_fname;
}

/**
* Resize a given image
*/
function resizeImage($filename, $target, $target_size = 110, $target_quality = 76)
{
	$info = @getimagesize($filename);  // Determine whether file is an image using getimagesize()
	if ($info)
	{
		if ($info[2] > 3)  // Use Imagemagick to convert other filetypes
		{
		/*
			// SWF, PSD, BMP, TIFF (intel + motorola)
			if ($info[2] == 4 || $info[2] == 5 || $info[2] == 6 || $info[2] == 7 || $info[2] == 8)
			{
				$cmd = $this->convert . " \"" . addslashes($filename) . "\" -quality $target_quality -quiet -resize $target_size" . "x" . "$target_size \"jpeg:" . addslashes($target) . "\"";
				shell_exec($cmd);
			}
		*/
		}
		else  // Use the GD library to convert jpeg, gif, and png images
		{
			switch ($info[2])  // Check image type
			{
				case 1:
					$img = @imagecreatefromgif($filename);
					break;
				case 2:
					$img = @imagecreatefromjpeg($filename);
					break;
				case 3:
					$img = @imagecreatefrompng($filename);
					break;
			}

			if (!$img) return false;  // Incompatible type

			$width = imageSX($img);
			$height = imageSY($img);
			if (!$width || !$height) return;  // Invalid width or height

			$ratio = ($width / $height);

			$new_height = $height;
			$new_width = $width;
			if ($new_height > $target_size)
			{
				$new_height = $target_size;
				$new_width = ceil($new_height * $ratio);
			}
			if ($new_width > $target_size)
			{
				$new_width = $target_size;
				$new_height = ceil($new_width / $ratio);
			}

			$new_img = imagecreatetruecolor($new_width, $new_height);
			if (!@imagefilledrectangle($new_img, 0, 0, $new_width, $new_height, 0)) return false;  // Could not fill image
			if (!@imagecopyresampled($new_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) return false;  // Could not resize image

			imagejpeg($new_img, $target, $target_quality);  // Save resulting thumbnail
			imagedestroy($new_img);
		}
	}
}
?>