<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_member
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_member extends privatemessaging_persistentdocument_memberbase
{
	/**
	 * @return forums_persistentdocument_member
	 */
	public function getForumMember()
	{
		if (ModuleService::getInstance()->moduleExists('forums'))
		{
			return forums_MemberService::getInstance()->getByUser($this->getUser());
		}
		return null;
	}
	
	/**
	 * @return String
	 */
	public function getLabel()
	{
		$forumMember = $this->getForumMember();
		if ($forumMember !== null)
		{
			return $forumMember->getLabel();
		}
		return $this->getUser()->getLogin();
	}

	/**
	 * @return String
	 */
	public function getLabelAsHtml()
	{
		$forumMember = $this->getForumMember();
		if ($forumMember !== null)
		{
			return $forumMember->getLabelAsHtml();
		}
		return $this->getUser()->getLoginAsHtml();
	}

	/**
	 * @return String
	 */
	public function getMemberLink()
	{
		$forumMember = $this->getForumMember();
		if ($forumMember !== null)
		{
			return '<a href="'.LinkHelper::getDocumentUrl($forumMember).'" class="link">'.$forumMember->getLabelAsHtml().'</a>';
		}
		return $this->getUser()->getLoginAsHtml();
	}

	/**
	 * @return Boolean
	 */
	public function isme()
	{
		$current = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($current !== null)
		{
			return $this->getId() == $current->getId();
		}
		return false;
	}

	/**
	 * @param Integer $size
	 * @param String $defaultImageUrl
	 * @param String $rating
	 * @return String
	 */
	public function getGravatarUrl($size = '32', $defaultImageUrl = '', $rating = 'g')
	{
		$url = 'http://www.gravatar.com/avatar/' . md5($this->getEmail()) . '?s=' . $size . '&amp;r=' . $rating;
		if ($defaultImageUrl)
		{
			$url .= '&amp;d=' . urlencode($defaultImageUrl);
		}
		return $url;
	}

	/**
	 * @return String
	 */
	public function getEmail()
	{
		return $this->getUser()->getEmail();
	}

	/**
	 * @return Array<threadId: Integer, lastReadDate: String>
	 */
	public function getTrackingByThread()
	{
		$data = parent::getTrackingByThread();
		return $data !== null ? unserialize($data) : array();
	}

	/**
	 * @param Array<threadId: Integer, lastReadDate: String> $data
	 */
	public function setTrackingByThread($data)
	{
		if (is_array($data) && f_util_ArrayUtils::isNotEmpty($data))
		{
			parent::setTrackingByThread(serialize($data));
		}
		else
		{
			parent::setTrackingByThread(null);
		}
	}

	/**
	 * @param Integer $threadId
	 */
	public function getLastReadDateByThreadId($threadId)
	{
		$track = $this->getTrackingByThread();
		return (isset($track[$threadId])) ? $track[$threadId] : null;
	}
}