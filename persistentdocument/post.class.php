<?php
/**
 * Class where to put your custom methods for document privatemessaging_persistentdocument_post
 * @package modules.privatemessaging.persistentdocument
 */
class privatemessaging_persistentdocument_post extends privatemessaging_persistentdocument_postbase 
{
	/**
	 * @return Boolean
	 */
	public function isEditable()
	{
		// TODO: editable if unread.
		return false;
	}
	
	/**
	 * @return String
	 */
	public function getAuthorName()
	{
		$member = $this->getPostauthor();
		if ($member instanceof privatemessaging_persistentdocument_member)
		{
			return $member->getLabel();
		}
		return LocaleService::getInstance()->transFO('m.privatemessaging.fo.unknown', array('ucf'));
	}
	
	/**
	 * @return String
	 */
	public function getAuthorNameAsHtml()
	{
		$member = $this->getPostauthor();
		if ($member instanceof privatemessaging_persistentdocument_member)
		{
			return $member->getLabelAsHtml();
		}
		return LocaleService::getInstance()->transFO('m.privatemessaging.fo.unknown', array('ucf'));
	}
	
	/**
	 * @return String
	 */
	public function getTextAsHtml()
	{
		$parser = new website_BBCodeParser();
		return $parser->convertXmlToHtml($this->getText());
	}

	/**
	 * @return string
	 */
	public function getTextAsBBCode()
	{
		$parser = new website_BBCodeParser();
		return $parser->convertXmlToBBCode($this->getText());
	}

	/**
	 * @param string $bbcode
	 */
	public function setTextAsBBCode($bbcode)
	{
		$parser = new website_BBCodeParser();
		$this->setText($parser->convertBBCodeToXml($bbcode, $parser->getModuleProfile('privatemessaging')));
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
	
	/**
	 * @return String
	 */
	public function getPostUrlInThread()
	{
		$postPerPage = 10; // TODO
		$thread = $this->getThread();
		if ($thread instanceof privatemessaging_persistentdocument_thread)
		{
			$pageNumber = $this->getPageNumberInThread($postPerPage);
			$parameters = array();
			if ($pageNumber > 0)
			{
				$parameters = array('forumsParam[page]' => $pageNumber);
			}
			$link = LinkHelper::getDocumentUrl($thread, null, $parameters) . "#post-" . $this->getId();
			return $link;
		}
		return null;
	}
	
	/**
	 * @return String
	 */
	public function getPostIdLink($strong = false)
	{
		$link = '<a class="link" href="' . LinkHelper::getDocumentUrl($this) . '">';
		if ($strong == true)
		{
			$link .= '<strong>';
		}
		$link .= $this->getPostId();
		if ($strong == true)
		{
			$link .= '</strong>';
		}
		$link .= '</a>';
		return $link;
	}
	
	/**
	 * @param Integer $postPerPage
	 * @return Integer
	 */
	public function getPageNumberInThread($postPerPage)
	{
		return ceil($this->getNumber() / $postPerPage);
	}
	
	/**
	 * @return string
	 */
	public function getDeleteddate()
	{
		return null;
	}
}