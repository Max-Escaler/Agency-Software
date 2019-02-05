CREATE TABLE tbl_staff_phone (
	staff_phone_id 		SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff(staff_id),
	staff_phone_date 		DATE NOT NULL,
	staff_phone_date_end 	DATE,
	phone_type_code		VARCHAR(10) NOT NULL DEFAULT 'WORK' REFERENCES tbl_l_phone_type(phone_type_code),
	number			VARCHAR(14) NOT NULL CHECK (number ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	extension			VARCHAR(5),
	direct_dial_number	VARCHAR(14) CHECK (direct_dial_number ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	voice_mail_number		VARCHAR(14) CHECK (voice_mail_number ~ '\\([0-9]{3}\\) [0-9]{3}-[0-9]{4}$'),
	voice_mail_extension	VARCHAR(5),
	comments			TEXT,
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log 				TEXT

	CONSTRAINT only_good_staff_phone_data CHECK (
		(phone_type_code = 'WORK' AND (direct_dial_number IS NULL OR direct_dial_number<>number)
			AND (direct_dial_number IS NULL OR voice_mail_number<>number)
		)
		OR 
		(direct_dial_number IS NULL AND voice_mail_number IS NULL AND voice_mail_extension IS NULL)
	)
);

CREATE OR REPLACE VIEW staff_phone AS
SELECT * FROM tbl_staff_phone WHERE NOT is_deleted;

CREATE INDEX index_tbl_staff_phone_staff_id ON tbl_staff_phone ( staff_id );
CREATE INDEX index_tbl_staff_phone_staff_phone_date ON tbl_staff_phone ( staff_phone_date );
