--
-- Name: tbl_income; Type: TABLE; Schema: public; Owner: agency; Tablespace: 
--

CREATE TABLE tbl_income (
    income_id SERIAL PRIMARY KEY,
    client_id integer NOT NULL REFERENCES tbl_client (client_id),
    income_date date NOT NULL,
    income_date_end date,
    monthly_income_primary numeric(8,2) NOT NULL,
    income_primary_code character varying(10) NOT NULL REFERENCES tbl_l_income (income_code),
    monthly_income_secondary numeric(8,2),
    income_secondary_code character varying(10) REFERENCES tbl_l_income (income_code),
    monthly_interest_income numeric(8,2),
    other_assistance_1_code character varying(10) REFERENCES tbl_l_other_assistance (other_assistance_code),
    other_assistance_2_code character varying(10) REFERENCES tbl_l_other_assistance (other_assistance_code),
    is_income_certification boolean DEFAULT false NOT NULL,
    is_sha_income_certification boolean,
    housing_unit_code character varying(10)  REFERENCES tbl_housing_unit(housing_unit_code),
    income_certification_type_code character varying(10)  REFERENCES tbl_l_income_certification_type(income_certification_type_code),
    rent_date_effective date,
    rent_amount_tenant numeric(8,2),
    rent_amount_total numeric(8,2),
    fund_type_code character varying(10)  REFERENCES tbl_l_fund_type(fund_type_code),
    grant_number integer,
    added_by integer NOT NULL  REFERENCES tbl_staff(staff_id),
    added_at timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    changed_by integer NOT NULL  REFERENCES tbl_staff(staff_id),
    changed_at timestamp(0) without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    is_deleted boolean DEFAULT false NOT NULL,
    deleted_at timestamp(0) without time zone,
    deleted_by integer  REFERENCES tbl_staff(staff_id),
    deleted_comment text,
    sys_log text,
    CONSTRAINT income_certification_check CHECK (((((((is_income_certification AND (income_certification_type_code IS NOT NULL)) AND (is_sha_income_certification IS NOT NULL)) AND (rent_amount_tenant IS NOT NULL)) AND (housing_unit_code IS NOT NULL)) AND (rent_date_effective IS NOT NULL)) OR ((((((NOT is_income_certification) AND (income_certification_type_code IS NULL)) AND (is_sha_income_certification IS NULL)) AND (rent_amount_tenant IS NULL)) AND (housing_unit_code IS NULL)) AND (rent_date_effective IS NULL)))),
    CONSTRAINT income_date_sanity CHECK (((income_date_end IS NULL) OR (income_date_end >= income_date))),
    CONSTRAINT scattered_site_check CHECK (((((((housing_unit_code IS NULL) OR ((housing_unit_code)::text !~ '^S'::text)) AND (rent_amount_total IS NULL)) AND (fund_type_code IS NULL)) AND (grant_number IS NULL)) OR ((((housing_unit_code)::text ~ '^S'::text) AND (rent_amount_total IS NOT NULL)) AND (fund_type_code IS NOT NULL))))
);


CREATE INDEX index_tbl_income_client_id ON tbl_income USING btree (client_id);
CREATE INDEX index_tbl_income_client_id_income_date ON tbl_income USING btree (client_id, income_date);
CREATE INDEX index_tbl_income_income_date ON tbl_income USING btree (income_date);
CREATE INDEX index_tbl_income_income_date_end ON tbl_income USING btree (income_date_end);

