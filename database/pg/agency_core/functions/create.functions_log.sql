CREATE OR REPLACE FUNCTION log_insert_verify() RETURNS trigger AS '
BEGIN
	IF (NEW.log_text IS NULL) THEN
		RAISE EXCEPTION ''log_text cannot be null'';
	END IF;

	IF (NEW.subject IS NULL) THEN
		RAISE EXCEPTION ''subject cannot be null'';
	END IF;

	RETURN NEW;
END;' LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION log_references( text ) RETURNS text AS '
DECLARE
	fulltext	ALIAS FOR $1;
	continued	BOOLEAN = false;
	noun		text;
	logtext		text;
	logreg		text;
	logrestreg	text;
	contrestreg	text;
	contreg		text;
	logno		text;
	result		integer[];

BEGIN
	result := NULL;
	logtext := fulltext;
	noun := ''log'';
    logreg := ''(?i)^(?:.*?)'' || noun || ''s?(?:\\\\s|\\\\n|<br>|<br \\\\/>)?(?:entry|no|number|\\\\#)?(?:\\\\s|<br>|<br \\\\/>)?([0-9]{1,7})(.*)$'';
    logrestreg := ''(?i)^(?:.*?)'' || noun || ''s?(?:\\\\s|\\\\n|<br>|<br \\\\/>)?(?:entry|no|number|\\\\#)?(?:\\\\s|<br>|<br \\\\/>)?(?:[0-9]{1,7})(.*)$'';
	contreg := ''(?i)^(?:,?\\\\s(?:[ \\\\#,+\\\\-\\\\&]|and|&amp;|\\\\n|<br>|<br \\\\/>)*[ ]*)?([0-9]{1,7})(([^0-9]|$).*?)$'';
	contrestreg := ''(?i)^(?:,?\\\\s(?:[ \\\\#,+\\\\-\\\\&]|and|&amp;|\\\\n|<br>|<br \\\\/>)*[ ]*)?(?:[0-9]{1,7})(.*)$'';
	LOOP
      IF ( continued ) THEN
			LOOP
			logno := SUBSTRING(logtext FROM contreg);
			IF logno IS NOT NULL AND logno <> '''' THEN
				IF ( result IS NULL ) OR ( logno <> ALL( result )) THEN
					result := COALESCE(result || logno::int,ARRAY[logno::int]);
				END IF;
				logtext := SUBSTRING(logtext FROM contrestreg);
			ELSE
				continued := false;
				EXIT;
			END IF;
			END LOOP;
		ELSIF SUBSTRING(logtext FROM logreg) <> '''' THEN
				logno := SUBSTRING(logtext FROM logreg);
				IF ( result IS NULL ) OR ( logno <> ALL( result )) THEN
					result := COALESCE(result || logno::int,ARRAY[logno::int]);
				END IF;
				logtext := SUBSTRING(logtext FROM logrestreg);
				continued := true;
		ELSE
			EXIT;
		END IF;
	END LOOP;
	RETURN result;
END;' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION post_log_references() RETURNS trigger AS '
DECLARE
  refs integer[];
  st integer;
  ends integer;
  res integer;
BEGIN
   refs := log_references(COALESCE(new.subject,'''') || COALESCE(new.log_text,''''));
   st := 1;
   ends := array_count(refs);
   IF (ends > 0) THEN
      FOR i IN st..ends LOOP
		INSERT INTO tbl_reference (
			from_table,
			from_id_field,
			from_id,
			to_table,
			to_id_field,
			to_id,
			added_at,
			added_by,
			changed_at,
			changed_by)
		VALUES (
			''log'',
			''log_id'',
			NEW.log_id,
			''log'',
			''log_id'',
			refs[i],
			current_timestamp,
			sys_user(),
			current_timestamp,
			sys_user());
        END LOOP;
    END IF;
    RETURN NEW;
END;' language 'plpgsql';

