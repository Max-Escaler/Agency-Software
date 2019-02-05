CREATE TABLE tbl_hospital (
	hospital_id			 	SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	facility				VARCHAR(60) NOT NULL,
	hospital_date			DATE NOT NULL,
	hospital_date_accuracy		VARCHAR(10) NOT NULL REFERENCES tbl_l_accuracy ( accuracy_code ) DEFAULT 'E',
	hospital_date_source_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_data_source ( data_source_code ) DEFAULT 'KC',
	hospital_date_end			DATE,
	hospital_date_end_accuracy	VARCHAR(10) REFERENCES tbl_l_accuracy ( accuracy_code ),
	hospital_date_end_source_code	VARCHAR(10) REFERENCES tbl_l_data_source ( data_source_code ),
	is_voluntary			BOOLEAN NOT NULL,
	comments				TEXT,
--sys fields--
	added_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT

	CONSTRAINT end_date_date CHECK (
			(hospital_date_end IS NULL AND hospital_date_end_accuracy IS NULL AND hospital_date_end_source_code IS NULL)
			OR
			(hospital_date_end IS NOT NULL AND hospital_date_end_accuracy IS NOT NULL AND hospital_date_end_source_code IS NOT NULL)
		)
);

CREATE VIEW hospital AS SELECT
	h.hospital_id,
	h.client_id,
	h.facility,
	h.hospital_date,
	h.hospital_date_accuracy,
	h.hospital_date_source_code,
	h.hospital_date_end,
	h.hospital_date_end_accuracy,
	h.hospital_date_end_source_code,
	COALESCE(h.hospital_date_end,CURRENT_DATE) - h.hospital_date AS days_in_hospital,
	h.hospital_date_end + 7 AS dal_due_date,
	h.is_voluntary,
	CASE
		WHEN h.hospital_date_end IS NOT NULL
			OR (SELECT COUNT(*) FROM tbl_hospital h2 WHERE h2.hospital_date > h.hospital_date AND h.client_id=h2.client_id)>0 THEN TRUE
		ELSE FALSE
	END AS is_released,
	h.comments,
	h.added_at,
	h.added_by,
	h.changed_at,
	h.changed_by,
	h.is_deleted,
	h.deleted_at,
	h.deleted_by,
	h.deleted_comment,
	h.sys_log
FROM tbl_hospital h WHERE NOT is_deleted;
