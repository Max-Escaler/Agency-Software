CREATE OR REPLACE VIEW db_agency_relations AS
SELECT c.oid AS db_agency_relations_id,
	n.nspname AS schema, 
	c.relname AS name, 
	CASE c.relkind 
		WHEN 'r' THEN 'table' 
		WHEN 'v' THEN 'view' 
		WHEN 'i' THEN 'index' 
		WHEN 'S' THEN 'sequence' 
		WHEN 's' THEN 'special' 
	END AS type, 
	pg_catalog.obj_description(c.oid, 'pg_class') AS description,
	pg_get_viewdef(c.relname) AS view_definition, 
	u.usename AS owner,
	c.relacl AS access_privileges
FROM pg_catalog.pg_class c 
	LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner 
	LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace 
WHERE NOT (nspname ='pg_catalog' OR nspname='pg_toast') 
	AND pg_catalog.pg_table_is_visible(c.oid) IS TRUE ;
