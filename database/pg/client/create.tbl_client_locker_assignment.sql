CREATE TABLE tbl_client_locker_assignment (
	client_locker_assignment_id		SERIAL PRIMARY KEY,
	client_id					INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	client_locker_assignment_date		DATE NOT NULL DEFAULT CURRENT_DATE,
	client_locker_assignment_date_end	DATE NOT NULL,
	client_locker_code			VARCHAR(10) NOT NULL REFERENCES tbl_locker ( locker_code ),
	renewal_date_1				DATE CHECK (renewal_date_1 BETWEEN client_locker_assignment_date AND client_locker_assignment_date_end),
	renewal_date_2				DATE CHECK (renewal_date_2 BETWEEN renewal_date_1 AND client_locker_assignment_date_end),
	
	--system fields
	added_by					INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at					TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by					INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at					TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted					BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at					TIMESTAMP(0),
	deleted_by					INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment				TEXT,
	sys_log					TEXT

	CONSTRAINT locker_end_dates  CHECK (
		(client_locker_assignment_date_end >= client_locker_assignment_date)
			AND
		(client_locker_assignment_date_end - client_locker_assignment_date <= 90) )

	CONSTRAINT renewal_dates CHECK (
		(renewal_date_2 IS NULL OR (renewal_date_2 IS NOT NULL AND renewal_date_1 IS NOT NULL))
	)
);

CREATE INDEX index_tbl_client_locker_assignment_client_id ON tbl_client_locker_assignment ( client_id );
CREATE INDEX index_tbl_client_locker_assignment_date
	ON tbl_client_locker_assignment ( client_locker_assignment_date );
CREATE INDEX index_tbl_client_locker_assignment_date_end 
	ON tbl_client_locker_assignment ( client_locker_assignment_date_end );
CREATE INDEX index_tbl_client_locker_assignment_dates 
	ON tbl_client_locker_assignment ( client_locker_assignment_date,client_locker_assignment_date_end );
CREATE INDEX index_tbl_client_locker_assignment_client_locker_code ON tbl_client_locker_assignment ( client_locker_code );


CREATE VIEW client_locker_assignment AS
SELECT cl.client_locker_assignment_id,
	cl.client_id,
	cl.client_locker_assignment_date,
	cl.client_locker_assignment_date_end,
	cl.client_locker_code,
	l.combination,
	cl.renewal_date_1,
	cl.renewal_date_2,
	CURRENT_DATE - COALESCE(renewal_date_2,renewal_date_1,client_locker_assignment_date) AS days_from_last_renewal,
	cl.added_by,
	cl.added_at,
	cl.changed_by,
	cl.changed_at,
	cl.is_deleted,
	cl.deleted_at,
	cl.deleted_by,
	cl.deleted_comment,
	cl.sys_log
FROM tbl_client_locker_assignment cl
	LEFT JOIN locker l ON (cl.client_locker_code=l.locker_code)
WHERE NOT cl.is_deleted;

CREATE VIEW client_locker_assignment_current AS
SELECT * FROM client_locker_assignment
WHERE client_locker_assignment_date <= CURRENT_DATE AND 
	(client_locker_assignment_date_end >= CURRENT_DATE 
		OR client_locker_assignment_date_end IS NULL);

CREATE VIEW client_locker_assignment_recent AS
SELECT * FROM client_locker_assignment
WHERE client_locker_assignment_date <= CURRENT_DATE AND 
	(client_locker_assignment_date_end >= CURRENT_DATE - '7 days'::interval
		OR client_locker_assignment_date_end IS NULL);

