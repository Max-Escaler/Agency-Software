INSERT INTO tbl_help (help_title, help_text, is_html, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('test_database', 'The test database box warns you that you are using some kind of test version of AGENCY, so that you do not think you are working with real data.

Note to administrators:  if this _is_ a production database, you can eliminate this warning box by changing the constant AG_PRODUCTION_DATABASE
_NAME, currently located in the client_config.php file.', false, sys_user(), sys_user(), false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_help (help_title, help_text, is_html, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('Logs', 'AGENCY can have multiple logs.  Each log entry can be in one or more of these logs.  You can choose to view any combination of logs all at once.  Use the orange checkboxes to select which logs you want to view, and then press the View button.

Note to administrators:  You can customize the log types.  See "Customizing log types" in the AGENCY wiki.', false, sys_user(),sys_user(), false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_help (help_title, help_text, is_html, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('BarCodes', '<B>Assaultive and Automatic BRC</b><br>
<br>
AS = Assault on Staff<br>
AC = Assault on Client<br>
DV = Domestic Violence<br>
FT = Fighting<br>
MC = Menacing Client<br>
MS = Menacing Staff<br>
<br>
<B>Automatic BRC</b><br>
<br>
CR = Crack in Agency<br>
CT = Criminal Trespass<br>
EM = Exchanging Money<br>
IE = Illegal Entry<br>
PD = Property Destruction<br>
SPD = Seattle Police Dept<br>
TC = Threatening Client<br>
TH = Theft<br>   
TS = Threatening Staff<br>
<br>
<B>1-Day and 3-Day Bar</b><br>
<br>
ETOH = Under Influence<br>
NC = Non-Cooperation<br>
PB = Prior BRC<br>   
PO = Poss Drugs, Alc, Equip<br>
RA = Respite Abuse<br>
RD = Redeeming<br>      
RL = Racist Language<br>
SIB = Sexually Inappropriate Behavior<br>
SM = Smoking<br> 
VA = Verbally Abusive<br>
WN = Weapons<br>', true, sys_user(),sys_user(), false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_help (help_title, help_text, is_html, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('QuickSearch', 'The Client Quick Search tries to match your search string against client names, in the <em>LAST, FIRST</em> form.
<br><br>
You can enter all or part of a first or last name to get a match. You can also enter all or part of first and last name. For example, <em>frank</em> will match anyone with frank in their first or last names. <em>johnson, j</em> will match Jenny, James, Julie, etc.
<em>stein, gr</em> would match all people whose last name ends in stein, and whose first name begins with gr (this would include anyone whose last name <EM>is</EM> stein!) Upper and lowercase don''t matter in these searches.
<br><br>
<br><br>You can also search by these additional methods:
<ul>
<li>Chasers Client ID: entering a client''s Chasers ID will take you directly to that client''s page</li>
<li>Social security number search (of the form 123-45-6789)</li>
<li>Date-of-Birth searches for all clients born on the given date</li>
<li>Clinical ID search: c[clinical id]<br>for example, <em>c123</em> or <em> case 123</em></li>
<li>King County ID search: kc[kcid]<br>for example,<em>kc123</em> or <em>kcid 123</em></li>
<li>King County authorization number search: a[auth no]<br> for example,<em>a123</em> or <em>auth 123</em></li>
</ul>', true, sys_user(),sys_user(), false, NULL, NULL, NULL, NULL);
INSERT INTO tbl_help (help_title, help_text, is_html, added_by, changed_by, is_deleted, deleted_at, deleted_by, deleted_comment, sys_log) VALUES ('residence_other', 'These records are meant to track the living situations of clients in housing other than that provided by your organization.<br><br>

Although they are sometimes referred to as <i>housing history</i> or <i>residential arrangments</i>, they can record a period or episode of homelessness as well.<br><br>

Do not use these records to record when a person lived your own organization''s housing (including people housed through your own tenant-based subsidies).  These records are kept in a separate table of the organization''s housing history.', false, sys_user(),sys_user(), false, NULL, NULL, NULL, NULL);



