CREATE OR REPLACE FUNCTION income_verify() RETURNS TRIGGER AS '
DECLARE
	iid INTEGER;
BEGIN
	--SELECT INTO iid income_id FROM income WHERE income.client_id=NEW.client_id AND income.income_date_end IS NULL;
	SELECT INTO iid income_id FROM income WHERE income.client_id=NEW.client_id AND 
	( 
		COALESCE(new.income_date_end,income.income_date_end) IS NULL
		OR ( CASE WHEN income.income_date_end IS NULL THEN
				(new.income_date_end >= income.income_date)
			WHEN new.income_date_end IS NULL THEN
				(income.income_date_end >= new.income_date)
			ELSE
				( (new.income_date <= income_date_end)
					AND ( new.income_date_end >= income.income_date))
			END)
	) LIMIT 1;
	IF iid IS NOT NULL THEN
		RAISE EXCEPTION ''Client % has a current income record (income_id: %).  Cannot add record from % to %'',NEW.client_id, iid,NEW.income_date,NEW.income_date_end;
	END IF;	
	RETURN NEW;
END;' LANGUAGE PLPGSQL;

CREATE TRIGGER verify_income_record  BEFORE INSERT
    ON tbl_income FOR EACH ROW
    EXECUTE PROCEDURE income_verify();

