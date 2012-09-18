<?php
/**
 * @package modules.privatemessaging
 * @method privatemessaging_PrivatemessagingprofileService getInstance()
 */
class privatemessaging_PrivatemessagingprofileService extends users_ProfileService
{
	/**
	 * @return privatemessaging_persistentdocument_privatemessagingprofile
	 */
	public function getNewDocumentInstance()
	{
		return $this->getNewDocumentInstanceByModelName('modules_privatemessaging/privatemessagingprofile');
	}

	/**
	 * Create a query based on 'modules_privatemessaging/privatemessagingprofile' model.
	 * Return document that are instance of privatemessaging_persistentdocument_privatemessagingprofile,
	 * including potential children.
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/privatemessagingprofile');
	}
	
	/**
	 * Create a query based on 'modules_privatemessaging/privatemessagingprofile' model.
	 * Only documents that are strictly instance of privatemessaging_persistentdocument_privatemessagingprofile
	 * (not children) will be retrieved
	 * @return f_persistentdocument_criteria_Query
	 */
	public function createStrictQuery()
	{
		return $this->getPersistentProvider()->createQuery('modules_privatemessaging/privatemessagingprofile', false);
	}
	
	/**
	 * @param integer $accessorId
	 * @param boolean $required
	 * @return privatemessaging_persistentdocument_privatemessagingprofile || null
	 */
	public function getByAccessorId($accessorId, $required = false)
	{
		return parent::getByAccessorId($accessorId, $required);
	}
	
	/**
	 * @return privatemessaging_persistentdocument_privatemessagingprofile
	 */
	public function getCurrent()
	{
		return parent::getCurrent();
	}
	
	/**
	 * @param privatemessaging_persistentdocument_privatemessagingprofile $document
	 * @param string[] $propertiesName
	 * @param array $datas
	 * @param integer $accessorId
	 */
	public function addFormProperties($document, $propertiesName, &$datas, $accessorId = null)
	{
		if ($document->isNew()) {$datas['id'] = 0;}
		$datas['notifyNewMessages'] = $document->getNotifyNewMessages();
		$datas['viewAvatars'] = $document->getViewAvatars();
		$datas['viewSignatures'] = $document->getViewSignatures();
	}
}