CREATE TABLE tbl_l_response (
	response_code VARCHAR(10) PRIMARY KEY,
	description TEXT,
	response_coding TEXT,
	response_comment VARCHAR(60),
	total_design_printing_cost NUMERIC(12,2),
	postage_cost NUMERIC(12,2),
	donors_contacted INTEGER,
	date_sent	DATE,
	mail_merge_file	VARCHAR(100),
	query	TEXT,
	notes TEXT,
    --system fields
    added_by                        INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
    added_at                        TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by                      INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at                      TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted                      BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at                      TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS
 NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
    deleted_by                      INTEGER REFERENCES tbl_staff(staff_id)
                                       CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
    deleted_comment         TEXT,
    sys_log                 TEXT
);

CREATE VIEW l_response AS (SELECT * FROM tbl_l_response WHERE NOT is_deleted);

