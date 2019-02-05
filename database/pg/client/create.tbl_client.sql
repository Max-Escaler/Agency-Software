CREATE TABLE tbl_client (
	client_id 			SERIAL PRIMARY KEY,
	clinical_id 			INTEGER,
	king_cty_id 			INTEGER,
	resident_id 			INTEGER,
	issue_no 			INTEGER DEFAULT 0 NOT NULL,
	name_last 			VARCHAR(40) NOT NULL,
	name_first 			VARCHAR(30) NOT NULL,
	name_middle 			VARCHAR(30),
	name_alias 			VARCHAR(120),
	dob 				DATE NOT NULL CHECK (dob <= CURRENT_DATE),
	ssn 				CHARACTER(11) NOT NULL,
	gender_code 			VARCHAR(10) NOT NULL REFERENCES tbl_l_gender ( gender_code ),
	hispanic_origin_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_hispanic_origin,
	needs_interpreter_code 		VARCHAR(10) NOT NULL REFERENCES tbl_l_yes_no ( yes_no_code ),
	language_code 			VARCHAR(10) NOT NULL REFERENCES tbl_l_language (language_code) DEFAULT 0 /* 0=unknown */ ,
	pronoun_subject		VARCHAR,
	pronoun_object		VARCHAR,
	pronoun_possessive	VARCHAR,
	pronoun_possessive_pronoun		VARCHAR,
	pronoun_reflexive		VARCHAR,
	comments 			TEXT,
	med_issues 			TEXT,
	medications 			TEXT,
	med_allergies 			TEXT,
	veteran_status_code 		VARCHAR(10) NOT NULL REFERENCES tbl_l_veteran_status ( veteran_status_code ),
	sexual_minority_status_code	VARCHAR(10) NOT NULL DEFAULT 'NOT_ASKED' REFERENCES tbl_l_sexual_minority_status,
	last_photo_at 			timestamp(0) without time zone,
	spc_id				INTEGER,
	name_suffix 			VARCHAR(10) REFERENCES tbl_l_name_suffix ( name_suffix_code ),
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

CREATE INDEX index_tbl_client_dob ON tbl_client ( dob ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_client_client_id_dob_gender ON tbl_client ( client_id, dob, gender_code) WHERE NOT is_deleted;
CREATE INDEX index_tbl_client_ssn ON tbl_client ( ssn ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_client_name_alias ON tbl_client ( name_alias ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_client_name_first ON tbl_client ( name_first ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_client_name_last ON tbl_client ( name_last ) WHERE NOT is_deleted;


CREATE UNIQUE INDEX tbl_client_clinical_id_key ON tbl_client ( clinical_id ) WHERE NOT is_deleted;   
CREATE UNIQUE INDEX tbl_client_king_cty_id_key ON tbl_client ( king_cty_id ) WHERE NOT is_deleted;
