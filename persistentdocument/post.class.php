<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_post
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_post extends privatemessaging_persistentdocument_postbase 
{
	/**
	 * @return String
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
	 * @return String
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
	 * @return Boolean
	 */
	public function isAnswer()
	{
		return ($this->getAnswerof() !== null);
	}
	
	/**
	 * @return String
	 */
	public function getThreadLabel()
	{
		return $this->getThread()->getLabel();
	}
	
	/**
	 * @return String
	 */
	public function getThreadLabelAsHtml()
	{
		return $this->getThread()->getLabelAsHtml();
	}
	
	/**
	 * @return String
	 */
	public function getPostId()
	{
		return $this->getThread()->getId() . '.' . $this->getNumber();
	}
}