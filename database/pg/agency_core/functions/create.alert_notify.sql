CREATE OR REPLACE FUNCTION alert_notify() RETURNS trigger AS $$
DECLARE
    textMessage text;
	textLabel   text;
    textSubject text;
    textSender  text;
    textRecipient text;
    emailAlert boolean;
    alert_url  text;
    url_base text;
    settings_url text;
	email_template text;

BEGIN
     SELECT INTO emailAlert opt_alerts_email FROM user_option WHERE staff_id=NEW.staff_id;
     IF emailAlert THEN
          textSender=email_sender();
          SELECT INTO textRecipient staff_email FROM staff WHERE staff_id=NEW.staff_id;
		  SELECT INTO email_template template_alert FROM config_email ORDER BY config_email_id DESC limit 1;
          SELECT INTO url_base agency_base_url();
          settings_url := url_base || E'display.php?control\%5baction\%5d=view&control\%5bobject\%5d=user_option&control\%5bformat\%5d=&control\%5bid\%5d=' || NEW.staff_id;
          alert_url := url_base || E'display.php?control\%5baction\%5d=view&control\%5bobject\%5d=alert&control%5bformat%5d=&control\%5bid\%5d=' || NEW.alert_id;
          textLabel := INITCAP(NEW.ref_table) || ' Record ' || NEW.ref_id;
          textSubject := get_db_name()||' Alert: ' || textLabel;
  	       textLabel := COALESCE(NEW.alert_text_public,INITCAP(NEW.ref_table) || ' Record ' || NEW.ref_id);
          textSubject := get_db_name()||' Alert: ' || COALESCE(NEW.alert_subject_public,textLabel);
          textMessage := replace(replace(replace(
							email_template,'$alert_url',alert_url),
                     		'$settings_url',settings_url),
							'$label',textLabel);
          IF (is_test_db() IS FALSE) THEN
               perform pgmail(textSender,textRecipient,textSubject, textMessage);
          END IF;
    end if;
    return NEW;
END;$$ language 'plpgsql';

CREATE OR REPLACE FUNCTION verify_alert_notify() RETURNS trigger AS $$
#check for existence of table, and, if applicable, column

     spi_exec "SELECT oid FROM pg_class WHERE relname ~ '^$NEW(alert_object)$'"

     if {![info exists oid]} {
          elog ERROR "Must use valid objects in tbl_alert_notify. $NEW(alert_object) does not exist."
     }

	set match_fields { alert_notify_field alert_notify_field2 alert_notify_field3 alert_notify_field4 match_program_field match_project_field match_position_field match_facility_field match_shift_field match_supervisor_field match_supervisees_field match_assignments_field }

	foreach f $match_fields {
		if {[info exists NEW($f)]} {
			spi_exec "SELECT a.attrelid AS col FROM pg_catalog.pg_attribute a
				WHERE a.attrelid = $oid AND a.attname ~ '^$NEW($f)$' AND NOT a.attisdropped"
			if {![info exists col]} {
				elog ERROR "Invalid alert_notify record: field $NEW($f) does not exist in $NEW(alert_object)"
			}
		}
	}

     return [array get NEW]
$$ LANGUAGE pltcl;

CREATE OR REPLACE FUNCTION alert_notify_enable(varchar,varchar) RETURNS boolean AS $$
		if {[info exists 1]} {
			set TABLE $1
		} else {
			elog ERROR "no table passed to alert_notify()"
			return false
		}
		if {[info exists 2]} {
			set CUSTOM_COLUMN  $2
		} else {
			set CUSTOM_COLUMN ""
		}
        set cre_exec  "CREATE TRIGGER ${TABLE}_alert_notify
        AFTER INSERT OR UPDATE OR DELETE ON ${TABLE}
        FOR EACH ROW EXECUTE PROCEDURE table_alert_notify(${CUSTOM_COLUMN})"
        spi_exec $cre_exec
        return true
$$ LANGUAGE pltcl;

