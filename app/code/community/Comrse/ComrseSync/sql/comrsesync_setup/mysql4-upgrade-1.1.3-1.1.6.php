<?php
try{
	$installer = $this;
	$installer->startSetup();
	$installer->run("
		ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `token` LONGTEXT NOT NULL;
    ALTER TABLE {$this->getTable('comrsesync')} ADD COLUMN `last_customers_synced` LONGTEXT NOT NULL;
		ALTER TABLE {$this->getTable('comrsesync')} DROP COLUMN `username`;
		ALTER TABLE {$this->getTable('comrsesync')} DROP COLUMN `password`;
	");
	$installer->endSetup();
}
catch(Exception $e){
	Mage::log('Failed to upgrade Comrse Plugin: '.$e->getMessage());
}