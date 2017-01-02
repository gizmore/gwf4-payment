<?php
/**
 * Generic Payment Module.
 * You need to inherit this to create a new full featured payment module.
 * @author gizmore
 */
abstract class GWF_PaymentModule extends GWF_Module
{
	public function payment() { return GWF_Module::getModule('Payment'); }
	
	################
	### Abstract ###
	################
	public abstract function getSiteName();
	public abstract function getSiteNameToken();
	public abstract function getSupportedCurrencies();
	public abstract function displayPaysiteButton(GWF_Module $module, GWF_Order $order, GWF_Orderable $gdo, GWF_User $user);
	public function canAfford(GWF_User $user, $price) { return $price > 0; }
	public function canOrder(GWF_User $user, GWF_Orderable $gdo) { return true; }
	
	##################
	### GWF_Module ###
	##################
	public function getDefaultPriority() { return GWF_Module::DEFAULT_PRIORITY + 1; } # Start at least one later
	public function getDependencies() { return array('Payment'=>1.00); }
	public function onInstall($dropTable)
	{
		return
			GWF_ModuleLoader::installVars($this, array(
				'fee_buy' => array('1.00', 'float', '-50', '50'),
				'fee_sell' => array('2.00', 'float', '-50', '50'),
			));
	}
	public function cfgSiteFeeBuy() { return $this->getModuleVarFloat('fee_buy', 0.0); }
	public function cfgSiteFeeSell() { return $this->getModuleVarFöpat('fee_sell', 0.0); }
	
	#####################
	### Accessability ###
	#####################
	private static $payment_modules = array();
	
	public static function registerPaymentModule(GWF_PaymentModule $module)
	{
		self::$payment_modules[$module->getSiteNameToken()] = $module;
	}
	
	/**
	 * @param string $sitename_token (2 or 3 chars)
	 * @return GWF_PaymentModule
	 */
	public static function getPaymentModule($sitename_token)
	{
		return isset(self::$payment_modules[$sitename_token]) ? self::$payment_modules[$sitename_token] : false;
	}
	
	public static function displayPaymentButtons(GWF_Orderable $gdo, $price_total)
	{
		$back = '';
		$user = GWF_User::getStaticOrGuest();
		foreach (self::$payment_modules as $module)
		{
			$module->onLoadLanguage();
			if ($module->canAfford($user, $price_total) && $module->canOrder($user, $gdo))
			{
				$back .= $module->displayPaymentButton();
			}
		}
		return $back;
	}
	
	public function paymentButtonHidden($order_token=false)
	{
		$hidden = GWF_Form::hidden('paysite', $this->getSiteNameToken());
		$hidden .= $order_token === false ? '' : GWF_Form::hidden('gwf_order', $order_token);
		return $hidden;
	}
	
	public function displayPaymentButton($step='2', $order_token=false)
	{
		$tVars = array(
			'action' => GWF_HTML::display($_SERVER['REQUEST_URI']),
			'hidden' => $this->paymentButtonHidden($order_token),
			'button_name' => 'on_order_'.$step,
			'site_token' => $this->getSiteNameToken(),
		);
		return $this->payment()->template('paybutton.php', $tVars);
	}
}
