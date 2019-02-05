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


$engine['cod_assessment'] = array('singular'=>'COD Assessment',
					    'perm'=>'clinical,cd',
					    'allow_edit'=>false,
					    'list_fields' => array('assessment_date','assessment_by','cod_quadrant_code'),
					    'fields'=>array(
								  'assessment_by'   => array('default'=>'$GLOBALS["UID"]'),
								  'cod_quadrant_code' => array('lookup_format'=>'radio_v',
													 'label'=>'COD Quadrant',
													 'lookup_order'=>'CODE',
													 'show_lookup_code'=>'BOTH'
													 ),
								  'dal_id' => array('is_html'=>true,
											  'value'=>'be_null($x) ? link_engine(array("object"=>"dal","action"=>"add","rec_init"=>array("client_id"=>$rec["client_id"],"dal_code"=>"631","dal_date"=>$rec["assessment_date"],"performed_by"=>$rec["assessment_by"])),"Add COD Assessment DAL") : elink("dal",$x,"View DAL")'),
								  'export_kc_id'=>array('display'=>'display',
												'display_add'=>'hide')
								  )
					    );
					   

?>