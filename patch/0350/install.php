<?php
/**
 * privatemessaging_patch_0350
 * @package modules.privatemessaging
 */
class privatemessaging_patch_0350 extends patch_BasePatch
{
	/**
	 * Entry point of the patch execution.
	 */
	public function execute()
	{
		$pp = f_persistentdocument_PersistentProvider::getInstance();
		$tm = f_persistentdocument_TransactionManager::getInstance();
		$parser = new website_BBCodeParser();
		
		try 
		{
			$tm->beginTransaction();
			foreach (privatemessaging_PostService::getInstance()->createQuery()->find() as $doc)
			{
				$text = $doc->getText();
				if (f_util_StringUtils::beginsWith($text, '<div data-profile="'))
				{
					$text = $parser->convertXmlToBBCode($text);
				}
				$doc->setTextAsBBCode($text);
				$pp->updateDocument($doc);
			}
			$tm->commit();
		}
		catch (Exception $e)
		{
			$tm->rollback($e);
		}
	}
}