/*
	Creates a view of client IDs and "eligibility status"
	This currently refers to eligiblity for entry, used by entry_browse.php
	Could be extended or genericized
*/

CREATE OR REPLACE VIEW status_eligible AS 
(
	SELECT
		client_id,

/*
	This code is used by Recovery Cafe
	You could create your own org-specific logic here
*/
/*
		COALESCE((SELECT attendance_code 
	FROM  (SELECT DISTINCT ON (client_id) client_id,attendance_code,attended_on FROM recovery_circle_attendance ORDER BY client_id,attended_on DESC) rca
	WHERE client.client_id=rca.client_id
--	AND attended_on >= (current_date - date_part('dow',current_date)::int-7)
	ORDER BY attended_on DESC
	LIMIT 1),'')='ABSENT'
*/
		FALSE /* default everyone is eligible */
	    AS is_ineligible
	FROM client
);
