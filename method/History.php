<?php
final class Payment_History extends GWF_Method
{
	public function getHTAccess()
	{
		return 'RewriteRule ^payments/?$ index.php?mo=Payment&me=History [QSA]'.PHP_EOL;
	}
	
	public function execute()
	{
		if (true === isset($_POST['qsearch']))
		{
			return $this->onQuickSearch();
		}
		return $this->templateStaff();
	}

	### Search Templates
	public function templateAdvSearch() { return GWF_FormGDO::getSearchForm($this->module, $this, GDO::table('GWF_Order'), GWF_User::getStaticOrGuest()); }
	public function templateQuickSearch() { return GWF_FormGDO::getQuickSearchForm($this->module, $this, GDO::table('GWF_Order'), GWF_User::getStaticOrGuest()); }

	private function getTable()
	{
		$user = GWF_User::getStaticOrGuest();
		$orders = GDO::table('GWF_Order');
		$paid = GWF_Order::PAID;
		$exec = GWF_Order::PROCESSED;
		$o = Common::getGet('o') !== false;
		$op = $o ? '!=' : '=';
		$op2 = $o ? 'AND' : 'OR';
		$conditions = "(order_status$op'$paid' $op2 order_status$op'$exec') and order_uid={$user->getID()}";
		$bit = $o ? 'o' : 't';
		$sortURL = $this->getMethodHref('&'.$bit.'=1&by=%BY%&dir=%DIR%');
		return GWF_TableGDO::display($this->module, $orders, GWF_Session::getUser(), $sortURL, $conditions, 25, true, array('order_uid'));
	}

	public function templateStaff()
	{
		$tVars = array(
			'quicksearch' => $this->templateQuickSearch()->templateX($this->module->lang('ft_search')),
			'table' => $this->getTable(),
			'href_orders' => $this->getMethodHref(sprintf('&o=1&by=order_id&dir=DESC&page=1')),
			'href_transactions' => $this->getMethodHref(sprintf('&t=1&by=order_id&dir=DESC&page=1')),
		);
		return $this->module->templatePHP('history.php', $tVars);
	}

	private function onQuickSearch()
	{
		$tVars = array(
			'quicksearch' => $this->templateQuickSearch()->templateX($this->module->lang('ft_search')),
			'table' => $this->getQuickSearchTable(),
			'href_orders' => $this->getMethodHref(sprintf('&o=1&by=order_id&dir=DESC&page=1')),
			'href_transactions' => $this->getMethodHref(sprintf('&t=1&by=order_id&dir=DESC&page=1')),
		);
		return $this->module->templatePHP('history.php', $tVars);
	}

	private function getQuickSearchTable()
	{
		$user = GWF_User::getStaticOrGuest();
		$orders = GDO::table('GWF_Order');
		$orders instanceof GWF_Order;

		$o = Common::getGet('o') !== false;
		$bit = $o ? 'o' : 't';
		$sortURL = $this->getMethodHref('&'.$bit.'=1&by=%BY%&dir=%DIR%');

		$fields = $orders->getSearchableFields($user);

		$term = Common::getRequestString('term', '');

		if (false === ($conditions = GWF_QuickSearch::getQuickSearchConditions($orders, $fields, $term)))
		{
			$conditions = '0';
		}
		return GWF_TableGDO::display($this->module, $orders, $user, $sortURL, $conditions, 25, true);
	}
}

