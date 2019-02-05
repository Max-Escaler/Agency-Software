BEGIN;

/*
 * db_mod.TEMPLATE
 */
 
/*
NOTE:  I believe it is not possible to know the
git SHA ID before actually making a commit.

It is possible to know a git tag, and include
that in the commit.
*/

INSERT INTO tbl_db_revision_history 
	(
	db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by
	)

	 VALUES (
		'ADD_FORMATTED_NUMBER_TO_PHONE', /*UNIQUE_DB_MOD_NAME */
		'Adds formatted phone number to phone view. Also adds phone_current view',
		'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
		'', /* git SHA ID, if applicable */
		'db_mod.60', /* git tag, if applicable */
		current_timestamp, /* Applied at */
		'', /* comment */
		sys_user(),
		sys_user()
	 );


DROP VIEW phone;
CREATE  VIEW phone AS
SELECT tbl_phone.*,
	number
	|| CASE WHEN extension IS NOT NULL THEN ', ext: ' || extension ELSE '' END
	|| ' (' || description || ')'
	AS number_f
FROM tbl_phone LEFT JOIN l_phone_type USING (phone_type_code)
WHERE NOT tbl_phone.is_deleted;

CREATE OR REPLACE VIEW phone_current AS
SELECT * FROM phone WHERE (phone_date <= current_date) AND (COALESCE(phone_date_end,current_date)>=current_date);

COMMIT;

