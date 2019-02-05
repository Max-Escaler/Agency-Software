<?php

$engine['config_staff_password']=array(

'perm'=>'super_user',

'list_order'=>array('config_staff_password_id'=>true),
'allow_edit'=>false,
'list_fields'=>array(
	'added_at',
	'default_expiration_days',
	'allow_override_default_expiration',
	'expiration_warning_days',
	'delete_excess_passwords_on_change'
),

);

?>
