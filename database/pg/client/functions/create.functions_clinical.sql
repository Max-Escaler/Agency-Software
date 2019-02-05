/******************************
   All Functions Clinical
*******************************/

CREATE OR REPLACE FUNCTION get_case_id ( cid int4 ) RETURNS int4 AS $$
DECLARE
      case_id int4;
BEGIN
      SELECT INTO case_id clinical_id FROM tbl_client WHERE NOT is_deleted AND client_id = cid;
      RETURN case_id;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION client_get_id_from_case_id ( int4 ) RETURNS int4 AS $$
	SELECT client_id FROM client WHERE clinical_id = $1;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_get_kcid ( INTEGER ) RETURNS INTEGER AS $$
	SELECT king_cty_id FROM client WHERE client_id = $1;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_get_id_from_kcid ( INTEGER ) RETURNS INTEGER AS $$
	SELECT client_id FROM client WHERE king_cty_id = $1;
$$ LANGUAGE sql STABLE;

/*******************************
        TIER FUNCTIONS
********************************/

CREATE OR REPLACE FUNCTION tier_program( /*tier*/ varchar(3) ) RETURNS text AS $$

	SELECT COALESCE(clinical_project_code,description) FROM l_benefit_type WHERE benefit_type_code = $1;

$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION tier ( cid int4, asof date, ttype varchar ) RETURNS text AS $$
DECLARE
        ctier   varchar;
BEGIN

	IF ttype = 'ALL' THEN

		SELECT INTO ctier
			benefit_type_code
		FROM clinical_reg
		WHERE client_id = cid
			AND clinical_reg_date <= asof
			AND (clinical_reg_date_end >= asof OR clinical_reg_date_end IS NULL)
			AND kc_authorization_status_code NOT IN ('CX')
	
		ORDER BY clinical_reg_date DESC;

	ELSIF ttype IN ('HOST','SAGE') THEN

		SELECT INTO ctier
			benefit_type_code
		FROM clinical_reg
		WHERE client_id = cid
			AND tier_program(benefit_type_code) = ttype
			AND clinical_reg_date <= asof
			AND (clinical_reg_date_end >= asof OR clinical_reg_date_end IS NULL)
			AND kc_authorization_status_code NOT IN ('CX')
	
		ORDER BY clinical_reg_date DESC;

	ELSE

		SELECT INTO ctier
			benefit_type_code
		FROM clinical_reg
		WHERE client_id = cid
			AND benefit_type_code = ttype
			AND clinical_reg_date <= asof
			AND (clinical_reg_date_end >= asof OR clinical_reg_date_end IS NULL)
			AND kc_authorization_status_code NOT IN ('CX')
	
		ORDER BY clinical_reg_date DESC;

	END IF;

	RETURN ctier;

END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION tier( int4 ) RETURNS text AS $$
DECLARE
        cid             ALIAS FOR $1;
        ctier   varchar;
BEGIN
        SELECT INTO ctier tier( cid, current_date);
        RETURN ctier;
END;$$ LANGUAGE 'plpgsql' STABLE;

CREATE OR REPLACE FUNCTION tier( cid int4, asof date ) RETURNS text AS $$
BEGIN

	RETURN tier(cid,asof,'ALL');

END;$$ LANGUAGE 'plpgsql' STABLE;

CREATE OR REPLACE FUNCTION is_special_pop ( cid int4, sp_date date ) RETURNS boolean AS $$
DECLARE
	c          RECORD;
	clin_id	   INTEGER;
	ethnicity  BOOLEAN;
	gender     BOOLEAN;
	hispanic   BOOLEAN;
	disab      BOOLEAN;
	sexmin     BOOLEAN;
