<?php
/**
 * privatemessaging_BaseBlockAction
 * @package modules.privatemessaging.lib.blocks
 */
abstract class privatemessaging_BaseBlockAction extends website_BlockAction
{
	/**
	 * @return TemplateObject
	 */
	protected function getForbiddenView()
	{
		change_Controller::getInstance()->getStorage()->writeForUser('users_illegalAccessPage', $_SERVER["REQUEST_URI"]);
		$user = users_UserService::getInstance()->getCurrentUser();
		$this->getRequest()->setAttribute('user', $user);
		$profile = ($user) ? privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($user->getId()) : null;
		$this->getRequest()->setAttribute('profile', $profile);
		return $this->getTemplateByFullName('modules_privatemessaging', 'Privatemessaging-Block-Generic-Forbidden');
	}
}