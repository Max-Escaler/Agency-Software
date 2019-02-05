CREATE TABLE tbl_client_protected (
	client_protected_id	SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	client_protected_date	DATE NOT NULL DEFAULT CURRENT_DATE,
	client_protected_date_end	DATE,
	name_last			VARCHAR(40),
	name_first			VARCHAR(30),
	name_middle			VARCHAR(30),
	name_alias			VARCHAR(120),
	dob				date,
	ssn				CHAR(11),
--system fields
	added_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT
);

CREATE VIEW client_protected AS SELECT * FROM tbl_client_protected WHERE NOT is_deleted;
CREATE VIEW client_protected_current AS
SELECT DISTINCT ON (client_id) * FROM client_protected
WHERE client_protected_date <= CURRENT_DATE AND (client_protected_date_end > CURRENT_DATE OR client_protected_date_end IS NULL);

CREATE INDEX index_tbl_client_protected_client_id ON tbl_client_protected ( client_id );
CREATE INDEX index_tbl_client_protected_dates ON tbl_client_protected ( client_protected_date,client_protected_date_end );
CREATE INDEX index_tbl_client_protected_all ON tbl_client_protected ( client_id,client_protected_date,client_protected_date_end,is_deleted );
CREATE INDEX index_tbl_client_protected_is_deleted ON tbl_client_protected ( is_deleted );
