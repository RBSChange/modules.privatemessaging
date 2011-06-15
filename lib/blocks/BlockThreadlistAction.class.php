<?php
/**
 * privatemessaging_BlockThreadlistAction
 * @package modules.privatemessaging.lib.blocks
 */
class privatemessaging_BlockThreadlistAction extends website_BlockAction
{
	/**
	 * @see website_BlockAction::execute()
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	function execute($request, $response)
	{
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}

		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		if ($member === null)
		{
			return website_BlockView::NONE;
		}
		$request->setAttribute('member', $member);

		$threads = privatemessaging_ThreadService::getInstance()->getByMember($member);
		$paginator = new paginator_Paginator('privatemessaging', $request->getParameter('page', 1), $threads, $this->getNbItemPerPage($request, $response));
		
		$request->setAttribute('paginator', $paginator);
		
		return website_BlockView::SUCCESS;
	}
	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return Integer default 10
	 */
	private function getNbItemPerPage($request, $response)
	{
		return $this->getConfiguration()->getNbitemperpage();
	}
}