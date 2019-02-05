CREATE OR REPLACE FUNCTION make_safe_harbors_id() RETURNS VARCHAR(15) AS $$
DECLARE
	flag boolean;
	new_safe_harbors_id varchar(15);
BEGIN
	flag := false;
	WHILE flag<>true LOOP
		SELECT INTO new_safe_harbors_id (random()*10000000)::integer;
		IF (SELECT COUNT(*) FROM client_export_id WHERE export_organization_code='SAFE_HARB' AND export_id=new_safe_harbors_id) < 1 THEN
			flag := true;
		END IF;
	END LOOP;
	RETURN new_safe_harbors_id;
END;$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION shelter_reg_insert() RETURNS TRIGGER AS $$
DECLARE
	cid INTEGER;
	safe_harbors_id	VARCHAR(15);
BEGIN
	cid := NEW.client_id;

	--verify safe_harbors export id
	IF (SELECT export_id FROM client_export_id WHERE client_id=cid AND export_organization_code='SAFE_HARB') IS NULL THEN
		INSERT INTO tbl_client_export_id (client_id,export_organization_code,export_id,added_by,changed_by,sys_log)
			VALUES (cid,'SAFE_HARB',make_safe_harbors_id(),NEW.added_by,NEW.changed_by,'Auto-creating Safe Harbors Export ID');
	END IF;

	RETURN NEW;

END;$$ LANGUAGE plpgsql;
