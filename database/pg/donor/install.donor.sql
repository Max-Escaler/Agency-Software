/**********************************************************\
 *                         Donor                          *
 *                                                        *
 * Structure this table as desired. It will serve as the  *
 * key 'parent' record for all subsequent 'child'         *
 * records.                                               *
\**********************************************************/

/* basic lookups */
\i create.l_active.sql
\i create.l_donor_type.sql
\i create.l_donor_flag_type.sql
\i create.l_send_mail.sql
\i create.l_gift_cash_form.sql
\i create.l_ask.sql
\i create.l_agency.sql

\i add.l_active.sql
\i add.l_donor_flag_type.sql
\i add.l_gift_cash_form.sql

-- address lookups--needed for donor table
\i create.l_address_type.sql
\i add.l_address_type.sql
\i create.l_address_obsolete_reason.sql

/* Donor Table */
\i create.tbl_donor.sql

/* Address Table */
\i create.tbl_address.sql

/* Donor & Address View */

\i functions/create.function_address.sql
\i create.view.address.sql
\i create.view.donor.sql
\i create.view.address_preferred.sql

/* Staff assignments */

\i create.l_staff_assign_type.sql
\i create.tbl_staff_assign.sql

/* functions */
\i functions/create.functions_donor.sql

-- donor flags & links
\i create.donor_flag.sql
\i create.donor_link.sql

-- donor notes
\i functions/create.functions_donor_note.sql
\i create.tbl_donor_note.sql

-- gift_cash lookups & gift_cash
\i create.l_response.sql
\i add.l_response.sql

\i create.l_restriction.sql
\i add.l_restriction.sql

-- contract
\i create.l_contract.sql

\i create.tbl_gift_cash.sql
\i create.view.gift_cash.sql

--gift_inkind lookups & gift_inkind
\i create.l_inkind_item.sql
\i add.l_inkind_item.sql

\i create.tbl_gift_inkind.sql

--United Way gifts
\i create.tbl_gift_united_way.sql

/*
-- imports
\i create.donor_imp.sql
\i add.donor_imp.sql

\i create.gift_cash_imp.sql
\i add.gift_cash_imp.sql

\i create.gift_inkind_imp.sql
\i add.gift_inkind_imp.sql
*/

--Sent mail records
\i create.tbl_sent_mail.sql

-- views
\i create.view.gift.sql
\i create.view.donor_total.sql
\i create.view.donor_stat.sql

--indexes
\i create.indexes.sql

/* VOLUNTEER REGISTRATIONS AND HOURS */
\i install.volunteer.sql

/* PROPOSALS */
\i install.proposal.sql

/* Add sample/provided client report(s) */
\i add.report.donor.sql

