<?php
/**
 * privatemessaging_PrivatemessagingprofileScriptDocumentElement
 * @package modules.privatemessaging.persistentdocument.import
 */
class privatemessaging_PrivatemessagingprofileScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return privatemessaging_persistentdocument_privatemessagingprofile
	 */
	protected function initPersistentDocument()
	{
		return privatemessaging_PrivatemessagingprofileService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return privatemessaging_persistentdocument_privatemessagingprofilemodel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_privatemessaging/privatemessagingprofile');
	}
}