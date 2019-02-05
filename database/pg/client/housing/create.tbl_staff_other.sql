CREATE TABLE tbl_staff_other (
	staff_other_id			SERIAL PRIMARY KEY,
	name_last				VARCHAR(40) NOT NULL,
	name_first				VARCHAR(30),-- NOT NULL, (generic staff and facilities (Seattle Mental Health etc) won't have this)
	kc_staff_id				VARCHAR(10),
	is_active				BOOLEAN NOT NULL DEFAULT TRUE, --?do we need this, or determine by dates?
	position_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_position (staff_position_code),
	agency_code				VARCHAR(10) REFERENCES tbl_l_agency (agency_code),
--	agency_program_code       	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_program (agency_program_code), I don't know that we need these in this table
--	agency_project_code       	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),
	staff_other_date			DATE NOT NULL,
	staff_other_date_end		DATE,
	gender_code             	VARCHAR(10) NOT NULL     REFERENCES tbl_l_gender (gender_code), --do we need this?
	notes					TEXT,
	--system fields
	added_by				INTEGER NOT NULL    ,-- REFERENCES staff (staff_id)
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL    ,-- REFERENCES staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER,
	deleted_comment			TEXT,
	sys_log					TEXT
);

SELECT SETVAL('tbl_staff_other_staff_other_id_seq',10000); --shouldn't conflict with desc staff id anytime soon

CREATE OR REPLACE VIEW staff_other AS
SELECT * FROM tbl_staff_other WHERE NOT is_deleted;

CREATE OR REPLACE VIEW case_manager_other AS
SELECT * 
FROM tbl_staff_other
WHERE NOT is_deleted
	AND position_code IN ('');

