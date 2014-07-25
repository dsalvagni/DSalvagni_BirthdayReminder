<?php 
class DSalvagni_BirthdayReminder_Model_Observer extends Mage_Core_Model_Abstract {

    const XML_PATH_EMAIL_DIA_ANIVERSARIO     = 'birthdayreminder/envio/template_data_aniversario';
    const XML_PATH_EMAIL_MES_ANIVERSARIO     = 'birthdayreminder/envio/template_mes_aniversario';
    const XML_PATH_BIRTHDAYREMINDER_IDENTITY = 'birthdayreminder/envio/remetente';

    public function runSchedule() {
        
    	$EnvioAtivo = Mage::getStoreConfig('birthdayreminder/envio/habilitado');
        $Intervalo    = Mage::getStoreConfig('birthdayreminder/envio/intervalo');

    	if(!$EnvioAtivo) return false;

        $EnviarDiaAniversario     = Mage::getStoreConfig('birthdayreminder/envio/enviar_data_aniversario');
        $EnviarMesAniversario     = Mage::getStoreConfig('birthdayreminder/envio/enviar_mes_aniversariante');

        $EnviaCupomDiaAniversario = Mage::getStoreConfig('birthdayreminder/cupom_data_aniversario/cupom_data_aniversario_habilitado');
        $EnviaCupomMesAniversario = Mage::getStoreConfig('birthdayreminder/cupom_mes_aniversariante/cupom_aniversariante');

        $IDPromoDiaAniversario = Mage::getStoreConfig('birthdayreminder/cupom_data_aniversario/promocao_aniversario');
        $IDPromoMesAniversario = Mage::getStoreConfig('birthdayreminder/cupom_mes_aniversariante/promocao_aniversariante');

        $PrefixDiaAniversario = Mage::getStoreConfig('birthdayreminder/cupom_data_aniversario/prefixo_cupom_aniversario');
        $PrefixMesAniversario = Mage::getStoreConfig('birthdayreminder/cupom_mes_aniversariante/prefixo');

        $ExpireDiaAniversario = Mage::getStoreConfig('birthdayreminder/cupom_data_aniversario/validade_cupom_aniversario');
        $ExpireMesAniversario = Mage::getStoreConfig('birthdayreminder/cupom_mes_aniversariante/validade_cupom_aniversariante');

        if(!$EnviarDiaAniversario && !$EnviarMesAniversario) return false;

        $Hoje = date('Y-m-d');
        $PrimeiroDiaDoMes = date('Y-m')."-01";
        $UltimoDiaDoMes   = date("Y-m-t");


        $Aniversariantes = Mage::getResourceModel('customer/customer_collection')
                                ->addNameToSelect()
                                ->addFieldToFilter('dob',array('gteq'=>$PrimeiroDiaDoMes.' 00:00:00'))
                                ->addFieldToFilter('dob',array('lteq'=>$UltimoDiaDoMes.' 00:00:00'));
        
        $TotalCustomers = $Aniversariantes->getSize();
        $Successful = 0;
        $NotSent    = 0;

        if(!$TotalCustomers) return false;

        $PromoDiaAniversario = ($EnviaCupomDiaAniversario) ? Mage::getModel('salesrule/rule')->load($IDPromoDiaAniversario) : null;
        $PromoMesAniversario = ($EnviaCupomMesAniversario) ? Mage::getModel('salesrule/rule')->load($IDPromoMesAniversario) : null;



        foreach($Aniversariantes as $C):

            $DOB = date("Y-m-d",strtotime($C->getDob()));

            $Report = $this->reportExist($C);

            if($Report)
            {
                if(!$this->canSend($Report))
                   continue; 

            }
            
                $Type = ($DOB==$Hoje) ? 'day' : 'month';

                if($Type=='day' && !$EnviarDiaAniversario)
                    continue;

                $Promo  = ($Type=='day') ? $PromoDiaAniversario  : $PromoMesAniversario ;
                $Prefix = ($Type=='day') ? $PrefixDiaAniversario : $PrefixMesAniversario ;
                $Expire = ($Type=='day') ? $ExpireDiaAniversario : $ExpireMesAniversario ;

                if(!$Report)
                {
                    $Report = Mage::getModel('birthdayreminder/report');
                    $Cupom = ($Promo) ? $this->generateCoupon($Promo, $Prefix, $Expire) : null;
                }
                else $Cupom = null;

                if($this->sendMail($C,$Type,$Cupom))
                {
                    $Successful++;                    
                    $this->updateMailReport($Report, $C, $Promo, $Cupom);
                }
                else
                    $NotSent++;

        endforeach;

        Mage::log("Birthday Reminder Log: {$TotalCustomers} customers. Successful: {$Successful}. Not sent: {$NotSent}");
    }

    protected function generateCoupon($Promo,$Prefix,$Expire)
    {
        $generator = Mage::getModel('salesrule/coupon_massgenerator');


        $data = array(
            'max_probability'   => .25,
            'max_attempts'      => 1,
            'uses_per_customer' => 1,
            'uses_per_coupon'   => 1,
            'prefix'            => $Prefix,
            'qty'               => 1, 
            'length'            => 12, 
            'to_date'           => date('Y-m-d', strtotime(" + {$Expire} days")),
            'format'            => Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC,
            'rule_id'           => $Promo->getId()
        );

        Mage::log($data);

        $Validate = $generator->validateData($data);
        if(!$Validate) return null;

        $generator->setData($data);

        $generator->generatePool();
        
        $Coupon = Mage::getResourceModel('salesrule/coupon_collection')
            ->addRuleToFilter($Promo)
            ->addGeneratedCouponsFilter()
            ->getLastItem();

        return $Coupon;
    }   

