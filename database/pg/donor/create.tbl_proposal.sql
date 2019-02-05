CREATE TABLE tbl_proposal (

	proposal_id		SERIAL PRIMARY KEY,
	donor_id		INT NOT NULL REFERENCES tbl_donor (donor_id),
	proposal_amount	DECIMAL (12,2),
	request_purpose_code	VARCHAR(10) REFERENCES tbl_l_restriction ( restriction_code ),
	funding_guess	DECIMAL (12,2),
	has_deadline		BOOLEAN,
	deadline		DATE,
	submitted_on	DATE,
	award_amount	DECIMAL (12,2),
	proposal_current_status_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_proposal_current_status ( proposal_current_status_code ),
	proposal_next_action_code	VARCHAR(10) REFERENCES tbl_l_proposal_next_action ( proposal_next_action_code ),
	next_action_date	DATE,
	comment			TEXT,
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

CREATE VIEW proposal AS (SELECT * FROM tbl_proposal WHERE NOT is_deleted);
