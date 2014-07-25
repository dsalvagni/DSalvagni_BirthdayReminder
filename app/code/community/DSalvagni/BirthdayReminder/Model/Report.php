<?php
/**
* Gerencia o registro de envios
* @package DSalvagni_BirthdayReminder
* @author Daniel Salvagni <danielsalvgni@gmail.com>
*/
class DSalvagni_BirthdayReminder_Model_Report extends Mage_Core_Model_Abstract
{
	
	const ENTITY= 'birthdayreminder_report';
	const CACHE_TAG = 'birthdayreminder_report';

	protected $_eventPrefix = 'birthdayreminder_report';
	protected $_eventObject = 'report';

	public function _construct(){
		parent::_construct();
		$this->_init('birthdayreminder/report');
	}

	protected function _beforeSave(){
		parent::_beforeSave();
		$now = Mage::getSingleton('core/date')->gmtDate();
		if ($this->isObjectNew()){
			$this->setCreatedAt($now);
		}
		$this->setUpdatedAt($now);
		return $this;
	}

	protected function _afterSave() {
		return parent::_afterSave();
	}
}