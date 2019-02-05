BEGIN;

INSERT INTO tbl_db_revision_history
    (db_revision_code,
    db_revision_description,
    agency_flavor_code,
    git_sha,
    git_tag,
    applied_at,
    comment,
    added_by,
    changed_by)

     VALUES ('CREATE_CHARGE_AND_PAYMENT', /*UNIQUE_DB_MOD_NAME */
            'Creates charge and payment tables.', /* DESCRIPTION */
            'CLIENT', /* Which flavor of AGENCY.  AGENCY_CORE applies to all installations */
            '', /* git SHA ID, if applicable */
            '', /* git tag, if applicable */
            current_timestamp, /* Applied at */
            'This is an optional modification', /* comment */
            sys_user(),
            sys_user()
          );



\i ../client/functions/create.charge.sql
\i ../client/create.l_charge_type.sql
\i ../client/create.tbl_charge.sql

\i ../client/create.l_payment_type.sql
\i ../client/create.l_void_reason.sql
\i ../client/create.l_payment_form.sql
\i ../client/populate.l_payment_form.sql
\i ../client/create.tbl_payment.sql

\i ../client/report.payment_receipt.sql

COMMIT;
