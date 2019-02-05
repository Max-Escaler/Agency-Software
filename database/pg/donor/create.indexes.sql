CREATE INDEX index_tbl_donor_flag_donor_id ON tbl_donor_flag(donor_id);
CREATE INDEX index_tbl_donor_link_donor_id ON tbl_donor_link(donor_id);

--gifts
CREATE INDEX index_tbl_gift_inkind_donor_id ON tbl_gift_inkind(donor_id);
CREATE INDEX index_tbl_gift_inkind_gift_inkind_date ON tbl_gift_inkind(gift_inkind_date);

