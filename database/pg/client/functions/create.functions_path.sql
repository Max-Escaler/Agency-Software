/*
 *    WARNING: This file has been modified for the elimination of SHAMIS
 *
 *     DO NOT RUN THIS INTO THE AGENCY DATABASE
 *
 */

CREATE OR REPLACE FUNCTION client_get_initial_care_plan_date ( cid integer, asofdate date ) RETURNS DATE AS $$
DECLARE
	initcpdate DATE;
BEGIN
	SELECT INTO initcpdate dal_date FROM dal WHERE client_id = cid AND dal_code = 'INITCP' AND dal_date::date <= asofdate ORDER BY dal_date DESC;
	RETURN initcpdate;
END;$$ LANGUAGE plpgsql STABLE; 

CREATE OR REPLACE FUNCTION client_get_initial_care_plan_date ( cid integer ) RETURNS DATE AS $$
BEGIN
	RETURN client_get_initial_care_plan_date(cid,CURRENT_DATE);
END;$$ LANGUAGE plpgsql STABLE; 

/* 90-day cp dates */
CREATE OR REPLACE FUNCTION client_get_90_day_care_plan_date ( cid integer, asofdate date ) RETURNS DATE AS $$
DECLARE
	cp90date DATE;
BEGIN
	SELECT INTO cp90date dal_date FROM dal WHERE client_id = cid AND dal_code = '90CP' AND dal_date::date <= asofdate ORDER BY dal_date DESC;
	RETURN cp90date;
END;$$ LANGUAGE plpgsql STABLE; 

CREATE OR REPLACE FUNCTION client_get_90_day_care_plan_date ( cid integer ) RETURNS DATE AS $$
BEGIN
	RETURN client_get_90_day_care_plan_date(cid,CURRENT_DATE);
END;$$ LANGUAGE plpgsql STABLE; 

CREATE OR REPLACE FUNCTION is_path_enrolled( int4 ) RETURNS boolean AS $$
DECLARE
	cid			ALIAS FOR $1;
BEGIN
	RETURN is_path_enrolled(cid,CURRENT_DATE);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION is_path_enrolled( int4, date ) RETURNS boolean AS $$
DECLARE
	cid ALIAS FOR $1;
	pdate ALIAS FOR $2;
	cp_date DATE;
	benefit_start DATE;
	is_path BOOLEAN;
BEGIN
--	SELECT INTO cp_date init_cp_date FROM clin_client_view WHERE client_id = cid;
	cp_date := client_get_initial_care_plan_date(cid);
	benefit_start := last_tier_start ( cid,'60',pdate );
	is_path := CASE
			WHEN cp_date BETWEEN benefit_start AND pdate AND tier_program(tier(cid,pdate))='HOST' THEN TRUE
			ELSE FALSE
		END;
	RETURN is_path;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION to_path_yes_no(boolean) RETURNS text AS $$
DECLARE
      st ALIAS FOR $1;
      yesno VARCHAR(10);
      res text;
BEGIN
      yesno := CASE
            WHEN st THEN 'YES'
            WHEN NOT st THEN 'NO'
            WHEN st IS NULL THEN 'UNKNOWN'
      END;
      SELECT INTO res COALESCE(path_code,description) FROM l_path_yes_no WHERE path_yes_no_code = yesno;
      RETURN res;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION to_path_yes_no(boolean,boolean) RETURNS text AS $$
DECLARE
      st ALIAS FOR $1;
	null_ok ALIAS FOR $2;
      yesno VARCHAR(10);
      res text;
BEGIN
      yesno := CASE
            WHEN st THEN 'YES'
            WHEN NOT st THEN 'NO'
            WHEN st IS NULL AND NOT null_ok THEN 'UNKNOWN'
		ELSE null
      END;
      SELECT INTO res COALESCE(path_code,description) FROM l_path_yes_no WHERE path_yes_no_code = yesno;
      RETURN res;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION to_path_yes_null(boolean) RETURNS VARCHAR AS $$
DECLARE
      st ALIAS FOR $1;
      yesno VARCHAR(10);
BEGIN
      yesno := CASE
            WHEN st THEN 'Y'
		ELSE null
      END;
	RETURN yesno;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION make_path_id() RETURNS varchar(15) AS $$
DECLARE
	flag boolean;
	new_path_id varchar(15);
