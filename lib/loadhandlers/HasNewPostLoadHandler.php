<?php
class privatemessaging_HasNewPostLoadHandler extends website_ViewLoadHandlerImpl
{
	/**
	 * @param website_BlockActionRequest $request
	 * @param website_BlockActionResponse $response
	 */
	public function execute($request, $response)
	{
		$request->setAttribute('hasNewPost', privatemessaging_ModuleService::getInstance()->hasNewPost());
	}
}