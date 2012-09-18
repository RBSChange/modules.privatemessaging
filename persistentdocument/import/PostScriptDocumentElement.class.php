<?php
/**
 * privatemessaging_PostScriptDocumentElement
 * @package modules.privatemessaging.persistentdocument.import
 */
class privatemessaging_PostScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return privatemessaging_persistentdocument_post
	 */
	protected function initPersistentDocument()
	{
		return privatemessaging_PostService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_privatemessaging/post');
	}
}