<?php
/**
 * @package modules.privatemessaging
 * @method privatemessaging_ThreadService getInstance()
 */
class privatemessaging_ThreadService extends f_persistentdocument_DocumentService
{
	/**
	 * @return privatemessaging_persistentdocument_thread
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_privatemessaging/thread');
	}

	/**
	 * Create a query based on 'modules_privatemessaging/thread' model.
	 * Return document that are instance of modules_privatemessaging/thread,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/thread');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/thread' model.
	 * Only documents that are strictly instance of modules_privatemessaging/thread
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/thread', false);
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @param integer $start
	 * @param integer $limit
	 * @return privatemessaging_persistentdocument_post[]
	 */
	public function getPosts($thread, $start = null, $limit = 20, $order = 'asc')
	{
		$query = privatemessaging_PostService::getInstance()->createQuery()->add(Restrictions::published());
		$query->add(Restrictions::eq('thread', $thread->getId()));
		if ($start !== null)
		{
			$query->add(Restrictions::ge('number', $start));
		}
		$query->setMaxResults($limit);
		if ($order == 'desc')
		{
			$query->addOrder(Order::desc('number'));
		}
		else
		{
			$query->addOrder(Order::asc('number'));
		}
		return $query->find();
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return privatemessaging_persistentdocument_post
	 */
	public function getLastPost($thread)
	{
		$query = privatemessaging_PostService::getInstance()->createQuery()->add(Restrictions::eq('thread', $thread))
			->addOrder(Order::desc('document_creationdate'))
			->setFirstResult(0)->setMaxResults(1);
		return f_util_ArrayUtils::firstElement($query->find());
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @param string $date
	 * @return privatemessaging_persistentdocument_post
	 */
	public function getFirstUnreadPost($thread, $date)
	{
		$query = privatemessaging_PostService::getInstance()->createQuery()->add(Restrictions::eq('thread', $thread))
			->add(Restrictions::gt('creationdate', $date))
			->addOrder(Order::asc('document_creationdate'))
			->setFirstResult(0)->setMaxResults(1);
		return f_util_ArrayUtils::firstElement($query->find());
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @return string
	 */
	public function getUserUrl($thread)
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		$profile = ($user) ? privatemessaging_PrivatemessagingprofileService::getInstance()->getByAccessorId($user->getId()) : null;
		if ($profile)
		{
			$date = $profile->getLastReadDateByThreadId($thread->getId());
			if ($date !== null)
			{
				$post = $this->getFirstUnreadPost($thread, $date);
				if ($post !== null)
				{
					return LinkHelper::getDocumentUrl($post);
				}
			}
		}
		return LinkHelper::getDocumentUrl($thread);
	}
	
	/**
	 * @param users_persistentdocument_user $user
	 * @return privatemessaging_persistentdocument_thread[]
	 */
	public function getByUser($user)
	{
		return privatemessaging_ThreadService::getInstance()->createQuery()->add(Restrictions::published())
			->add(Restrictions::eq('followers', $user))
			->addOrder(Order::desc('lastpostdate'))->find();
	}
	
	/**
	 * @param users_persistentdocument_user[] $users
	 * @return privatemessaging_persistentdocument_thread[]
	 */
	public function getByUsers($users)
	{
		if (count($users) < 1)
		{
			return array();
		}
		$query = privatemessaging_ThreadService::getInstance()->createQuery()->add(Restrictions::published());
		foreach ($users as $user)
		{
			$query->add(Restrictions::eq('followers', $user));
		}
		return $query->addOrder(Order::desc('lastpostdate'))->find();
	}

	/**
	 * @param privatemessaging_persistentdocument_thread $document
	 * @param integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
				
		$document->setInsertInTree(false);
		
		$user = users_UserService::getInstance()->getCurrentUser();
		if ($user !== null)
		{
			$document->addFollowers($user);
		}
	}

	/**
	 * @param privatemessaging_persistentdocument_thread $document
	 * @return integer
	 */
	public function getWebsiteId($document)
	{
		return website_WebsiteService::getInstance()->getCurrentWebsite()->getId();
	}
		
	/**
	 * @param users_persistentdocument_user $user
	 * @param integer $max the maximum number of threads that can treat
	 * @return integer the number of treated threads
	 */	
	public function treatThreadsForUserDeletion($user, $max)
	{
		$query = $this->createQuery();
		$query->add(Restrictions::eq('authorid', $user->getId()));
		$query->setFirstResult(0)->setMaxResults($max);
		$threads = $query->find();
		foreach ($threads as $thread)
		{
			/* @var $thread privatemessaging_persistentdocument_thread */
			$thread->removeFollowers($user);		
			$thread->save();
		}
		$count = count($threads);
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . ' ' . $count . ' threads treated');
		}
		return $count;
	}
	
	// TODO: delete if no follower.
}