<?php
/**
 * privatemessaging_BlockPostListBaseAction
 * @package modules.privatemessaging
 */
abstract class privatemessaging_BlockPostListBaseAction extends website_BlockAction
{
	/**
	 * @var Integer
	 */
	const DEFAULT_ITEMS_PER_PAGE = 20;
	
	/**
	 * @return Array
	 */
	protected function getDisplayConfig()
	{
		$displayConfig = array();
		
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		$displayConfig['showGravatars'] = $member->getViewAvatars();
		$displayConfig['avatarsSize'] = $this->getConfigurationValue('avatarsSize', 64);
		$displayConfig['showSignatures'] = $member->getViewSignatures();
		$displayConfig['showActions'] = $this->getConfigurationValue('showActions', false);
		$displayConfig['showPagination'] = $this->getConfigurationValue('showPagination', true);
		$displayConfig['currentMember'] = $member;
		
		return $displayConfig;
	}
	
	/**
	 * @return Integer
	 */
	protected function getNbItemPerPage()
	{
		$itemsPerPage = $this->getConfigurationValue('nbitemperpage');
		return ($itemsPerPage !== null) ? $itemsPerPage : self::DEFAULT_ITEMS_PER_PAGE;
	}
	
	/**
	 * @param String $name
	 * @param Mixed $defaultValue
	 * @return Mixed
	 */
	protected function getConfigurationValue($name, $defaultValue = null)
	{
		$configuration = $this->getConfiguration();
		$getter = 'get'.ucfirst($name);
		if (f_util_ClassUtils::methodExists($configuration, 'get'.ucfirst($name)))
		{
			return $configuration->{$getter}();
		}
		return $defaultValue;
	}
}