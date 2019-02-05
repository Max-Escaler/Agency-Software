CREATE OR REPLACE VIEW alert_notify_enabled_objects AS

SELECT REPLACE(cc.relname,'tbl_','') AS alert_object_code,
	INITCAP(REPLACE(REPLACE(cc.relname,'tbl_',''),'_',' ')) AS description
FROM pg_catalog.pg_trigger t 
     LEFT JOIN pg_catalog.pg_class cc ON ( t.tgrelid = cc.oid )
WHERE t.tgname ~ '_alert_notify$'
	AND (NOT tgisinternal  OR NOT EXISTS  
		   (SELECT 1 FROM pg_catalog.pg_depend d    
			     JOIN pg_catalog.pg_constraint c ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
			WHERE d.classid = t.tableoid AND d.objid = t.oid AND d.deptype = 'i' AND c.contype = 'f')
	);
