<?php
try{
	$installer = $this;
	$installer->startSetup();
	$installer->run("
		ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_customers_synced` LONGTEXT NOT NULL;
	");
	$installer->endSetup();
}
catch(Exception $e){
	Mage::log('Failed to upgrade Comrse Plugin: '.$e->getMessage());
}