<?php
/**
 * privatemessaging_BlockPostListBaseAction
 * @package modules.privatemessaging
 */
abstract class privatemessaging_BlockPostListBaseAction extends privatemessaging_BaseBlockAction
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
		
		$user = users_UserService::getInstance()->getCurrentUser();
		$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($user->getId(), true);
		$displayConfig['showGravatars'] = $profile->getViewAvatars();
		$displayConfig['avatarsSize'] = $this->getConfigurationValue('avatarsSize', 64);
		$displayConfig['showSignatures'] = $profile->getViewSignatures();
		$displayConfig['showActions'] = $this->getConfigurationValue('showActions', false);
		$displayConfig['showPagination'] = $this->getConfigurationValue('showPagination', true);
		$displayConfig['currentUser'] = $user;
		$displayConfig['currentProfile'] = $profile;
		
		return $displayConfig;
	}
	
	/**
	 * @return integer
	 */
	protected function getNbItemPerPage()
	{
		$itemsPerPage = $this->getConfigurationValue('nbitemperpage');
		return ($itemsPerPage !== null) ? $itemsPerPage : self::DEFAULT_ITEMS_PER_PAGE;
	}
	
	/**
	 * @param string $name
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