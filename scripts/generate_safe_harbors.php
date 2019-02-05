#!/usr/bin/php -q
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


// needs lots of memory for big files
ini_set('memory_limit','256M');

if (phpversion() < '4.3') {
	die("\n".'ERROR: Safe Harbors export requires PHP >= 4.3'."\n\n\n");
}

$off = dirname(__FILE__).'/../';

$date_start = $_SERVER['argv'][1];
$date_end = $_SERVER['argv'][2];

require_once $off.'command_line_includes.php';
require_once $off.'safe_harbors_export.php';
$ST = getmicrotime();
$programs = array('PROG'); //can be multiple, define below
if (!(dateof($date_start) and dateof($date_end))) {
	print 'Can\'t run without start and end dates!!!'."\n";
	print 'Correct usage: '.$_SERVER['argv'][0].' [start_date] [end_date]'."\n\n\n";
	exit;
}

//COMMENT OUT TO TEST: 

if ($_SERVER['AGENCY_SCRIPT_USER'] !== 'AUTH') {
	log_error('Unauthorized user ('.$_SERVER['USER'].') attempting to run '.$_SERVER['argv'][0]);
	exit;
 } 


/* end passed variables */

/* AGENCY DB constants */
define('SAFE_HARBORS_CLIENT_VIEW','export_safe_harbors_client');
define('SAFE_HARBORS_INCOME_VIEW','export_safe_harbors_income');

/* export constants */
//FIXME:  define('HUD_XML_NAMESPACE_SCHEMA','http://iww.desc.org/docs/HUD_HMIS_XML_Schema_V2.61_modified.xsd');
define('HUD_DATABASE_ID_NUM','2000');
define('HUD_DATABASE_NAME',$GLOBALS['AG_TEXT']['ORGANIZATION']);
define('HUD_CONTACT_FIRST','JOHN');
define('HUD_CONTACT_LAST','DOE');
define('HUD_CONTACT_PHONE','1234567890');
define('HUD_CONTACT_EXT','000');
define('HUD_CONTACT_EMAIL','');
define('SAFE_HARBORS_REMOVED_GENDER','2006-12-01 00:00:00'); //gender removed from consent form

//this needs confirmation, and is only of use if reporting a single service per night
define('HUD_ORG_SERVICE_TYPE','4'); 
$HUD_CURRENT_EXPORT_ID = sql_get_sequence('seq_safe_harbors_export_id'); 
$HUD_PROGRAM_INFO = array(
				  /*
				   * Program Type code Options:
				   *	1 = Emergency shelter
				   *	2 = Transitional housing
				   *	3 = Permanent supportive housing
				   *	4 = Street outreach
				   *	5 = Homeless prevention
				   *	6 = Services only type of program
				   *	7 = Other
				   *
				   * Target population Options:
				   * 1 = SF Single Female 18 and over
				   * 2 = SM Single Male 18 and over
				   * 3 = SMF Single Male and Female 18 and over
				   * 4 = FC Families with Children
				   * 5 = YM Unaccompanied young males under 18
				   * 6 = YF Unaccompanied young females under 18
				   * 7 = YFM Unaccompanied young females and males under 18
				   *
				   */

				  /* Fill in with real values */
				  'PROG' => array('id'=>'0000',
							'name'=>'Program Name',
							'fips'=>'000000',
							'facility'=>'0000',
							'coc'=>'0000',
							'type'=>'1',
							'individual_family'=>'1',
							'target_pop'=>'3',
							'parent_id'=>'0'
							)
					// , 'PROG2' => array('id'=> ...
				  );

$generated_at = datetimeof('now','ISO');

/* get clients for time period */
$clients = hud_get_clients($date_start,$date_end);

