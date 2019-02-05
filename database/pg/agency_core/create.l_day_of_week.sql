CREATE TABLE tbl_l_day_of_week (
    day_of_week_code        SERIAL PRIMARY KEY,
    description             VARCHAR(50) NOT NULL UNIQUE,
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

CREATE VIEW l_day_of_week AS SELECT * FROM tbl_l_day_of_week WHERE NOT is_deleted;

INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
	VALUES  ('0', 'Sunday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
	VALUES	('1', 'Monday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
    VALUES	('2', 'Tuesday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
    VALUES	('3', 'Wednesday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
    VALUES	('4', 'Thursday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
    VALUES	('5', 'Friday',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_day_of_week ( day_of_week_code, description,added_by,added_at,changed_by,changed_at)
    VALUES	('6', 'Saturday',sys_user(),current_timestamp,sys_user(),current_timestamp);

