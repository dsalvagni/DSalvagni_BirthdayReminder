<?php

$installer = $this;
$installer->startSetup();

$table_one = $installer->getConnection()
->newTable($installer->getTable('birthdayreminder/report'))
->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'identity' => true,
    'nullable' => false,
    'primary' => true,
), 'Entity id')
->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_TEXT, 63, array(
    'nullable' => true,
    'default' => null,
), 'Store view id')

->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 150, array(
    'nullable' => false,
), 'Email')

->addColumn('coupon_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 20, array(
    'nullable' => true,
    'default' => null, 
), 'Coupon Code')

->addColumn('coupon_expire', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    'nullable' => true,
    'default' => null, 
), 'Coupon Expire Date')

->addColumn('coupon_expire', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    'nullable' => true,
    'default' => null, 
), 'Coupon Expire Date')

->addColumn('counter_sent', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => true,
    'default' => '0', 
), 'Counter sent')

->addColumn('interval', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => true, 
), 'Interval')


/**
 * About the promotion
 */
->addColumn('promo_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'nullable' => true,
), 'Promo ID')

->addColumn('promo_free_shipping', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
    'nullable' => true,
    'default' => 0, 
), 'Is free shipping?')

->addColumn('promo_action', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
    'nullable' => true,
    'default' => 0, 
), 'Action')
/**
 * END PROMO INFO
 */

/**
 * Statistics
 */
->addColumn('success', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
    'nullable' => true,
    'default' => 0, 
), 'Is "Cart Fixed"?')

/*
 * Finished

->addColumn('is_finalized', Varien_Db_Ddl_Table::TYPE_TINYINT, 1, array(
    'nullable' => true,
    'default' => 0, 
), 'Is finalized?')
 */

->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    'nullable' => true,
    'default' => null, 
), 'Creation time')

->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    'nullable' => true,
    'default' => null, 
), 'Updated time - last sent')

->addColumn('ordered_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    'nullable' => true,
    'default' => null, 
), 'If was successful')

// ->addIndex($installer->getIdxName(
// 	    $installer->getTable('birthdayreminder/report'),
// 	    array('store_id'),
// 	    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
// 	), 
// 	array('store_id'), 
// 	array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
// )
->addIndex($installer->getIdxName(
	    $installer->getTable('birthdayreminder/report'),
	    array('promo_id'),
	    Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX
	), 
	array('promo_id'), 
	array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
)
->setComment('BirthdayReminder reports');
$installer->getConnection()->createTable($table_one);

$table = $installer->getConnection()
    ->newTable($installer->getTable('birthdayreminder/report_store'))
    ->addColumn('report_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'primary'   => true,
        ), 'Report ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store ID')
    ->addIndex($installer->getIdxName('birthdayreminder/report_store', array('store_id')), array('store_id'))
    ->addForeignKey($installer->getFkName('birthdayreminder/report_store', 'report_id', 'birthdayreminder/report', 'entity_id'), 'report_id', $installer->getTable('birthdayreminder/report'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('birthdayreminder/report_store', 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Store link');
$installer->getConnection()->createTable($table);

$installer->endSetup();