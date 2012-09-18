<?php
/**
 * privatemessaging_BlockThreadlistAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockThreadlistAction extends privatemessaging_BaseBlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return string
	 */
	function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}

		$user = users_UserService::getInstance()->getCurrentUser();
		if (!($user instanceof users_persistentdocument_user))
		{
			return $this->getForbiddenView();
		}
		$request->setAttribute('user', $user);

		$threads = privatemessaging_ThreadService::getInstance()->getByUser($user);
		$paginator = new paginator_Paginator('privatemessaging', $request->getParameter('page', 1), $threads, $this->getNbItemPerPage($request, $response));
		
		$request->setAttribute('paginator', $paginator);
		
		return website_BlockView::SUCCESS;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return integer default 10
	 */
	private function getNbItemPerPage($request, $response)
	{
		return $this->getConfiguration()->getNbitemperpage();
	}
}