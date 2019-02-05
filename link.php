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

function seclink( $id, $label, $options=null)
{
	// return a link to a section of current page
	$extra = $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '';
	$page=$_SERVER['PHP_SELF'].$extra.'#'.$id;
	return hlink( $page, orr($label,$id),'',$options );
}

function dead_link($text,$options='')
{
      //return a gray pseudo link
      if (preg_match('/^(.*class=".[^"]*)(".*)$/i',$options,$matches)) {
	$options = $matches[1] . ' deadlink' . $matches[2];
      } else {
	$options = 'class="deadlink"';
      }
      return span($text,$options);
}

function link_map($address,$label='map')
{
	if (AG_MAP_URL) {
		$address=preg_replace('/\n/',' ',trim($address));
		$address=preg_replace('/#[a-z]?[0-9]{0,}(-[0-9]{1,3})?/i','',$address); //filter out unit no
		$address=str_replace(array('.',',','-'),' ',$address);
		$address = webify(preg_replace('/\s{1,}/','+',$address));
		return hlink(AG_MAP_URL . $address,$label,'','target="_blank" class="mapLink"');
	}
	return false;
}

function link_email( $email )
{
        return hlink("mailto:%20$email",$email);
}

function link_bedreg($label="BedReg",$type="Link")
{
    return hlink(AG_BEDREG_URL,$label,$type);
}
 
function link_agency_home($label='',$type='Link',$options='')
{
    global $agency_home_url;
    $label=orr($label,$GLOBALS['AG_TEXT']['LINK_HOME'] );
    return hlink( $agency_home_url, $label, $type, $options );
}

function link_organization_home($label="",$type="Link")
{
    global $organization_home_url;
    $label=orr($label,$GLOBALS['AG_TEXT']['LINK_ORG_HOME'] );
    return hlink( $organization_home_url, $label, $type,'target="_blank"' );
}

function link_agency_donate($label='',$type='Button',$options='')
{
	global $agency_donate_url;
	return $agency_donate_url;
	if (strpos($options,'target=')===false) {
		$options .= ' target="_blank"';
	}
	return hlink($agency_donate_url,orr($label,'<img src="http://images.sourceforge.net/images/project-support.jpg" width="88" height="32" border="0" alt="Donate to AGENCY" />','BUTTON',' target="_BLANK"'),$options);
}

function link_agency_public_home($label="",$type="Link",$options='')
{
    global $agency_public_home_url;
    $label=orr($label,'AGENCY' );
	if (strpos($options,'target=')===false) {
		$options .= ' target="_blank"';
	}
    return hlink( $agency_public_home_url, $label, $type, $options );
}

function link_homes( $sep="<br />\n")
{
    return link_agency_home() . $sep . link_organization_home();
}

function link_entry( $label="Browse Gatekeeping", $type="Link")
{
	global $entry_browse_url;
	return hlink( $entry_browse_url, $label, $type);
}

function link_admin( $label="AGENCY Administration", $type="Link")
{
	$perm = has_perm('admin','RW') || has_perm('update_shamis') || has_perm('mip_export');
	return hlink_if( AG_AGENCY_ADMIN_URL, $label, $perm,$type);
}

function link_unduplication($label='',$step="")
{
      global $undups_url;
	$mo_def=get_def(AG_MAIN_OBJECT_DB);
	$mo_noun=$mo_def['singular'];
	$label = orr($label,'Unduplicate '.ucwords($mo_noun).'s');
      $undups_url .= $step ? "?step=$step" : "";
      $can_undup = ($step=="undup" || $step=="undup_overlooked") ? can_undup_db() : can_undup();
      return hlink_if($undups_url,$label,$can_undup);
}

function link_wiki($db_title,$label='',$options='')
{
	if (!AG_WIKI_BASE_URL) {
		return false;
	}
	$label = orr($label,str_replace('_',' ',$db_title));
	if (strpos($options,'target=')===false) {
		$options .= ' target="_blank"';
	}
	return hlink(AG_WIKI_BASE_URL.$db_title,$label,'',$options);
}

function link_wiki_public($db_title,$label='',$options='')
{
	if (!AG_WIKI_PUBLIC_BASE_URL) {
		return false;
	}
	$label = orr($label,str_replace('_',' ',$db_title));
	if (strpos($options,'target=')===false) {
		$options .= ' target="_blank"';
	}
	if (strpos($options,'class=')===false) {
		$options .= ' class="linkWikiPublic"';
	}
	return hlink(AG_WIKI_PUBLIC_BASE_URL.$db_title,$label,'',$options);
}

function link_last($file,$label="Last")
{
	return hlink( orr($file,$_SERVER['PHP_SELF']) . "?action=last", $label);
} 

function link_first($file, $label="First")
{
	return hlink( orr($file,$_SERVER['PHP_SELF']) . "?action=first", $label);
} 

function link_prev($file,$label="Previous")	
{				
	global $pos;
	return hlink( orr($file,$_SERVER['PHP_SELF']) . "?action=back&pos=$pos", $label);
}
       
function link_next($file, $label="Next")
{
	global $pos;
    $file = orr($file, $_SERVER['PHP_SELF']);
    return hlink( orr($file,$_SERVER['PHP_SELF']) . "?action=forward&pos=$pos", $label);
} 

