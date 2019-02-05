CREATE TABLE tbl_sex_offender_reg (
	sex_offender_reg_id		SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client (client_id),
	has_registration_requirement	BOOLEAN NOT NULL,
	reoffense_risk_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_reoffense_risk (reoffense_risk_code),
	victim_type_code			VARCHAR(10) NOT NULL REFERENCES tbl_l_victim_type (victim_type_code),
	reg_required_length_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_reg_required_length (reg_required_length_code),
	police_bulletin_no			VARCHAR(80),
	classifying_jurisdiction	TEXT,
	comment				TEXT,
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

CREATE VIEW sex_offender_reg AS SELECT * FROM tbl_sex_offender_reg WHERE NOT is_deleted;
