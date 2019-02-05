/*
 * This view shows tenants who are ineligible to receive guests.
 * Due to information in the bar table, but could draw on other
 * sources as well.
 */

/*
 * This particular view is customized for Plymouth Housing Group
 * but included as a sample.
 */

CREATE VIEW client_guest_ineligible AS (

	SELECT client_id
	FROM bar_current

/*
	-- Plymouth Housing Group will want this WHERE clause instead
	WHERE bar_type_code IN ('GUEST','bedbug')
*/

	WHERE ( ('GUEST' = ANY(bar_reason_codes))
		OR ('bedbug'= ANY(bar_reason_codes)))

	AND unit_no(client_id,current_date) IS NOT NULL

);

