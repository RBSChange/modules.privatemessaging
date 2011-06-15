<?php
class privatemessaging_MemberListener
{
	/**
	 * @param f_persistentdocument_PersistentDocument $sender
	 * @param array $params
	 * @return void
	 */
	public function onPersistentDocumentCreated($sender, $params)
	{
		$document = $params['document'];
		if ($document instanceof forums_persistentdocument_member)
		{
			privatemessaging_MemberService::getInstance()->getByUser($document->getUser(), true);
		}
	}
}