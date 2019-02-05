/*
 * Housing specific lookups and tables
 */



/* residence related lookups ( required for either of the residence tables )*/
\i housing/create.l_chronic_homeless_status.sql
\i housing/create.l_departure_type.sql
\i housing/create.l_departure_reason.sql
\i housing/create.l_facility.sql
\i housing/create.l_geography.sql
\i housing/create.l_geography_detail.sql
--\i housing/create.view.l_housing_project.sql
\i create.l_housing_project.sql

/* units */
\i housing/create.l_unit_type.sql

/* unit subsidies (contract rent) */
\i housing/create.l_fund_type.sql

\i housing/create.tbl_housing_unit.sql
\i housing/create.tbl_housing_unit_subsidy.sql


/* Other org residence records */
\i housing/create.tbl_residence_other.sql

/* Own org residence records */
\i housing/create.view.l_move_out_was.sql
\i functions/create.multi_occupancy_functions.sql
\i housing/create.tbl_residence_own.sql
\i housing/create.view.residence_own.sql
\i housing/create.view.residence_own_current.sql

/* joint view combining both residence tables */
\i housing/create.view.housing_history.sql

/* functions */
\i functions/create.housing_functions.sql

/* housing applications */
\i create.l_application_status.sql
\i create.l_application_rank.sql
\i create.l_approval.sql
\i create.l_referral_source.sql
\i create.tbl_application_housing.sql

