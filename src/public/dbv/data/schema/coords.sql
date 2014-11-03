CREATE TABLE `coords` (
  `coords_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `coords_nom` varchar(100) NOT NULL,
  `coords_desc` varchar(255) NOT NULL,
  `coords_adresse` varchar(255) NOT NULL,
  `coords_url` varchar(255) NOT NULL,
  PRIMARY KEY (`coords_id`),
  UNIQUE KEY `coords_url_UNIQUE` (`coords_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8