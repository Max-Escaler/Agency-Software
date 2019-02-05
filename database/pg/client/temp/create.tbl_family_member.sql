CREATE TABLE tbl_family_member (
	family_member_id SERIAL PRIMARY KEY,
	family_member_date	DATE NOT NULL,
	family_member_date_End	DATE,
	household_head_id	INTEGER NOT NULL REFERENCES tbl_client (client_id),
	client_id		INTEGER NOT NULL REFERENCES tbl_client (client_id),
	family_relation_code	VARCHAR NOT NULL REFERENCES tbl_l_family_relation (family_relation_code),
	comment			TEXT,

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
	CONSTRAINT date_sanity_check CHECK (COALESCE(family_member_date_end,family_member_date)>=family_member_date),
	CONSTRAINT no_relate_to_self CHECK (client_id != household_head_id)
);

CREATE VIEW family_member AS (SELECT * FROM tbl_family_member WHERE NOT is_deleted);

CREATE INDEX tbl_family_member_family_member_date ON tbl_family_member (family_member_date);
CREATE INDEX tbl_family_member_family_member_date_end ON tbl_family_member (family_member_date_end);
CREATE INDEX tbl_family_member_household_head_id ON tbl_family_member (household_head_id);
CREATE INDEX tbl_family_member_family_relation_code ON tbl_family_member (family_relation_code);
CREATE INDEX tbl_family_member_client_id ON tbl_family_member (client_id);

