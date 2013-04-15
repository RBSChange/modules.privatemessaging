<?php
/**
 * privatemessaging_BlockNewthreadAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockNewthreadAction extends privatemessaging_BlockPostListBaseAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}
		
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		$receiver = $this->getDocumentParameter();
		$request->setAttribute('receiver', $receiver);
		if ($receiver === null || !in_array($receiver, $member->getBlockedMembers()))
		{
			return $this->getInputViewName();
		}		
		return website_BlockView::ERROR;
	}
	
	/**
	 * @return String
	 */
	public function getInputViewName()
	{
		return website_BlockView::SUCCESS;
	}

	/**
	 * @return string[]
	 */
	public function getThreadBeanInclude()
	{
		if (Framework::getConfigurationValue('modules/website/useBeanPopulateStrictMode') != 'false')
		{
			return array('receivers', 'label', 'firstPost.textAsBBCode');
		}
		return null;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return Boolean
	 */
	public function validateSubmitInput($request, privatemessaging_persistentdocument_thread $thread)
	{
		return $this->validateThread($request, $thread);
	}

	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return String
	 */
	public function executeSubmit($request, $response, privatemessaging_persistentdocument_thread $thread)
	{
		$post = $thread->getFirstPost();
		$thread->setFirstPost(null);
		$thread->save();
		
		$post->save($thread->getId());
		$post->getDocumentService()->activate($post->getId());
		
		$url = LinkHelper::getDocumentUrl($thread);
		HttpController::getInstance()->redirectToUrl($url);
	}

	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return Boolean
	 */
	public function validatePreviewInput($request, privatemessaging_persistentdocument_thread $thread)
	{
		return $this->validateThread($request, $thread);
	}
	
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return String
	 */
	public function executePreview($request, $response, privatemessaging_persistentdocument_thread $thread)
	{
		$post = $thread->getFirstPost();
		$post->setThread($thread);
		$post->setPostauthor(privatemessaging_MemberService::getInstance()->getCurrentMember());
		$post->setCreationdate(date_Calendar::getInstance()->toString());
		$request->setAttribute('thread', $thread);
		
		$postListInfo = array();
		$postListInfo['displayConfig'] = $this->getDisplayConfig();
		$postListInfo['displayConfig']['hidePostLink'] = true;
		$postListInfo['paginator'] = array($post);
		$request->setAttribute('previewPostInfo', $postListInfo);
		
		return $this->getInputViewName();
	}

	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return Boolean
	 */
	private function validateThread($request, $thread)
	{
		$rules = array_merge(BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_thread', null, null), BeanUtils::getSubBeanValidationRules('privatemessaging_persistentdocument_thread', 'firstPost', null, array('label', 'thread')));
		$ok = $this->processValidationRules($rules, $request, $thread);
		
		$usernames = explode(',', $request->getParameter('receivers'));
		foreach ($usernames as $username)
		{
			$member = privatemessaging_MemberService::getInstance()->getByUserName(trim($username));
			if ($member === null)
			{
				$ok = false;
				$this->addError(LocaleService::getInstance()->transFO('m.privatemessaging.fo.unknown-username', array('ucf'), array('username' => $username)));
			}
			else 
			{
				$thread->addFollowers($member);
			}
		}
		return $ok;
	}
}