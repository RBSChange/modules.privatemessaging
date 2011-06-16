<?php
/**
 * privatemessaging_PostService
 * @package modules.privatemessaging
 */
class privatemessaging_PostService extends f_persistentdocument_DocumentService
{
	/**
	 * @var privatemessaging_PostService
	 */
	private static $instance;

	/**
	 * @return privatemessaging_PostService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}

	/**
	 * @return privatemessaging_persistentdocument_post
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_privatemessaging/post');
	}

	/**
	 * Create a query based on 'modules_privatemessaging/post' model.
	 * Return document that are instance of modules_privatemessaging/post,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_privatemessaging/post');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/post' model.
	 * Only documents that are strictly instance of modules_privatemessaging/post
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_privatemessaging/post', false);
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		
		$document->setInsertInTree(false);
		
		$document->setMeta('author.ip', RequestContext::getInstance()->getClientIp());
		if ($document->getPostauthor() === null)
		{
			$document->setPostauthor(privatemessaging_MemberService::getInstance()->getCurrentMember());
		}
		
		if ($parentNodeId !== null && $document->getThread() === null)
		{
			$parent = DocumentHelper::getDocumentInstance($parentNodeId);
			if ($parent instanceof forums_persistentdocument_thread)
			{
				$document->setThread($parent);
			}
		}
						
		if ($document->getLabel() === null)
		{
			$replacements = array(
				'author' => ($document->getPostauthor() !== null) ? $document->getPostauthor()->getLabel() : '[...]'
			);
			$document->setLabel(LocaleService::getInstance()->transFO('m.privatemessaging.document.post.label-patern', $replacements));
		}
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preSave($document, $parentNodeId = null)
	{
		parent::preSave($document, $parentNodeId);
		
		if ($document->isPropertyModified('text'))
		{
			$document->setText(website_BBCodeService::getInstance()->fixContent($document->getText()));
		}
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function postInsert($document, $parentNodeId = null)
	{
		parent::postInsert($document, $parentNodeId);
		
		$thread = $document->getThread();
		if ($thread->getFirstPost() === null)
		{
			$thread->setFirstPost($document);
		}

		$document->setNumber($thread->getNbpost() + 1);
		$this->pp->updateDocument($document);
		
		$thread->setNbpost($thread->getNbpost() + 1);
		$thread->setModificationdate(date_Calendar::now()->toString());
		if ($thread->getTofollow() === null && $document->getNumber() > 1)
		{
			$thread->setTofollow($document);
		}
		$thread->setLastPostDate($document->getCreationdate());
		
		$this->pp->updateDocument($thread);
		
		$ns = notification_NotificationService::getInstance();
		$notif = $ns->getNotificationByCodeName('modules_privatemessaging/newprivatemessage');
		$followers = $thread->getFollowersArray();
		foreach ($followers as $follower)
		{
			if (!DocumentHelper::equals($follower, $document->getPostauthor()) && $follower->getNotifyNewMessages())
			{
				$forumMember = forums_MemberService::getInstance()->getByUser($follower->getUser());
				$ms = $forumMember->getDocumentService();
				$notif = $ns->getConfiguredByCodeName('modules_privatemessaging/newprivatemessage', $ms->getWebsiteId($forumMember), $forumMember->getLang());
				if ($notif instanceof notification_persistentdocument_notification)
				{
					$user = $forumMember->getUser();
					$callback = array($this, 'getNotificationParameters');
					$callbackParams = array('post' => $document, 'member' => $forumMember);
					$user->getDocumentService()->sendNotificationToUserCallback($notif, $user, $callback, $callbackParams);
				}
			}
		}
	}
	
	/**
	 * @param array $params
	 * @return array
	 */
	public function getNotificationParameters($params)
	{
		$parameters = array();
		
		$post = $params['post'];		
		$parameters['trheadLabel'] = $post->getThread()->getLabelAsHtml();
		$parameters['postUrl'] = $post->getPostUrlInThread();
		$authorForumMember = forums_MemberService::getInstance()->getByUser($post->getPostauthor()->getUser());
		$parameters['postAuthor'] = $authorForumMember->getLabelAsHtml();
		
		if (isset($params['member']) && $params['member'] instanceof forums_persistentdocument_member)
		{
			$member = $params['member'];
			$parameters['receiverPseudonym'] = $member->getLabelAsHtml();
		}
		
		if (isset($params['specificParams']) && is_array($params['specificParams']))
		{
			$parameters = array_merge($parameters, $params['specificParams']);
		}
		return $parameters;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @return String
	 */
	public function generateUrl($document)
	{
		return LinkHelper::getDocumentUrl($document->getThread(), null, array('privatemessagingParam[postId]' => $document->getId())) . "#post-" . $document->getId();
	}
}