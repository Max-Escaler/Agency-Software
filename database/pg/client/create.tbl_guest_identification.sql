CREATE TABLE tbl_guest_identification (
	guest_identification_id	SERIAL PRIMARY KEY,
	guest_id INTEGER NOT NULL REFERENCES tbl_guest (guest_id),
	identification_document_scan INTEGER,  --for copy of vistor ID
	identification_type_code VARCHAR(20) NOT NULL REFERENCES tbl_l_identification_type (identification_type_code),
	identification_number TEXT,
    identification_expiration_date DATE NOT NULL,
	comment TEXT,

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

CREATE VIEW guest_identification AS SELECT * FROM tbl_guest_identification WHERE NOT is_deleted;
CREATE VIEW guest_identification_current AS SELECT * FROM guest_identification WHERE COALESCE(identification_expiration_date,current_date) >= current_date;

