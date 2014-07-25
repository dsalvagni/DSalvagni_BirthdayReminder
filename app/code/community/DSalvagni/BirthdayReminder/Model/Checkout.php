<?php 
/**
* Verifica se existe algum registro de e-mails enviados para o e-mail do cliente que acabou de fazer a compra.
* Caso exista, atualiza para informar que o envio de e-mail resultou em conversão.
* @package DSalvagni_BirthdayReminder
* @author Daniel Salvagni <danielsalvgni@gmail.com>
*/
class DSalvagni_BirthdayReminder_Model_Checkout extends Mage_Core_Model_Abstract {
	
	/**
	* Evento executado ao finalizar um compra.
	* @access public
	* @param $observer
	* @return void
	* @author Daniel Salvagni <danielsalvgni@gmail.com>
	*/
	public function updateReport($observer=null)
	{
		$Email  = $observer->getOrder()->getCustomer()->getEmail();
		$Report = $this->reportExist($Email);
		if(!$Report) return;

		$Report->setData('success',1);
		$Report->setData('ordered_at',date('Y-m-d H:i:s'));
		
		$Report->save();
	}
	/**
	* Verifica se existe o registro de envio para o e-mail informado.
	* @access protected
	* @param $email <string>
	* @return Boolean
	* @author Daniel Salvagni <danielsalvgni@gmail.com>
	*/
	protected function reportExist($Email)
    {
        $Report = Mage::getModel('birthdayreminder/report')->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('email',array('eq'=>$Email));
        if($Report->getSize())
        {
            $Report = $Report->getFirstItem();
            return $Report;
        }

        return false;
    }
}
