-------------------------------------
--
--     generic array functions
--
-------------------------------------

CREATE OR REPLACE FUNCTION array_count(anyarray) RETURNS integer AS '
DECLARE
	a alias for $1;
	count INTEGER;
BEGIN
	count := replace(split_part(array_dims(a),'':'',2),'']'','''')::int;
	return count;
END;' LANGUAGE 'plpgsql' IMMUTABLE;

CREATE OR REPLACE FUNCTION array_unique(anyarray) RETURNS anyarray AS '
DECLARE
	a alias for $1;
	na alias for $0;
	count INTEGER;
	null_flag BOOLEAN;
BEGIN
	null_flag := false;
	count := array_count(a);
	IF (count > 0) THEN
		FOR i IN 1..count LOOP
			IF( a[i] IS NULL) THEN
				IF NOT null_flag THEN
					na:=COALESCE(array_append(na,a[i]),ARRAY[a[i]]);
					null_flag=true;
				END IF;
				continue;
			END IF;
			na := CASE WHEN na IS NOT NULL AND a[i]=ANY (na) THEN na
					ELSE COALESCE(array_append(na,a[i]),ARRAY[a[i]]) END;
		END LOOP;
	END IF;
	RETURN na;
END;' LANGUAGE 'plpgsql' IMMUTABLE;

CREATE OR REPLACE FUNCTION array_format(anyarray,text) RETURNS text AS '
DECLARE
	arr ALIAS FOR $1;
	delimiter ALIAS FOR $2;
	count INTEGER;
	out TEXT;
BEGIN
	count := array_count(arr);
	IF (count > 0) THEN
		FOR i IN 1..count LOOP
			out := COALESCE(out||delimiter,'''')||arr[i];
		END LOOP;
	END IF;
	RETURN out;
END;
' LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION array_lookup(anyarray,text) RETURNS text[] AS '
DECLARE
	arr ALIAS FOR $1;
	table ALIAS FOR $2;
	count INTEGER;
	out TEXT[];
BEGIN
	count := array_count(arr);
	IF (count > 0) THEN
		FOR i IN 1..count LOOP
			out := COALESCE(array_append(out,lookup_value($1[i],$2)),ARRAY[lookup_value($1[i],$2)]);
		END LOOP;
	END IF;
	RETURN out;
END;
' LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION lookup_value(text,text) RETURNS text AS '

	set table "l_"
	set field "_code"

	spi_exec "SELECT description FROM $table$2 WHERE $2$field = ''$1''"

	return $description
' LANGUAGE pltcl STABLE;

CREATE OR REPLACE FUNCTION array_sort(anyarray) RETURNS anyarray AS $$
  SELECT ARRAY(SELECT unnest($1) ORDER BY 1)
$$ LANGUAGE sql IMMUTABLE;
