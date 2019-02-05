CREATE TABLE tbl_mail (
	mail_id			SERIAL PRIMARY KEY,
	client_id		INTEGER,
	mail_date		DATE,
	mail_type_code	VARCHAR(10) REFERENCES tbl_l_mail_type (mail_type_code),
	delivery_date	DATE,
	mail_delivery_code	VARCHAR(10) REFERENCES tbl_l_mail_delivery (mail_delivery_code) DEFAULT 'WAITING',
	comment			TEXT,
	added_at		TIMESTAMP(0) DEFAULT current_timestamp,
	added_by		INTEGER,
	changed_at		TIMESTAMP(0) DEFAULT current_timestamp,
	changed_by		INTEGER,
	is_deleted		boolean DEFAULT false,
	deleted_at		TIMESTAMP(0),
	deleted_by		INTEGER,
	deleted_comment	TEXT,
	sys_log			TEXT
);

CREATE VIEW mail AS
	SELECT * FROM tbl_mail
	WHERE NOT is_deleted;

CREATE INDEX index_tbl_mail_client_id_mail_date ON tbl_mail ( client_id,mail_date );
CREATE INDEX index_tbl_mail_mail_date_client_id ON tbl_mail ( mail_date,client_id );
CREATE INDEX index_tbl_mail_mail_date ON tbl_mail ( mail_date );
