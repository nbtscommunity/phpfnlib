CREATE TABLE messages (
  date date default NULL,
  poster varchar(128) default NULL,
  subject varchar(255) default NULL,
  body text,
  id int(11) NOT NULL auto_increment,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
