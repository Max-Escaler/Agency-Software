BEGIN;

INSERT INTO tbl_db_revision_history 
	(db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by)

	 VALUES ('ENABLE_MULTIPLE_ETHNICITIES', /*UNIQUE_DB_MOD_NAME */
			'Genericizes (somewhat) client child records, and applies to disabilties and ethnicities.  Removes ethnicity from client table, adds ethnicity table.  Creates backup of client ethnicity data.' , /* DESCRIPTION */
			'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'multiple_ethnicity', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );

/* Create ethnicity table */
\i ../client/create.tbl_ethnicity.sql

/* Create ethnicity backup */
SELECT 
	client_id,
	ethnicity_code,
	added_by,
	added_at,
	changed_by,
	changed_at
INTO client_ethnicity_backup
FROM client;

/* Populate ethnicity table */
INSERT INTO tbl_ethnicity
 (client_id,
 ethnicity_code,
 ethnicity_date,
 added_by,
 changed_by,
 added_at,
 changed_at)
SELECT
 client_id,
 ethnicity_code,
 added_at::date AS ethnicity_date,
 added_by,
 changed_by,
 added_at,
 changed_at
FROM client
WHERE ethnicity_code <> '12';

/* Enable Table Logging */
SELECT * INTO tbl_ethnicity_log FROM tbl_ethnicity LIMIT 0;
ALTER TABLE tbl_ethnicity_log ADD COLUMN trigger_mode VARCHAR(10);
ALTER TABLE tbl_ethnicity_log ADD COLUMN trigger_tuple VARCHAR(5);
ALTER TABLE tbl_ethnicity_log ADD COLUMN trigger_changed TIMESTAMP;
ALTER TABLE tbl_ethnicity_log ADD COLUMN trigger_id BIGINT;
CREATE SEQUENCE tbl_ethnicity_log_id;
SELECT SETVAL('tbl_ethnicity_log_id', 1, FALSE);
ALTER TABLE tbl_ethnicity_log ALTER COLUMN trigger_id SET DEFAULT NEXTVAL('tbl_ethnicity_log_id');

-- create trigger
CREATE TRIGGER tbl_ethnicity_log_chg 
--	AFTER UPDATE OR INSERT OR DELETE ON tbl_ethnicity 
	AFTER UPDATE OR INSERT OR DELETE ON tbl_ethnicity 
	FOR EACH ROW EXECUTE PROCEDURE table_log();
-- Disable updates & deletes of log table
CREATE RULE tbl_ethnicity_log_nodelete AS
	ON DELETE TO tbl_ethnicity_log DO INSTEAD NOTHING;
CREATE RULE tbl_ethnicity_log_noupdate AS
	ON UPDATE TO tbl_ethnicity_log DO INSTEAD NOTHING;

/* drop client view */
DROP VIEW client;

/* remove ethnicity_code from tbl_client and tbl_client_log */
ALTER TABLE tbl_client DROP COLUMN ethnicity_code;
ALTER TABLE tbl_client_log DROP COLUMN ethnicity_code;

/* add client view */
\i ../client/create.view.client.sql

/* Replace ethnicity functions */
\i ../client/functions/create.functions_client.sql

/* Remove "multiple" option from lookup */
UPDATE tbl_l_ethnicity
	SET is_deleted=TRUE,
	deleted_by=sys_user(),
	deleted_at=current_timestamp,
	deleted_comment='Removing multiple ethnicity option since they now have their own table.'
WHERE ethnicity_code='12';


COMMIT;
