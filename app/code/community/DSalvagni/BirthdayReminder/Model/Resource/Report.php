<?php
class DSalvagni_BirthdayReminder_Model_Resource_Report extends Mage_Core_Model_Resource_Db_Abstract{
	/**
	 * Constructor
	 * Inicializa
	 * @access public
	 * @return void
	 * @author Daniel Salvagni <danielsalvagni@gmail.com>
	 */
	public function _construct(){
		$this->_init('birthdayreminder/report', 'entity_id');
	}
	
	/**
	 * Retorna os IDs das lojas cujo item específico está relacionado.Get store ids to which specified item is assigned
	 * @access public
	 * @param int $reportId
	 * @return array
	 * @author Daniel Salvagni <danielsalvagni@gmail.com>
	 */
	public function lookupStoreIds($reportId){
		$adapter = $this->_getReadAdapter();
		$select  = $adapter->select()
			->from($this->getTable('birthdayreminder/report_store'), 'store_id')
			->where('report_id = ?',(int)$reportId);
		return $adapter->fetchCol($select);
	}
	/**
	 * Depois de carregar
	 * @access public
	 * @param Mage_Core_Model_Abstract $object
	 * @return DSalvagni_BirthdayReminder_Model_Resource_Report
	 * @author Daniel Salvagni <danielsalvagni@gmail.com>
	 */
	protected function _afterLoad(Mage_Core_Model_Abstract $object){
		if ($object->getId()) {
			$stores = $this->lookupStoreIds($object->getId());
			$object->setData('store_id', $stores);
		}
		return parent::_afterLoad($object);
	}

	/**
	 * Retorna o objeto com os dados selecionados
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param DSalvagni_BirthdayReminder_Model_Report $object
	 * @return Zend_Db_Select
	 */
	protected function _getLoadSelect($field, $value, $object){
		$select = parent::_getLoadSelect($field, $value, $object);
		if ($object->getStoreId()) {
			$storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
			$select->join(
				array('birthdayreminder_report_store' => $this->getTable('birthdayreminder/report_store')),
				$this->getMainTable() . '.entity_id = birthdayreminder_report_store.report_id',
				array()
			)
			->where('birthdayreminder_report_store.store_id IN (?)', $storeIds)
			->order('birthdayreminder_report_store.store_id DESC')
			->limit(1);
		}
		return $select;
	}
	/**
	 * Relaciona com as views da loja
	 * @access protected
	 * @param Mage_Core_Model_Abstract $object
	 * @return DSalvagni_BirthdayReminder_Model_Resource_Report
	 * @author Daniel Salvagni <danielsalvagni@gmail.com>
	 */
	protected function _afterSave(Mage_Core_Model_Abstract $object){
		$oldStores = $this->lookupStoreIds($object->getId());
		$newStores = (array)$object->getStores();
		if (empty($newStores)) {
			$newStores = (array)$object->getStoreId();
		}
		$table  = $this->getTable('birthdayreminder/report_store');
		$insert = array_diff($newStores, $oldStores);
		$delete = array_diff($oldStores, $newStores);
		if ($delete) {
			$where = array(
				'report_id = ?' => (int) $object->getId(),
				'store_id IN (?)' => $delete
			);
			$this->_getWriteAdapter()->delete($table, $where);
		}
		if ($insert) {
			$data = array();
			foreach ($insert as $storeId) {
				$data[] = array(
					'report_id'  => (int) $object->getId(),
					'store_id' => (int) $storeId
				);
			}
			$this->_getWriteAdapter()->insertMultiple($table, $data);
		}
		return parent::_afterSave($object);
	}}