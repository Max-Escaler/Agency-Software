CREATE TABLE tbl_config_staff_password (
	config_staff_password_id		SERIAL PRIMARY KEY,
	default_expiration_days	INTEGER NOT NULL DEFAULT -1,
	allow_override_default_expiration	BOOLEAN NOT NULL DEFAULT false,
	expiration_warning_days		INTEGER NOT NULL DEFAULT 7,
	password_retention_count	INTEGER NOT NULL DEFAULT 0,
	delete_excess_passwords_on_change	BOOLEAN NOT NULL DEFAULT true,
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
);

CREATE VIEW config_staff_password AS SELECT * FROM tbl_config_staff_password WHERE NOT is_deleted;
CREATE VIEW config_staff_password_current AS SELECT * FROM config_staff_password ORDER BY added_at DESC LIMIT 1;

--FIXME:  Add trigger to enforce delete_excess_passwords_on_change

