CREATE TABLE tbl_auth_token (
	auth_token_id		SERIAL PRIMARY KEY,
	staff_id			INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
	token				CHAR(32),
	email_address		VARCHAR(80),
	expires_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP + '2 days',
	is_valid			BOOLEAN NOT NULL DEFAULT TRUE,
	was_used			BOOLEAN NOT NULL DEFAULT FALSE,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id) DEFAULT sys_user(),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE VIEW auth_token AS SELECT * FROM tbl_auth_token WHERE NOT is_deleted;

CREATE VIEW auth_token_current AS 
	SELECT * FROM auth_token WHERE current_timestamp < expires_at AND is_valid AND NOT was_used;
