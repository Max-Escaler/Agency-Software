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
	(db_revision_code,
	db_revision_description,
	agency_flavor_code,
	git_sha,
	git_tag,
	applied_at,
	comment,
	added_by,
	changed_by)

	 VALUES ('UPDATE_GENDER_CODES', /*UNIQUE_DB_MOD_NAME */
			'Changes codes from numberic to char', /* DESCRIPTION */
			'DONOR', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
			'', /* git SHA ID, if applicable */
			'db_mod.45', /* git tag, if applicable */
			current_timestamp, /* Applied at */
			'', /* comment */
			sys_user(),
			sys_user()
		  );


UPDATE tbl_l_gender SET description='X-'||description;
INSERT INTO tbl_l_gender VALUES ('FEMALE', 'Female',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('MALE', 'Male',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_M', 'Transgender (Female to Male)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_F', 'Transgender (Male to Female)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_MF', 'Transgender (F to M), CONSIDER FEMALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_FM', 'Transgender (M to F), CONSIDER MALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('TR_UNKNOWN', 'Transgender (Direction Unknown)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('UNKNOWN', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);

UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='FEMALE' WHERE gender_code='1';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='MALE' WHERE gender_code='2';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_M' WHERE gender_code='3';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_F' WHERE gender_code='4';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_MF' WHERE gender_code='5';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_FM' WHERE gender_code='6';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_UNKNOWN' WHERE gender_code='7';
UPDATE tbl_staff SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='UNKNOWN' WHERE gender_code='8';

/*
--Comment out for donor version, or if gender is not in your client table

UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='FEMALE' WHERE gender_code='1';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='MALE' WHERE gender_code='2';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_M' WHERE gender_code='3';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_F' WHERE gender_code='4';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_MF' WHERE gender_code='5';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_FM' WHERE gender_code='6';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='TR_UNKNOWN' WHERE gender_code='7';
UPDATE tbl_client SET changed_by=sys_user(),changed_at=current_timestamp,sys_log=COALESCE(sys_log||E'\n')||'Updating gender codes (db_mod.x)',gender_code='UNKNOWN' WHERE gender_code='8';
*/

DELETE FROM tbl_l_gender WHERE gender_code IN ('1','2','3','4','5','6','7','8');

/*
INSERT INTO tbl_l_gender VALUES ('1', 'Female',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('2', 'Male',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('3', 'Transgender (Female to Male)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('4', 'Transgender (Male to Female)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('6', 'Transgender (F to M), CONSIDER FEMALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('7', 'Transgender (M to F), CONSIDER MALE',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('5', 'Transgender (Direction Unknown)',sys_user(),current_timestamp,sys_user(),current_timestamp);
INSERT INTO tbl_l_gender VALUES ('8', 'Unknown',sys_user(),current_timestamp,sys_user(),current_timestamp);
*/
/*
COMMIT;
*/
