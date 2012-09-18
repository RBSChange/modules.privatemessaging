<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_thread
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_thread extends privatemessaging_persistentdocument_threadbase 
{
	/**
	 * @return boolean
	 */
	public function isViewable()
	{
		$user = users_UserService::getInstance()->getCurrentUser();
		return in_array($user, $this->getFollowersArray());
	}
	
	/**
	 * @return boolean
	 */
	public function isWriteable()
	{
		return $this->isViewable();
	}
	
	/**
	 * @return string
	 */
	public function getUserUrl()
	{
		return $this->getDocumentService()->getUserUrl($this);
	}
	
	/**
	 * @return integer
	 */
	public function getNbnewpost()
	{
		if (!$this->isViewable())
		{
			return 0;
		}
		
		$user = users_UserService::getInstance()->getCurrentUser();
		$profile = privatemessaging_PrivatemessagingprofileService::getInstance()->getCurrent($user->getId(), true);
		$date = $profile->getLastReadDateByThreadId($this->getId());
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