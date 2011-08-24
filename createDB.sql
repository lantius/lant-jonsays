CREATE TABLE `says_lines` (
  `id` int(11) NOT NULL auto_increment,
  `words` varchar(200) character set utf8 NOT NULL default '',
  `count` int(11) NOT NULL default '0',
  `last` datetime NOT NULL default '0000-00-00 00:00:00',
  `ignorethis` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `words` (`words`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=98 ;