BEGIN

	SELECT INTO c * FROM client WHERE client_id = cid;
	clin_id := c.clinical_id;

	IF clin_id IS NULL THEN
		RETURN FALSE;
	END IF;

	ethnicity := CASE WHEN c.ethnicity_code NOT IN ('0','1') THEN true ELSE false END;
	gender    := CASE WHEN c.gender_code NOT IN ('1','2','8') THEN true ELSE false END;
	hispanic  := CASE WHEN c.hispanic_origin_code NOT IN ('999','998') THEN true ELSE false END;
	sexmin    := CASE WHEN c.sexual_minority_status_code = '2' THEN true ELSE false END;

	SELECT INTO disab CASE WHEN d.client_id IS NOT NULL THEN true ELSE false END
		FROM disability_clinical d WHERE d.client_id = cid AND disability_clinical_date <= sp_date
			AND d.disability_clinical_code IN ('23','32','33')
	ORDER BY disability_clinical_date DESC LIMIT 1;

	IF ethnicity OR gender OR hispanic OR disab OR sexmin THEN
		RETURN true;
	END IF;

	RETURN false;
END;$$ LANGUAGE 'plpgsql' STABLE;

CREATE OR REPLACE FUNCTION tier_sort ( varchar ) RETURNS integer AS $$
BEGIN
/*
 * currently (8/2005) available tier status codes and their ranking
 * AA - 2
 * CX - 0
 * ID - 6?
 * KC - 6?
 * PN - 4
 * SX - 6?
 * TM - 1
 * UA - 3
 * WL - 5
 */
	RETURN
		CASE
			WHEN $1='CX'  THEN 0
			WHEN $1='TM'  THEN 1
			WHEN $1='AA'  THEN 2
			WHEN $1='UA'  THEN 3
			WHEN $1='PN'  THEN 4
			WHEN $1='WL'  THEN 5
			ELSE 6
		END;
END;$$ LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION last_tier_start_continuous( int4, varchar, date ) RETURNS date AS $$
DECLARE
	cid ALIAS FOR $1;
      ben_code ALIAS FOR $2;
      bdate ALIAS FOR $3;
	s_date DATE;
	p_date DATE;
BEGIN
	SELECT INTO s_date last_tier_start( cid, ben_code, bdate );
	SELECT INTO p_date last_tier_start( cid, ben_code, s_date-1 );
 	RETURN CASE WHEN p_date IS NOT NULL 
		THEN last_tier_start_continuous( cid, ben_code, p_date )
		ELSE s_date END;
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION tier_end_continuous( cid int4, ben_code varchar, bdate date) RETURNS date AS $$
DECLARE
	e_date DATE;
	n_date DATE;
BEGIN
	SELECT INTO e_date next_tier_end( cid, ben_code, bdate);
	SELECT INTO n_date next_tier_end( cid, ben_code, e_date + 1);
	RETURN CASE WHEN  n_date IS NOT NULL
		THEN tier_end_continuous( cid, ben_code, n_date )
		ELSE e_date END;
END; $$ LANGUAGE plpgsql STABLE;

/*
This does not cover 3R, 98, 34 or 75
*/
CREATE OR REPLACE FUNCTION last_tier_start( int4, varchar, date ) RETURNS date AS $$
DECLARE
	cid ALIAS FOR $1;
      ben_code ALIAS FOR $2;
      bdate ALIAS FOR $3;
	s_date DATE;
BEGIN
	IF ben_code='ANY_TIER' THEN
		SELECT INTO s_date MAX(clinical_reg_date) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code != 'CX' AND clinical_reg_date <= bdate;
	ELSIF ben_code IN ('HOST','SAGE') THEN
		SELECT INTO s_date MAX(clinical_reg_date) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code != 'CX' AND clinical_reg_date <= bdate
			AND tier_program(benefit_type_code) = ben_code;
	ELSE
		SELECT INTO s_date MAX(clinical_reg_date) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code != 'CX' AND clinical_reg_date <= bdate
			AND benefit_type_code = ben_code;
	END IF;
	RETURN s_date;
END;$$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION next_tier_end( cid int4, ben_code varchar, bdate date) RETURNS date AS $$
DECLARE
	/*
	 * Given a date, this function will find the next end date after the given date
	 */
	e_date DATE;
