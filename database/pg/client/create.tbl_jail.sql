CREATE TABLE tbl_jail (
	jail_id			SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	jail_date			TIMESTAMP(0) NOT NULL,
	jail_date_accuracy	VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy(accuracy_code) DEFAULT 'E',
	jail_date_source_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_data_source ( data_source_code ) DEFAULT 'KC',
	jail_date_end		TIMESTAMP(0),
	jail_date_end_accuracy	VARCHAR(10) REFERENCES tbl_l_accuracy(accuracy_code),
	jail_date_end_source_code	VARCHAR(10),
	jail_facility_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_jail_facility (jail_facility_code),
	jail_county_code 		VARCHAR(10) NOT NULL REFERENCES tbl_l_washington_county (washington_county_code),
	ba_number			INTEGER,
	comments			TEXT,
--sys fields--
	added_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment		TEXT,
	sys_log			TEXT

	CONSTRAINT end_date_date CHECK (
			(jail_date_end IS NULL AND jail_date_end_accuracy IS NULL AND jail_date_end_source_code IS NULL)
			OR
			(jail_date_end IS NOT NULL AND jail_date_end_accuracy IS NOT NULL AND jail_date_end_source_code IS NOT NULL)
		)
);


CREATE UNIQUE INDEX tbl_jail_ba_number_key ON tbl_jail ( ba_number ) WHERE NOT is_deleted;
CREATE INDEX index_tbl_jail_client_id_jail_date ON tbl_jail ( client_id,jail_date );



CREATE VIEW jail AS
SELECT
	j.jail_id,
	j.client_id,
	j.jail_date,
	j.jail_date_accuracy,
	j.jail_date_source_code,
	j.jail_facility_code,
	j.jail_county_code,
	j.jail_date_end,
	j.jail_date_end_accuracy,
	j.jail_date_end_source_code,
	j.ba_number,
	COALESCE(j.jail_date_end::date,CURRENT_DATE) - j.jail_date::date AS days_in_jail,
/* FIXME: dal_due_date could probably get removed */
	j.jail_date_end::date + 7 AS dal_due_date,
	CASE
		WHEN j.jail_date_end IS NOT NULL
			OR (SELECT COUNT(*) FROM tbl_jail j2 WHERE j2.jail_date > j.jail_date AND j.client_id=j2.client_id)>0 THEN TRUE
		ELSE FALSE
	END AS is_released,
	j.comments,
	j.added_at,
	j.added_by,
	j.changed_at,
	j.changed_by,
	j.is_deleted,
	j.deleted_at,
	j.deleted_by,
	j.deleted_comment,
	j.sys_log
FROM tbl_jail j WHERE NOT is_deleted;

