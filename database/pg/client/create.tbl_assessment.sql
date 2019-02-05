CREATE TABLE      tbl_assessment (
	assessment_id		SERIAL PRIMARY KEY,
	client_id		INTEGER NOT NULL,
	survival_rating		SMALLINT NOT NULL,
	basic_rating		SMALLINT NOT NULL,
	physical_rating		SMALLINT NOT NULL,
	organization_rating	SMALLINT NOT NULL,
	mh_rating		SMALLINT NOT NULL,
	cd_rating		SMALLINT NOT NULL,
	communication_rating	SMALLINT NOT NULL,
	socialization_rating 	SMALLINT NOT NULL,
	homelessness_rating	SMALLINT NOT NULL,
	comments		TEXT,
	assessed_by		INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	assessed_at		DATE NOT NULL,
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


CREATE VIEW assessment AS SELECT
	assessment_id,
	client_id,
	survival_rating,
	basic_rating,
	physical_rating,
	organization_rating,
	mh_rating,
	cd_rating,
	communication_rating,
	socialization_rating,
	homelessness_rating,
	(survival_rating 
		+ basic_rating 
		+ physical_rating
		+ organization_rating
		+ mh_rating
		+ cd_rating
		+ communication_rating
		+ socialization_rating
		+ homelessness_rating) AS total_rating,
	comments,
	assessed_by,
	assessed_at,
	added_by,
	added_at,
	changed_by,
	changed_at,
	sys_log
   FROM tbl_assessment WHERE NOT is_deleted;

CREATE INDEX index_tbl_assessment_client_id_assessed_at ON tbl_assessment ( client_id,assessed_at );

