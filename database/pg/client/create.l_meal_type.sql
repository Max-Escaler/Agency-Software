CREATE TABLE tbl_l_meal_type_general (
    meal_type_general_code	VARCHAR(10) PRIMARY KEY NOT NULL,
    description		VARCHAR(100) NOT NULL UNIQUE,
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

INSERT INTO tbl_l_meal_type_general
       (meal_type_general_code, description,added_by,added_at,changed_by,changed_at)
VALUES ('BREAKFAST', 'Breakfast',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('LUNCH', 'Lunch',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('DINNER', 'Dinner',sys_user(),current_timestamp,sys_user(),current_timestamp);



CREATE TABLE tbl_l_meal_type
       (
       meal_type_code		VARCHAR(10) PRIMARY KEY NOT NULL,
       meal_type_general_code	VARCHAR(10) REFERENCES tbl_l_meal_type_general (meal_type_general_code),
       description		VARCHAR(100) NOT NULL UNIQUE
       );

INSERT INTO tbl_l_meal_type
       (meal_type_code, meal_type_general_code, description,added_by,added_at,changed_by,changed_at)

VALUES ('BREAKFAST', 'BREAKFAST', 'Breakfast',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('BRUNCH', 'BREAKFAST', 'Brunch',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('LUNCH', 'LUNCH', 'Lunch',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('DINNER', 'DINNER', 'Dinner',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('PIZZA', 'DINNER', 'Pizza Night',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('SNACK_AM', NULL, 'AM Snack',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('SNACK_PM', NULL, 'PM Snack',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('GROCERY', NULL, 'Groceries',sys_user(),current_timestamp,sys_user(),current_timestamp),
       ('OTHER', NULL, 'Other - Please Describe',sys_user(),current_timestamp,sys_user(),current_timestamp);

CREATE VIEW l_meal_type AS (SELECT * FROM tbl_l_meal_type WHERE NOT is_deleted);

