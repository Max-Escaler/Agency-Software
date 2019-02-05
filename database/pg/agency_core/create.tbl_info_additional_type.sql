CREATE TABLE tbl_info_additional_type (
	info_additional_type_id		SERIAL PRIMARY KEY,
    info_additional_type_code     VARCHAR(30) UNIQUE,
    description VARCHAR NOT NULL UNIQUE,
	applicable_tables	VARCHAR[] NOT NULL,
	value_type_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_value_type (value_type_code),
	null_ok		BOOLEAN NOT NULL,
	comment	TEXT,
--	widget_type_code	VARCHAR(10) REFERENCES tbl_l_widget_type_code,
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

CREATE VIEW info_additional_type AS SELECT * FROM tbl_info_additional_type WHERE NOT is_deleted;

