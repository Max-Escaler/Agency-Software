/*

SELECT CASE WHEN p.proretset THEN 'setof ' ELSE '' END ||
  pg_catalog.format_type(p.prorettype, NULL) as "Result data type",
  n.nspname as "Schema",
  p.proname as "Name",
  pg_catalog.oidvectortypes(p.proargtypes) as "Argument data types"
FROM pg_catalog.pg_proc p
     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace
WHERE p.prorettype <> 'pg_catalog.cstring'::pg_catalog.regtype
      AND p.proargtypes[0] <> 'pg_catalog.cstring'::pg_catalog.regtype
      AND NOT p.proisagg
      AND pg_catalog.pg_function_is_visible(p.oid)
ORDER BY 2, 3, 1, 4;



SELECT CASE WHEN p.proretset THEN 'setof ' ELSE '' END ||
  pg_catalog.format_type(p.prorettype, NULL) as "Result data type",
  n.nspname as "Schema",
  p.proname as "Name",
  pg_catalog.oidvectortypes(p.proargtypes) as "Argument data types",
  u.usename as "Owner",
  l.lanname as "Language",
  p.prosrc as "Source code",
  pg_catalog.obj_description(p.oid, 'pg_proc') as "Description"
FROM pg_catalog.pg_proc p
     LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace
     LEFT JOIN pg_catalog.pg_language l ON l.oid = p.prolang
     LEFT JOIN pg_catalog.pg_user u ON u.usesysid = p.proowner
WHERE p.prorettype <> 'pg_catalog.cstring'::pg_catalog.regtype
      AND p.proargtypes[0] <> 'pg_catalog.cstring'::pg_catalog.regtype
      AND NOT p.proisagg
      AND pg_catalog.pg_function_is_visible(p.oid)
      AND p.proname ~ '^table_alert_no'
ORDER BY 2, 3, 1, 4;
*/

CREATE OR REPLACE VIEW db_agency_functions AS
SELECT p.proname as name,
	l.lanname AS language,
	pg_catalog.format_type(p.prorettype, NULL) as result_data_type,
	pg_catalog.oidvectortypes(p.proargtypes) as argument_data_types,
	p.prosrc AS source_code
FROM pg_catalog.pg_proc p
	LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace
     LEFT JOIN pg_catalog.pg_language l ON l.oid = p.prolang
     LEFT JOIN pg_catalog.pg_user u ON u.usesysid = p.proowner
WHERE p.prorettype != 'pg_catalog.cstring'::pg_catalog.regtype
	AND p.proargtypes[0] != 'pg_catalog.cstring'::pg_catalog.regtype
	AND NOT p.proisagg
	AND pg_catalog.pg_function_is_visible(p.oid)
	AND n.nspname='public'
ORDER BY 1;
