CREATE TABLE tbl_housing_key (
	housing_key_id 		VARCHAR(10) PRIMARY KEY,
	description			VARCHAR(40) NOT NULL,
	hook_tag_number 	INTEGER NOT NULL,
	key_type			VARCHAR(2) NOT NULL,
	agency_project_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),
--system fields
    added_by     	INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
    added_at     	TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by   	INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at     	TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted    	BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at   	TIMESTAMP(0),
    deleted_by   	INTEGER REFERENCES tbl_staff(staff_id),
    deleted_comment TEXT,
    sys_log TEXT
);

CREATE OR REPLACE VIEW housing_key AS
SELECT * FROM tbl_housing_key WHERE NOT is_deleted;
