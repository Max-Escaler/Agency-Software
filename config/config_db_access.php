<?php

$engine['db_access']=array(
	'perm'=>'db_access',
	'fields'=>array(
		//FIXME: AGENCY doesn't properly handle IP type, so setting to varchar for now
		'access_ip'=>array(
			'data_type'=>'varchar'
		)
	)
);

?>
