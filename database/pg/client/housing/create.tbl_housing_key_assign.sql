CREATE TABLE tbl_housing_key_assign (
	housing_key_assign_id 	SERIAL PRIMARY KEY,
	client_id 				INTEGER NOT NULL REFERENCES tbl_client(client_id),
	key_assign_date 		DATE NOT NULL,
	key_assign_date_end 	DATE,
	housing_key_code 		VARCHAR(10) NOT NULL REFERENCES tbl_housing_key (housing_key_id),
	key_assign_reason_code  VARCHAR(10) NOT NULL REFERENCES tbl_l_key_assign_reason (key_assign_reason_code),
	charge_sent_code		VARCHAR(10) REFERENCES tbl_l_charge_sent (charge_sent_code),
	agency_project_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),

--system fields
	added_by     			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   			TIMESTAMP(0),
	deleted_by   			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment 		TEXT,
	sys_log 				TEXT
);

CREATE OR REPLACE VIEW housing_key_assign AS
SELECT * FROM tbl_housing_key_assign WHERE NOT is_deleted;
