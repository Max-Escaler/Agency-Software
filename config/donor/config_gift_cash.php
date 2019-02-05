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

$engine["gift_cash"] = array(
				     
				     'allow_delete'=>true,
				     'add_another'=>true,
				     'add_another_and_remember'=>true,
				     'singular'=>'cash gift',
				     'list_fields'=>array('gift_cash_date','gift_cash_form_code','gift_cash_amount','gift_cash_comment'),
				     "title" => 'ucwords($action) . "ing Cash Gift record for " . client_link($rec["donor_id"])',
				     "title_add"=>'ucwords($action) . "ing a new Cash Gift record."',
				     'fields'=>array(
							   'donor_id' => array(
										     'add_main_objects'=>true,
										     'edit_main_objects'=>true,
										     'display'=>'regular',
										     'add_another_remember'=>true),
							   'received_date' => array(
											    'add_another_remember'=>true,
											    'data_type'=>'date_past'),
							   'gift_cash_date' => array(
											     'label'=>'Deposit Date',
											     'add_another_remember'=>true,
											     'data_type'=>'date_past',
											     'confirm'=>array('$action=="edit" || be_null($x)'=>'Warning: If {$Y} is set, this gift will not be exported to MIP')),
							   'gift_cash_amount' => array('label'=>'Amount',
												 'data_type'=>'currency'),
							   'account_code' => array(
											   'add_another_remember'=>true),
							   'response_code' => array(
											    'add_another_remember'=>true,
											    'show_lookup_code'=>'BOTH'),
							   'restriction_code' => array(
												 'add_another_remember'=>true),
							   'skip_thanks' => array(
											  'add_another_remember'=>true),
							   'gift_cash_comment' => array(
												  'add_another_remember'=>true),
							   'gift_cash_form_code' => array(
												    'add_another_remember'=>true,
												    'null_ok'=>false),
							   //			'reference_no' => array(
							   //				'add_another_remember'=>true),
							   'contract_code' => array( 'display' => 'hide' ),

							   'expiration' => array(
											 'add_another_remember'=>true),
							   'authorization_no' => array(
												 'add_another_remember'=>true),
							   'is_anonymous' => array(
											   'add_another_remember'=>true),
							   'mip_export_session_id' => array('display'=>'display',
													'display_add' => 'hide')
							   )
				     );
?>
