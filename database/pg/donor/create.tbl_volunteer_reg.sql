CREATE TABLE tbl_volunteer_reg (
	volunteer_reg_id SERIAL PRIMARY KEY,
	donor_id		INTEGER NOT NULL REFERENCES tbl_donor ( donor_id ),
	volunteer_reg_date	DATE NOT NULL,
	length_commitment_code	VARCHAR(10) REFERENCES tbl_l_length_commitment ( length_commitment_code ),
	minimum_hour_commitment NUMERIC(12,2),
	referral_source_code	VARCHAR(10) REFERENCES tbl_l_referral_source ( referral_source_code ),
	volunteer_reg_date_end	DATE,
	volunteer_location_code VARCHAR(10) NOT NULL REFERENCES tbl_l_volunteer_location ( volunteer_location_code ),
--system fields--
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT

	CONSTRAINT good_data_please CHECK (length_commitment_code IS NOT NULL OR minimum_hour_commitment IS NOT NULL)
);

CREATE VIEW volunteer_reg AS SELECT * FROM tbl_volunteer_reg WHERE NOT is_deleted;

CREATE INDEX index_volunteer_reg_donor_id ON tbl_volunteer_reg ( donor_id );
