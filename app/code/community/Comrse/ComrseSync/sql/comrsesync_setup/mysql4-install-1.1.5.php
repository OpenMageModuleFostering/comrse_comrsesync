<?php
try
{
  $installer = $this;
  $installer->startSetup();
  $installer->run("
  DROP TABLE IF EXISTS {$this->getTable('comrsesync')};
  CREATE TABLE {$this->getTable('comrsesync')} (
    `comrsesync_id` int(11) unsigned NOT NULL auto_increment,
    `org` varchar(255) NOT NULL default '',
    `token` varchar(255) NOT NULL default '',
    `synced` tinyint(4) NOT NULL default 0,
    `total_orders` INT(11) NOT NULL default 0,
    `last_synced_time` BIGINT(20) NOT NULL default 0,
    `last_order_synced` INT(11) NOT NULL default 0,
    `last_orders_synced` LONGTEXT NOT NULL,
    `last_products_synced` LONGTEXT NOT NULL,
    PRIMARY KEY (`comrsesync_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  INSERT INTO {$this->getTable('comrsesync')} (`org`, `token`, `synced`, `total_orders`, `last_synced_time`, `last_order_synced`, `last_orders_synced`, `last_products_synced`)VALUES('', '', '', 0, 0, 0, 0, '', '');
  ");

  $installer->endSetup();
}
catch(Exception $e)
{
  Mage::log('Failed to install Comr.se Plugin: '.$e->getMessage());
}