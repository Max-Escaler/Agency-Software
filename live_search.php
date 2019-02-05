<?php

//$AG_FULL_INI = false;
$quiet='Y';
include 'includes.php';

$term=strip_tags(urldecode($_REQUEST['term']));
$type=$_REQUEST['type'];
preg_match('/^[a-z_]*$/i',$type) or die("Bad type passed");
preg_match('/^[a-z_ ,0-9()]*$/i',$type) or die("Bad term passed");

$def=get_def($type);
$order=list_query_order(orr($def['quick_search']['list_order'],$def['list_order']));

if (has_perm($def['perm_view'])) {
	$alias = (($type==AG_MAIN_OBJECT_DB) and array_key_exists(AG_MAIN_OBJECT_DB,$def['fields']))
		? " || COALESCE(name_alias,'')"
		: '';
	$filter=object_qs_filter($term,$type);
	$name = ( $GLOBALS['AG_DEMO_MODE'] and ($type==AG_MAIN_OBJECT_DB))
		? "'XXXXXX'"
		: $type . '_name('.$type.'_id)';
	$query='SELECT ' . $def['id_field'] . ' FROM ' . $def['table'];
	$recs=agency_query($query,$filter,$order,100);
	while ($x=sql_fetch_assoc($recs)) {
		$id=$x[$def['id_field']];
		$results[]=strip_tags(object_label($type,$id) . ' (' . $id. ')');
	}
	echo(json_encode($results));
}

page_close(TRUE); // silent
exit;

?>	