CREATE OR REPLACE FUNCTION alert_notify_enable(varchar) RETURNS BOOLEAN AS $$
DECLARE table ALIAS FOR $1;
BEGIN
	RETURN alert_notify_enable($1,'');
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION table_alert_notify() RETURNS trigger AS $$

     spi_exec "SELECT relname AS table FROM pg_class WHERE oid=$TG_relid"

     regsub "^tbl_" $table "" object
     set action $TG_op

     if {[info exists 1]} { 

          #odd-ball primary key fields can be passed during trigger creation
          set object_id_column $1

     } else {

          set id "_id"
          set object_id_column "$object$id"

     }
	switch $TG_op {
		DELETE {
			array set record [array get OLD]
			set human_action "Deleted"
		}
		INSERT {
			array set record [array get NEW]
			set human_action "Added"
		}
		UPDATE {
			array set record [array get NEW]
			set human_action "Edited"
		}
		default {
			/* Shouldn't get here */
			array set record [array get NEW]
			set human_action "UNKNOWN OPERATION: $TG_op"
		}
	}
     set object_id $record($object_id_column)

     #get staff and insert alert
     set staff [list]
     spi_exec -array notify_recs "SELECT staff_id, 
		agency_program_code,
		agency_project_code,
		staff_position_code,
		agency_facility_code,
		staff_shift_code,
		match_program_field,
		match_project_field,
		match_facility_field,
		match_position_field,
		match_shift_field,
		match_assignments_field,
		match_supervisor_field,
		match_supervisees_field,
		alert_notify_field,
		alert_notify_value,
		alert_notify_field2,
		alert_notify_value2,
		alert_notify_field3,
		alert_notify_value3,
		alert_notify_field4,
		alert_notify_value4
		FROM alert_notify_current 
		WHERE alert_object='$object' 
		AND alert_notify_action_code IN ('$action','ANY')" {

		  set notify1 false
		  set notify2 false
		  set notify3 false
		  set notify4 false

          if {[info exists notify_recs(alert_notify_field)]} {
               #
               # field is set, determine if value matches
               #
               if {[info exists record($notify_recs(alert_notify_field))] 
                    && [info exists notify_recs(alert_notify_value)]} { #value match
                    	if { $record($notify_recs(alert_notify_field)) == $notify_recs(alert_notify_value) } {
							set notify1 true
                    	}
               } elseif {![info exists record($notify_recs(alert_notify_field))] 
                    && ![info exists notify_recs(alert_notify_value)]} { #null value match
						set notify1 true
               }
          } else {
				set notify1 true
          }

		  # This is unnecessarily long, duplicating 1 field/value for 2nd pair
          if {[info exists notify_recs(alert_notify_field2)]} {
               if {[info exists record($notify_recs(alert_notify_field2))] 
                    && [info exists notify_recs(alert_notify_value2)]} { #value match
                    	if { $record($notify_recs(alert_notify_field2)) == $notify_recs(alert_notify_value2) } {
							set notify2 true
                    	}
               } elseif {![info exists record($notify_recs(alert_notify_field2))] 
                    && ![info exists notify_recs(alert_notify_value2)]} { #null value match
						set notify2 true
               }
          } else {
				set notify2 true
          }
          
		if {[info exists notify_recs(alert_notify_field3)]} {
               if {[info exists record($notify_recs(alert_notify_field3))] 
                    && [info exists notify_recs(alert_notify_value3)]} { #value match
                    	if { $record($notify_recs(alert_notify_field3)) == $notify_recs(alert_notify_value3) } {
							set notify3 true
                    	}
               } elseif {![info exists record($notify_recs(alert_notify_field3))] 
                    && ![info exists notify_recs(alert_notify_value3)]} { #null value match
						set notify3 true
               }
          } else {
				set notify3 true
          }

		if {[info exists notify_recs(alert_notify_field4)]} {
               if {[info exists record($notify_recs(alert_notify_field4))] 
                    && [info exists notify_recs(alert_notify_value4)]} { #value match
                    	if { $record($notify_recs(alert_notify_field4)) == $notify_recs(alert_notify_value4) } {
							set notify4 true
                    	}
               } elseif {![info exists record($notify_recs(alert_notify_field4))] 
                    && ![info exists notify_recs(alert_notify_value4)]} { #null value match
						set notify4 true
               }
          } else {
				set notify4 true
          }

		if { $notify1 && $notify2 && $notify3 && $notify4 } {
			set query_select "SELECT distinct staff_id FROM staff WHERE is_active "
			set filter ""
            if { [info exists notify_recs(agency_program_code)] } {
				set filter "$filter AND staff.agency_program_code = '$notify_recs(agency_program_code)'"
			}
            if { [info exists notify_recs(agency_project_code)] } {
				set filter "$filter AND staff.agency_project_code = '$notify_recs(agency_project_code)'"
			}
            if { [info exists notify_recs(staff_position_code)] } {
				set filter "$filter AND staff.staff_position_code = '$notify_recs(staff_position_code)'"
			}
            if { [info exists notify_recs(agency_facility_code)] } {
				set filter "$filter AND staff.agency_facility_code = '$notify_recs(agency_facility_code)'"
			}
            if { [info exists notify_recs(staff_shift_code)] } {
				set filter "$filter AND staff.staff_shift_code = '$notify_recs(staff_shift_code)'"
			}
            if { [info exists notify_recs(match_facility_field)] } {
				set filter "$filter AND staff.agency_facility_code = '$record($notify_recs(match_facility_field))'"
			}
            if { [info exists notify_recs(match_project_field)] } {
				set filter "$filter AND staff.agency_project_code = '$record($notify_recs(match_project_field))'"
			}
            if { [info exists notify_recs(match_program_field)] } {
				set filter "$filter AND staff.agency_program_code = '$record($notify_recs(match_program_field))'"
			}
            if { [info exists notify_recs(match_position_field)] } {
				set filter "$filter AND staff.staff_position_code = '$record($notify_recs(match_position_field))'"
			}
            if { [info exists notify_recs(match_shift_field)] } {
				set filter "$filter AND staff.staff_shift_code = '$record($notify_recs(match_shift_field))'"
			}
            if { [info exists notify_recs(match_assignments_field)] } {
				set filter "$filter AND staff.staff_id = ANY(client_staff_assign_own($record($notify_recs(match_assignments_field))))"
			}
			# Immediate supervisor
            if { [info exists notify_recs(match_supervisor_field)] } {
				set filter "$filter AND staff.supervised_by = '$record($notify_recs(match_supervisor_field))'"
			}
			# Any supervisee below in direct chain:
            if { [info exists notify_recs(match_supervisees_field)] } {
				set filter "$filter AND is_supervised_by('$record($notify_recs(match_supervisor_field))',staff.supervised_by)"
			}
            if { [info exists notify_recs(staff_id)] } {
				set filter "$filter AND staff.staff_id = '$notify_recs(staff_id)'"
			}
			spi_exec -array notify_staff "$query_select $filter" {
				lappend staff $notify_staff(staff_id)	
		 	}
		}
     }

     set alert_subject "$object $object_id has been $human_action"
     set alert_text "$alert_subject\n\n\nThis alert was auto-generated by an alert_notify record.\nThese can be modified from your staff page"

     foreach x [lsort -unique $staff] { #only need unique staff

          spi_exec "INSERT INTO tbl_alert (
                staff_id,
                ref_table,
                ref_id,
                alert_subject,
                alert_text,
                added_by,
                changed_by
          ) VALUES ($x,'$object',$object_id,'$alert_subject','$alert_text',sys_user(),sys_user())"

     }

    return [array get record]

$$ LANGUAGE pltcl;

CREATE OR REPLACE FUNCTION get_alert_text(INTEGER,TEXT,INTEGER) RETURNS text AS $$
DECLARE
     sid    ALIAS FOR $1;
     rtab   ALIAS FOR $2;
     rid    ALIAS FOR $3;
     t_text TEXT;
     tt_text TEXT;
     t_sub  TEXT;
     alrt   RECORD;
     first  BOOLEAN;
BEGIN

     first := true;
     FOR alrt IN SELECT * FROM alert WHERE staff_id=sid AND ref_table=rtab AND ref_id=rid
               ORDER BY alert_id DESC
     LOOP
          tt_text := alrt.alert_text;
          IF (NOT first) THEN
                tt_text := '====='||COALESCE(alrt.alert_subject,'')||E'=====\n'
                    ||'From: '||staff_name(alrt.added_by)||', at: '||to_char(alrt.added_at,'MM/DD/YYYY HH:MI a.m.')||E'\n'||tt_text;
          ELSE
               first := false;
          END IF;
          t_text := COALESCE(t_text||E'\n\n\n','') || tt_text;
     END LOOP;

     RETURN t_text;
END;$$ LANGUAGE plpgsql STABLE;
