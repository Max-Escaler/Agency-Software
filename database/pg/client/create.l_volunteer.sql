CREATE TABLE tbl_l_volunteer (
    volunteer_code character varying(10) PRIMARY KEY,
    description character(30),
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

INSERT INTO tbl_l_volunteer VALUES ('AM', 'AM Volunteer',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_volunteer VALUES ('PM', 'PM Volunteer',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_volunteer VALUES ('SAT', 'Saturday Volunteer',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_volunteer VALUES ('MEAL', 'Meal Volunteer',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_volunteer AS (SELECT * FROM tbl_l_volunteer WHERE NOT is_deleted);

