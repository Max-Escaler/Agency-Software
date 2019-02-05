--
-- Name: sys_flag; Type: TABLE; Schema: public; Owner: agency; Tablespace: 
--

CREATE TABLE sys_flag (
    flag_id SERIAL  PRIMARY KEY,
    flag_name character varying(80) UNIQUE,
    is_flag boolean,
    changed_at timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    changed_by integer
);


CREATE RULE flag_delete AS ON DELETE TO sys_flag DO INSTEAD NOTHING;

/* populate initial flags */
 INSERT INTO sys_flag
	 (	flag_name,
		is_flag,
		changed_by) 
	SELECT	'lock_bed_' || bed_group_code,
			false,
			sys_user()
	FROM l_bed_group;