BEGIN
	flag := false;
	WHILE flag<>true LOOP
		SELECT INTO new_path_id 'PATH'|| (random()*10000000)::integer;
		IF (SELECT COUNT(*) FROM path_ids WHERE path_id=new_path_id) < 1 THEN
			flag := true;
		END IF;
	END LOOP;
	RETURN new_path_id;
END;$$ LANGUAGE plpgsql VOLATILE;


CREATE OR REPLACE FUNCTION get_clinical_veteran_status(cid int4) RETURNS VARCHAR AS $$
--This function will first look at Clinical Veteran Status, then AGENCY
DECLARE
	vet_stat VARCHAR(10);
BEGIN
	SELECT INTO vet_stat COALESCE(cvet.veteran_status_code,c.veteran_status_code)
		FROM client c
			LEFT JOIN veteran_status_clinical_current cvet USING (client_id)
	WHERE c.client_id = cid;
	RETURN vet_stat;
END;$$ LANGUAGE plpgsql STABLE;

--------------------------------------------
--
--            insert triggers
--
--------------------------------------------

CREATE OR REPLACE FUNCTION make_path_export_record(int4) RETURNS boolean AS $$
DECLARE 
	pid ALIAS FOR $1;
	existing int4;
	cur_pt RECORD;
	old_ex RECORD;
	old_pt RECORD;
	client_rec RECORD;
	cid int4;
	client_path_id varchar(15);
	clin_id int4;
	update BOOLEAN;

--new values
	new_client_gender_simple varchar(10);
	new_co_subab_disorder_code varchar(10);
	new_is_path_enrolled	BOOLEAN;
	new_is_first_contact	BOOLEAN;	
	new_services_to_unenrolled BOOLEAN;
	new_path_enrolled_date	DATE;
	new_veteran_status_code VARCHAR(10);
BEGIN
--determine if a record exists
	SELECT INTO existing export_path_tracking_id FROM tbl_export_path_tracking WHERE export_path_tracking_id = pid;
	IF existing IS NOT NULL THEN
		RAISE EXCEPTION 'this record already exists in tbl_export_path_tracking';
		RETURN FALSE;
	END IF;

--record does not exist

--verify/create path_id
	--client record
	SELECT INTO cid client_id FROM tbl_path_tracking WHERE path_tracking_id = pid;
	SELECT INTO client_rec * FROM tbl_client WHERE client_id = cid;
	clin_id := client_rec.clinical_id; --get_case_id(cid);
	--old path export record
	SELECT INTO old_ex * FROM tbl_export_path_tracking WHERE export_path_tracking_id < pid
		AND tbl_export_path_tracking.client_id = cid ORDER BY export_path_tracking_id DESC LIMIT 1;
	--current path tracking record
	SELECT INTO cur_pt * FROM tbl_path_tracking WHERE path_tracking_id = pid;
	--previous path tracking record
	SELECT INTO old_pt * FROM tbl_path_tracking WHERE path_tracking_id < pid 
		AND tbl_path_tracking.client_id = cid ORDER BY path_tracking_id DESC LIMIT 1;

--this is redundant, but doesn\'t really hurt anybody
	IF (SELECT path_id FROM path_ids WHERE case_id=clin_id) IS NULL THEN
		INSERT INTO path_ids VALUES (make_path_id(),clin_id);
	END IF;

	--clients path_id
	SELECT INTO client_path_id path_id FROM path_ids WHERE case_id = clin_id;

