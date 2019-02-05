CREATE TABLE tbl_locker (
	locker_code			VARCHAR(10) PRIMARY KEY CHECK ( locker_code ~ '^[0-9]{3}$'), --check on number
	locker_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_locker_type ( locker_type_code ),
	lock_code			VARCHAR(10) NOT NULL REFERENCES tbl_lock ( lock_code ),
	current_lock_position	INTEGER NOT NULL CHECK (current_lock_position BETWEEN 1 AND 5),
	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id)
						  CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE INDEX tbl_locker_code_locker_type_code ON tbl_locker ( locker_type_code );
CREATE INDEX tbl_locker_code_lock_code ON tbl_locker ( lock_code );


CREATE VIEW locker AS SELECT
	l.locker_code,
	l.locker_type_code,
	l.lock_code,
	l.current_lock_position,
	CASE
		WHEN current_lock_position = 1 THEN combination_1
		WHEN current_lock_position = 2 THEN combination_2
		WHEN current_lock_position = 3 THEN combination_3
		WHEN current_lock_position = 4 THEN combination_4
		WHEN current_lock_position = 5 THEN combination_5
	END AS combination,
	l.added_by,
	l.added_at,
	l.changed_by,
	l.changed_at,
	l.is_deleted,
	l.deleted_at,
	l.deleted_by,
	l.deleted_comment,
	l.sys_log
FROM tbl_locker l LEFT JOIN lock USING ( lock_code )
WHERE NOT l.is_deleted;

CREATE VIEW l_client_locker AS SELECT 
	locker_code AS client_locker_code,
	locker_code AS description
FROM locker WHERE locker_type_code = 'CLIENT';

/* sample data
INSERT INTO tbl_locker (locker_code,lock_code,locker_type_code,current_lock_position,added_by,changed_by)
	VALUES ('001','10H01','STAFF',1,923,923);
*/
