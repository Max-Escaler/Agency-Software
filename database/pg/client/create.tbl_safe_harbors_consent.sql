CREATE TABLE tbl_safe_harbors_consent (
    safe_harbors_consent_id SERIAL PRIMARY KEY,
    client_id INTEGER NOT NULL REFERENCES tbl_client (client_id),
    consent_status_code VARCHAR(10) NOT NULL REFERENCES tbl_l_consent_status (consent_status_code),
    safe_harbors_consent_date DATE,
	safe_harbors_consent_paper_form INT,
    added_by integer NOT NULL  REFERENCES tbl_staff(staff_id),
    added_at timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    changed_by integer NOT NULL  REFERENCES tbl_staff(staff_id),
    changed_at timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    is_deleted boolean DEFAULT false NOT NULL,
    deleted_at timestamp(0) without time zone,
    deleted_by integer  REFERENCES tbl_staff(staff_id),
    deleted_comment TEXT,
    sys_log TEXT
);

CREATE OR REPLACE VIEW safe_harbors_consent AS SELECT * FROM tbl_safe_harbors_consent WHERE NOT is_deleted;

