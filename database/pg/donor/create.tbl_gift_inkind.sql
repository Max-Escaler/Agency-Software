CREATE TABLE "tbl_gift_inkind" (
	gift_inkind_id SERIAL PRIMARY KEY,
	donor_id INTEGER REFERENCES tbl_donor (donor_id),
	gift_inkind_date	DATE NOT NULL,
--	is_new	BOOLEAN, -- condition_code?
	inkind_item_code VARCHAR(10) REFERENCES tbl_l_inkind_item (inkind_item_code),
--	quantity	INTEGER,
--	value_unit	DECIMAL (12,2),
	value_total	DECIMAL (12,2), -- this field should be dropped, and replaced w/ calc field in the view
								-- but first we should make sure that all the v_u * q = v_t
								-- As of 5/7/04, no records where data is there and the two
								-- don't match
	reference_no	VARCHAR(16),
	response_code	VARCHAR(10) REFERENCES tbl_l_response (response_code),
	restriction_code	VARCHAR(10) REFERENCES tbl_l_restriction (restriction_code),
	skip_thanks BOOLEAN NOT NULL,
	is_anonymous BOOLEAN NOT NULL,
	gift_inkind_comment	TEXT,
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
    added_at     TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
    changed_at     TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
    deleted_at   TIMESTAMP(0),
    deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
    deleted_comment TEXT,
    sys_log TEXT
);

CREATE VIEW gift_inkind AS SELECT * FROM tbl_gift_inkind WHERE NOT is_deleted;
