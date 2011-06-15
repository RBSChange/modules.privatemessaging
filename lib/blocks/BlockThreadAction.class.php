<?php
/**
 * privatemessaging_BlockThreadAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockThreadAction extends privatemessaging_BlockPostListBaseAction
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
			Controller::getInstance()->redirect('website', 'Error500');
		}
				
		$thread = $this->getDocumentParameter();
		if ($thread === null || !($thread instanceof privatemessaging_persistentdocument_thread) || !in_array($member, $thread->getFollowersArray()))
		{
			$this->redirect('privatemessaging', 'Threadlist');
		}
		$request->setAttribute('thread', $thread);
				
		if ($request->hasNonEmptyParameter('unfollow'))
		{
			if ($request->hasNonEmptyParameter('confirmUnfollow'))
			{
				$thread->removeFollowers(privatemessaging_MemberService::getInstance()->getCurrentMember());
				$thread->save();
				$this->redirect('privatemessaging', 'Threadlist');
			}
			return 'ConfirmUnfollow';
		}
		else if ($request->hasNonEmptyParameter('addFollower'))
		{
			return 'AddFollower';
		}

		$nbItemPerPage = 10; // TODO: parametrize.
		$page = 1;
		if ($request->hasParameter('page'))
		{
			$page = $request->getParameter('page');
		}
		else if ($request->hasParameter('postId'))
		{
			$page = ceil(DocumentHelper::getDocumentInstance($request->getParameter('postId'))->getNumber() / $nbItemPerPage);		
		}
		if (!is_numeric($page) || $page < 1 || $page > ceil($thread->getNbpost() / $nbItemPerPage))
		{
			$page = 1;
		}
		$posts = privatemessaging_ThreadService::getInstance()->getPosts($thread, ($nbItemPerPage * ($page - 1)) + 1, $nbItemPerPage);
		$paginator = new paginator_Paginator('privatemessaging', $page, $posts, $nbItemPerPage);
		$paginator->setItemCount($thread->getNbpost());
				
		if (count($posts) > 0)
		{
			$member->getDocumentService()->setPostAsReadForMember($member, f_util_ArrayUtils::lastElement($posts));
		}
		
		$request->setAttribute('unfollowUrl', LinkHelper::getDocumentUrl($thread, null, array('privatemessagingParam[page]' => $page, 'privatemessagingParam[unfollow]' => 1)));
		
		// Post list info.
		$postListInfo = array();
		$postListInfo['displayConfig'] = $this->getDisplayConfig();
		$postListInfo['paginator'] = $paginator;
		$request->setAttribute('postListInfo', $postListInfo);
				
		return website_BlockView::SUCCESS;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return String
	 */
	function executeAddFollower($request, $response, privatemessaging_persistentdocument_thread $thread)
	{
		$usernames = explode(',', $request->getParameter('receivers'));
		$ok = true;
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
		if ($ok)
		{
			$thread->save();
			$this->redirect('privatemessaging', 'Threadlist');
		}
		return 'AddFollower';
	}
}