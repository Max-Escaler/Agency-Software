<?php

$engine['family_member']=array(
	'add_link_show'=>false,
	'singular'=>'Family Dependent',
	'fields'=>array(
		'household_head_id'=>array(
			'data_type'=>'client'
		),
		'in_household_manual'=>array('comment'=>'Leave blank except for unusual cases'),
		'is_dependent_manual'=>array('comment'=>'Leave blank except for unusual cases'),
		'client_id'=>array(
			'label'=>'Dependent'
		)
	)
);

?>
