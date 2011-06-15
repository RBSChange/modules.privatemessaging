<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_thread
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_thread extends privatemessaging_persistentdocument_threadbase 
{
	/**
	 * @return Boolean
	 */
	public function isViewable()
	{
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		return in_array($member, $this->getFollowersArray());
	}
	
	/**
	 * @return Boolean
	 */
	public function isWriteable()
	{
		return $this->isViewable();
	}
	
	/**
	 * @return String
	 */
	public function getUserUrl()
	{
		return $this->getDocumentService()->getUserUrl($this);
	}
	
	/**
	 * @return Integer
	 */
	public function getNbnewpost()
	{
		if (!$this->isViewable())
		{
			return 0;
		}
		
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		$date = $member->getLastReadDateByThreadId($this->getId());
		$query = privatemessaging_PostService::getInstance()->createQuery()
			->add(Restrictions::eq('thread', $this))
			->setProjection(Projections::rowCount('count'))
			->setFetchColumn('count');
		if ($date)
		{
			$query->add(Restrictions::gt('creationdate', $date));
		}
		return f_util_ArrayUtils::firstElement($query->find());
	}
	
	/**
	 * @return privatemessaging_persistentdocument_post
	 */
	public function getLastPost()
	{
		return $this->getDocumentService()->getLastPost($this);
	}
}