<?php
/**
 * privatemessaging_MemberService
 * @package modules.privatemessaging
 */
class privatemessaging_MemberService extends f_persistentdocument_DocumentService
{
	/**
	 * @var privatemessaging_MemberService
	 */
	private static $instance;

	/**
	 * @return privatemessaging_MemberService
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
	 * @return privatemessaging_persistentdocument_member
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_privatemessaging/member');
	}

	/**
	 * Create a query based on 'modules_privatemessaging/member' model.
	 * Return document that are instance of modules_privatemessaging/member,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->pp->createQuery('modules_privatemessaging/member');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/member' model.
	 * Only documents that are strictly instance of modules_privatemessaging/member
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->pp->createQuery('modules_privatemessaging/member', false);
	}
	
	/**
	 * @return privatemessaging_persistentdocument_member
	 */
	public function getCurrentMember()
	{
		return $this->getByUser(users_UserService::getInstance()->getCurrentFrontEndUser());
	}
	
	/**
	 * @param users_persistentdocument_user $user
	 * @param Boolean $createIfNull
	 * @return privatemessaging_persistentdocument_member
	 */
	public function getByUser($user, $createIfNull = true)
	{
		if ($user instanceof users_persistentdocument_user)
		{
			$member = $this->createQuery()->add(Restrictions::eq('user', $user))->findUnique();
			if ($member === null && $createIfNull)
			{
				$member = $this->getNewDocumentInstance();
				$member->setUser($user);
				$member->save();
			}
			return $member;
		}
		return null;
	}
	
	/**
	 * @param string $userName
	 * @param website_persistentdocument_website $website
	 * @return privatemessaging_persistentdocument_member
	 */
	public function getByUserName($userName, $website = null)
	{
		if ($website === null)
		{
			$website = website_WebsiteService::getInstance()->getCurrentWebsite();
		}
		
		$forumMember = forums_MemberService::getInstance()->getByLabel($userName, $website->getId());
		return $forumMember !== null ? $this->getByUser($forumMember->getUser(), true) : null;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_member $member
	 * @param privatemessaging_persistentdocument_post $post
	 */
	public function setPostAsReadForMember($member, $post)
	{
		$postDate = $post->getCreationdate();
		try
		{
			$this->tm->beginTransaction();
			
			$track = $member->getTrackingByThread();
			$thread = $post->getThread();
			$threadId = $thread->getId();
			if (!isset($track[$threadId]) || $track[$threadId] < $postDate)
			{
				$track[$threadId] = $postDate;
			}
			$member->setTrackingByThread($track);
			
			$this->pp->updateDocument($member);
			$this->tm->commit();
		}
		catch (Exception $e)
		{
			$this->tm->rollBack($e);
		}
	}
		
	/**
	 * @param privatemessaging_persistentdocument_member $document
	 * @param Integer $parentNodeId Parent node ID where to save the document.
	 * @return void
	 */
	protected function preInsert($document, $parentNodeId = null)
	{
		parent::preInsert($document, $parentNodeId);
		
		$document->setInsertInTree(false);
	}
	
	/**
	 * @param privatemessaging_persistentdocument_member $member
	 * @param integer $max the maximum number of posts that can treat
	 * @return integer the number of treated posts
	 */
	public function deleteMember($member, $max)
	{
		// Handle members.
		$max -= $this->treatMembersForMemberDeletion($member, $max);
		if ($max < 1)
		{
			return $max;
		}
		
		// Handle posts.
		$max -= privatemessaging_PostService::getInstance()->treatPostsForMemberDeletion($member, $max);
		if ($max < 1)
		{
			return $max;
		}
		
		// Handle threads.
		$max -= privatemessaging_ThreadService::getInstance()->treatThreadsForMemberDeletion($member, $max);
		if ($max < 1)
		{
			return $max;
		}
		
		$member->delete();
		return $max - 1;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_member $member
	 * @param integer $max the maximum number of posts that can treat
	 * @return integer the number of treated posts
	 */
	protected function treatMembersForMemberDeletion($member, $max)
	{
		$count = 0;
		foreach (array('blockedMembers', 'contacts') as $fieldName)
		{
			$query = $this->createQuery();
			$query->add(Restrictions::eq($fieldName, $member));
			$query->setFirstResult(0)->setMaxResults($max - $count);
			$docs = $query->find();
			foreach ($docs as $doc)
			{
				/* @var $doc privatemessaging_persistentdocument_member */
				$post->getDocumentService()->treatMemberForMemberDeletion($doc, $member);
			}
			$count += count($docs);
		}
		if (Framework::isInfoEnabled())
		{
			Framework::info(__METHOD__ . ' ' . $count . ' members treated');
		}
		return $count;
	}
	
	/**
	 * @param privatemessaging_persistentdocument_member $doc
	 * @param privatemessaging_persistentdocument_member $member
	 */	
	protected function treatMemberForMemberDeletion($doc, $member)
	{
		$doc->removeBlockedMembers($member);
		$doc->removeContacts($member);
		$doc->save();
	}
}