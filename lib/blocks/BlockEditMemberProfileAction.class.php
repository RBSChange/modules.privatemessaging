<?php
/**
 * privatemessaging_BlockEditMemberProfileAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockEditMemberProfileAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($this->isInBackofficeEdition() || $user === null)
		{
			return website_BlockView::NONE;
		}
		
		$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($user->getId());
		if ($profile === null)
		{
			$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getNewDocumentInstance();
			$profile->setAccessor($user);
		}
		$request->setAttribute('profile', $profile);
		return website_BlockView::INPUT;
	}

	/**
	 * @return boolean
	 */
	public function saveNeedTransaction()
	{
		return true;
	}
	
    /**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_privatemessagingprofile $profile
	 * @return String
	 */
	public function executeSave($request, $response, privatemessaging_persistentdocument_privatemessagingprofile $profile)
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($profile->isNew())
		{
			$profile->setAccessor($user);
		}
		elseif ($user->getId() != $profile->getAccessorId()) 
		{
			throw new BaseException('Not your profile!', 'm.users.fo.not-your-profile');
		}
		$profile->save();
		$request->setAttribute('profile', $profile);
		RequestContext::getInstance()->resetProfile();
		users_ProfileService::getInstance()->initCurrent(false);
		$this->addMessage(LocaleService::getInstance()->trans('m.users.frontoffice.informations-updated', array('ucf', 'html')));
		return website_BlockView::INPUT;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_privatemessagingprofile $profile
	 */
	public function validateSaveInput($request, $profile)
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		$profile->setAccessor($user);
				
		$rules = BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_privatemessagingprofile');
		return $this->processValidationRules($rules, $request, $profile);
	}
}