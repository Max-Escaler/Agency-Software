CREATE OR REPLACE VIEW address_preferred AS (
 SELECT *
   FROM address ad
   WHERE ad.address_id =
	( SELECT address.address_id
          FROM address
	      LEFT JOIN donor USING (donor_id)
	  WHERE ad.donor_id = address.donor_id 
		AND COALESCE(address.address_date_end, current_date) >= current_date
	  ORDER BY donor.preferred_address_code = address.address_type_code DESC,
           CASE
               WHEN donor.donor_type_code = 'INDI' 
			THEN address.address_type_code = 'HOME'
               WHEN donor.donor_type_code IN ('CORP','FFO','FOUN','GOVE','ORGN','REL') 
			THEN address.address_type_code = 'BUSINESS'
               ELSE NULL::boolean
           END, address.address_date DESC
    LIMIT 1));

