/* pltcl */
SELECT oid FROM pg_language WHERE lanname = 'pltcl';
SELECT oid FROM pg_proc WHERE proname = 'pltcl_call_handler' AND prorettype = (SELECT oid FROM pg_type WHERE typname = 'language_handler') AND pronargs = 0;
CREATE FUNCTION "pltcl_call_handler" () RETURNS language_handler AS '$libdir/pltcl' LANGUAGE C;
CREATE TRUSTED LANGUAGE "pltcl" HANDLER "pltcl_call_handler";

