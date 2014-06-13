<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
	
	$this->startSetup();

	$types = array(
		1 => array('product', 'post'),
		2 => array('product', 'category'),
		3 => array('category', 'post'),
		4 => array('category', 'category'),
		5 => array('cms_page', 'post'),
		6 => array('cms_page', 'category'),
	);
	
	$sql = "	
		CREATE TABLE IF NOT EXISTS {$this->getTable('wordpress/association_type')} (
			`type_id` int(11) unsigned NOT NULL auto_increment,
			`object` varchar(16) NOT NULL default '',
			`wordpress_object` varchar(16) NOT NULL default '',
			PRIMARY KEY(type_id)
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
		CREATE TABLE IF NOT EXISTS {$this->getTable('wordpress/association')} (
			`assoc_id` int(11) unsigned NOT NULL auto_increment,
			`type_id` int(3) unsigned NOT NULL default 0,
			`object_id` int(11) unsigned NOT NULL default 0,
			`wordpress_object_id` int(11) unsigned NOT NULL default 0,
			`position` int(4) unsigned NOT NULL default 4444,
			`store_id` smallint(5) unsigned NOT NULL default 0,
			PRIMARY KEY (`assoc_id`),
			CONSTRAINT `FK_WP_ASSOC_TYPE_ID_WP_ASSOC_TYPE` FOREIGN KEY (`type_id`) REFERENCES `{$this->getTable('wordpress/association_type')}` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
			KEY `FK_WP_ASSOC_TYPE_ID_WP_ASSOC_TYPE` (`type_id`),
			KEY `FK_STORE_ID_WP_ASSOC` (`store_id`),
			CONSTRAINT `FK_STORE_ID_WP_ASSOC` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	";

	try {
		$this->run($sql);

		foreach($types as $typeId => $data) {
			try {
				$this->run(sprintf("INSERT INTO %s VALUES (%d, '%s', '%s');\n", $table = $this->getTable('wordpress/association_type'), $typeId, $data[0], $data[1]));
			}
			catch (Exception $e) {}
		}	
	}
	catch (Exception $e) {
		Mage::helper('wordpress')->log($e);
		throw $e;
	}
	
	$this->endSetup();
