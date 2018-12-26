CREATE TABLE `oc_baycik_sync_entries` (
  `sync_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_id` int(11) DEFAULT NULL,
  `is_changed` int(3) DEFAULT '1',
  `category_lvl1` varchar(45) DEFAULT NULL,
  `category_lvl2` varchar(45) DEFAULT NULL,
  `category_lvl3` varchar(45) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `model` varchar(64) DEFAULT NULL,
  `mpn` varchar(45) DEFAULT NULL,
  `url` varchar(512) DEFAULT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `min_order_size` varchar(45) DEFAULT NULL,
  `stock_count` varchar(45) DEFAULT NULL,
  `stock_status` varchar(45) DEFAULT NULL,
  `manufacturer` varchar(45) DEFAULT NULL,
  `origin_country` varchar(45) DEFAULT NULL,
  `attribute1` varchar(100) DEFAULT NULL,
  `attribute2` varchar(100) DEFAULT NULL,
  `attribute3` varchar(100) DEFAULT NULL,
  `attribute4` varchar(100) DEFAULT NULL,
  `attribute5` varchar(100) DEFAULT NULL,
  `option1` varchar(45) DEFAULT NULL,
  `option2` varchar(45) DEFAULT NULL,
  `option3` varchar(45) DEFAULT NULL,
  `image` varchar(512) DEFAULT NULL,
  `image1` varchar(512) DEFAULT NULL,
  `image2` varchar(512) DEFAULT NULL,
  `image3` varchar(512) DEFAULT NULL,
  `image4` varchar(512) DEFAULT NULL,
  `image5` varchar(512) DEFAULT NULL,
  `price1` float DEFAULT NULL,
  `price2` float DEFAULT NULL,
  `price3` float DEFAULT NULL,
  `price4` float DEFAULT NULL,
  PRIMARY KEY (`sync_entry_id`),
  KEY `index2` (`category_lvl1`,`category_lvl2`,`category_lvl3`),
  KEY `index3` (`model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `oc_baycik_sync_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `sync_id` int(11) DEFAULT NULL,
  `category_path` varchar(602) DEFAULT NULL,
  `category_lvl1` varchar(200) DEFAULT NULL,
  `category_lvl2` varchar(200) DEFAULT NULL,
  `category_lvl3` varchar(200) DEFAULT NULL,
  `total_products` int(11) DEFAULT NULL,
  `comission` int(11) DEFAULT NULL,
  `destination_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `category_path_UNIQUE` (`category_path`,`sync_id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COMMENT='';

CREATE TABLE `oc_baycik_sync_list` (
  `sync_id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) DEFAULT NULL,
  `sync_name` varchar(45) DEFAULT NULL,
  `sync_parser_name` varchar(45) DEFAULT NULL,
  `sync_config` text,
  `sync_last_started` datetime DEFAULT NULL,
  PRIMARY KEY (`sync_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

