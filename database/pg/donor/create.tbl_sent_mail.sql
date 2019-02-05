CREATE TABLE tbl_sent_mail (
	sent_mail_id SERIAL PRIMARY KEY,
	donor_id INTEGER NOT NULL REFERENCES tbl_donor ( donor_id ),
	response_code VARCHAR(10) NOT NULL REFERENCES tbl_l_response,
--system fields
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at    TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

CREATE VIEW sent_mail AS SELECT 
	sent_mail_id,
	donor_id,
	sm.response_code,
	l_response.date_sent,
	sm.added_by,
	sm.added_at,
	sm.changed_by,
	sm.changed_at,
	sm.is_deleted,
	sm.deleted_at,
	sm.deleted_by,
	sm.deleted_comment,
	sm.sys_log
FROM tbl_sent_mail sm
	LEFT JOIN l_response USING (response_code)
WHERE NOT sm.is_deleted;


CREATE INDEX index_sent_mail_donor_id ON tbl_sent_mail ( donor_id );
