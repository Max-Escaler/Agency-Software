<?php

/*
<LICENSE>

This file is part of AGENCY.

AGENCY is Copyright (c) 2003-2017 by Ken Tanzer and Downtown Emergency
Service Center (DESC).

All rights reserved.

For more information about AGENCY, see http://agency-software.org/
For more information about DESC, see http://www.desc.org/.

AGENCY is free software: you can redistribute it and/or modify
it under the terms of version 3 of the GNU General Public License
as published by the Free Software Foundation.

AGENCY is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with AGENCY.  If not, see <http://www.gnu.org/licenses/>.

For additional information, see the README.copyright file that
should be included in this distribution.

</LICENSE>
*/

function image_grayscale($original,$destination)
{
	//returns grayscale image

	if (!is_file($original) or !is_readable($original)) {
		log_error('image_grayscale() cannot access file '.$original);
		clearstatcache() ;
		return false;
	}

	// WARNING: this function will overwrite the destination file if it exists. If this
	// is undesirable, it should be checked for prior to calling image_grayscale()
	if ((is_file($destination) && !is_writable($destination))
	    || !is_writable(dirname($destination))) {
		clearstatcache();
		log_error('image_grayscale() cannot write to file '.$destination);
		return false;
	}
	$working = image_create_from_type($original);
	
	$x_dim = imagesx($working);
	$y_dim = imagesy($working);

	//FIXME: move these to agency_config.php once this function is in use
	// To use this function, image functions must be installed (php-gd)
	define('AG_COLOR_GRAYSCALE_RED',0.15);
	define('AG_COLOR_GRAYSCALE_GREEN',0.5);
	define('AG_COLOR_GRAYSCALE_BLUE',0.35);

	//replace pixel by pixel
	for ($i=0; $i<$x_dim; $i++) {
		for ($j=0; $j<$y_dim; $j++) {
			$position = imagecolorat($working,$i,$j);
			$color    = imagecolorsforindex($working,$position);
			$gs_trans = $color['red']*AG_COLOR_GRAYSCALE_RED
				+ $color['green']*AG_COLOR_GRAYSCALE_GREEN
				+ $color['blue']*AG_COLOR_GRAYSCALE_BLUE;
			$gs_val   = imagecolorresolve($working,$gs_trans,$gs_trans,$gs_trans);
			imagesetpixel($working,$i,$j,$gs_val);
		}
	}

	image_write($working,$destination);
}

function image_resize($original,$destination,$width,$height,$force_size=false)
{
	// This function requires GD 2.0 or later, so it is not yet in use

	if (!is_file($original) or !is_readable($original)) {
		log_error('image_resize() cannot access file '.$original);
		clearstatcache() ;
		return false;
	}

	// WARNING: this function will overwrite the destination file if it exists. If this
	// is undesirable, it should be checked for prior to calling image_resize()
	if ((is_file($destination) && !is_writable($destination))
	    || !is_writable(dirname($destination))) {
		clearstatcache();
		log_error('image_resize() cannot write to file '.$destination);
		return false;
	}

	$working = image_create_from_type($original);

	$o_width  = imagesx($working);
	$o_height = imagesy($working);

	if (!$force_size) {

		// if $force_size is true, the image will be returned in the exact size
		// specified. Otherwise, height and width will be assumed to be a maximum boundary,
		// and the image will be scaled accordingly (without distortion to aspect ratio).

		if ($o_width < $o_height) {
			//portrait
			$width = ($height / $o_height) * $o_width;
		} else {
			//landscape (or possibly square)
			$height = ($width / $o_width) * $o_height;
		}
	}

	// Create the new image
	$new = imagecreatetruecolor($width, $height);
	imagecopyresampled($new, $working, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
	return image_write($new,$destination);
}

function image_create_from_type($original)
{

	// wrapper function for the PHP imagecreatefrom{TYPE} functions

	$type = get_extension($original);
	switch ($type) {
	case 'png':
		$working = imagecreatefrompng($original);
		break;
	case 'gif':
		$working = imagecreatefromgif($original);
		break;
	case 'jpg':
	case 'jpeg':
		$working = imagecreatefromjpeg($original);
		break;
	default:
		log_error('unknown image type ('.$type.') passed to image_create_from_type()');
		return false;
	}
	return $working;
}

function image_write($new,$dest)
{

	// wrapper function for the PHP image{TYPE} functions

	$type = get_extension($dest);
	switch ($type) {
	case 'png':
		return imagepng($new,$dest);
	case 'gif':
		return imagegif($new,$dest);
	case 'jpg':
	case 'jpeg':
		return imagejpeg($new,$dest);
	default:
		log_error('unsupported image type ('.$type.') passed to image_create_from_type()');
		return false;
	}
}

?>
