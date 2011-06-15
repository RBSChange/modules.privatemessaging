<?php
/**
 * privatemessaging_BlockNewthreadAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockNewpostAction extends privatemessaging_BlockPostListBaseAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_post $post
	 * @return String
	 */
	public function execute($request, $response, privatemessaging_persistentdocument_post $post)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}

		$thread = $this->getDocumentParameter();
		if ($thread->isWriteable())
		{
			if ($request->getParameter('quote') == 'true' && !$request->getParameter('text') && $request->getParameter('postid'))
			{
				$quotedPost = privatemessaging_persistentdocument_post::getInstanceById($request->getParameter('postid'));
				$post->setText('[quote="' . $quotedPost->getAuthorNameAsHtml() . '"]' . $quotedPost->getText() . '[/quote]');
				$request->setAttribute('post', $post);
			}
			
			$this->setRequestAttributes($request);
			return $this->getInputViewName();
		}
		
		$agaviUser = Controller::getInstance()->getContext()->getUser();
		$agaviUser->setAttribute('illegalAccessPage', $_SERVER["REQUEST_URI"]);
		$request->setAttribute('member', forums_MemberService::getInstance()->getCurrentMember());
		return $this->getTemplateByFullName('modules_forums', 'Forums-Block-Generic-Forbidden');
	}

	/**
	 * @param f_mvc_Request $request
	 */
	private function setRequestAttributes($request)
	{
		$thread = $this->getDocumentParameter();
		$request->setAttribute('thread', $thread);
		$answerId = $request->getParameter('postid');
		if ($answerId !== null)
		{
			$answerTo = DocumentHelper::getDocumentInstance($answerId);
			$request->setAttribute('answerof', $answerTo);
			$postListInfo = array();
			$postListInfo['displayConfig'] = $this->getDisplayConfig();
			$postListInfo['paginator'] = array($answerTo);
			$request->setAttribute('answerListInfo', $postListInfo);
		}
		else 
		{
			$posts = privatemessaging_ThreadService::getInstance()->getPosts($thread, 0, $this->getNbItemPerPage(), 'desc');
			$postListInfo = array();
			$postListInfo['displayConfig'] = $this->getDisplayConfig();
			$postListInfo['paginator'] = $posts;
			$request->setAttribute('lastPostListInfo', $postListInfo);
		}
	}
	
	/**
	 * @return String
	 */
	public function getInputViewName()
	{
		return website_BlockView::SUCCESS;
	}

	/**
	 * @return Array
	 */
	public function getSubmitInputValidationRules()
	{
		return BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_post', null, array('label', 'thread'));
	}

	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executeSubmit($request, $response, privatemessaging_persistentdocument_post $post)
	{
		if	($post->getAnswerof() !== null && $post->getAnswerof()->getThread()->getId() != $post->getThread()->getId())
		{
			$post->setAnswerof(null);
		}
		$post->save();
		$post->getDocumentService()->activate($post->getId());
						
		HttpController::getInstance()->redirectToUrl(LinkHelper::getDocumentUrl($post));
	}
	
	/**
	 * @return Array
	 */
	public function getPreviewInputValidationRules()
	{
		return BeanUtils::getBeanValidationRules('privatemessaging_persistentdocument_post', null, array('label', 'thread'));
	}
	
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	public function executePreview($request, $response, privatemessaging_persistentdocument_post $post)
	{
		if	($post->getAnswerof() !== null && $post->getAnswerof()->getThread()->getId() != $post->getThread()->getId())
		{
			$post->setAnswerof(null);
		}
		$post->setText(website_BBCodeService::getInstance()->fixContent($post->getText()));
		$post->setPostauthor(forums_MemberService::getInstance()->getCurrentMember());
		$post->setCreationdate(date_Calendar::getInstance()->toString());
		$request->setAttribute('post', $post);
		
		$postListInfo = array();
		$postListInfo['displayConfig'] = $this->getDisplayConfig();
		$postListInfo['displayConfig']['hidePostLink'] = true;
		$postListInfo['paginator'] = array($post);
		$request->setAttribute('previewPostInfo', $postListInfo);
		
		$this->setRequestAttributes($request);
		
		return $this->getInputViewName();
	}
}