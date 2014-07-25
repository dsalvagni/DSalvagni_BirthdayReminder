<?php 
/**
* Fonte de dados para o campo de seleção de promoção a ser relacionada
* @package DSalvagni_BirthdayReminder
* @author Daniel Salvagni <danielsalvagni@gmail.com>
*/
class DSalvagni_BirthdayReminder_Model_Source_Promotion
{
	protected $options = null;

	/**
	* Retorna a lista de promoções
	* @access protected
	* @return array
	* @author Daniel Salvagni <danielsalvagni@gmail.com>
	*/
	protected function _getOptions()
	{
		$Promos = Mage::getModel('salesrule/rule')->getCollection();
		$arrPromos = array();

		if($Promos->getSize())
		{
			foreach($Promos as $Promo):
				if(!$Promo->getIsActive()) continue;
					$arrPromos[] = array(
							'label' => $Promo->getName(),
							'value' => $Promo->getId()
						);
			endforeach;
		}

		if($arrPromos)
		{
			array_unshift($arrPromos, array('label'=>"Selecione...", 'value'=>''));
			return $arrPromos;
		}
		else
			return array(
					array(
						'label'=>'Nenhuma promoção ativa',
						'value'=>''
						)
				);
	}

	/**
	* Retornar um array com as promoções
	* @access public
	* @return array
	* @author Daniel Salvagni <danielsalvagni@gmail.com>
	*/
	public function toOptionArray()
	{
		if(is_null($this->options))
			$this->options = $this->_getOptions();
		
		return $this->options;
	}	
}
?>