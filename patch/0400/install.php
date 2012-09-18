<?php
/**
 * privatemessaging_patch_0400
 * @package modules.privatemessaging
 */
class privatemessaging_patch_0400 extends change_Patch
{
	/**
	 * @return array
	 */
	public function getPreCommandList()
	{
		return array(
			array('disable-site'),
			array('compile-documents'),
			array('generate-database'),
		);
	}
	
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		// At this time, users should not have any privatemessagingprofile.
		privatemessaging_PrivatemessagingprofileService::getInstance()->createQuery()->delete();
				
		// Convert existing members to forumsprofiles, preserving ids.
		$this->executeSQLQuery("INSERT INTO m_users_doc_profile (document_id, document_model, document_label, document_author, document_authorid, document_creationdate, document_modificationdate, document_publicationstatus, document_lang, document_modelversion, document_startpublicationdate, document_endpublicationdate, document_metas, document_version, accessorId)
			SELECT document_id, 'modules_privatemessaging/privatemessagingprofile' AS document_model, document_label, document_author, document_authorid, document_creationdate, document_modificationdate, document_publicationstatus, document_lang, document_modelversion, document_startpublicationdate, document_endpublicationdate, document_metas, 0, user FROM m_privatemessaging_doc_member;");
		$this->executeSQLQuery("UPDATE f_document SET document_model = 'modules_privatemessaging/privatemessagingprofile' WHERE document_model = 'modules_privatemessaging/member';");
		$delRelId = $this->getPersistentProvider()->getRelationId('user');
		$this->executeSQLQuery("DELETE FROM f_relation WHERE document_model_id1 = 'modules_privatemessaging/member' AND relation_id = $delRelId;");
		$this->executeSQLQuery("UPDATE f_relation SET document_model_id1 = 'modules_privatemessaging/privatemessagingprofile' WHERE document_model_id1 = 'modules_privatemessaging/member';");
		$this->executeSQLQuery("UPDATE f_relation SET document_model_id2 = 'modules_privatemessaging/privatemessagingprofile' WHERE document_model_id2 = 'modules_privatemessaging/member';");
		
		// Update posts stucture.
		$delRelId = $this->getPersistentProvider()->getRelationId('postauthor'); // Deleted
		$this->executeSQLQuery("UPDATE m_privatemessaging_doc_post SET document_authorid = postauthor;");
		$this->executeSQLQuery("ALTER TABLE m_privatemessaging_doc_post DROP COLUMN postauthor;");
		$this->executeSQLQuery("DELETE FROM f_relation WHERE document_model_id1 = 'modules_privatemessaging/post' AND relation_id = $delRelId;");
		
		// Update thread stucture.
		$delRelId = $this->getPersistentProvider()->getRelationId('tofollow'); // Deleted
		$this->executeSQLQuery("ALTER TABLE m_privatemessaging_doc_thread DROP COLUMN tofollow;");
		$this->executeSQLQuery("DELETE FROM f_relation WHERE document_model_id1 = 'modules_privatemessaging/thread' AND relation_id = $delRelId;");
		
		$this->executeSQLQuery("TRUNCATE f_cache;");
		
		// Dispatch data from members.
		$statement = $this->executeSQLSelect("SELECT * FROM m_privatemessaging_doc_member;");
		$statement->execute();
		$memberInfos = $statement->fetchAll();
		foreach ($memberInfos as $memberInfo)
		{
			
			// Create the privatemessaging profile.
			$pmps = privatemessaging_PrivatemessagingprofileService::getInstance();
			$profile = $pmps->getByAccessorId($memberInfo['user']);
			if ($profile == null)
			{
				$profile = $pmps->getNewDocumentInstance();
			}
			$user = users_persistentdocument_user::getInstanceById($memberInfo['user']);
			$profile->setAccessor($user);
			
			$profile->setViewAvatars($memberInfo['viewavatars']);
			$profile->setViewSignatures($memberInfo['viewsignatures']);
			$profile->setNotifyNewMessages($memberInfo['notifynewmessages']);
			$profile->setTrackingByThread($memberInfo['trackingbythread']);
			$profile->save();
		}
	}
	
	/**
	 * @return array
	 */
	public function getPostCommandList()
	{
		return array(
			array('clear-documentscache'),
			array('enable-site'),
		);
	}
	
	/**
	 * @return string
	 */
	public function getExecutionOrderKey()
	{
		return '2011-11-03 11:11:47';
	}
		
	/**
	 * @return string
	 */
	public function getBasePath()
	{
		return dirname(__FILE__);
	}
	
	/**
	 * @return false
	 */
	public function isCodePatch()
	{
		return false;
	}
}