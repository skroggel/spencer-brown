CREATE TABLE IF NOT EXISTS `log` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `create_timestamp` int(11) NOT NULL DEFAULT '0',
  `change_timestamp` int(11) NOT NULL DEFAULT '0',

  `level` tinyint NOT NULL DEFAULT '0',
  `class` varchar(255) DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `api_call` text,
  `comment` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',


  PRIMARY KEY (uid),
  KEY `level` (`level`),
  KEY `create_timestamp` (`create_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
