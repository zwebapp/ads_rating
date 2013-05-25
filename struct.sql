CREATE TABLE /*TABLE_PREFIX*/t_rated_items (
  fk_i_item_id INT UNSIGNED NOT NULL,
  fk_i_user_id INT UNSIGNED,
  i_value INT UNSIGNED NOT NULL,
  t_datetimestamp TIMESTAMP NOT NULL,
  s_hash VARCHAR(255) NULL,
  FOREIGN KEY (fk_i_item_id) REFERENCES /*TABLE_PREFIX*/t_item (pk_i_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';

CREATE TABLE /*TABLE_PREFIX*/t_dev (
  i_item_id_1 int (11) NOT NULL default '0 ' ,
	i_item_id_2 int (11) NOT NULL default '0 ' ,
	i_count int (11) NOT NULL default '0 ' ,
	i_sum int (11) NOT NULL default '0 ' , 
	PRIMARY KEY(i_item_id_1 , i_item_id_2)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'UTF8' COLLATE 'UTF8_GENERAL_CI';
