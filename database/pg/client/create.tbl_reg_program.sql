CREATE TABLE tbl_reg_program (
	reg_program_id			SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	program_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_program (program_code),
	reg_program_date			DATE NOT NULL,
	reg_program_date_end		DATE,
	referral_source_code		VARCHAR(10) REFERENCES tbl_l_referral_source ( referral_source_code ),
	referred_by			INTEGER REFERENCES tbl_staff ( staff_id ),
	exit_referral_code	VARCHAR(10) REFERENCES tbl_l_referral_source ( referral_source_code ),
	exit_status_code		VARCHAR(10) REFERENCES tbl_l_exit_status ( exit_status_code ),
	program_completed_code	VARCHAR(10) REFERENCES tbl_l_yes_no (yes_no_code),
	comment				TEXT,
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT

	CONSTRAINT reg_program_exit_date CHECK (
		(reg_program_date_end IS NULL AND exit_referral_code IS NULL AND exit_status_code IS NULL)
		OR
		(reg_program_date_end IS NOT NULL AND exit_referral_code IS NOT NULL AND exit_status_code IS NOT NULL)
	)
);

CREATE VIEW reg_program AS SELECT * FROM tbl_reg_program WHERE NOT is_deleted;

CREATE VIEW reg_program_current AS 
	SELECT * FROM reg_program WHERE reg_program_date <= CURRENT_DATE
		AND COALESCE(reg_program_date_end,CURRENT_DATE + 1) > CURRENT_DATE;


CREATE INDEX index_tbl_reg_program_client_id ON tbl_reg_program ( client_id );
CREATE INDEX index_tbl_reg_program_dates ON tbl_reg_program ( reg_program_date, reg_program_date_end );
CREATE INDEX index_tbl_reg_program_reg_program_date ON tbl_reg_program ( reg_program_date );
CREATE INDEX index_tbl_reg_program_referred_by ON tbl_reg_program ( referred_by );
CREATE INDEX index_tbl_reg_program_client_id_reg_program_date ON tbl_reg_program ( client_id,reg_program_date );
CREATE INDEX index_tbl_reg_program_reg_program_date_client_id ON tbl_reg_program ( reg_program_date,client_id );

