/*
 * Assorted comparison/ranking functions for ordering client search results.
 */


CREATE OR REPLACE FUNCTION rank_client_search_results ( 

       /*
        * returned values
        */
       result_name_last varchar, 
       result_name_first varchar,
       result_name_alias varchar, 
       result_ssn varchar, 
       result_dob date,
       
       /*
        * query parameters
        */
       query_name_last varchar,
       query_name_first varchar,
       query_ssn varchar,
       query_dob date

) RETURNS NUMERIC AS $$

DECLARE

       /*
        * ranking system
        */

       rank_ssn_exact   int;
       rank_ssn_8_digit int;
       rank_ssn_7_digit int;
       rank_ssn_6_digit int;  --below this threshold, matches become mostly useless
       
       rank_name_last_exact    int;
       rank_name_last_contains int;
       rank_name_last_fuzzy    int;

       rank_name_first_exact    int;
       rank_name_first_contains int;
       rank_name_first_fuzzy    int;

       rank_name_alias_exact          int;
       rank_name_alias_contains_both  int;
       rank_name_alias_contains_last  int;
       rank_name_alias_contains_first int;

       rank_dob_exact int;

       /*
        * internal variables
        */

       metaphone_max      int;
       levenshtein_accept numeric;
       good_ssn           boolean;
       max_score          int;
       score              int;
       rank               numeric;

BEGIN

       /*
        * set ranking values
        */

       rank_ssn_exact   := 100;
       rank_ssn_8_digit := 80;
       rank_ssn_7_digit := 65;
       rank_ssn_6_digit := 42;

       rank_name_last_exact    := 40;
       rank_name_last_contains := 20;
       rank_name_last_fuzzy    := 15;

       rank_name_first_exact    := 20;
       rank_name_first_contains := 15;
       rank_name_first_fuzzy    := 10;

       rank_name_alias_exact          := 55;
       rank_name_alias_contains_both  := 30;
       rank_name_alias_contains_last  := 20;
       rank_name_alias_contains_first := 10;

       rank_dob_exact := 15;

       --fuzzy string match configuration

       metaphone_max      := 15;
       levenshtein_accept := 0.20; --20%

       --ssn exclusion section
       good_ssn := CASE WHEN query_ssn IN ('999-99-9999') OR query_ssn IS NULL OR TRIM(query_ssn)='' THEN FALSE ELSE TRUE END;

       /*
        * end configuration section
        */

       max_score := rank_ssn_exact
              + rank_name_last_exact
              + rank_name_first_exact
              + rank_dob_exact;

       score := 0;

       /*
        * ssn section
        */

       IF good_ssn AND query_ssn = result_ssn THEN

              score := score + rank_ssn_exact;

       ELSIF good_ssn AND (rank_ssn_digits(query_ssn,result_ssn,8)) THEN

              score := score + rank_ssn_8_digit;

       ELSIF good_ssn AND (rank_ssn_digits(query_ssn,result_ssn,7)) THEN

              score := score + rank_ssn_7_digit;

       ELSIF good_ssn AND (rank_ssn_digits(query_ssn,result_ssn,7)) THEN

              score := score + rank_ssn_6_digit;

       END IF;

       /*
        * name_last section
        */
       IF query_name_last = result_name_last THEN

              score := score + rank_name_last_exact;

       ELSIF (rank_name_contains(query_name_last,result_name_last)) THEN

              score := score + rank_name_last_contains;

       ELSIF (rank_name_fuzzy(query_name_last,result_name_last,metaphone_max,levenshtein_accept)) THEN

              score := score + rank_name_last_fuzzy;

       END IF;


       /*
        * name_first section
        */

       IF query_name_first = result_name_first THEN

              score := score + rank_name_first_exact;

       ELSIF (rank_name_contains(query_name_first,result_name_first)) THEN

              score := score + rank_name_first_contains;

       ELSIF (rank_name_fuzzy(query_name_first,result_name_first,metaphone_max,levenshtein_accept)) THEN

              score := score + rank_name_first_fuzzy;

       END IF;

       /*
        * name_alias section
        */

       IF result_name_alias = query_name_first||' '||query_name_last
              OR result_name_alias = query_name_last||', '||query_name_first THEN

              score := score + rank_name_alias_exact;

       ELSIF (rank_name_contains(query_name_first,result_name_alias) AND rank_name_contains(query_name_last,result_name_alias)) THEN

              score := score + rank_name_alias_contains_both;

       ELSIF (rank_name_contains(query_name_last,result_name_alias)) THEN

              score := score + rank_name_alias_contains_last;

       ELSIF (rank_name_contains(query_name_first,result_name_alias)) THEN

              score := score + rank_name_alias_contains_first;

       END IF;
       


       /*
        * dob section
        */

       IF query_dob = result_dob THEN

              score := score + rank_dob_exact;

       END IF;

       rank := (score::numeric / max_score::numeric)::numeric(5,2);

       RETURN rank;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION rank_name_contains(n1 varchar, n2 varchar) RETURNS BOOLEAN AS $$
BEGIN

       IF (n1 ILIKE '%'||n2||'%') OR (n2 ILIKE '%'||n1||'%') THEN

              RETURN TRUE;

       ELSE

              RETURN FALSE;

       END IF;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION rank_name_fuzzy(

       n1 varchar,
       n2 varchar,
       metaphone_max integer,
       levenshtein_accept numeric

) RETURNS BOOLEAN AS $$

DECLARE

      meta1 NUMERIC;
      meta2 NUMERIC;

BEGIN

       meta1 := LENGTH(METAPHONE(n1,metaphone_max))::numeric;
       meta2 := LENGTH(METAPHONE(n2,metaphone_max))::numeric;

       meta1 := CASE WHEN meta1=0 THEN 1 ELSE meta1 END;
       meta2 := CASE WHEN meta2=0 THEN 1 ELSE meta2 END;

       IF (
              LEVENSHTEIN(METAPHONE(n1,metaphone_max),
                            METAPHONE(n2,metaphone_max)  )::numeric
                     / meta1 <= levenshtein_accept

                     OR

              LEVENSHTEIN(METAPHONE(n2,metaphone_max),
                            METAPHONE(n1,metaphone_max)  )::numeric
                     / meta2 <= levenshtein_accept
              ) 


              THEN
              RETURN TRUE;

       ELSE

              RETURN FALSE;

       END IF;

END; $$ LANGUAGE plpgsql STABLE;

CREATE OR REPLACE FUNCTION rank_ssn_digits( varchar, varchar, digits integer) RETURNS BOOLEAN AS $$
DECLARE

       ssn1 VARCHAR;
       ssn2 VARCHAR;
       i INTEGER;
       l text;
       match boolean;
       wild_card text;

BEGIN

       match := false;

       ssn1 := REPLACE($1,'-','');
       ssn2 := REPLACE($2,'-','');

       FOR i IN 1..(9-digits) LOOP
              wild_card := COALESCE(wild_card,'')||'_';
       END LOOP;

       FOR i IN 1..digits + 1 LOOP

              IF NOT match THEN

                     l := substring(ssn1,1,i-1)||wild_card||substring(ssn1,i+(9-digits),9);
                     match := ssn2 like l;

              END IF;

       END LOOP;

       RETURN match;

END; $$ LANGUAGE plpgsql STABLE;

