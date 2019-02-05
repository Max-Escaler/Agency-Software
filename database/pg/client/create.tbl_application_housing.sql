CREATE TABLE tbl_application_housing (
	application_housing_id			SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	application_date			DATE NOT NULL,
	housing_project_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_project (housing_project_code),
	application_status_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_application_status (application_status_code),
	referral_source_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_referral_source (referral_source_code),
	needs_physical_accommodation		BOOLEAN,

	--case manager stuff here
	is_homeless				BOOLEAN,
	uses_alcohol				BOOLEAN,
	uses_drugs				BOOLEAN,
	is_substance_use_housing_issue		BOOLEAN,
	substance_use_housing_issue_text	TEXT,
	had_substance_use_treatment 		BOOLEAN,
	is_willing_to_talk			BOOLEAN,
	has_criminal_history			BOOLEAN,
	is_currently_on_probation		BOOLEAN,
	was_evicted				BOOLEAN,
	owes_sha				BOOLEAN,
	owes_landlord				BOOLEAN,
	has_other_applications_pending		BOOLEAN,
	why_appropriate_per_cm			TEXT,
	is_sex_offender				BOOLEAN,
	has_violent_history			BOOLEAN,
	submitted_to_sha_date			DATE,
	sha_approval_code			VARCHAR(10) REFERENCES tbl_l_approval (approval_code),
	application_rank_code 			VARCHAR(10) REFERENCES tbl_l_application_rank ( application_rank_code),
	comments				TEXT,
	legal_issues 				TEXT,
	behavioral_issues			 TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT

);

CREATE VIEW application_housing AS (SELECT * FROM tbl_application_housing WHERE NOT is_deleted);

CREATE INDEX index_tbl_application_housing_client_id_application_date ON tbl_application_housing ( client_id,application_date );
CREATE INDEX index_tbl_application_housing_application_date_client_id ON tbl_application_housing ( application_date,client_id );
CREATE INDEX index_tbl_application_housing_application_date ON tbl_application_housing ( application_date );
