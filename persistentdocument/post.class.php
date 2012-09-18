<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_post
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_post extends privatemessaging_persistentdocument_postbase 
{
	/**
	 * @return string
	 */
	public function getAuthorName()
	{
		$user = $this->getAuthoridInstance();
		if ($user instanceof users_persistentdocument_user)
		{
			return $user->getLabel();
		}
		return LocaleService::getInstance()->trans('m.privatemessaging.fo.unknown', array('ucf'));
	}
	
	/**
	 * @return string
	 */
	public function getAuthorNameAsHtml()
	{
		$user = $this->getAuthoridInstance();
		if ($user instanceof users_persistentdocument_user)
		{
			return $user->getLabelAsHtml();
		}
		return LocaleService::getInstance()->trans('m.privatemessaging.fo.unknown', array('ucf'));
	}
	
	/**
	 * @return privatemessaging_persistentdocument_privatemessagingprofile
	 */
	public function getAuthorProfile()
	{
		$user = $this->getAuthoridInstance();
		if ($user instanceof users_persistentdocument_user)
		{
			return $user->getProfile('privatemessaging');
		}
		return null;
	}
	
	/**
	 * @return forums_persistentdocument_forumsprofile
	 */
	public function getAuthorForumsProfile()
	{
		if (ModuleService::getInstance()->moduleExists('forums'))
		{
			$user = $this->getAuthoridInstance();
			if ($user instanceof users_persistentdocument_user)
			{
				return $user->getProfile('forums');
			}
		}
		return null;
	}
	
	/**
	 * @return boolean
	 */
	public function isAnswer()
	{
		return ($this->getAnswerof() !== null);
	}
	
	/**
	 * @return string
	 */
	public function getThreadLabel()
	{
		return $this->getThread()->getLabel();
	}
	
	/**
	 * @return string
	 */
	public function getThreadLabelAsHtml()
	{
		return $this->getThread()->getLabelAsHtml();
	}
	
	/**
	 * @return string
	 */
	public function getPostId()
	{
		return $this->getThread()->getId() . '.' . $this->getNumber();
	}
	
	/**
	 * @return string
	 */
	public function getAnchor()
	{
		return 'post-'.$this->getId();
	}
}