BEGIN
	IF ben_code = 'ANY_TIER' THEN
		SELECT INTO e_date MIN(clinical_reg_date_end) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code <> 'CX' AND clinical_reg_date_end >= bdate;
	ELSIF ben_code IN ('HOST','SAGE') THEN
		SELECT INTO e_date MIN(clinical_reg_date_end) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code <> 'CX' AND clinical_reg_date_end >= bdate
			AND tier_program(benefit_type_code) = ben_code;
	ELSE
		SELECT INTO e_date MIN(clinical_reg_date_end) FROM clinical_reg WHERE client_id = cid
			AND kc_authorization_status_code <> 'CX' AND clinical_reg_date_end >= bdate
			AND benefit_type_code = ben_code;
	END IF;
	RETURN e_date;
END; $$ LANGUAGE plpgsql STABLE;


CREATE OR REPLACE FUNCTION last_tier_start( cid int4, ben_code varchar ) RETURNS date AS $$
BEGIN
	RETURN last_tier_start( cid, ben_code, CURRENT_DATE );
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION last_tier_start( int4, date ) RETURNS date AS $$
BEGIN
	RETURN last_tier_start( $1, 'ANY_TIER'::varchar, $2);
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION last_tier_start( int4 ) RETURNS date AS $$
BEGIN
	RETURN last_tier_start( $1, tier($1));
END;$$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION next_tier_end( cid int4, ben_code varchar ) RETURNS date AS $$
BEGIN
	RETURN next_tier_end( cid, ben_code, CURRENT_DATE);
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION next_tier_end( cid int4, date ) RETURNS date AS $$
BEGIN
	RETURN next_tier_end( cid, 'ANY_TIER'::varchar, $2);
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION next_tier_end( cid int4 ) RETURNS date AS $$
BEGIN
	RETURN next_tier_end( cid, tier(cid));
END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION cg_dal_cutoff() RETURNS BIGINT AS $$
BEGIN
--last DAL ID to be entered in SHAMIS. This is used by various views and functions
	RETURN 811099;
END;$$ LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION cg_dal_kc_late() RETURNS INTEGER AS $$
BEGIN
--time limit, in days, to submit service data to the county
	RETURN 30;
END;$$ LANGUAGE plpgsql IMMUTABLE;

CREATE OR REPLACE FUNCTION client_primary_diagnosis ( int4 /* cid */, date ) RETURNS VARCHAR AS $$
/*
 * Return primary diagnosis
 */
	SELECT diagnosis_code FROM diagnosis WHERE client_id = $1 
		AND diagnosis_code IS NOT NULL --exclude GAFs
		AND diagnosis_date <= $2
	ORDER BY diagnosis_date DESC, is_primary_treatment_focus DESC, diagnosis_id ;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION client_primary_diagnosis_icd ( int4, date ) RETURNS VARCHAR AS $$
	SELECT COALESCE(icd_code,diagnosis_code) FROM l_diagnosis WHERE diagnosis_code = client_primary_diagnosis($1,$2);
$$ LANGUAGE sql STABLE;


CREATE OR REPLACE FUNCTION client_change_export_demographic() RETURNS TRIGGER AS $$
/*
 * Detect 
 *  a) if a client is currently enrolled with a case-rate kc benefit
 *
 *    and if so, 
 *
 *  b) if fields relevant to king county tier have changed,
 * 
 *  If both these conditions are met, then the record is set to be uploaded
 */
DECLARE
	benefit VARCHAR;
	cid	INTEGER;
