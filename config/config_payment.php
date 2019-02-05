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


$engine['payment'] = array(
	'add_another' => true,
	'perm'     => 'rent,housing',
	'list_fields'=>array('client_id','payment_date','amount','payment_type_code','receipt'),
	'fields'   => array(
		'check_from'=>array( 'comment'=>'Only for third-party checks' ),
		'housing_project_code' => array(
			'default'=>'EVAL: last_residence_own($rec_init["client_id"])',
			'display_add' => 'hide'),
		'amount'=>array( 
			'data_type'=>'currency',
			'value_format_list'=>'sql_true($rec["is_void"]) ? strike($x) : ((($x <0) ? red($x) : $x) . smaller(" (".link_engine(array("object"=>"payment","id"=>$rec["payment_id"],"action"=>"void")).")",2) )',
			'total_value_list'=>'sql_true($rec["is_void"]) ? 0 : $x'),
	    'payment_form_code' => array(
			'valid' => array(
				'($x == "CHECK_3P" && !be_null($rec["check_from"])) || $x != "CHECK_3P"' => 'Payment From must be filled in for 3rd-Party Checks')),
		//FIXME: this should be handled generically as a system field:
	    'void_reason_code' => array('display_add' => 'hide','display_edit'=>'hide'),
		// FIXME: report ID (9) hardcoded in link below.  Should be fixed when reports can be identified by code...
		'receipt' => array('display_add' => 'hide',
			'value_format' => 'sql_true($rec["is_void"]) ? httpimage($GLOBALS["AG_IMAGES"]["RECORD_VOIDED"],30,30,0) : link_report("PAYMENT_RECEIPT","Print Receipt",array("pid" => $rec["payment_id"]),"generate","payment_receipt.odt")',
			'label'=>''
		     )
	    )
);

// This might be useful if you import your payment records from another system, and don't want them changed
/*
foreach ( array('agency_project_code','payment_date','payment_form_code','payment_document_number',
		   'amount','is_subsidy','posted_comment','check_from') as $tmp_f ) {

	$engine['payment']['fields'][$tmp_f]['display_edit'] = 'display';

}
*/

?>