    protected function sendMail($Customer,$Type, $Cupom)
    {
        

        $TemplateEmail = ($Type=='day') ? self::XML_PATH_EMAIL_DIA_ANIVERSARIO : self::XML_PATH_EMAIL_MES_ANIVERSARIO;

        /**
         * Desativa o translate inline
         */
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        /**
         * Instancia o model de template de e-mail
         */
        $mailTemplate = Mage::getModel('core/email_template');

         /**
         * Pega o ID do template de e-mail, filtrando pela loja que o pedido está relacionado
         */
        $template = Mage::getStoreConfig($TemplateEmail, Mage::app()->getStore()->getId());
        /**
         * Define as configurações de design do template
         */

        $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
                    /**
                     * Envia o e-mail
                     * Definição da função de envio
                     *
                     * @param   int $templateId Id do Template
                     * @param   string|array $sender Informações do remetente
                     * @param   string $email E-mail do Destinatário
                     * @param   string $name Nome do Destinatário
                     * @param   array $vars Variáveis que podem ser utilizadas no template
                     * @param   int|null $storeId Id da Loja
                     * @return  Mage_Core_Model_Email_Template
                     */
                     ->sendTransactional(
                                $template,
                                Mage::getStoreConfig(
                                    self::XML_PATH_BIRTHDAYREMINDER_IDENTITY,
                                    Mage::app()->getStore()->getId()
                                ),
                                $Customer->getEmail(),
                                $Customer->getFirstname().' '.$Customer->getLastname(),
                                /**
                                 * NESSE PONTO DEFINIMOS AS VARIÁVEIS PASSADAS PARA O TEMPLATE
                                 */
                                array(
                                        /**
                                         * Envia o objeto do cliente para que seja possível retornar os atributos do cliente no template
                                         */
                                        'customer'     => $Customer,
                                        /**
                                         * Envia o objeto do pedido para que seja possível retornar os atributos do pedido no template
                                         */
                                        'coupon'        => (is_null($Cupom)) ? false : $Cupom->getCode(),
                                        /**
                                         * Expira
                                         */
                                        'expire'        => (is_null($Cupom)) ? false : Mage::helper('core')->formatDate($Cupom->getExpirationDate(), 'medium', false)
                                )
                     );
        /**
         * Reativa o translate
         */
        $translate->setTranslateInline(true);
    	
    	/**
    	 * Insere/Altera registro de envio
    	 */
    	
    	/**
    	 * Retorna
    	 */
        return true;
    }

    protected function reportExist($C)
    {
        $Report = Mage::getModel('birthdayreminder/report')->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('email',array('eq'=>$C->getEmail()));
                    //->addFieldToFilter('is_finalized', array('eq' => 0));
        if($Report->getSize())
        {
            $Report = $Report->getFirstItem();
            return $Report;
        }

        return false;
    }

    protected function canSend($R)
    {
        /**
         * Compra Realizada
         */
        if($R->getSuccess()==1)
            return false;

        /**
         * Finalizado
         */
        if($R->getIsFinalized()==1)
            return false;

        /**
         * INTERVALO
         */
        $UltimoEnvio = strtotime($R->getUpdatedAt());
        $IntevaloConfigurado = $R->getInterval();

        $Hoje      = strtotime(date('Y-m-d H:i:s'));

        $Intervalo = intval(abs($UltimoEnvio - $Hoje)/86400);
        if($Intervalo <= $IntevaloConfigurado)
            return false;
         /**
         * FIM INTERVALO
         */

    }

    protected function updateMailReport($Report, $Customer, $Promo = null, $Cupom = null)
    {
    	/**
         * Salva o report
         */
        if($Report->getEmail() == "")
        {
            Mage::log('Action: '.$Promo->getSimpleAction());
            /**
             * É novo
             */
            $Intervalo    = Mage::getStoreConfig('birthdayreminder/envio/intervalo');
            $data = array(
                    'store_id' => Mage::app()->getStore()->getId(),
                    'email' => $Customer->getEmail(),
                    'coupon_code' => (is_null($Cupom)) ? null : $Cupom->getCode(),
                    'coupon_expire' => (is_null($Cupom)) ? 0 : $Cupom->getExpirationDate(),
                    'counter_sent' => 1,
                    'interval' => $Intervalo,
                    'promo_id' => (is_null($Promo)) ? 0 : $Promo->getId(),
                    'promo_free_shipping' => (is_null($Promo)) ? 0 : $Promo->getSimpleFreeShipping(),
                    'promo_action' => (is_null($Promo)) ? 0 : $Promo->getSimpleAction(),
                    'success' => 0,
                    //'is_finalized' => ($Hoje == $UltimoDiaDoMes) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
            $Report->addData($data);
             
        } else 
        {
            $Counter = $Report->getCounterSent();
            $Counter++;
            $Now = date('Y-m-d H:i:s');

            $Report->setData('counter_sent',$Counter);
            $Report->setData('updated_at',$Now);
            //if($Hoje == $UltimoDiaDoMes)
              //  $Report->setData('is_finalized','1');
                 
        }

        $Report->save();  
    }
}
?>