BEGIN

	/* Nothing for deleted records */
	IF NEW.is_deleted THEN
		RETURN NEW;
	END IF;

	cid := NEW.client_id;

	/* a) Is client currently in HOST or SAGE */
	benefit := tier_program(tier(cid));
	IF (benefit IS NULL OR benefit NOT IN ('HOST','SAGE')) THEN

		RETURN NEW;

	END IF;

	/*
         * Have any of these fields changed:
	 */

	IF (OLD.ssn <> NEW.ssn

		OR OLD.dob <> NEW.dob

		OR OLD.name_last <> NEW.name_last

		OR OLD.name_first <> NEW.name_first

		OR COALESCE(OLD.name_middle,'') <> COALESCE(NEW.name_middle,'') --must account for NULLS

		OR COALESCE(OLD.name_suffix,'') <> COALESCE(NEW.name_suffix,'')
	
		OR OLD.gender_code <> NEW.gender_code

		OR OLD.ethnicity_code <> NEW.ethnicity_code

		OR OLD.needs_interpreter_code <> NEW.needs_interpreter_code

		OR COALESCE(OLD.language_code,'') <> COALESCE(NEW.language_code,'')

	--hispanic origins, sexual minority status
		) THEN

		--make sure record isn't already set to be exported
		IF (SELECT object_id FROM export_kc_transaction_upload
			WHERE object_type='client' AND object_id = NEW.client_id) IS NULL THEN
			-- INSERT record to flag for upload		
			INSERT INTO tbl_export_kc_transaction (
				object_type,
				object_id,
				added_by,
				changed_by
			) VALUES (
				'client',
				NEW.client_id,
				NEW.changed_by,
				NEW.changed_by
			);
		END IF;

	END IF;

	RETURN NEW;

END; $$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION staff_register_with_county() RETURNS trigger AS $$
/*
 * When old_mh_id is set in the staff record, the staff is registered
 * with the county
 */
DECLARE
	phone_rec_id INTEGER;
BEGIN

	IF OLD.old_mh_id IS NULL AND NEW.old_mh_id IS NOT NULL THEN

		--make sure staff has a phone number
		SELECT INTO phone_rec_id staff_phone_id FROM staff_phone sp
			WHERE sp.staff_id = NEW.staff_id AND phone_type_code = 'WORK' ORDER BY staff_phone_id DESC LIMIT 1;
		IF phone_rec_id IS NULL THEN
			RAISE EXCEPTION 'No work phone record found for staff %. Cannot register with the county',NEW.staff_id;
		ELSE
			INSERT INTO tbl_export_kc_transaction (
				object_type,
				object_id,
				added_by,
				changed_by
			) VALUES (
				'staff_phone',
				phone_rec_id,
				NEW.changed_by,
				NEW.changed_by
			);
		END IF;

		--make sure record isn't already set to be uploaded
		IF (SELECT object_id FROM export_kc_transaction_upload
			WHERE object_type='staff' AND object_id = NEW.staff_id) IS NULL THEN

			INSERT INTO tbl_export_kc_transaction (
				object_type,
				object_id,
				added_by,
				changed_by
			) VALUES (
				'staff',
				NEW.staff_id,
				NEW.changed_by,
				NEW.changed_by
			);

		END IF;

	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION clinical_reg_request_process_insert() RETURNS trigger AS $$
/*
 *   For non-KC funding sources, posts record to clinical_reg
 */
DECLARE

BEGIN
	IF NEW.funding_source_code = 'KC' THEN

		RETURN NEW;

	END IF;

	INSERT INTO tbl_clinical_reg (
		client_id,
		clinical_reg_date,
		clinical_reg_date_end,
		benefit_type_code,
		funding_source_code,
		kc_authorization_status_code,
		added_by,
		changed_by,
		sys_log
	) VALUES (
		NEW.client_id,
		NEW.assessment_date,
		NEW.assessment_date + '1 year'::interval - '1 day'::interval,
		NEW.benefit_type_code,
		NEW.funding_source_code,
		'AA',
		NEW.added_by,
		NEW.changed_by,
		'Insert trigger auto-creating clinical_reg from clinical_reg_request'
	);

	RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION kc_authorization_response_insert_update() RETURNS trigger AS $$

