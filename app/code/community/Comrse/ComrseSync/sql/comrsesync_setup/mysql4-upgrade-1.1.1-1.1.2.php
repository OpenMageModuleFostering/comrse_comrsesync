<?php
try{
	$installer = $this;
	$installer->startSetup();
	$installer->run("
	ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `total_orders` INT(11) NOT NULL default 0;
	ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_orders_synced` INT(11) NOT NULL default 0;
	ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_synced_time` BIGINT(20) NOT NULL default 0;
	ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_order_synced` INT(11) NOT NULL default 0;
	");
	$installer->endSetup();
}
catch(Exception $e){
	@mail("rhen@comr.se", "Plugin Error", $e->getMessage());
	Mage::log('Failed to upgrade Comr.se Plugin: '.$e->getMessage());
}