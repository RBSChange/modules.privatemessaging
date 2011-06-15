<?php
/**
 * privatemessaging_BlockEditMemberProfileAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockEditMemberProfileAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 *
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
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_member $member
	 * @return String
	 */
	public function executeSave($request, $response, privatemessaging_persistentdocument_member $member)
	{
		$member->save();

		$this->addMessage(LocaleService::getInstance()->transFO('m.users.frontoffice.informations-updated', array('ucf')));
		
		return website_BlockView::INPUT;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_member $member
	 */
	public function validateSaveInput($request, $member)
	{
		$val = BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_member', null, array('user'));
		return $this->processValidationRules($val, $request, $member);
	}
}