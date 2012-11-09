<?php
/**
 * @package modules.privatemessaging
 * @method privatemessaging_PostService getInstance()
 */
class privatemessaging_PostService extends f_persistentdocument_DocumentService
{
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
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/post');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/post' model.
	 * Only documents that are strictly instance of modules_privatemessaging/post
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/post', false);
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		
		$document->setInsertInTree(false);
		
		$document->setMeta('author.ip', RequestContext::getInstance()->getClientIp());
		
		if ($parentNodeId !== null && $document->getThread() === null)
		{
			$parent = DocumentHelper::getDocumentInstance($parentNodeId);
			if ($parent instanceof privatemessaging_persistentdocument_thread)
			{
				$document->setThread($parent);
			}
		}

		if ($document->getLabel() === null)
		{
			$author = $document->getAuthoridInstance();
			if (!$author)
			{
				$author = users_UserService::getInstance()->getCurrentUser();
			}
			$replacements = array(
				'author' => ($author !== null) ? $author->getLabel() : '[...]'
			);
			$rc = RequestContext::getInstance();
			$document->setLabel($ls->formatKey($rc->getLang(), 'm.privatemessaging.document.post.label-patern', array('ucf'), $replacements));
		}
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
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
		$this->getPersistentProvider()->updateDocument($document);
		
		$thread->setNbpost($thread->getNbpost() + 1);
		$thread->setModificationdate(date_Calendar::now()->toString());
		$thread->setLastPostDate($document->getCreationdate());
		
		$this->getPersistentProvider()->updateDocument($thread);
		
		$ns = notification_NotificationService::getInstance();
		$notif = $ns->getNotificationByCodeName('modules_privatemessaging/newprivatemessage');
		$followers = $thread->getFollowersArray();
		$website = website_WebsiteService::getInstance()->getCurrentWebsite();
		foreach ($followers as $follower)
		{
			if ($follower->getId() === $document->getAuthorid())
			{
				continue;
			}
			
			$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($follower->getId(), true);
			if ($profile->getNotifyNewMessages())
			{
				$notif = $ns->getConfiguredByCodeName('modules_privatemessaging/newprivatemessage');
				if ($notif instanceof notification_persistentdocument_notification)
				{
					$callback = array($this, 'getNotificationParameters');
					$callbackParams = array('post' => $document, 'user' => $follower);
					$follower->getDocumentService()->sendNotificationToUserCallback($notif, $follower, $callback, $callbackParams);
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
		$parameters['threadLabel'] = $post->getThread()->getLabelAsHtml();
		$parameters['postUrl'] = LinkHelper::getDocumentUrl($post);
		$parameters['postAuthor'] = $post->getAuthoridInstance()->getLabelAsHtml();
		
		if (isset($params['user']) && $params['user'] instanceof users_persistentdocument_user)
		{
			$user = $params['user'];
			$parameters['receiverPseudonym'] = $user->getLabelAsHtml();
		}
		
		if (isset($params['specificParams']) && is_array($params['specificParams']))
		{
			$parameters = array_merge($parameters, $params['specificParams']);
		}
		return $parameters;
	}

	/**
	 * @param website_UrlRewritingService $urlRewritingService
	 * @param privatemessaging_persistentdocument_post $document
	 * @param website_persistentdocument_website $website
	 * @param string $lang
	 * @param array $parameters
	 * @return f_web_Link | null
	 */
	public function getWebLink($urlRewritingService, $document, $website, $lang, $parameters)
	{
		$parameters['postId'] = $document->getId();
		$link = $urlRewritingService->getDocumentLinkForWebsite($document->getThread(), $website, $lang, $parameters);
		if ($link) {
			$link->setFragment($document->getAnchor());
		}
		return $link;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_post $post
	 * @param users_persistentdocument_user $user
	 */
	public function setAsReadForUser($post, $user)
	{
		$postDate = $post->getCreationdate();
		try
		{
			$this->getTransactionManager()->beginTransaction();
			
			$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($user->getId(), true);
			$track = $profile->getDecodedTrackingByThread();
			if (is_array($track))
			{
				$threadId = $post->getThread()->getId();
				if (!isset($track[$threadId]) || $track[$threadId] < $postDate)
				{
					$track[$threadId] = $postDate;
				}
				$profile->setTrackingByThread($track);
			}
			
			$this->getPersistentProvider()->updateDocument($profile);
			$this->getTransactionManager()->commit();
		}
		catch (Exception $e)
		{
			$this->getTransactionManager()->rollBack($e);
		}
	}
}