function button( $label="Submit", $type='', $name='',$value='', $onClick='' , $options=null )
{
	$type = orr($type,'submit');
    return "<input type=\"$type\" value=\"$label\""
    . ($name ? " name=\"$name\"" : "" )
    . ($value ? " value=\"$value\"" : "" )
    . ($onClick ? " onclick=\"$onClick\"" : "" )
    . $options
    . "/>\n";
}

function button_if( $label="Submit", $type="submit", $name="",$value="", $onClick="",$condition )
{
    return $condition 
				? button( $label, $type, $name, $value, $onClick )
				: dead_link($label);
}

function button_link( $url, $label )
{
    return formto( $url ) . button( $label ) . formend();
}

function hlink( $url, $text="", $type="Link", $options=null )
{
	//replacing & w/ &amp; etc
	$url = htmlentities($url);

      $type=ucfirst($type);
	$text = orr($text, $url );
	if ($type=="Button")
	return (button_link( $url, $text ));
	else
	return ("<a href=\"$url\" $options>$text</a>");
}

function hlink_if( $url,$text='',$link_if=true,$type='Link',$options='')
{
	/*
	 * Wrapper function for hlink()
	 *
	 * if $link_if is true, display link,
	 * else show grayed-out text
	 */

	return $link_if
			? hlink($url,$text,$type,$options)
			: dead_link(orr($text,$url));
}

function httplink( $httpurl, $text )
{
    return ($hlink("http://" . urlencode("$httpurl"), $text ) );
}

function httpimage( $url, $width=NULL, $height=NULL, $border=0, $options="" )
{
    return '<img src="'.$url .'" border="'.$border.'" '
		. (($height===NULL) ? '' : 'height="'.$height.'" ')
		. (($width===NULL)  ? '' : 'width="'.$width.'" ')
	. ($options ? " $options" : "")
	.  ' />';
}

function link_style_sheet($file,$media='screen',$opts='',$abs=false)
{
	global $AG_HEAD_TAG,$agency_style_directory;

	$offset=$abs ? '' : $agency_style_directory . '/';
	$link = '<link rel="stylesheet" href="'.$offset .$file.'" type="text/css" media="'.$media.'"'.$opts.'/>'."\n";
	if (!strstr($link,$AG_HEAD_TAG)) {
		$AG_HEAD_TAG .= $link;
	}
	return;
}

function link_javascript($file,$absolute = false)
{
	global $AG_HEAD_TAG,$agency_javascript_directory;

	$file = $absolute ? $file : $agency_javascript_directory.'/'.$file;

	$js = Java_Engine::get_js('',' src="'.$file.'"');
	if (!strstr($js,$AG_HEAD_TAG)) {
		$AG_HEAD_TAG .= $js;
	}
	return;
}

function agency_logo_small($options='')
{
	global $AG_IMAGES;
	if (strpos($options,'alt=')===false) {
		$options .= ' alt="AGENCY Logo"';
	}
	if (strpos($options,'border=')===false) {
		$options .= ' border="0"';
	}
	if (strpos($options,'class=')===false) {
		$options .= ' class="logo"';
	}
    return link_agency_public_home(html_image($AG_IMAGES['AGENCY_LOGO_SMALL'],$options));
}

function agency_logo_medium($options='')
{
	global $AG_IMAGES;
	if (strpos($options,'alt=')===false) {
		$options .= ' alt="AGENCY Logo"';
	}
	if (strpos($options,'border=')===false) {
		$options .= ' border="0"';
	}
	if (strpos($options,'class=')===false) {
		$options .= ' class="logo"';
	}
	return link_agency_public_home(html_image($AG_IMAGES['AGENCY_LOGO_MEDIUM'],$options));
}

function organization_logo_small($options='')
{
	global $AG_IMAGES,$AG_TEXT;
	if (strpos($options,'alt=')===false) {
		$options .= ' alt="' . $AG_TEXT['ORGANIZATION_SHORT'] . ' Logo"';
	}
	if (strpos($options,'border=')===false) {
		$options .= ' border="0"';
	}
	if (strpos($options,'class=')===false) {
		$options .= ' class="logo"';
	}
    return link_organization_home(html_image($AG_IMAGES['ORGANIZATION_LOGO_SMALL'],$options));
}

function organization_logo_medium($options='')
{
	global $AG_IMAGES,$AG_TEXT;
	if (strpos($options,'alt=')===false) {
		$options .= ' alt="' . $AG_TEXT['ORGANIZATION_SHORT'] . ' Logo"';
	}
	if (strpos($options,'border=')===false) {
		$options .= ' border="0"';
	}
	if (strpos($options,'class=')===false) {
		$options .= ' class="logo"';
	}
    return link_organization_home(html_image($AG_IMAGES['ORGANIZATION_LOGO_MEDIUM'],$options));
}

function doc_link($filename,$label='') {

// Quick & Dirty add to allow links to documents (for onscreen help)
// FIXME: move to table with attachment handling

//	global $AG_HELP_DOC_LOCATION;

	// FIXME:  is it worth testing first to see if file exists?
	return hlink(AG_HELP_DOC_LOCATION . '/' . basename($filename),orr($label,$filename),NULL,'target="_blank"');
}


?>