?>
<?php print '<?xml version="1.0" encoding="UTF-8"?>'."\n" /* php was having trouble with the "<?xml" tag */ ?>
<dataroot xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="<?php print HUD_XML_NAMESPACE_SCHEMA ;?>" generated="<?php print $generated_at; ?>">
	<SourceDatabase>
	<?php
            /*
		 * The database fields below actually refer to the agency names and an agency identifier. Safe Harbors
		 * will pre-register all agencies in the database and generate an agency identifier for each agency.
		 * The <databaseIDNum> field will represent this agency identifier and is to be used by the
		 * agency in all transmissions to Safe Harbors. You must also include the matching agency name provided
		 * by Safe Harbors.

		 * The contact information is entered by the agency to reflect the current contact person, phone, etc.
		 */

	?>

		<DatabaseID>
			<DatabaseIDNum><?php print HUD_DATABASE_ID_NUM;?></DatabaseIDNum>
		</DatabaseID>
		<DatabaseName><?php print HUD_DATABASE_NAME; ?></DatabaseName>
		<DatabaseContactFirst><?php print HUD_CONTACT_FIRST; ?></DatabaseContactFirst>
		<DatabaseContactLast><?php print HUD_CONTACT_LAST; ?></DatabaseContactLast>
		<DatabaseContactPhone><?php print phone_of(HUD_CONTACT_PHONE,'num')?></DatabaseContactPhone>
		<DatabaseContactExtension><?php print HUD_CONTACT_EXT;?></DatabaseContactExtension>
		<DatabaseContactEmail><?php print HUD_CONTACT_EMAIL; ?></DatabaseContactEmail>
		<?php
		     /*
			* The export fields below are our scheduled uploads to Safe Harbors. THey will contain the date and time
			* of the report.
			*/
		?>

		<Export>
			<ExportID>
				<ExportIDNum><?php print $HUD_CURRENT_EXPORT_ID;?></ExportIDNum>
			</ExportID>
			<ExportDate><?php print datetimeof(dateof('now'),'ISO');?></ExportDate>
			<ExportPeriodBegin><?php print datetimeof($date_start,'ISO');?></ExportPeriodBegin>
			<ExportPeriodEnd><?php print datetimeof($date_end,'ISO');?></ExportPeriodEnd>
			<ExportHashing>0<?php /* FIXME: what is this?? */?></ExportHashing>
		</Export>
		<?php
			/*
			 * Safe Harbors will pre-register all programs in the HMIS database. The transmission from the agency
			 * will reflect the program identifier used by Safe Harbors for the specific program. The agency
			 * will include at least the ProgramIDNum and ProgramName fields as recorded by Safe Harbors.

			 * Safe Harbors requires information on the following program fields:
			 *	- ProgramTypeCode
			 *	- TargetPopulation
			 */
		?>
		<?php foreach ($programs as $program): ?>

		<Program>
			<ProgramID>
				<ProgramIDNum><?php print $HUD_PROGRAM_INFO[$program]['id']; ?></ProgramIDNum>
			</ProgramID>
			<ProgramName><?php print $HUD_PROGRAM_INFO[$program]['name']; ?></ProgramName>
			<FIPSCode><?php print $HUD_PROGRAM_INFO[$program]['fips']; ?></FIPSCode>
			<FacilityCode><?php print $HUD_PROGRAM_INFO[$program]['facility']; ?></FacilityCode>
			<COCCode><?php print $HUD_PROGRAM_INFO[$program]['coc']; ?></COCCode>
			<ProgramTypeCode><?php print $HUD_PROGRAM_INFO[$program]['type']; ?></ProgramTypeCode>
				      <?php
				          /*
			<ProgramTypeCodeOther/>
					    */
			?>
			<IndividualFamilyCode><?php print $HUD_PROGRAM_INFO[$program]['individual_family']; ?></IndividualFamilyCode>
			<TargetPopulation><?php print $HUD_PROGRAM_INFO[$program]['target_pop']; ?></TargetPopulation>
			<ProgramParentID>
				<ProgramParentIDNum><?php print $HUD_PROGRAM_INFO[$program]['parent_id']; ?></ProgramParentIDNum>
			</ProgramParentID>
		</Program>
		<?php endforeach;	?>

		<?php
		/*
		 *	Include a ClientExport element set for each person that you are reporting. The PersonIDNum
		 *	represents your unique clientID number for the person. The ExportIDNum is the current report 
		 *	number defined in the Export section above.
		 */
		?>
		
		<?php foreach ($clients as $client): set_time_limit(240); ?>

		<ClientExport>
			<CEPersonID>
				<CEPersonIDNum><?php print $client->safe_harbors_id; ?></CEPersonIDNum>
			</CEPersonID>
			<CEExportID>
				<CEExportIDNum><?php print $HUD_CURRENT_EXPORT_ID;?></CEExportIDNum>
			</CEExportID>
		</ClientExport>
		<?php endforeach; ?>

		<?php
		/*
		 *	This is the end of the ClientExport section. Remember we created an entry for each person in the
		 *	<ClientExport> section.
		 *	Now we must create a more detailed record for each person that maps to the PersonIDNum field 
		 *	in the <ClientExport> above. You will have as many <client>...</client> sections as you had
		 *	entries in the <ClientExport> section.
		 */
		?>

		<?php foreach ($clients as $client): ?>

		<Client>
			<PersonID>
				<PersonIDNum><?php print $client->safe_harbors_id; ?></PersonIDNum>
			</PersonID>
			<?php if (!be_null($client->name_first)): ?><LegalFirstName><?php print $client->name_first; ?></LegalFirstName><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->name_middle)): ?><LegalMiddleName><?php print $client->name_middle; ?></LegalMiddleName><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->name_last)): ?><LastName><?php print $client->name_last; ?></LastName><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->name_suffix)): ?><LegalSuffix><?php print $client->name_suffix; ?></LegalSuffix><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->ssn)): ?><SocialSecurityNumber>
				<SsNumberGiven><?php print $client->ssn; ?></SsNumberGiven>
			</SocialSecurityNumber><?php endif; echo "\n"; ?>
			<SocialSecurityNumberQualityCode><?php print $client->get_ssn_quality(); ?></SocialSecurityNumberQualityCode>
			<?php if (!be_null($client->dob)): ?><DateOfBirth><?php print $client->dob; ?></DateOfBirth><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->ethnicity)): ?><Ethnicity><?php print $client->ethnicity==6 ? 1 : 0; ?></Ethnicity><?php endif; echo "\n"; ?>
			<?php if (isset($client->gender)): ?><Gender><?php print $client->gender; ?></Gender><?php endif; echo "\n"; ?>
			<?php if (!be_null($client->ethnicity)): ?><Race><?php print $client->ethnicity; ?></Race><?php endif; echo "\n"; ?>
			
			<?php if (!be_null($client->immigrant_status)):  ?> 
			  <ImmigrantRefugeeStatus><?php print $client->immigrant_status; ?> </ImmigrantRefugeeStatus>
			  <?php endif; ?>


			<?php foreach ($client->bed_nights as $night): ?>

			<ProgramParticipation>
				<?php
			          /*
				     *      Each time a client enrolls in a program, you will create a new program participation
				     *	number for that enrollment. In the example below, the client used your 
				     *	program on 3 different occasions and it shows 3 <ProgramParticipation> records.
				     *
				     *	For each enrollment, you will have a distinct <ProgramParticipation> record to 
				     *	reflect this. The ProgramParticipationIDNum is your unique association number between 
				     *	the client and each enrollment.
				     *
				     *	Note that for each enrollment, one or more services could have been rendered to 
				     *	the client.
				     *
				     *	For each of the 3 times the agency encountered the client in the example below, 
				     *	the agency will gather some basic information and/or additional information about 
				     *	the client. The additional information is captured in the <ClientHistorical> section.
				     */
					
			      ?>

				<ProgramParticipationID>
					<?php
			                 /*
						*	Your number indicating the client's involvement within your program.
						*
						*  use bed_id until better explanation
						*/
					?><ProgramParticipationIDNum><?php print $night->bed_id; ?></ProgramParticipationIDNum>
				</ProgramParticipationID>
				<?php
					/*
					 *	The PPProgramID is the number assigned by Safe Harbors for this specific program.
					 */
				?>

				<PPProgramID>
					<PPProgramIDNum><?php print $HUD_PROGRAM_INFO[$night->program]['id'] ?></PPProgramIDNum>
				</PPProgramID>
				<PPPersonID>
					<PPPersonIDNum><?php /* Your unique client identifier */ print $client->safe_harbors_id; ?></PPPersonIDNum>
				</PPPersonID>

				<?php
					/*
					 *	The following fields up to the ClientHistory section are captured once for each
					 *	enrollment within a program. 
					 */
				?>

				<VeteranStatus><?php print $client->veteran_status; ?></VeteranStatus>
				<DisablingCondition><?php print $client->has_disabling_condition(); ?></DisablingCondition>
				<?php if ($night->prior): ?>

				<PriorResidence><?php print $night->prior['residence_code']; ?></PriorResidence>
				<?php if ($night->prior['other']): ?><PriorResidenceOther><?php print $night->prior['other']; ?></PriorResidenceOther><?php endif; ?>

				<LengthOfStayAtPriorResidence><?php print $night->prior['length_of_stay']; ?></LengthOfStayAtPriorResidence>
				<?php if (!be_null($night->prior['zip'])): ?>

				<LastPermanentZipCode><?php print $night->prior['zip']['code']; ?></LastPermanentZipCode>
				<ZipQualityCode><?php print $night->prior['zip']['quality']; ?></ZipQualityCode>
				<?php endif /* end zip */; ?>
                     
				  <?php if (!be_null($night->prior['city'])):  ?> 
				    <PreviousCity><?php print $night->prior['city']; ?></PreviousCity>
				  <?php endif; ?>
				  <?php if (!be_null($night->prior['state'])):  ?> 
				    <PreviousState><?php print $night->prior['state']; ?></PreviousState>
				  <?php endif; ?>  
				    
				<?php endif /* end prior residence */;
				/* 
				 *	Residence Prior to program entry Options:
				 *		1 = Emergency Shelter
				 *		2 = Transitional housing for homeless persons
				 *		3 = Permanent housing for homeless persons
				 *		4 = Psychiatric hospital or other psychiatric facility
				 *		5 = Substance abuse treatment facility or detox center
				 *		6 = Hospital
				 *		7 = Jail, prison or juvenile detention facility
				 * 		10 = Room, apartment or house that you rent
				 *		11 = Apartment or house that you own
				 *		12 = Staying or living in a family member's room, apartment, or house
				 *		13 = Staying or living in a friend's room, apartment, or house
				 *		14 = Hotel or motel paid for without emergency shelter voucher
				 *		15 = Foster care home or foster care group home
				 *		16 = Place not meant for habitation
				 *		17 = Other
				 *		8 = Don't know
				 *		9 = Refused
				 *
				 *	Length of Stay in previous place options:
				 *		1 = One week or less
				 *		2 = More than one week, but less than one month
				 *		3 = One to three months
				 *		4 = More than three months, but less than one year
				 *		5 = One year or longer
				 *
				 *	Zip Quality code Options:
				 *		1 = Full Zip code recorded
				 *		8 = Don't know
				 *		9 = Refused
				 *
				 */
				?>

				<ProgramEntryDate><?php print datetimeof($night->bed_date,'ISO'); ?></ProgramEntryDate>
				<ProgramExitDate><?php print datetimeof(next_day($night->bed_date),'ISO'); ?></ProgramExitDate>
				<HeadOfHousehold>1<?php /* True? */?></HeadOfHousehold>
				<?php
				/*
				 *	Information collected in the ClientHistorical section is data you collect
				 *	in addition to the basic elements listed above for the enrollment. You will gather 
				 *	this information over time, and include it in your report
				 *	in this <ClientHistorical> section. You will have a clientHistorical section for Intake,
				 *	Exit, and Follow Up(s).
				 *
				 *	In the first record shown <ClientHistoryIDNum>=4, information is captured during
				 *	the intake stage (DataCollectionType=1). Include those elements that you were able to
				 *	capture.
				 */
				?>

				<ClientHistorical>
					<ClientHistoryID>
						<ClientHistoryIDNum><?php print sql_nextval('seq_safe_harbors_client_history_id'); ?></ClientHistoryIDNum>
					</ClientHistoryID>
					<DataCollectionType>1</DataCollectionType>
				      <?php
				           /*
						* Designates whether the data was collected at intake, exit, or followup
						*		Options:
						*			1 = Intake
						*			2 = Exit
						*			3 = Followup
						*/
				      ?>

					<DataCollectionDate><?php print datetimeof($client->last_changed_at,'ISO'); ?></DataCollectionDate>
 				      <?php
			 	          /*
					     * <PhysicalDisability/>
					     *
					     *		Physical Disability Options:
					     *			0 = No
					     *			1 = Yes
					     */
				      ?>

																		  
					<?php //added March 2008
					?>				
				      <?php if (!be_null($client->chronic_homeless_status)):  ?> 
						<ClientChronicEpisodes><?php print $client->chronic_homeless_status; ?> </ClientChronicEpisodes>
					<?php endif; ?>

					<ReasonForLeaving>2</ReasonForLeaving>
				      <?php
				          /*
					     * Reason for Leaving Options:
					     *	1 = Left for a housing opportunity before completing program
					     *	2 = Completed program
					     *	3 = Non-payment of rent/occupancy charge
					     *	4 = Non-compliance with program
					     *	5 = Criminal activity/violence/destruction of property
					     *	6 = Reached maximum time allow in program (same as completed program)
					     *	7 = Needs could not be met by program
					     *	8 = Disagreement with rules
					     *	9 = Death
					     *	10 = Unknown/disappeared
					     *	11 = Other
					     */
				      ?>

					
				      <?php
				          /*
					     * <HealthStatus/>
					     *
					     * General Health Status Options:
					     *	1 = Excellent
					     *	2 = Very Good
					     *	3 = Good
					     *	4 = Fair
					     *	5 = Poor
					     *	8 = Don't Know
					     */
				      ?>

				      <?php if (isset($client->income->monthly_income_total)): /*Total Income*/ ?> 
					<TotalIncome><?php print orr($client->income->monthly_income_total,0.00); ?></TotalIncome>
					<?php endif; ?>

					<?php if (isset($client->substance_abuse)): ?>

					<SubstanceAbuseProblem><?php print $client->substance_abuse; ?></SubstanceAbuseProblem>
				      <?php endif;
				           /*
						* Substance Abuse Problem Options:
						*	1 = Alcohol abuse
						*	2 = Drug abuse
						*	3 = Dually diagnosed
						*
						* 
						* <SubstanceAbuseIndefinite/>
						*/
				      ?>
					
					<?php if (!is_null($client->domestic_violence)): ?><DomesticViolence><?php print $client->domestic_violence; ?></DomesticViolence>
					<DomesticViolenceHowLong>8</DomesticViolenceHowLong>
					<?php endif;?>

					<?php if (!is_null($client->income->employed)): ?>
					<CurrentlyEmployed><?php print $client->income->employed; ?></CurrentlyEmployed>
					<?php endif; ?>

				      <?php
				          /*
					     * Employment Tenure Options:
					     *	1 = Permanent
					     *	2 = Temporary
					     *	3 = Seasonal
					     *
					     * <HoursWorkedLastWeek/>
					     * <EmploymentTenure/>
					     */
				      ?>

				      <?php if (!be_null($client->school_status)):  ?> 
						<CurrentlyInSchool><?php print $client->school_status; ?> </CurrentlyInSchool>
					<?php endif; ?>

					
				      <?php if (!be_null($client->highest_education)):  ?> 
						<HighestSchoolLevel><?php print $client->highest_education; ?> </HighestSchoolLevel>
					<?php endif; ?>

			
						<?php
				          /*
					     * <ChildCurrentlyEnrolledInSchool/>
					     *
					     * <Veteran>
					     *	<ServiceEra/>
					     *	<MilitaryServiceDuration/>
					     *	<ServedInWarZone/>
					     *	<WarZone/>
					     *	<MonthsInWarZone/>
					     *	<ReceivedFire/>
					     *	<MilitaryBranch/>
					     *	<MilitaryBranchOther/>
					     *	<DischargeType/>
					     * </Veteran>
					     *
					     * <DegreeCode/>
					     */
				      ?>
					

				      <?php
				          /*
					<HIVAIDSStatus/>
					<MentalHealthProblem/>
					<MentalHealthIndefinite/>
					    */
					?>
					<?php if ($client->income->has_income_data): /* Begin income section*/ ?>

					<?php foreach ($client->income->other as $other): /*Other income loop*/ ?>
					<NonCashSourceCode><?php print $other; ?></NonCashSourceCode>

					<?php endforeach /* end other source loop */; ?>

					<?php foreach ($client->income->incomes AS $inc_info): ?>

					<IncomeAndSources>
						<IncomeSourceCode><?php print $inc_info[0]; ?></IncomeSourceCode>
						<Amount><?php print orr($inc_info[1],0); ?></Amount>
					</IncomeAndSources>
					<?php endforeach; /*end income loop */ ?>


				      <?php endif /* end income section */;
					
				          /*
					     * Non-cash source Options:
					     *	1 = Are you receiving Food Stamps or money for food on a benefits card?
					     *	2 = Are you receiving Medicaid?
					     *	3 = Are you receiving Medicare?
					     *	4 = Are you receiving State Children’s Health Insurance Program?
					     *	5 = Are you receiving WIC?
					     *	6 = Are you receiving Veterans Administration Medical Services?
					     *	7 = Are you receiving TANF child care services?
					     *	8 = Are you receiving TANF transportation services?
					     *	9 = Are you receiving other TANF-funded services?
					     *	10 = Are you receiving rental assistance, such as Section 8 vouchers or public housing?
					     *	11 = Other Source	
					     */
				      ?>

				</ClientHistorical>
				<?php /* Include in here all services offered during this specific program enrollment. */ 
					foreach ($night->get_services() as $service): ?>

				<ServiceEvent>
					<DateOfService><?php print datetimeof($service->date_start,'ISO'); ?></DateOfService>
					<DateOfServiceEnd><?php print datetimeof($service->date_end,'ISO'); ?></DateOfServiceEnd>
					<QuantityOfService><?php print $service->quantity; ?></QuantityOfService>
					<ServiceUnit><?php print $service->unit; ?></ServiceUnit>
					<TypeOfService><?php print $service->type; ?></TypeOfService>
				      <?php
				          /*
					<AIRSCode/>
					    */
					?>

				</ServiceEvent>
				<?php endforeach; ?>

			</ProgramParticipation>
			<?php /* tmp */ endforeach; ?>

		</Client>

		<!-- End of client record -->
		<?php /* tmp */ endforeach; ?>

	</SourceDatabase>
</dataroot>
<!-- Execution time: <?php print getmicrotime() - $ST;?> seconds -->
