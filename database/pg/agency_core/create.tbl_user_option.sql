CREATE TABLE tbl_user_option (
staff_id               INTEGER NOT NULL PRIMARY KEY REFERENCES tbl_staff (staff_id),
opt_alerts_email           BOOLEAN NOT NULL DEFAULT TRUE,
options_array		TEXT, --a serialized user_options array
added_at               TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
added_by               INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
changed_at             TIMESTAMP(0) NOT NULL DEFAULT current_timestamp,
changed_by             INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
is_deleted             BOOLEAN NOT NULL DEFAULT FALSE,
deleted_at             TIMESTAMP(0),
deleted_by             INTEGER REFERENCES tbl_staff (staff_id),
deleted_comment        TEXT,
sys_log                TEXT
);

CREATE OR REPLACE VIEW user_option AS SELECT * FROM tbl_user_option;
