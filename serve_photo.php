<?php

$quiet='Y';
include 'includes.php';
//include 'photo.php';
global $AG_CLIENT_PHOTO_BY_FILE,$AG_DEMO_MODE,$AG_IMAGES;
$error='';
$id=$_REQUEST['id'];
$object=strtolower($_REQUEST['object']);
$scale=orr($_REQUEST['scale'],1);

if ($object != 'photo_upload') {
	if (!(is_valid($id,'integer') and is_numeric($scale) and in_array($object,array_keys($engine)))) {
		$error[]="Invalid request passed to serve_photo.php. Object: $object, id: $id, scale: $scale";
	}
	if (!(has_perm($engine[$object]['perm_view']))) {
		$error[]= "You don't have permission to view $object objects";
	}
}

if ($error) {
	log_error(implode($oline,$error));
	page_close(TRUE);//silent
	exit;
}

if (!has_perm('photo')) {
	$filename=$AG_HOME_BY_FILE . $AG_IMAGES['NO_PHOTO'];
} elseif ($AG_DEMO_MODE) {
	$filename=$AG_HOME_URL . $AG_IMAGES['DEMO_PHOTO'];
} elseif ($object=='photo_upload') {
	$filename=object_photo_filename($id,$object,$scale);
} else {
	$filename=object_photo_filename($id,$object,$scale);
}

if ( $photo=@file_get_contents($filename)) {
//outline("going for $filename");
	return serve_photo($photo);
} else {
	outline("Failed to get photo file for " . $filename);
	return; 
}
?>