--new values
	new_client_gender_simple := client_gender_simple (cid);
	new_is_path_enrolled := is_path_enrolled(cid,cur_pt.service_date);
	new_is_first_contact := CASE
		WHEN (SELECT path_tracking_id
			FROM tbl_path_tracking p2 
			WHERE 
				(p2.client_id = cur_pt.client_id ) AND
				( (p2.service_date<=cur_pt.service_date AND p2.path_tracking_id<cur_pt.path_tracking_id)
				OR (p2.service_date < cur_pt.service_date AND p2.path_tracking_id>cur_pt.path_tracking_id) )
				AND ( p2.service_date >= last_tier_start(cur_pt.client_id,'60',cur_pt.service_date)
					OR last_tier_start(cur_pt.client_id,'60',cur_pt.service_date) IS NULL )
				LIMIT 1) IS NULL 
		THEN TRUE
		ELSE FALSE
		END;

	new_co_subab_disorder_code := CASE 
		WHEN new_is_path_enrolled AND (
			(SELECT imp.client_id FROM clinical_impression imp WHERE imp.client_id = cid
				AND clinical_impression_date BETWEEN last_tier_start(cid,'60',cur_pt.service_date) AND cur_pt.service_date
				AND (imp.alcohol_abuse OR imp.drug_abuse)
			ORDER BY clinical_impression_date DESC LIMIT 1) IS NOT NULL
			OR (SELECT dis.client_id FROM disability_clinical dis WHERE dis.client_id = cid
				AND dis.disability_clinical_date BETWEEN last_tier_start(cid,'60',cur_pt.service_date) AND cur_pt.service_date
				AND substance_abuse_clinical_code NOT IN ('8','9') --none and unknown
				ORDER BY dis.disability_clinical_date DESC LIMIT 1) IS NOT NULL
		)
			 THEN 'YES'
		WHEN new_is_path_enrolled AND (
			(SELECT imp.client_id FROM clinical_impression imp WHERE imp.client_id = cid
				AND clinical_impression_date BETWEEN last_tier_start(cid,'60',cur_pt.service_date) AND cur_pt.service_date
				AND (imp.alcohol_abuse IS FALSE AND imp.drug_abuse IS FALSE)
			ORDER BY clinical_impression_date DESC LIMIT 1) IS NOT NULL
			AND (SELECT dis.client_id FROM disability_clinical dis WHERE dis.client_id = cid
				AND dis.disability_clinical_date BETWEEN last_tier_start(cid,'60',cur_pt.service_date) AND cur_pt.service_date
				AND substance_abuse_clinical_code IN ('8','9') --none and unknown
				ORDER BY dis.disability_clinical_date DESC LIMIT 1) IS NOT NULL
		)
			 THEN 'NO'
		WHEN new_is_path_enrolled THEN
			'UNKNOWN'
		ELSE NULL
	END;
	new_services_to_unenrolled := CASE 
		WHEN NOT new_is_path_enrolled AND cur_pt.was_outreach_services THEN true
		ELSE false
		END;
	new_path_enrolled_date := client_get_initial_care_plan_date(cid);
	new_veteran_status_code := get_clinical_veteran_status( cid );
--determine if change
	IF old_pt.client_id IS NOT NULL AND (
			client_rec.dob <> old_ex.dob
		OR	client_rec.ethnicity_code <> old_ex.ethnicity_code
		OR	new_veteran_status_code <> old_ex.veteran_status_code
		OR	old_ex.client_gender_simple <> new_client_gender_simple
		OR	old_ex.co_subab_disorder_code <> new_co_subab_disorder_code
		OR	old_pt.path_housing_status_code <> cur_pt.path_housing_status_code
		OR	old_pt.path_housing_status_time_code <> cur_pt.path_housing_status_time_code
		OR	old_pt.path_principal_diagnosis_code <> cur_pt.path_principal_diagnosis_code
		OR	old_pt.co_occurring_disorder_code <> cur_pt.co_occurring_disorder_code )
	THEN
		update := true;
	ELSE
		update := false;
	END IF;
	INSERT INTO tbl_export_path_tracking VALUES (
		pid,
		cid,
		client_path_id,
		new_is_first_contact,
		new_services_to_unenrolled,
		new_is_path_enrolled,
		new_path_enrolled_date,
		update,
		new_client_gender_simple,
		client_rec.ethnicity_code,
		client_rec.dob,
		new_veteran_status_code,
		new_co_subab_disorder_code
	);
	RAISE NOTICE 'Inserted record into tbl_export_path_tracking';
	RETURN TRUE;
END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION path_tracking_insert() RETURNS trigger AS $$
DECLARE
	clin_id INTEGER;
	cid     INTEGER;
	pid INTEGER;
	sus boolean;
BEGIN
	cid := NEW.client_id;
	clin_id := get_case_id( cid );
--verify clinical
	IF clin_id IS NULL THEN
		RAISE EXCEPTION 'ERROR: Cannot insert non-clinical clients into tbl_path_tracking';
		RETURN NEW;
	END IF;
--verify/create path_id
	IF (SELECT path_id FROM path_ids WHERE case_id=clin_id) IS NULL THEN
		INSERT INTO path_ids VALUES (make_path_id(),clin_id);
	END IF;
--insert record into tbl_export_path_tracking
	pid := NEW.path_tracking_id;
	sus := make_path_export_record(pid);
	RETURN NEW;
END;$$ LANGUAGE plpgsql;
