CREATE OR REPLACE VIEW table_log_enabled_tables AS

SELECT cc.relname AS table
FROM pg_trigger t
	LEFT JOIN pg_class cc ON t.tgrelid = cc.oid
WHERE t.tgname ~ '_log_chg$'::text AND (NOT t.tgisinternal OR NOT (EXISTS ( 
	SELECT 1
	FROM pg_depend d
		JOIN pg_constraint c ON d.refclassid = c.tableoid AND d.refobjid = c.oid
	WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i'::char AND c.contype = 'f'::char)))
;

