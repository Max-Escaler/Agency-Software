/*
 * pgMail is a function that can be used for email notification.
 *
 * pgmail may already be bundled with AGENCY.
 * If not, you can download it from:
 *
 * http://sourceforge.net/projects/pgmail/
 *
 * and copy the pgMail.sql file into this directory.
 *
 * If email notification is not desired, simply comment out the two icnlues, and uncomment the place holder function below it.
 */

\i create.tclu_language.sql
\i pgMail.sql

/*
CREATE FUNCTION pgmail(text,text,text,text) RETURNS INTEGER AS $$
BEGIN
    RETURN 1;
END;$$ LANGUAGE plpgsql;
*/

