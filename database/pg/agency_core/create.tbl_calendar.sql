CREATE TABLE tbl_calendar (
	calendar_id		SERIAL PRIMARY KEY,
	staff_id				INTEGER REFERENCES tbl_staff ( staff_id ),
	inanimate_item_code		VARCHAR(10) REFERENCES tbl_l_inanimate_item ( inanimate_item_code ),
	calendar_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_calendar_type ( calendar_type_code ),
	calendar_date			DATE NOT NULL,
	calendar_date_end			DATE CHECK (calendar_date_end >= calendar_date),

	/* permissions */
	calendar_permission_add		VARCHAR(100),
	calendar_permission_delete	VARCHAR(100),
	calendar_permission_edit	VARCHAR(100),
	calendar_permission_list	VARCHAR(100),
	calendar_permission_view	VARCHAR(100),

	schedule_ahead_interval		INTERVAL,
	schedule_ahead_permission	VARCHAR(100),

	/* daily config stuff */
	standard_lunch_hour_start		TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (standard_lunch_hour_start::text ~ '(00|15|30|45):00$'),
	standard_lunch_hour_end			TIME(0)  NOT NULL DEFAULT '00:00:00' CHECK (standard_lunch_hour_end >= standard_lunch_hour_start
									AND standard_lunch_hour_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--setting day_x_end = day_x_start will set day x as a non-schedulable day

	--sunday
	day_0_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_0_start::text ~ '(00|15|30|45):00$'), 
	day_0_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_0_end >= day_0_start AND day_0_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--monday
	day_1_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_1_start::text ~ '(00|15|30|45):00$'), 
	day_1_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_1_end >= day_1_start AND day_1_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--tuesday
	day_2_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_2_start::text ~ '(00|15|30|45):00$'), 
	day_2_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_2_end >= day_2_start AND day_2_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--wednesday
	day_3_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_3_start::text ~ '(00|15|30|45):00$'), 
	day_3_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_3_end >= day_3_start AND day_3_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--thursday
	day_4_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_4_start::text ~ '(00|15|30|45):00$'), 
	day_4_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_4_end >= day_4_start AND day_4_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--friday
	day_5_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_5_start::text ~ '(00|15|30|45):00$'), 
	day_5_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_5_end >= day_5_start AND day_5_end::text ~ '(00|15|30|45|(23:59)):00$'),

	--saturday
	day_6_start				TIME(0) NOT NULL DEFAULT '00:00:00' CHECK (day_6_start::text ~ '(00|15|30|45):00$'), 
	day_6_end				TIME(0) NOT NULL DEFAULT '23:59:00' CHECK (day_6_end >= day_6_start AND day_6_end::text ~ '(00|15|30|45|(23:59)):00$'),

--system fields
	added_by				INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
	added_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	changed_by				INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
	changed_at				TIMESTAMP(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
	is_deleted				BOOLEAN NOT NULL DEFAULT FALSE,
	deleted_at				TIMESTAMP(0),
	deleted_by				INTEGER REFERENCES tbl_staff(staff_id),
	deleted_comment			TEXT,
	sys_log				TEXT	

	CONSTRAINT staff_id_or_inanimate_item_code CHECK ( (staff_id IS NOT NULL AND inanimate_item_code IS NULL)
										OR
									   (staff_id IS NULL AND inanimate_item_code IS NOT NULL))
);

