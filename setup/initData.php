<?php
/**
 * @package modules.privatemessaging.setup
 */
class privatemessaging_Setup extends object_InitDataSetup
{
	public function install()
	{
		$this->executeModuleScript('init.xml');
	}

	/**
	 * @return string[]
	 */
	public function getRequiredPackages()
	{
		return array('modules_users');
	}
}