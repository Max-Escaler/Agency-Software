CREATE OR REPLACE FUNCTION validate_charge_modify() RETURNS trigger AS '
	BEGIN
		IF NOT (OLD.added_at=NEW.added_at
			AND OLD.added_by=NEW.added_by
			AND OLD.is_subsidy=NEW.is_subsidy
			AND OLD.effective_date=NEW.effective_date
			AND OLD.charge_type_code=NEW.charge_type_code
			AND OLD.housing_unit_code=NEW.housing_unit_code
			AND OLD.housing_project_code=NEW.housing_project_code
			AND OLD.amount=NEW.amount
			AND OLD.subsidy_type_code=NEW.subsidy_type_code
			AND OLD.period_start=NEW.period_start
			AND OLD.period_end=NEW.period_end
			AND OLD.comment=NEW.comment)

			THEN RAISE EXCEPTION ''Cannot make changes to existing charge.'';
		END IF;
		RETURN NEW;
	END;
	' LANGUAGE 'plpgsql';


/*
 * This is a sample function that can be used to send notification emails
 * for miscellaneous charges.  Right now, the actual sending of email is
 * commented out in the code, so it does nothing.
 *
 * It is also not invoked by default, as creation of the trigger is
 * commented out in create.tbl_charge.sql.
 *
 * To make this work, you would need to uncomment those sections.
 */

CREATE OR REPLACE FUNCTION charge_notify_misc() RETURNS trigger AS '
DECLARE
    customerRec RECORD;
    textMessage text;
    textSubject text;
    cl_url      text;
    cl_name     text;
BEGIN
    cl_url := CLIENT_URL( NEW.client_id);
    cl_name := CLIENT_NAME( NEW.client_id);
    if NOT NEW.charge_type_code IN (''SUBSIDY'',''RENT'',''SECURITY'') THEN
        textMessage := ''A new Charge has been added to AGENCY''
                        || ''\n\n        Client: '' || COALESCE(cl_name,''(none)'')
                        || ''\n   Charge Type: '' || COALESCE(NEW.charge_type_code,''(none)'')
                        || ''\nEffective Date: '' || COALESCE(text(NEW.effective_date),''(none)'')
                        || ''\n        Amount: '' || COALESCE(text(NEW.amount),''(none)'')
                        || ''\n       Project: '' || COALESCE(NEW.housing_project_code,''(none)'')
                        || ''\n       Unit No: '' || COALESCE(NEW.housing_unit_code,''(none)'')
                        || ''\n  Period Start: '' || COALESCE(text(NEW.period_start),''(none)'')
                        || ''\n    Period End: '' || COALESCE(text(NEW.period_end),''(none)'')
                        || ''\n      Added By: '' || COALESCE(staff_name(NEW.added_by),''(none)'')
                        || ''\n     Charge ID: '' || COALESCE(text(NEW.charge_id),''(none)'')
                        || ''\n       Comment: '' || COALESCE(NEW.comment,''(none)'')
                        || ''\n\n(this charge can be viewed at '' || COALESCE(cl_url,''(none)'') || '')'';
        textSubject := ''New '' || COALESCE(NEW.charge_type_code,''(none)'') || '' Charge for '' || COALESCE(cl_name,''(none)'');
/*
Email sending commented out here
        perform pgmail(''AGENCY SYSTEM <No_Email@XXX>'',''fill in email here'',textSubject, textMessage);
*/
    end if;
    return NEW;
END;' language 'plpgsql';

CREATE OR REPLACE FUNCTION charge_insert() RETURNS trigger AS '
BEGIN
	IF (NEW.housing_project_code IS NULL OR NEW.housing_unit_code IS NULL) THEN
		RAISE EXCEPTION ''missing project or unit code'';
	END IF;
	RETURN NEW;
END;' LANGUAGE 'plpgsql';


