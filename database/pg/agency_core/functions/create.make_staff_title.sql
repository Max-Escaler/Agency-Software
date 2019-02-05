CREATE OR REPLACE FUNCTION make_staff_title(pos VARCHAR,fac VARCHAR, proj VARCHAR, shift VARCHAR ) RETURNS text AS $$
DECLARE
	staff_title TEXT;
	project_description TEXT;
	position_description TEXT;
	facility_description TEXT;
        shift_description TEXT;
BEGIN

	SELECT INTO project_description COALESCE(short_description,description)
		FROM l_agency_project WHERE agency_project_code = proj;

	SELECT INTO position_description description
		FROM l_staff_position WHERE staff_position_code = pos;

        SELECT INTO facility_description COALESCE(short_description,description)
		FROM l_agency_facility WHERE agency_facility_code = fac;

	SELECT INTO shift_description description
		FROM l_staff_shift WHERE staff_shift_code = shift;


	staff_title := CASE 
			/* exceptions */
	                WHEN pos = 'ADMINSUPP'  THEN position_description || ' (' || facility_description || ')'

	                WHEN pos = 'RC' AND proj NOT IN ('HOUSFLEX', 'CONNECT', 'HOUSONCALL')
				THEN project_description || ' ' || position_description || ' (' || shift_description || ')'

	                WHEN pos = 'RC' AND proj = 'HOUSFLEX' THEN position_description || ' (Flex)'

	                WHEN ((pos IN ('SUPSHLTAST', 'SUPSHLT', 'CNSLRSHLT')) 
				OR (pos = 'RC' AND proj IN ('CONNECT', 'HOUSONCALL')))
				THEN position_description || ' (' || shift_description || ')'

			WHEN (pos IN ('VOLUNTEER', 'ASSTPROJ', 'MGRPROJ', 'RN') AND proj = 'SHELTER') THEN ltrim(project_description||' '||position_description, 'The Main ')

	                WHEN pos = 'ASST_MGR' AND proj = 'HOUSADMIN' THEN 'Asst. Housing Manager for Administration'

			WHEN pos IN ('SYSUSER','CNSLRIR','SUPIR','FACMGR', 'MGRENTRY', 'MGRSUPP', 'CNSLRSHLT', 'FUNDMGR', 'FUNDLEAD', 'FUNDCOORD', 'MGRADMIN', 'BSNSSMGR', 'MGR_ASSET', 'HOUSE_SCH' )
				OR proj IN ('MEDICAL','IS','HR') THEN position_description

			/* this is the default */
			ELSE project_description||' '||position_description

		END;

	RETURN staff_title;


END; $$ LANGUAGE plpgsql STABLE;