/*
 *  Insert or update tbl_clinical_reg accordingly

	--beginning of transaction structure
	authorization_number		BIGINT,
	reporting_unit_id		VARCHAR(3),
	agency_case_id			VARCHAR(10),
	benefit_program			VARCHAR(3),
	benefit_program_start_date	DATE,
	benefit_program_end_date	DATE,
	authorization_status		VARCHAR(2),
	status_reason_code		VARCHAR(2),
	authorization_source_code	VARCHAR(2),
	case_rate_reason_code		VARCHAR(4),
	current_case_rate		VARCHAR(8),
	care_coordinator_name		VARCHAR(25),
	record_date_timestamp		TIMESTAMP(0),
	king_cty_id			INTEGER,
	--end structure of transaction


 */
DECLARE
	auth_no INTEGER;
	case_id INTEGER;
	cid     INTEGER;
	existing_reg_rec RECORD;
BEGIN
	auth_no := NEW.authorization_number;
	case_id := NEW.agency_case_id;
	cid     := client_get_id_from_case_id( case_id );

	SELECT INTO existing_reg_rec * FROM clinical_reg WHERE kc_authorization_id = auth_no;
	IF existing_reg_rec.clinical_reg_id IS NOT NULL THEN --an update
		UPDATE tbl_clinical_reg SET
			clinical_reg_date = NEW.benefit_program_start_date,
			clinical_reg_date_end = NEW.benefit_program_end_date,
			benefit_type_code = NEW.benefit_program,
			kc_authorization_status_code = NEW.authorization_status,
			current_case_rate = NEW.current_case_rate::numeric,
			case_rate_reason_code = NEW.case_rate_reason_code,
			changed_by = sys_user(),
			changed_at = CURRENT_TIMESTAMP,
			sys_log = COALESCE(sys_log||E'\n','')||'Updating registration from KC batch no '||NEW.king_county_batch_number
		WHERE kc_authorization_id = auth_no;
	ELSE --an insert
		INSERT INTO tbl_clinical_reg (
			client_id,
			clinical_reg_date,
			clinical_reg_date_end,
			benefit_type_code,
			funding_source_code,
			kc_authorization_id,
			kc_authorization_status_code,
			current_case_rate,
			case_rate_reason_code,
			added_by,
			changed_by,
			sys_log
		) VALUES (
			cid,
			NEW.benefit_program_start_date,
			NEW.benefit_program_end_date,
			NEW.benefit_program,
			'KC',
			auth_no,
			NEW.authorization_status,
			NEW.current_case_rate::numeric,
			NEW.case_rate_reason_code,
			sys_user(),
			sys_user(),
			'Inserting registration from KC batch no '||NEW.king_county_batch_number
		);
	END IF;


	RETURN NEW;

END;$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION export_kc_data_for_client(cid INTEGER, edate DATE) RETURNS BOOLEAN AS $$
DECLARE
	ctier RECORD;
BEGIN
	/*
	 * For this to return true, it must find a valid kc-funded tier for the date provided
	 */

	SELECT INTO ctier * FROM clinical_reg
		WHERE client_id = cid
			AND clinical_reg_date <= edate
			AND (clinical_reg_date_end >= edate OR clinical_reg_date_end IS NULL)
			AND kc_authorization_status_code NOT IN ('CX')
			AND funding_source_code = 'KC'
	
		ORDER BY clinical_reg_date DESC;

	IF ctier.benefit_type_code IS NOT NULL AND ctier.benefit_type_code <> '75' THEN

		RETURN TRUE;

	ELSE

		RETURN FALSE;

	END IF;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION clinical_screening_intake_outcome_procedure() RETURNS TRIGGER AS $$
DECLARE
	client_record RECORD;
	request_dummy_reg BOOLEAN;
	ncreg_id INTEGER;
