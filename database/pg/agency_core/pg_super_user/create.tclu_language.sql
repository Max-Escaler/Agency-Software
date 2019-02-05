/* PL/tclu is only required if email notification is desired. */
SELECT oid FROM pg_language WHERE lanname = 'pltclu';
SELECT oid FROM pg_proc WHERE proname = 'pltclu_call_handler' AND prorettype = (SELECT oid FROM pg_type WHERE typname = 'language_handler') AND pronargs = 0;
CREATE FUNCTION "pltclu_call_handler" () RETURNS language_handler AS '$libdir/pltcl' LANGUAGE C;
CREATE LANGUAGE "pltclu" HANDLER "pltclu_call_handler";

