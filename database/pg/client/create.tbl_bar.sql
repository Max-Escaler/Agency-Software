CREATE TABLE      tbl_bar        (
bar_id				SERIAL PRIMARY KEY,
client_id				INTEGER REFERENCES tbl_client ( client_id ),
guest_id				INTEGER REFERENCES tbl_guest ( guest_id ),

/* non-client section */

non_client_name_last		VARCHAR(40),
non_client_name_first		VARCHAR(30),
non_client_description		TEXT,

/* end non-client section */


bar_date				DATE NOT NULL,
bar_date_end			DATE,
barred_by				INTEGER  NOT NULL REFERENCES tbl_staff ( staff_id ),
bar_incident_location_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_bar_location ( bar_location_code ),
bar_resolution_location_code	VARCHAR(10) REFERENCES tbl_l_bar_location ( bar_location_code ),

/* barred from facility */
barred_from_codes		VARCHAR(10)[] NOT NULL,

/* Bar flags */

bar_reason_codes		VARCHAR(10)[] NOT NULL,

/*
 Bar flags, as used at DESC:

 Assaultive and Automatic BRC<
 
 AS = Assault on Staff
 AC = Assault on Client
 DV = Domestic Violence
 FT = Fighting
 MC = Menacing Client
 MS = Menacing Staff
 
 Automatic BRC
 
 CR = Crack in Agency
 CT = Criminal Trespass
 EM = Exchanging Money
 IE = Illegal Entry
 PD = Property Destruction
 SPD = Seattle Police Dept
 TC = Threatening Client
 TH = Theft<br>   \r
 TS = Threatening Staff
 
 1-Day and 3-Day Bar
 
 ETOH = Under Influence
 NC = Non-Cooperation
 PB = Prior BRC
 PO = Poss Drugs, Alc, Equip
 RA = Respite Abuse
 RD = Redeeming
 RL = Racist Language
 SIB = Sexually Inappropriate Behavior
 SM = Smoking
 VA = Verbally Abusive
 WN = Weapons<br>
*/
description				TEXT,
staff_involved			INTEGER[],
gate_mail_date			DATE,
brc_elig_date			DATE,
brc_client_attended_date	DATE,
brc_resolution_code		VARCHAR(10) REFERENCES tbl_l_brc_resolution (brc_resolution_code),
appeal_elig_date		DATE,
reinstate_condition		TEXT,
brc_recommendation		TEXT,
comments				TEXT,
police_incident_number		VARCHAR(30),
--system fields
added_by				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
added_at				TIMESTAMP(0) NOT NULL  DEFAULT CURRENT_TIMESTAMP,
changed_by				INTEGER NOT NULL REFERENCES tbl_staff ( staff_id ),
changed_at				TIMESTAMP(0)  NOT NULL  DEFAULT CURRENT_TIMESTAMP,
is_deleted				BOOLEAN DEFAULT false,
deleted_by				INTEGER REFERENCES tbl_staff ( staff_id ),
deleted_at				TIMESTAMP(0),
deleted_comment			TEXT,
sys_log				TEXT

	CONSTRAINT resolution_location_required CHECK (
		(bar_date_end IS NULL AND bar_resolution_location_code IS NOT NULL)
			OR bar_date_end IS NOT NULL
	)

	CONSTRAINT barred_client_or_guest_or_non_client CHECK (
		(client_id IS NOT NULL AND guest_id IS NULL AND non_client_name_last IS NULL AND non_client_name_first IS NULL AND non_client_description IS NULL)
		OR
		(client_id IS NULL AND guest_id IS NULL AND non_client_name_last IS NOT NULL AND non_client_name_first IS NOT NULL AND non_client_description IS NOT NULL)
		OR
		(client_id IS NULL AND guest_id IS NOT NULL AND non_client_name_last IS NULL AND non_client_name_first IS NULL AND non_client_description IS NULL)
	)
); 


CREATE INDEX index_tbl_bar_client_id ON tbl_bar ( client_id );
CREATE INDEX index_tbl_bar_guest_id ON tbl_bar ( guest_id );
CREATE INDEX index_tbl_bar_bar_date ON tbl_bar ( bar_date );
CREATE INDEX index_tbl_bar_bar_date_end ON tbl_bar ( bar_date_end );
CREATE INDEX index_tbl_bar_bar_resolution_location_code ON tbl_bar ( bar_resolution_location_code );
CREATE INDEX index_tbl_bar_non_clients ON tbl_bar ( non_client_name_last, non_client_name_first );
CREATE INDEX index_tbl_bar_client_id_bar_date ON tbl_bar ( client_id,bar_date );
CREATE INDEX index_tbl_bar_guest_id_bar_date ON tbl_bar ( guest_id,bar_date );