BEGIN

	request_dummy_reg := FALSE;

	/*
	 * As of PG 8.2, all statements in an IF are evaluated, thus
	 * the different operations must be tested for independently
	 * since OLD causes and error on INSERT
	 */

	IF TG_OP = 'INSERT'
		AND NEW.screening_intake_outcome_code IS NOT NULL
		AND NEW.screening_intake_outcome_code <> 'ELIGIBLE' THEN

		request_dummy_reg := TRUE;

	END IF;

	IF TG_OP = 'UPDATE' THEN

		IF  NEW.screening_intake_outcome_code IS NOT NULL
			AND (OLD.screening_intake_outcome_code IS NULL
				OR NEW.screening_intake_outcome_code <> OLD.screening_intake_outcome_code)
			AND NEW.screening_intake_outcome_code <> 'ELIGIBLE' THEN

			request_dummy_reg := TRUE;
		END IF;

	END IF;

	IF request_dummy_reg THEN

		/*
		 * Add clinical ID, if necessary, and enter registration request
		 */

		SELECT INTO client_record * FROM client WHERE client_id = NEW.client_id;

		IF client_record.clinical_id IS NULL THEN 

			UPDATE tbl_client SET clinical_id = (SELECT NEXTVAL('seq_client_clinical_id')),
				changed_by = NEW.changed_by,
				changed_at = CURRENT_TIMESTAMP,
				sys_log    = COALESCE(sys_log||E'\n','')||'Auto-registering client with county via clinical_screening_intake_outcome_procedure()'
			WHERE client_id = NEW.client_id;

		END IF;

		/*
		 * Flag client record for upload
		 */

		INSERT INTO tbl_export_kc_transaction (
			object_type,
			object_id,
			event_date,
			added_by,
			changed_by,
			sys_log
		) VALUES (
			'client',
			NEW.client_id,
			NEW.referral_date,
			NEW.changed_by,
			NEW.changed_by,
			'Uploading client record to KC for request for services placeholder authorization'
		);

		/*
		 * Add clinical registration request
		 */

		INSERT INTO tbl_clinical_reg_request (
			client_id,
			benefit_type_code,
			assessment_date,
			benefit_change_code,
			funding_source_code,
			added_by,
			changed_by,
			sys_log
		) VALUES (
			NEW.client_id,
			'00',
			NEW.referral_date,
			'NA',
			'KC',
			NEW.changed_by,
			NEW.changed_by,
			'Auto-requesting placeholder authorization, from closure of Clinical Screening/Intake record '||NEW.clinical_screening_intake_id
		) RETURNING clinical_reg_request_id INTO ncreg_id;

		/*
		 * Flag new record for upload
		 */

		INSERT INTO tbl_export_kc_transaction (
			object_type,
			object_id,
			added_by,
			changed_by
		) VALUES (
			'clinical_reg_request',
			ncreg_id,
			NEW.changed_by,
			NEW.changed_by
		);

	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION client_medicaid_pic ( cid int ) RETURNS varchar AS $$

	SELECT medicaid_pic FROM medicaid m WHERE m.client_id = $1 ORDER BY medicaid_date DESC LIMIT 1;

$$ LANGUAGE sql STABLE;

/******************************
 *      KC Case Rates
 ******************************/

CREATE OR REPLACE FUNCTION clinical_kc_case_rate( benefit VARCHAR, cr_reason VARCHAR, cr_date DATE) RETURNS numeric AS $$

	SELECT daily_case_rate FROM kc_case_rate WHERE benefit_type_code = $1
		AND kc_case_rate_reason_code = $2
		AND kc_case_rate_date <= $3
		AND COALESCE(kc_case_rate_date_end,CURRENT_DATE + 1) > $3
	ORDER BY kc_case_rate_date DESC LIMIT 1;

$$ LANGUAGE sql;

CREATE OR REPLACE FUNCTION clinical_kc_case_rate( benefit VARCHAR, cr_reason VARCHAR) RETURNS numeric AS $$

	SELECT clinical_kc_case_rate($1,$2,CURRENT_DATE);

$$ LANGUAGE sql STABLE;
