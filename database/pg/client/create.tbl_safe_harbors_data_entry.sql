CREATE TABLE tbl_safe_harbors_data_entry (
safe_harbors_data_entry_id	SERIAL PRIMARY KEY,
client_id	INTEGER NOT NULL REFERENCES tbl_client (client_id),
collection_date	DATE NOT NULL,
diag_dd		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_mi		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_alcohol		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_drugs		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_physdis		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_hiv		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_health		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_other		VARCHAR(10)	NOT NULL REFERENCES tbl_l_yes_no_1year (yes_no_1year_code),
diag_other_comment	VARCHAR(80),
rcv_food_stamps	BOOLEAN NOT NULL,
rcv_medicaid	BOOLEAN NOT NULL,
rcv_medicare	BOOLEAN NOT NULL,
rcv_va_health	BOOLEAN NOT NULL,

domestic_violence	BOOLEAN NOT NULL,
armed_forces	BOOLEAN NOT NULL,
comment	TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT false,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT,
CONSTRAINT comment_on_other CHECK ((diag_other='NO' AND diag_other_comment IS NULL) or (diag_other <> 'NO' and diag_other_comment IS NOT NULL))
);

CREATE VIEW safe_harbors_data_entry AS (SELECT * FROM tbl_safe_harbors_data_entry WHERE NOT is_deleted);



/*


To be entered into AGENCY by HCM 
(Note:  this is information that HUD requires us to put into Safe Harbors )
Draft 2-2-11


Have you been diagnosed with:		Did you receive services for this in the past year?

___ Developmental Disability*			Yes	No

___ Mental Illness*				Yes	No

___ Problem with Alcohol			Yes	No

___ Problem with Drugs			Yes	No

___ Physical Disability*			Yes	No

___ AIDS/ HIV					Yes	No

___ Chronic Health Condition*			Yes	No

___ Other Special Needs:_______________	Yes	No

*Definition of Chronic Health Condition: “A chronic health condition is a diagnosed condition that is more than three months in duration and either not curable, or has residual effects that limit daily living and require adaptation in function or special assistance.”  Some examples of chronic health conditions include heart disease, severe asthma, disabilities, adult onset cognitive impairments and stroke.


Do you receive the following at this time?

___ Food stamps				Yes	No

___ Medicaid					Yes	No

___ Medicare					Yes	No

___ VA Health care benefits			Yes	No



Are you experiencing domestic violence at this time,          				 Yes	No
or are you afraid of spouse, partner/boyfriend or 
girlfriend who abused you previously?										

Have you ever served in any branch of the armed forces of the United States? 
(Including the National Guard, the Coast Guard, and the Armed Forces Reserve)	Yes	No   


IMPORTANT:  If your client discloses domestic violence and current fear of an abuser, or at any time discloses HIV/AIDS, please contact Sarah Sausner so that she can make sure that their personal information is “de-identified” in Safe Harbors.

*/
