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
	 * @return string
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}
		
		$user = users_UserService::getInstance()->getCurrentUser();
		$receiver = $this->getDocumentParameter();
		$request->setAttribute('receiver', $receiver);	
		return $this->getInputViewName();
	}
	
	/**
	 * @return string
	 */
	public function getInputViewName()
	{
		return website_BlockView::SUCCESS;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return boolean
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
	 * @return string
	 */
	public function executeSubmit($request, $response, privatemessaging_persistentdocument_thread $thread)
	{
		$post = $thread->getFirstPost();
		$thread->setFirstPost(null);
		$thread->save();
		
		$post->save($thread->getId());
		$post->getDocumentService()->activate($post->getId());
		
		$url = LinkHelper::getDocumentUrl($thread);
		change_Controller::getInstance()->redirectToUrl($url);
	}

	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return boolean
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
	 * @return string
	 */
	public function executePreview($request, $response, privatemessaging_persistentdocument_thread $thread)
	{
		$post = $thread->getFirstPost();
		$post->setThread($thread);
		$post->setAuthorid(users_UserService::getInstance()->getCurrentUser());
		$post->setCreationdate(date_Calendar::getInstance()->toString());
		$request->setAttribute('thread', $thread);
		
		$postListInfo = array();
		$postListInfo['displayConfig'] = $this->getDisplayConfig();
		$postListInfo['displayConfig']['hidePostLink'] = true;
		$paginator = new paginator_Paginator('privatemessaging', 1, array($post), 1);
		$paginator->setItemCount(1);
		$postListInfo['paginator'] = $paginator;
		$request->setAttribute('previewPostInfo', $postListInfo);
		
		return $this->getInputViewName();
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return boolean
	 */
	private function validateThread($request, $thread)
	{
		$rules = array_merge(BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_thread', null, null), BeanUtils::getSubBeanValidationRules('privatemessaging_persistentdocument_thread', 'firstPost', null, array('label', 'thread')));
		$ok = $this->processValidationRules($rules, $request, $thread);
		
		$website = website_WebsiteService::getInstance()->getCurrentWebsite();
		$usernames = explode(',', $request->getParameter('receivers'));
		foreach ($usernames as $username)
		{
			$user = users_UserService::getInstance()->getPublishedByLabel(trim($username), $website->getGroup()->getId());
			if ($user === null)
			{
				$ok = false;
				$this->addError(LocaleService::getInstance()->trans('m.privatemessaging.fo.unknown-username', array('ucf'), array('username' => $username)));
			}
			else 
			{
				$thread->addFollowers($user);
			}
		}
		return $ok;
	}
}