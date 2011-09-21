<?php
/**
 * privatemessaging_ThreadService
 * @package modules.privatemessaging
 */
class privatemessaging_ThreadService extends f_persistentdocument_DocumentService
{
	/**
	 * @var privatemessaging_ThreadService
	 */
	private static $instance;

	/**
	 * @return privatemessaging_ThreadService
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

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
		return $this->pp->createQuery('modules_privatemessaging/thread');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/thread' model.
	 * Only documents that are strictly instance of modules_privatemessaging/thread
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_privatemessaging/thread', false);
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @param String $text
	 */
	public function addPost($thread, $text)
	{
		$post = forums_PostService::getInstance()->getNewDocumentInstance();
		$post->setText($text);
		$post->setThread($thread);
		$post->save();
		$post->activate();
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @param Integer $start
	 * @param Integer $limit
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
	 * @param String $date
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
	 * @return String
	 */
	public function getUserUrl($thread)
	{
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($member !== null)
		{
			$date = $member->getLastReadDateByThreadId($thread->getId());
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
	 * @param privatemessaging_persistentdocument_member $forum
	 * @return privatemessaging_persistentdocument_threads[]
	 */
	public function getByMember($member)
	{
		return privatemessaging_ThreadService::getInstance()->createQuery()->add(Restrictions::published())
			->add(Restrictions::eq('followers', $member))
			->addOrder(Order::desc('lastpostdate'))->find();
	}

	/**
	 * @param privatemessaging_persistentdocument_thread $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
				
		$document->setInsertInTree(false);
		
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($member !== null)
		{
			$document->addFollowers($member);
		}
	}

	/**
	 * @param privatemessaging_persistentdocument_thread $document
	 * @return integer
	 */
	public function getWebsiteId($document)
	{
		return website_WebsiteModuleService::getInstance()->getCurrentWebsite()->getId();
	}
		
	/**
	 * @param privatemessaging_persistentdocument_member $member
	 * @param integer $max the maximum number of threads that can treat
	 * @return integer the number of treated threads
	 */	
	public function treatThreadsForMemberDeletion($member, $max)
	{
		$count = 0;
		foreach (array('followers') as $fieldName)
		{
			$query = $this->createQuery();
			$query->add(Restrictions::eq($fieldName, $member));
			$query->setFirstResult(0)->setMaxResults($max - $count);
			$threads = $query->find();
			foreach ($threads as $thread)
			{
				/* @var $thread privatemessaging_persistentdocument_thread */
				$thread->getDocumentService()->treatThreadForMemberDeletion($thread, $member);
			}
			$count += count($threads);
		}
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . ' ' . $count . ' threads treated');
		}
		return $count;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_thread $thread
	 * @param privatemessaging_persistentdocument_member $member
	 */	
	protected function treatThreadForMemberDeletion($thread, $member)
	{
		$thread->removeFollowers($member);		
		$thread->save();
	}
	
	// TODO: delete if no follower.
}