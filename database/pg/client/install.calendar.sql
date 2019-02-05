/*
 * Calendar-specific lookups, tables and views
 */


\i ../agency_core/create.l_calendar_appointment_resolution.sql
\i ../agency_core/create.l_calendar_type.sql
\i ../agency_core/create.l_inanimate_item.sql

\i ../agency_core/create.tbl_calendar.sql
\i ../agency_core/create.view.calendar.sql
\i ../agency_core/create.l_event_repeat_type.sql
\i create.tbl_calendar_appointment.sql
\i create.view.calendar_appointment.sql

\i functions/create.functions_calendar.sql
