CREATE TABLE tbl_gift_united_way (
    gift_united_way_id serial PRIMARY KEY,
    donor_id integer NOT NULL REFERENCES tbl_donor (donor_id),
    gift_united_way_amount DECIMAL(12,2), -- can be null for UWAY gifts
    received_date DATE NOT NULL,
    gift_united_way_date DATE,
    gift_united_way_form_code VARCHAR(10) REFERENCES tbl_l_gift_cash_form (gift_cash_form_code),
    reference_no VARCHAR(16),
--	account_code  VARCHAR(10) REFERENCES tbl_l_account (account_code),
    response_code VARCHAR(10) REFERENCES tbl_l_response (response_code),
    restriction_code VARCHAR(10) REFERENCES tbl_l_restriction (restriction_code) NOT NULL,
    skip_thanks BOOLEAN,
    gift_united_way_comment TEXT,
    expiration VARCHAR(5),
    authorization_no VARCHAR(8),
    is_anonymous BOOLEAN,
	added_by     INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at     TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by     INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at     TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted    BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at   TIMESTAMP(0),
	deleted_by   INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment TEXT,
	sys_log TEXT
);

--CREATE VIEW gift_united_way AS SELECT * FROM tbl_gift_united_way WHERE NOT is_deleted;
CREATE VIEW gift_united_way AS
SELECT g.*,
CASE
	WHEN (d.donor_type_code='INDI' AND g.response_code BETWEEN '600' AND '699' ) THEN '6425'
	WHEN (d.donor_type_code IN ('INDI','GOVE','OTHE') ) THEN '6420'
	WHEN (d.donor_type_code IN ('CORP') ) THEN '6430'
	WHEN (d.donor_type_code IN ('FOUN') ) THEN '6410'
	WHEN (d.donor_type_code IN ('ORGN') ) THEN '6440'
	WHEN (d.donor_type_code IN ('FFO') ) THEN '6450'
END AS account_code,
CASE
	WHEN g.restriction_code = '001' THEN '10'
	ELSE '30'
END AS account_117_code
FROM tbl_gift_united_way g
	LEFT JOIN tbl_donor d USING (donor_id)
WHERE NOT g.is_deleted;
