BEGIN;
--DROP TABLE tbl_shelter_reg CASCADE;

CREATE TABLE tbl_shelter_reg2 (
	shelter_reg_id		SERIAL PRIMARY KEY,
	client_id			INTEGER NOT NULL,
	shelter_reg_date		DATE NOT NULL,
	shelter_reg_date_end 	DATE,
	bed_rereg_code		VARCHAR(10) NOT NULL,
	overnight_eligible	BOOLEAN NOT NULL DEFAULT FALSE,
--	priority_open		BOOLEAN NOT NULL DEFAULT FALSE,
	priority_cd			BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_dd			BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_disabled		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_med     		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_mh     		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_new    		BOOLEAN NOT NULL DEFAULT FALSE,
 	priority_ksh    		BOOLEAN NOT NULL DEFAULT FALSE,
	last_residence_code	VARCHAR(10) NOT NULL,
	last_residence_ownr	VARCHAR(100),
	svc_need_code		VARCHAR(10) NOT NULL,
	comments			TEXT,
	added_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	added_by			INTEGER NOT NULL,
	changed_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by			INTEGER NOT NULL ,
	is_deleted			BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at			TIMESTAMP(0),
	deleted_by			INTEGER,
	deleted_comment		TEXT,
	sys_log			TEXT
);

CREATE OR REPLACE VIEW shelter_reg2 AS 
SELECT 
	shelter_reg_id,
	r.client_id,
	r.shelter_reg_date,
	r.shelter_reg_date_end,
	r.bed_rereg_code,
	r.overnight_eligible,
--	priority_open,
	CASE
		WHEN (CURRENT_DATE-c.dob)/365.24>=60 THEN TRUE
		ELSE FALSE
	END AS priority_elderly,
	CASE
		WHEN c.gender_code IN ('1','4','6') THEN TRUE
		ELSE FALSE
	END AS priority_female,
	r.priority_cd,
	r.priority_dd,
	r.priority_disabled,
	r.priority_med,
	r.priority_mh,
	r.priority_new,
	r.priority_ksh,
	r.last_residence_code,
	r.last_residence_ownr,
	r.svc_need_code,
	r.comments,
	r.added_at,
	r.added_by,
	r.changed_at,
	r.changed_by,
	r.is_deleted,
	r.deleted_at,
	r.deleted_by,
	r.deleted_comment,
	r.sys_log
FROM tbl_shelter_reg r
	LEFT JOIN tbl_client c USING(client_id)
WHERE NOT r.is_deleted;

GRANT SELECT ON shelter_reg2 TO gate;

CREATE TEMPORARY TABLE tmp_active_client AS
SELECT DISTINCT client_id FROM entry WHERE entered_at >='2003-01-01'
UNION
SELECT DISTINCT client_id FROM bed WHERE bed_date >='2003-01-01';

INSERT INTO tbl_shelter_reg2 (
	client_id,
	shelter_reg_date,
	shelter_reg_date_end,
	bed_rereg_code,
	overnight_eligible,
--	priority_open,
	priority_cd,
	priority_dd,
	priority_disabled,
	priority_med,
	priority_mh,
	priority_new,
	priority_ksh,
	last_residence_code,
	last_residence_ownr,
	svc_need_code,
	comments,
	added_by,
	changed_by
)
SELECT
	client_id,
	COALESCE(priority_date_start,c.added_at,c.changed_at) AS shelter_reg_date,
	priority_date_end AS shelter_reg_date_end,
	'DEFAULT' AS bed_rereg_code,
	TRUE AS overnight_eligible,
--	priority_open,
	priority_cd,
	priority_dd,
	priority_disabled,
	priority_med,
	priority_mh,
	priority_new,
	priority_ksh,
	COALESCE(last_residence_code,'0'),
	last_residence_ownr,
	svc_need_code,
	priority_comments AS comments,
	COALESCE(pb.staff_id,ab.staff_id,cb.staff_id,sys_user()) AS added_by,
	COALESCE(pb.staff_id,ab.staff_id,cb.staff_id,sys_user()) AS changed_by
FROM client c
	LEFT JOIN tbl_staff pb ON (c.priority_set_by=pb.staff_id)
	LEFT JOIN tbl_staff ab ON (c.added_by=ab.staff_id)
	LEFT JOIN tbl_staff cb ON (c.changed_by=cb.staff_id)
WHERE (priority_date_start IS NOT NULL OR priority_open
	OR c.gender_code IN ('1','4','6') OR (CURRENT_DATE-c.dob)/365.24>=60)
	AND c.client_id IN (SELECT client_id FROM tmp_active_client);


DROP TABLE tmp_active_client;
/*
CREATE INDEX tbl_shelter_reg_index_client_id ON tbl_shelter_reg (client_id);
CREATE INDEX tbl_shelter_reg_index_shelter_reg_date_end ON tbl_shelter_reg (shelter_reg_date_end);
CREATE INDEX tbl_shelter_reg_index_is_deleted ON tbl_shelter_reg (is_deleted);
CREATE INDEX tbl_shelter_reg_index_date_client_deleted ON tbl_shelter_reg (client_id,shelter_reg_date_end,is_deleted);
*/
END;
