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


$engine['medical_health'] = array(
				 'singular' => 'Medical Health Record',
				 //				 'list_fields'=>array('dispensed_on','dispensed_by','nicotine_dosage_code','nicotine_count'),
				 'rec_init_from_previous' => true,
				 'fields'=>array(
						     'medical_health_date'=>array('default'=>'NOW'),
						     'hiv_positive_symptomatic_asymptomatic_code'=>array('display' => 'hide'),
						     'stomach_intestine_infection_code'=>array('label' => 'Stomach or Intestine Infection'),
						     'shortness_of_breath_asthma_code'=>array('label' => 'Asthma'),
						     'shortness_of_breath_emphysema_code'=>array('label' => 'Emphysema'),
						     'dental_problems_infection_code'=>array('label' => 'Dental Problems or Infections'),
						     'cuts_injuries_code'=>array('label' => 'Cuts or Injuries'),
						     'hepatitis_type_code'=>array('valid'=>
											    array('!(be_null($x) and ($rec["hepatitis_code"] == "YES" or $rec["hepatitis_code"] == "YES3"))' =>'Please input the Hepatitis type if the client has Hepatitis')),
						     

						     'other_01_code'=>array('label' => 'Other Health Issue-1',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_01_detail"]))'=>' You have filled in an Other Health Issue-1 Detail. Please input Other Health Issue-1 or remove Detail.')),
						     'other_01_detail'=>array('label' => 'Other Health Issue-1 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_01_code"] == "YES" or $rec["other_01_code"] == "YES3"))' =>'Please input details for Other Health Issue-1')),

						     'other_02_code'=>array('label' => 'Other Health Issue-2',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_02_detail"]))'=>' You have filled in an Other Health Issue-2 Detail. Please input Other Health Issue-2 or remove Detail.')),
						     'other_02_detail'=>array('label' => 'Other Health Issue-2 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_02_code"] == "YES" or $rec["other_02_code"] == "YES3"))' =>'Please input details for Other Health Issue-2')),

						     'other_03_code'=>array('label' => 'Other Health Issue-3',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_03_detail"]))'=>' You have filled in an Other Health Issue-3 Detail. Please input Other Health Issue-3 or remove Detail.')),
						     'other_03_detail'=>array('label' => 'Other Health Issue-3 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_03_code"] == "YES" or $rec["other_03_code"] == "YES3"))' =>'Please input details for Other Health Issue-3')),

						     'other_04_code'=>array('label' => 'Other Health Issue-4',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_04_detail"]))'=>' You have filled in an Other Health Issue-4 Detail. Please input Other Health Issue-4 or remove Detail.')),
						     'other_04_detail'=>array('label' => 'Other Health Issue-4 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_04_code"] == "YES" or $rec["other_04_code"] == "YES3"))' =>'Please input details for Other Health Issue-4')),

						     'other_05_code'=>array('label' => 'Other Health Issue-5',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_05_detail"]))'=>' You have filled in an Other Health Issue-5 Detail. Please input Other Health Issue-5 or remove Detail.')),
						     'other_05_detail'=>array('label' => 'Other Health Issue-5 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_05_code"] == "YES" or $rec["other_05_code"] == "YES3"))' =>'Please input details for Other Health Issue-5')),


						     'other_06_code'=>array('label' => 'Other Health Issue-6',
										    'valid' =>array('!(be_null($x) and !be_null($rec["other_06_detail"]))'=>' You have filled in an Other Health Issue-6 Detail. Please input Other Health Issue-6 or remove Detail.')),
						     'other_06_detail'=>array('label' => 'Other Health Issue-6 Detail', 
											'valid'=> array('!(be_null($x) and ($rec["other_06_code"] == "YES" or $rec["other_06_code"] == "YES3"))' =>'Please input details for Other Health Issue-6'))						    		     
						
						     )
					    );
  
					    ?>
