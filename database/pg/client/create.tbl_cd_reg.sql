CREATE TABLE tbl_cd_reg (
	cd_reg_id			SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	cd_reg_date			DATE NOT NULL,
	referral_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_referral ( referral_code ),
	referred_by			INTEGER REFERENCES tbl_staff ( staff_id ),
	cd_reg_date_end		DATE,
	exit_referral_code	VARCHAR(10) REFERENCES tbl_l_referral ( referral_code ),
	exit_status_code		VARCHAR(10) REFERENCES tbl_l_exit_status ( exit_status_code ),
	exit_consumption_code	VARCHAR(10) REFERENCES tbl_l_progress (progress_code),
	exit_recovery_code	VARCHAR(10) REFERENCES tbl_l_progress (progress_code),
	comments			TEXT,
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

	CONSTRAINT cd_reg_exit_date CHECK (
		(cd_reg_date_end IS NULL AND exit_referral_code IS NULL AND exit_status_code IS NULL
			AND exit_consumption_code IS NULL AND exit_recovery_code IS NULL)
		OR
		(cd_reg_date_end IS NOT NULL AND exit_referral_code IS NOT NULL AND exit_status_code IS NOT NULL
			AND exit_consumption_code IS NOT NULL AND exit_recovery_code IS NOT NULL)
	)
);

CREATE VIEW cd_reg AS SELECT * FROM tbl_cd_reg WHERE NOT is_deleted;

CREATE VIEW cd_reg_current AS 
	SELECT * FROM cd_reg WHERE cd_reg_date <= CURRENT_DATE
		AND COALESCE(cd_reg_date_end,CURRENT_DATE + 1) > CURRENT_DATE;


CREATE INDEX index_tbl_cd_reg_client_id ON tbl_cd_reg ( client_id );
CREATE INDEX index_tbl_cd_reg_dates ON tbl_cd_reg ( cd_reg_date, cd_reg_date_end );
CREATE INDEX index_tbl_cd_reg_cd_reg_date ON tbl_cd_reg ( cd_reg_date );
CREATE INDEX index_tbl_cd_reg_referred_by ON tbl_cd_reg ( referred_by );
CREATE INDEX index_tbl_cd_reg_client_id_cd_reg_date ON tbl_cd_reg ( client_id,cd_reg_date );
CREATE INDEX index_tbl_cd_reg_cd_reg_date_client_id ON tbl_cd_reg ( cd_reg_date,client_id );

