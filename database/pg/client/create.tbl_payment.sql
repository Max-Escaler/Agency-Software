/*
Here is the current payment table:


Off the top of my head, I think we should add a void_reason field, that could
have choices like "payment bounced", "data entry error" or "other".  We should
also add a payment_form field, to indicate checks/money order/etc, along with a
payment_document_no to indicate check # or MO #.

*/

CREATE TABLE tbl_payment (
	payment_id				SERIAL PRIMARY KEY,
	client_id				INTEGER NOT NULL REFERENCES tbl_client ( client_id ),
	payment_date			DATE NOT NULL,
	payment_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_payment_type (payment_type_code) DEFAULT 'RENT',
	payment_form_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_payment_form,
	check_from				TEXT CHECK ((payment_form_code = 'CHECK_3P' AND check_from IS NOT NULL)
								OR payment_form_code != 'CHECK_3P'),
	payment_document_number	VARCHAR,
	amount					NUMERIC(9,2) NOT NULL,
	housing_project_code	VARCHAR(10) NOT NULL REFERENCES tbl_l_housing_project ( housing_project_code ),
	posted_comment			TEXT,
/*
 * These fields were used to integrate with QuickBooks
 *

	qb_id					INTEGER,
	qb_type					VARCHAR(15),
	qb_name					VARCHAR(200),
	qb_account				VARCHAR(100),
	qb_memo					TEXT,
	qb_no					VARCHAR(100),
*/
	is_subsidy				BOOLEAN NOT NULL DEFAULT FALSE,
	is_void					BOOLEAN NOT NULL DEFAULT FALSE,
	void_reason_code		VARCHAR(10) REFERENCES tbl_l_void_reason,
	voided_by				INTEGER REFERENCES tbl_staff ( staff_id ),
	void_comment			TEXT,
	voided_at				TIMESTAMP(0),
	--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0) CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id)
							CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
	deleted_comment			TEXT,
	sys_log					TEXT

	CONSTRAINT void_reason_check CHECK ( (NOT is_void AND void_reason_code IS NULL AND voided_by IS NULL AND void_comment IS NULL AND voided_at IS NULL)
						OR (is_void AND void_reason_code IS NOT NULL AND voided_by IS NOT NULL AND void_comment IS NOT NULL AND voided_at IS NOT NULL))
);

COMMENT ON COLUMN tbl_payment.check_from IS 'Required for 3rd-party checks';

CREATE VIEW payment AS SELECT * FROM tbl_payment WHERE NOT is_deleted;

CREATE INDEX index_tbl_payment_client_id ON tbl_payment ( client_id );
CREATE INDEX index_tbl_payment_client_id_payment_date ON tbl_payment ( client_id,payment_date );
CREATE INDEX index_tbl_payment_payment_date_client_id ON tbl_payment ( payment_date,client_id );
CREATE INDEX index_tbl_payment_payment_date ON tbl_payment ( payment_date );
