CREATE TABLE tbl_meal_count
       (
       meal_count_id		SERIAL PRIMARY KEY NOT NULL,
       served_at	     	TIMESTAMP NOT NULL,
       housing_project_code 	VARCHAR(10) NOT NULL REFERENCES tbl_l_agency_project (agency_project_code),  
       meal_type_code		VARCHAR(10) NOT NULL REFERENCES tbl_l_meal_type (meal_type_code),
       meal_type_other		VARCHAR(100)
       				CHECK ((meal_type_code = 'OTHER' AND meal_type_other IS NOT NULL) OR (meal_type_code != 'OTHER' AND meal_type_other IS NULL)),
       servings_first		INTEGER NOT NULL,
       servings_second		INTEGER,
       meal_temperature		INTEGER,
       menu_description		TEXT NOT NULL,
       food_percentage_nwh	INTEGER
				CHECK ((housing_project_code IN ('KSH','UNION','1811') AND food_percentage_nwh IS NOT NULL) OR (housing_project_code NOT IN ('KSH','UNION','1811') AND food_percentage_nwh IS NULL)),
       servings_farestart	INTEGER
       				CHECK ((housing_project_code IN ('KSH','1811') AND servings_farestart IS NOT NULL) OR (housing_project_code NOT IN ('KSH','1811') AND servings_farestart IS NULL)),
       servings_hopwa		INTEGER
				CHECK ((housing_project_code = 'LYON' AND servings_hopwa IS NOT NULL) OR (housing_project_code != 'LYON' AND servings_hopwa IS NULL)),
       servings_llaa		INTEGER
       				CHECK ((housing_project_code = 'LYON' AND servings_llaa IS NOT NULL) OR (housing_project_code != 'LYON' AND servings_llaa IS NULL)),
       comment			TEXT,
       --system fields
       added_by			INTEGER NOT NULL REFERENCES tbl_staff (staff_id),
       added_at			TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
       changed_by		INTEGER NOT NULL  REFERENCES tbl_staff (staff_id),
       changed_at		TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
       is_deleted		BOOLEAN NOT NULL DEFAULT FALSE,
       deleted_at		TIMESTAMP(0) 
				CHECK ((NOT is_deleted AND deleted_at IS NULL) OR (is_deleted AND deleted_at IS NOT NULL)),
       deleted_by		INTEGER REFERENCES tbl_staff (staff_id)
				CHECK ((NOT is_deleted AND deleted_by IS NULL) OR (is_deleted AND deleted_by IS NOT NULL)),
       deleted_comment		TEXT,
       sys_log			TEXT
       );


CREATE INDEX index_tbl_meal_count_served_at ON tbl_meal_count (served_at);
CREATE INDEX index_tbl_meal_count_agency_project ON tbl_meal_count (housing_project_code);
CREATE INDEX index_tbl_meal_count_meal_type ON tbl_meal_count (meal_type_code);
CREATE INDEX index_tbl_meal_count_food_percentage_nwh ON tbl_meal_count (food_percentage_nwh);
CREATE INDEX index_tbl_meal_count_servings_first ON tbl_meal_count (servings_first);
CREATE INDEX index_tbl_meal_count_servings_hopwa ON tbl_meal_count (servings_hopwa);
CREATE INDEX index_tbl_meal_counts_project_servings ON tbl_meal_count (housing_project_code, servings_first);
CREATE INDEX index_tbl_meal_counts_project_nwh ON tbl_meal_count (housing_project_code, food_percentage_nwh);
CREATE INDEX index_tbl_meal_counts_project_hopwa ON tbl_meal_count (housing_project_code, servings_hopwa);


CREATE VIEW meal_count AS SELECT * FROM tbl_meal_count WHERE NOT is_deleted;