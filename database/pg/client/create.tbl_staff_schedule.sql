CREATE TABLE tbl_staff_schedule (
	staff_schedule_id SERIAL PRIMARY KEY,
	staff_schedule_date	DATE NOT NULL,
	staff_shift_code VARCHAR(10) NOT NULL REFERENCES tbl_l_staff_shift (staff_shift_code),
	morr_rc_1	INTEGER REFERENCES tbl_staff (staff_id),
	morr_rc_2	INTEGER REFERENCES tbl_staff (staff_id),
	morr_rc_3	INTEGER REFERENCES tbl_staff (staff_id),
	morr_rc_4	INTEGER REFERENCES tbl_staff (staff_id),
	ksh_rc_1	INTEGER REFERENCES tbl_staff (staff_id),
	ksh_rc_2	INTEGER REFERENCES tbl_staff (staff_id),
	ksh_rc_3	INTEGER REFERENCES tbl_staff (staff_id),
	lyon_rc_1	INTEGER REFERENCES tbl_staff (staff_id),
	lyon_rc_2	INTEGER REFERENCES tbl_staff (staff_id),
	lyon_rc_3	INTEGER REFERENCES tbl_staff (staff_id),
	union_rc_1	INTEGER REFERENCES tbl_staff (staff_id),
	union_rc_2	INTEGER REFERENCES tbl_staff (staff_id),

	--system fields
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT

	CONSTRAINT no_simultaneous_staff CHECK (
		/* kludge of a constraint. null values not allowed in arrays, sequencing negative numbers */
		array_count(array[COALESCE(morr_rc_1,-1),COALESCE(morr_rc_2,-2),COALESCE(morr_rc_3,-3),COALESCE(morr_rc_4,-4),
					COALESCE(ksh_rc_1,-5),COALESCE(ksh_rc_2,-6),COALESCE(ksh_rc_3,-7),
					COALESCE(lyon_rc_1,-8),COALESCE(lyon_rc_2,-9),COALESCE(lyon_rc_3,-10),
					COALESCE(union_rc_1,-11),COALESCE(union_rc_2,-12)])
			= array_count(array_unique(array[COALESCE(morr_rc_1,-1),COALESCE(morr_rc_2,-2),COALESCE(morr_rc_3,-3),COALESCE(morr_rc_4,-4),
					COALESCE(ksh_rc_1,-5),COALESCE(ksh_rc_2,-6),COALESCE(ksh_rc_3,-7),
					COALESCE(lyon_rc_1,-8),COALESCE(lyon_rc_2,-9),COALESCE(lyon_rc_3,-10),
					COALESCE(union_rc_1,-11),COALESCE(union_rc_2,-12)]))
	)

);

CREATE VIEW staff_schedule AS 
SELECT *
FROM tbl_staff_schedule WHERE NOT is_deleted;

CREATE VIEW staff_shift AS

SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,morr_rc_1 
AS staff_id, 'MORR' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,morr_rc_2 
AS staff_id, 'MORR' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,morr_rc_3
AS staff_id, 'MORR' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,morr_rc_4
AS staff_id, 'MORR' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,lyon_rc_1 
AS staff_id, 'LYON' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,lyon_rc_2 
AS staff_id, 'LYON' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,lyon_rc_3 
AS staff_id, 'LYON' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,ksh_rc_1 
AS staff_id, 'KSH' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,ksh_rc_2 
AS staff_id, 'KSH' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,ksh_rc_3 
AS staff_id, 'KSH' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,union_rc_1 
AS staff_id, 'UNION' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

UNION
SELECT staff_schedule_id, staff_schedule_date,staff_shift_code,union_rc_2 
AS staff_id, 'UNION' AS agency_project_code,
added_by,added_at,changed_by,changed_at,is_deleted,deleted_by,deleted_comment,sys_log
FROM staff_schedule

;

CREATE UNIQUE INDEX index_tbl_staff_schedule_staff_schedule_date_unique  ON tbl_staff_schedule (staff_schedule_date,staff_shift_code);
CREATE INDEX index_tbl_staff_schedule_scheduled ON tbl_staff_schedule (morr_rc_1,morr_rc_2,morr_rc_3,morr_rc_4,
												ksh_rc_1,ksh_rc_2,ksh_rc_3,
												lyon_rc_1,lyon_rc_2,lyon_rc_3,
												union_rc_1,union_rc_2
										);
