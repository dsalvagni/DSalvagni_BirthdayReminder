<?php
/**
* Coleção dos registro de envios
* @package DSalvagni_BirthdayReminder
* @author Daniel Salvagni <danielsalvgni@gmail.com>
*/
class DSalvagni_BirthdayReminder_Model_Resource_Report_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	protected $_joinedFields = array();
	/**
	 * Inicializa
	 * @access public
	 * @return void
	 * @author Daniel Salvagni <danielsalvgni@gmail.com>
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('birthdayreminder/report');
		$this->_map['fields']['store'] = 'store_table.store_id';
	}
	/**
	 * Adiciona o filtro por loja
	 * @access public
	 * @param int|Mage_Core_Model_Store $store
	 * @param bool $withAdmin
	 * @return DSalvagni_BirthdayReminder_Model_Resource_Report_Collection
	 * @author Daniel Salvagni <danielsalvgni@gmail.com>
	 */
	public function addStoreFilter($store, $withAdmin = true){
		if (!isset($this->_joinedFields['store'])){
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
			if (!is_array($store)) {
				$store = array($store);
			}
			if ($withAdmin) {
				$store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
			}
			$this->addFilter('store', array('in' => $store), 'public');
			$this->_joinedFields['store'] = true;
		}
		return $this;
	}
	/**
	 * Une a table de relação caso haja filtro de loja.
	 * @access protected
	 * @return DSalvagni_BirthdayReminder_Model_Resource_Report_Collection
	 * @author Daniel Salvagni <danielsalvgni@gmail.com>
	 */
	protected function _renderFiltersBefore(){
		if ($this->getFilter('store')) {
			$this->getSelect()->join(
				array('store_table' => $this->getTable('birthdayreminder/report_store')),
				'main_table.entity_id = store_table.report_id',
				array()
			)->group('main_table.entity_id');
			$this->_useAnalyticFunction = true;
		}
		return parent::_renderFiltersBefore();
	}
	/**
	 * Retorna o total de registro do SQL
	 * @access public
	 * @return Varien_Db_Select
	 * @author Daniel Salvagni <danielsalvgni@gmail.com>
	 */
	public function getSelectCountSql(){
		$countSelect = parent::getSelectCountSql();
		$countSelect->reset(Zend_Db_Select::GROUP);
		return $countSelect;
	}
}