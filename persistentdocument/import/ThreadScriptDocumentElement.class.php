<?php
/**
 * privatemessaging_ThreadScriptDocumentElement
 * @package modules.privatemessaging.persistentdocument.import
 */
class privatemessaging_ThreadScriptDocumentElement extends import_ScriptDocumentElement
{
	/**
	 * @return privatemessaging_persistentdocument_thread
	 */
	protected function initPersistentDocument()
	{
		return privatemessaging_ThreadService::getInstance()->getNewDocumentInstance();
	}
	
	/**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_privatemessaging/thread');
	}
}