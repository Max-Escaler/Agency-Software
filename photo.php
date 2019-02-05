<?php

function object_photo_url( $id, $object=AG_MAIN_OBJECT_DB, $scale=1 )
{
    return "serve_photo.php?object=$object&id=$id&scale=$scale";
}

function serve_photo( $contents) {
	header('Content-Type: image/jpeg');
	echo($contents);
	page_close(TRUE); // SILENT
	exit;
}

function object_photo_filename( $id, $object, $scale, $all_in_array=false ) {
	global $AG_CLIENT_PHOTO_BY_FILE,$AG_HOME_BY_FILE,$AG_DATA_BY_FILE,$AG_IMAGES, $AG_DEMO_MODE;
	$ext='.jpg';
	$thumb = '.120x160';
	if ($object=='staff') {
		$offset="/$object/$id"; // could extend to other objects
	} elseif ($object==AG_MAIN_OBJECT_DB) {
		$offset = "/pc" . substr("0". intval($id/1000),-2) .  "/$id";
	} elseif ($object=='photo_upload') {
		return (is_file( $a=$AG_DATA_BY_FILE . '/agencylink/photo_upload/' .$id) or true)
			? $a
			: 'Upload photo ' .$a . ' not found';
	} else {
		log_error("object_photo_filename: Photos not supported for object type $object");
	}
	if (!$all_in_array) {
		if ($AG_DEMO_MODE) {
			return $AG_HOME_BY_FILE . '/' . $AG_IMAGES['DEMO_PHOTO'];
		} elseif (($scale<=1) and is_file($AG_CLIENT_PHOTO_BY_FILE . $offset . $thumb . $ext)) {
			return $AG_CLIENT_PHOTO_BY_FILE .$offset . $thumb . $ext;
		} elseif (is_file($AG_CLIENT_PHOTO_BY_FILE . $offset . $ext)) {
			return $AG_CLIENT_PHOTO_BY_FILE .$offset . $ext;
		} else {
			return $AG_HOME_BY_FILE . '/' .$AG_IMAGES['NO_PHOTO'];
		}
	}
	// return all photos
	$photos=glob($AG_CLIENT_PHOTO_BY_FILE.$offset.'*');
	foreach ($photos as $photo) {
		$p=array();
		$p['file']=basename($photo);
        //$p['http']=hlink($hlink,httpimage($hlink,120*$scale,160*$scale,0));
// FIXME: get rid of client_photo, add object_photo(()
        $p['http']=hlink(object_photo_url($id,$object),client_photo($id,$scale));
		$p['size']=preg_match('/120x160/i',$photo)
			? 'thumb' 
			: 'full';
        if (preg_match('/[-]([0-9]{4}([-][0-9]{2}){2} [0-9]{2}:[0-9]{2}:[0-9]{2})/', basename($photo), $matches ))
        {
            $p['timestamp']=datetimeof($matches[1]);
            $p['time']=timeof($matches[1]);
            $p['date']=dateof($matches[1]);
        }
        elseif (preg_match('/unknown/i',$f))
        {
            $p['timestamp']='unknown';
            $p['time']='unknown';
            $p['date']='unknown';
        }
	}
	return $p;
}

function photo_dialog_box( $id,$object=AG_MAIN_OBJECT_DB) {
//function button( $label="Submit", $type='', $name='',$value='', $onClick='' , $options=null )
	return
		formto('get_photo.php',"file_form",'class="photoUploadButton" enctype=multipart/form-data')
		. hiddenvar("MAX_FILE_SIZE",3000000)
		. hiddenvar("client_id",$id)
		. hiddenvar("object",$object)
		. hiddenvar("action","file_upload")
		. hiddenvar('photo_data')
		. span( oline('Click image to send.')
		. '<canvas class="imageCapture"></canvas>'
		,'class="imageSendContainer hidden"')
		. span( oline('Click video to capture, or'
		. button('Select file for upload','','','','','class="imageCaptureUploadButton"')
		. formfile('userfile','class="imageCaptureUpload hidden"'))
		. '<video autoplay class="videoStream"></video>'
		,'class="imageCaptureContainer"')
		. formend()
		. div('New Photo for ' . client_name($id),'','class="photoDialogTitle serverData"')
		;
}

?>
