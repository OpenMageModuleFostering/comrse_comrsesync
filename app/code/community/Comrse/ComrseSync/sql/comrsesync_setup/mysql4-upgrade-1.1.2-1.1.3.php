<?php
try{
	$installer = $this;
	$installer->startSetup();
	$installer->run("
	ALTER TABLE {$this->getTable('comrsesync')} MODIFY COLUMN `last_orders_synced` LONGTEXT NOT NULL;
	UPDATE {$this->getTable('comrsesync')} SET `last_orders_synced` = '';
	ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_products_synced` LONGTEXT NOT NULL;
	");
	$installer->endSetup();
}
catch(Exception $e){
	Mage::log('Failed to upgrade Comrse Plugin: '.$e->getMessage());
}