<?php
/**
 * privatemessaging_MemberScriptDocumentElement
 * @package modules.privatemessaging.persistentdocument.import
 */
class privatemessaging_MemberScriptDocumentElement extends import_ScriptDocumentElement
{
    /**
     * @return privatemessaging_persistentdocument_member
     */
    protected function initPersistentDocument()
    {
    	return privatemessaging_MemberService::getInstance()->getNewDocumentInstance();
    }
    
    /**
	 * @return f_persistentdocument_PersistentDocumentModel
	 */
	protected function getDocumentModel()
	{
		return f_persistentdocument_PersistentDocumentModel::getInstanceFromDocumentModelName('modules_privatemessaging/member');
	}
}