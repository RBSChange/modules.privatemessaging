<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_privatemessagingprofile
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_privatemessagingprofile extends privatemessaging_persistentdocument_privatemessagingprofilebase
{
	/**
	 * @param Integer $threadId
	 */
	public function getLastReadDateByThreadId($threadId)
	{
		$track = $this->getTrackingByThread();
		return (isset($track[$threadId])) ? $track[$threadId] : null;
	}
	
	/**
	 * @return privatemessaging_persistentdocument_thread
	 */
	public function getThreadsWithCurrentUser()
	{
		$users = array($this->getAccessorIdInstance(), users_UserService::getInstance()->getCurrentUser());
		return privatemessaging_ThreadService::getInstance()->getByUsers($users);
	}
}