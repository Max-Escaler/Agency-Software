BEGIN;

DROP VIEW client;

CREATE TABLE tbl_client_protected (
	client_protected_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	client_protected_date	DATE NOT NULL DEFAULT CURRENT_DATE,
	client_protected_date_end	DATE,
	name_last			VARCHAR(40),
	name_first			VARCHAR(30),
	name_middle			VARCHAR(30),
	name_alias			VARCHAR(120),
	dob				date,
	ssn				CHAR(11),
--system fields
	added_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff (staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT
);

CREATE VIEW client_protected AS SELECT * FROM tbl_client_protected WHERE NOT is_deleted;

CREATE INDEX index_tbl_client_protected_client_id ON tbl_client_protected ( client_id );
CREATE INDEX index_tbl_client_protected_all ON tbl_client_protected ( client_id, client_protected_date, client_protected_date_end );

CREATE VIEW client AS 

SELECT tbl_client.client_id, 
	COALESCE(prot.name_last,tbl_client.name_last) AS name_last,
	COALESCE(prot.name_first,tbl_client.name_first) AS name_first,
	COALESCE(prot.name_middle,tbl_client.name_middle) AS name_middle,
	COALESCE(prot.name_alias,tbl_client.name_alias) AS name_alias,
	COALESCE(prot.dob,tbl_client.dob) AS dob,
	COALESCE(prot.ssn,tbl_client.ssn) AS ssn,
	tbl_client.gender_code,
	tbl_client.needs_interpreter,
	tbl_client.language_code,
	tbl_client.veteran_status_code,
	tbl_client.clinical_id,
	tbl_client.king_cty_id,
	tbl_client.spc_id,
	tbl_client.comments,
	tbl_client.med_issues,
	tbl_client.medications,
	tbl_client.med_allergies,
	tbl_client.last_photo_at,
	tbl_client.issue_no,
	tbl_client.resident_id,
	tbl_client.added_at,
	tbl_client.added_by,
	tbl_client.changed_at,
	tbl_client.changed_by,
	tbl_client.is_deleted,
	tbl_client.deleted_at,
	tbl_client.deleted_by,
	tbl_client.deleted_comment,
	tbl_client.sys_log, 
	((((COALESCE(prot.name_last,tbl_client.name_last))::text || 
	', '::text) || 
	(COALESCE(prot.name_first,tbl_client.name_first))::text) || 
	CASE WHEN (COALESCE(prot.name_middle,tbl_client.name_middle) IS NULL) THEN ''::text 
		ELSE (' '::text || (COALESCE(prot.name_middle,tbl_client.name_middle))::text) END) AS name_full, 
	(((((COALESCE(prot.name_last,tbl_client.name_last))::text || ', '::text) || 
	(COALESCE(prot.name_first,tbl_client.name_first))::text) || 
		CASE WHEN (COALESCE(prot.name_middle,tbl_client.name_middle) IS NULL) THEN ''::text ELSE 
		(' '::text || (COALESCE(prot.name_middle,tbl_client.name_middle))::text) END) || 
		CASE WHEN (COALESCE(prot.name_alias,tbl_client.name_alias) IS NULL) THEN ''::text 
		ELSE (' '::text || (COALESCE(prot.name_alias,tbl_client.name_alias))::text) END) AS name_full_alias,
	CASE
		WHEN prot.client_protected_id IS NOT NULL THEN true
		ELSE false
	END AS is_protected_id 
	FROM tbl_client 
	LEFT JOIN (SELECT * FROM client_protected 
				WHERE current_date BETWEEN client_protected_date AND 
				COALESCE(client_protected_date_end,current_date)) AS prot USING (client_id)
WHERE NOT tbl_client.is_deleted;

COMMIT;
