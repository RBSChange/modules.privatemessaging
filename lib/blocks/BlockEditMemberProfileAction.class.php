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
	function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}

		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($member === null)
		{
			$user = users_UserService::getInstance()->getCurrentFrontEndUser();
			$member = forums_MemberService::getInstance()->getNewDocumentInstance();
			$member->setUser($user);
			$member->save();
		}
		$request->setAttribute('member', $member);

		return website_BlockView::INPUT;
	}

	/**
	 * @return string[]|null
	 */
	public function getMemberBeanInclude()
	{
		if (Framework::getConfigurationValue('modules/website/useBeanPopulateStrictMode') != 'false')
		{
			return array('notifyNewMessages', 'viewAvatars', 'viewSignatures');
		}
		return null;
	}

	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_member $member
	 * @throws Exception
	 * @return String
	 */
	public function executeSave($request, $response, privatemessaging_persistentdocument_member $member)
	{
		$currentMember = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($currentMember->getId() !== $member->getId())
		{
			throw new Exception('Bad parameter');
		}
		$member->save();

		$this->addMessage(LocaleService::getInstance()->transFO('m.users.frontoffice.informations-updated', array('ucf')));

		return website_BlockView::INPUT;
	}

	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_member $member
	 * @return bool
	 */
	public function validateSaveInput($request, $member)
	{
		$val = BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_member', null, array('user'));
		return $this->processValidationRules($val, $request, $member);
	}
}