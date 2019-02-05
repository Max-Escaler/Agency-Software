--
-- Name: bed_reg; Type: TABLE; Schema: public; Owner: agency; Tablespace: 
--

CREATE TABLE bed_reg (
    bed_reg_id SERIAL PRIMARY KEY,
    bed_group character varying(10),
    bed_no integer,
    client character varying(50),
    vol_status character(10),
    comments text,
    added_by integer,
    added_at timestamp without time zone,
    removed_by integer,
    removed_at timestamp without time zone,
    re_register boolean,
    sys_log text,
    bus_am boolean,
    bus_pm boolean
);


CREATE INDEX bed_reg_index_added_at ON bed_reg USING btree (added_at);
CREATE INDEX bed_reg_index_bed_group ON bed_reg USING btree (bed_group);
CREATE INDEX bed_reg_index_bed_no ON bed_reg USING btree (bed_no);
CREATE INDEX bed_reg_index_client ON bed_reg USING btree (client);
CREATE INDEX bed_reg_index_removed_at ON bed_reg USING btree (removed_at);
CREATE INDEX index_bed_reg_bed_group ON bed_reg USING btree (bed_group) WHERE (removed_at IS NULL);

