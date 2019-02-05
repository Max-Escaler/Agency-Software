-------------------------------------
--
--        Jail stuff
--
-------------------------------------

CREATE OR REPLACE FUNCTION jail_post_insert_alert(INTEGER,INTEGER,INTEGER,TIMESTAMP,TIMESTAMP) RETURNS int4 AS '
DECLARE
	jid alias for $1;
	cid alias for $2;
	staff alias for $3;
	jd_start alias for $4;
	jd_end alias for $5;
	alert_txt text;
	alert_sub varchar(90);
BEGIN
	alert_sub := client_name(cid)
		|| (CASE 
			WHEN jd_end IS NULL 
				THEN '' was incarcerated on '' || to_char(jd_start::date,''MM/DD/YYYY'')
			ELSE
				'' was released from jail on '' || to_char(jd_end::date,''MM/DD/YYYY'') 
			END);
	alert_txt := (CASE WHEN staff=sys_user() 
				THEN ''This record was obtained from King County, and may not contain exact, or current, information.\n\n''
				ELSE '''' END) 
		||''client: '' || client_name(cid) || '' ('' || cid || '')\n''
		|| ''booking date: '' || to_char(jd_start::date,''MM/DD/YYYY'') || ''\n''
		|| ''release date: '' || COALESCE(to_char(jd_end::date,''MM/DD/YYYY''),'''') || ''\n''
		|| ''DAL due date: '' || COALESCE(to_char((jd_end::date + 7),''MM/DD/YYYY''),''NA'') || ''\n''
		|| ''days in jail (as of ''||to_char(current_date,''MM/DD/YYYY'')||''): '' || COALESCE(jd_end::date,current_date) - jd_start::date;
	INSERT INTO tbl_alert (
		staff_id,
		ref_table,
		ref_id,
		alert_subject,
		alert_text,
		added_by,
		changed_by
	)
	VALUES (staff,''jail'',jid,alert_sub,alert_txt,sys_user(),sys_user());
	RETURN 1;
END;' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION jail_post_update_alert(INTEGER,INTEGER,INTEGER,TIMESTAMP,TIMESTAMP) RETURNS int4 AS '
DECLARE
	jid alias for $1;
	cid alias for $2;
	staff alias for $3;
	jd_start alias for $4;
	jd_end alias for $5;
	alert_txt text;
	alert_sub varchar(90);
BEGIN
	alert_sub := client_name(cid)
		|| (CASE 
			WHEN jd_end IS NULL 
				THEN '' was incarcerated on '' || to_char(jd_start::date,''MM/DD/YYYY'')
			ELSE
				'' was released from jail on '' || to_char(jd_end::date,''MM/DD/YYYY'') 
			END);
	alert_txt := (CASE WHEN staff=sys_user() 
				THEN ''This record was obtained from King County, and may not contain exact, or current, information.\n\n''
				ELSE '''' END) 
		||''client: '' || client_name(cid) || '' ('' || cid || '')\n''
		|| ''booking date: '' || to_char(jd_start::date,''MM/DD/YYYY'') || ''\n''
		|| ''release date: '' || COALESCE(to_char(jd_end::date,''MM/DD/YYYY''),'''') || ''\n''
		|| ''DAL due date: '' || COALESCE(to_char((jd_end::date + 7),''MM/DD/YYYY''),''NA'') || ''\n''
		|| ''days in jail (as of ''||to_char(current_date::date,''MM/DD/YYYY'')||''): '' || COALESCE(jd_end::date,current_date) - jd_start::date;
	INSERT INTO tbl_alert (
		staff_id,
		ref_table,
		ref_id,
		alert_subject,
		alert_text,
		added_by,
		changed_by
	)
	VALUES (staff,''jail'',jid,alert_sub,alert_txt,sys_user(),sys_user());
	RETURN 1;
END;' LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION jail_insert() RETURNS trigger AS '
DECLARE
  staff integer[];
  cid integer;
  st integer;
  ends integer;
  res integer;
BEGIN
	cid := NEW.client_id;
	IF (SELECT jail_id FROM jail WHERE client_id=cid AND jail_date > NEW.jail_date LIMIT 1) IS NULL 
		AND (CURRENT_DATE - COALESCE(NEW.jail_date_end::date,CURRENT_DATE) < 31 ) THEN
		staff := staff_alert_client_assign(cid);
		st := 1;
		ends := array_count(staff);
		IF (ends > 0) THEN
			FOR i IN st..ends LOOP
	   			res := jail_post_insert_alert(NEW.jail_id,cid,staff[i],NEW.jail_date,NEW.jail_date_end);
		  	END LOOP;
		END IF;
	END IF;
	RETURN NEW;
END;' language 'plpgsql';

CREATE TRIGGER jail_insert
    AFTER INSERT ON tbl_jail FOR EACH ROW
    EXECUTE PROCEDURE jail_insert();

/*
	For update we only send alerts if:
		a) an end date is attached to a record previously containing no end date
		b) this end date is less than 30 days in the past
*/
CREATE OR REPLACE FUNCTION jail_update() RETURNS trigger AS '
DECLARE
  staff integer[];
  cid integer;
  st integer;
  ends integer;
  res integer;
  flag integer;
BEGIN
	cid := NEW.client_id;
	flag := 0;
	IF (NEW.jail_date_end IS NOT NULL AND OLD.jail_date_end IS NULL 
		AND (CURRENT_DATE - NEW.jail_date_end::date < 31 )) 
		AND (SELECT jail_id FROM jail WHERE client_id=cid AND jail_date > NEW.jail_date LIMIT 1) IS NULL THEN
		flag := 1;  -- send alert
	END IF;
	
	IF (flag = 1) THEN
		staff := staff_alert_client_assign(cid);
		st := 1;
		ends := array_count(staff);
		IF (ends > 0) THEN
			FOR i IN st..ends LOOP
				IF (staff[i] <> NEW.changed_by) THEN
		   			res := jail_post_update_alert(NEW.jail_id,cid,staff[i],NEW.jail_date,NEW.jail_date_end);
				END IF;
		  	END LOOP;
		END IF;
	END IF;
	RETURN NEW;
END;' language 'plpgsql';

CREATE TRIGGER jail_update
    AFTER UPDATE ON tbl_jail FOR EACH ROW
    EXECUTE PROCEDURE jail_update();

-------------------------------------
--
--        Hospital stuff
--
-------------------------------------

CREATE OR REPLACE FUNCTION hospital_post_insert_alert(INTEGER,INTEGER,INTEGER,DATE,DATE,VARCHAR(60),BOOLEAN) RETURNS int4 AS '
DECLARE
	jid alias for $1;
	cid alias for $2;
	staff alias for $3;
	hos_start alias for $4;
	hos_end alias for $5;
	facility alias for $6;
	voluntary alias for $7;
	alert_txt text;
	alert_sub varchar(90);
BEGIN
	alert_sub := client_name(cid)
		|| (CASE 
			WHEN hos_end IS NULL 
				THEN '' was admitted to '' || facility || '' on '' || to_char(hos_start::date,''MM/DD/YYYY'')
			ELSE
				'' was released from '' || facility || '' on '' || to_char(hos_end::date,''MM/DD/YYYY'') 
			END);
	alert_txt := (CASE WHEN staff=sys_user() 
				THEN ''This record was obtained from King County, and may not contain exact, or current, information.\n\n''
				ELSE '''' END) 
		||''client: '' || client_name(cid) || '' ('' || cid || '')\n''
		|| ''This hospitalization was: '' || (CASE WHEN voluntary THEN ''VOLUNTARY'' ELSE ''INVOLUNTARY'' END) || ''\n''
		|| ''admitted date: '' || to_char(hos_start::date,''MM/DD/YYYY'') || ''\n''
		|| ''release date: '' || COALESCE(to_char(hos_end::date,''MM/DD/YYYY''),'''') || ''\n''
		|| ''DAL due date: '' || COALESCE(to_char((hos_end::date + 7),''MM/DD/YYYY''),''NA'') || ''\n''
		|| ''days in hospital (as of ''||to_char(current_date,''MM/DD/YYYY'')||''): '' || COALESCE(hos_end,current_date) - hos_start;
	INSERT INTO tbl_alert (
		staff_id,
		ref_table,
		ref_id,
		alert_subject,
		alert_text,
		added_by,
		changed_by
	)
	VALUES (staff,''hospital'',jid,alert_sub,alert_txt,sys_user(),sys_user());
	RETURN 1;
END;' LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION hospital_post_update_alert(INTEGER,INTEGER,INTEGER,DATE,DATE,VARCHAR(60),BOOLEAN) RETURNS int4 AS '
DECLARE
	jid alias for $1;
	cid alias for $2;
	staff alias for $3;
	hos_start alias for $4;
	hos_end alias for $5;
	facility alias for $6;
	voluntary alias for $7;
	alert_txt text;
	alert_sub varchar(90);
BEGIN
	alert_sub := client_name(cid)
		|| (CASE 
			WHEN hos_end IS NULL 
				THEN '' was admitted to '' || facility || '' on '' || to_char(hos_start::date,''MM/DD/YYYY'')
			ELSE
				'' was released from '' || facility || '' on '' || to_char(hos_end::date,''MM/DD/YYYY'') 
			END);
	alert_txt := (CASE WHEN staff=sys_user() 
				THEN ''This record was obtained from King County, and may not contain exact, or current, information.\n\n''
				ELSE '''' END) 
		||''client: '' || client_name(cid) || '' ('' || cid || '')\n''
		|| ''This hospitalization was: '' || (CASE WHEN voluntary THEN ''VOLUNTARY'' ELSE ''INVOLUNTARY'' END) || ''\n''
		|| ''admitted date: '' || to_char(hos_start::date,''MM/DD/YYYY'') || ''\n''
		|| ''release date: '' || COALESCE(to_char(hos_end::date,''MM/DD/YYYY''),'''') || ''\n''
		|| ''DAL due date: '' || COALESCE(to_char((hos_end::date + 7),''MM/DD/YYYY''),''NA'') || ''\n''
		|| ''days in hospital (as of ''||to_char(current_date,''MM/DD/YYYY'')||''): '' || COALESCE(hos_end,current_date) - hos_start;
	INSERT INTO tbl_alert (
		staff_id,
		ref_table,
		ref_id,
		alert_subject,
		alert_text,
		added_by,
		changed_by
	)
	VALUES (staff,''hospital'',jid,alert_sub,alert_txt,sys_user(),sys_user());
	RETURN 1;
END;' LANGUAGE 'plpgsql';


CREATE OR REPLACE FUNCTION hospital_insert() RETURNS trigger AS '
DECLARE
  staff integer[];
  cid integer;
  st integer;
  ends integer;
  res integer;
BEGIN
	cid := NEW.client_id;
	--check for more recent record
	IF (SELECT hospital_id FROM hospital WHERE client_id=cid AND hospital_date > NEW.hospital_date LIMIT 1) IS NULL 
		AND (CURRENT_DATE - COALESCE(NEW.hospital_date_end,CURRENT_DATE) < 31 ) THEN
		staff := staff_alert_client_assign(cid);
		st := 1;
		ends := array_count(staff);
		IF (ends > 0) THEN
			FOR i IN st..ends LOOP
	   			res := hospital_post_insert_alert(NEW.hospital_id,cid,staff[i],NEW.hospital_date,NEW.hospital_date_end,NEW.facility,NEW.is_voluntary);
		  	END LOOP;
		END IF;
	END IF;
	RETURN NEW;
END;' language 'plpgsql';


CREATE TRIGGER hospital_insert
    AFTER INSERT ON tbl_hospital FOR EACH ROW
    EXECUTE PROCEDURE hospital_insert();

/*
	For update we only send alerts if:
		a) an end date is attached to a record previously containing no end date
		b) this end date is less than 30 days in the past
*/
CREATE OR REPLACE FUNCTION hospital_update() RETURNS trigger AS '
DECLARE
  staff integer[];
  cid integer;
  st integer;
  ends integer;
  res integer;
  flag integer;
BEGIN
	cid := NEW.client_id;
	flag := 0;
	IF (NEW.hospital_date_end IS NOT NULL AND OLD.hospital_date_end IS NULL 
		AND (CURRENT_DATE - NEW.hospital_date_end < 31 ))
		AND (SELECT hospital_id FROM hospital WHERE client_id=cid AND hospital_date > NEW.hospital_date LIMIT 1) IS NULL THEN
		flag := 1;  -- send alert
	END IF;
	
	IF (flag = 1) THEN
		staff := staff_alert_client_assign(cid);
		st := 1;
		ends := array_count(staff);
		IF (ends > 0) THEN
			FOR i IN st..ends LOOP
				IF (staff[i] <> NEW.changed_by) THEN
		   			res := hospital_post_update_alert(NEW.hospital_id,cid,staff[i],NEW.hospital_date,NEW.hospital_date_end,NEW.facility,NEW.is_voluntary);
				END IF;
		  	END LOOP;
		END IF;
	END IF;
	RETURN NEW;
END;' language 'plpgsql';


CREATE TRIGGER hospital_update
    AFTER UPDATE ON tbl_hospital FOR EACH ROW
    EXECUTE PROCEDURE hospital_update();

