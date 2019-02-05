CREATE TABLE tbl_l_departure_reason (
    departure_reason_code character varying(10) PRIMARY KEY,
    old_code character varying(3),
    description character varying(100) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_departure_reason VALUES ('PERMHOUS', '1', 'Moved to permanent housing',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('CRIME', '12', 'Criminal activity',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('DECEASED', '15', 'Death',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('OTHER', '18', 'Other reason- describe in Notes field',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('VIO-DAM', '19', 'Violent behavior/Property destruction',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('NORENTPAY', '2', 'Voluntary departure-resident initiated',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('UNKNOWN', '20', 'UNKNOWN',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('BROKERULES', '6', 'Non-compliance with services',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_departure_reason VALUES ('TRANSFER', '10', 'Unit Transfer in same building',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_departure_reason AS (SELECT * FROM tbl_l_departure_reason WHERE NOT is_deleted);

