
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

	 VALUES ('UN_DESC_FACILITY_LABELS',
			'Remove DESC references from facility labels.',
			'CLIENT',
			'',
			'db_mod.41',
			current_timestamp,
			'', 
			sys_user(),
			sys_user()
		  );

UPDATE tbl_l_facility SET
	facility_code='SHEL-OWN',
	description='Emergency Shelter (own)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Updating labels per db_mod.41'
WHERE
	facility_code='SHEL-DESC'
	AND description='Emergency Shelter (DESC)';

UPDATE tbl_l_facility SET
	description='Emergency Shelter (not own)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Updating labels per db_mod.XX'
WHERE
	facility_code='SHEL-OTH'
	AND description='Emergency Shelter (not DESC)';

UPDATE tbl_l_facility SET
	facility_code='SUPP-OWN',
	description='Supportive Housing (own)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Updating labels per db_mod.XX'
WHERE
	facility_code='SUPP-DESC'
	AND description='Supportive Housing (DESC)';

UPDATE tbl_l_facility SET
	description='Supportive Housing (not own)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Updating labels per db_mod.XX'
WHERE
	facility_code='SUPP-OTH'
	AND description='Supportive Housing (non-DESC)';

UPDATE tbl_l_facility SET
	description='Transitional Housing (not own)',
	changed_by=sys_user(),
	changed_at=current_timestamp,
	sys_log=COALESCE(sys_log||E'\n','') || 'Updating labels per db_mod.XX'
WHERE
	facility_code='TRANS-OTH'
	AND description='Transitional Housing (not DESC)';

/* new code */
INSERT INTO tbl_l_facility VALUES ('TRANS-OWN', 'Transitional Housing (own)', 'Transitional Housing', '207', 'TRANSITION',sys_user(),current_timestamp,sys_user(),current_timestamp);

COMMIT; 
