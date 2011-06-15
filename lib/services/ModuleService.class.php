<?php
/**
 * @package modules.privatemessaging.lib.services
 */
class privatemessaging_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var privatemessaging_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return privatemessaging_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	/**
	 * @return boolean
	 */
	public function hasNewPost()
	{
		$member = privatemessaging_MemberService::getInstance()->getCurrentMember();
		$query = privatemessaging_ThreadService::getInstance()->createQuery();
		$query->add(Restrictions::published())->add(Restrictions::eq('followers', $member));
		$query->addOrder(Order::desc('lastpostdate'))->setMaxResults(1);
		$thread  = f_util_ArrayUtils::firstElement($query->find());
		return ($thread !== null) && ($thread->getNbnewpost() > 0);
	}
	
	/**
	 * @param f_peristentdocument_PersistentDocument $container
	 * @param array $attributes
	 * @param string $script
	 * @return array
	 */
	public function getStructureInitializationAttributes($container, $attributes, $script)
	{
		// Check container.
		if (!$container instanceof website_persistentdocument_topic)
		{
			throw new BaseException('Invalid topic', 'modules.privatemessaging.bo.general.Invalid-topic');
		}
		$websiteId = $container->getDocumentService()->getWebsiteId($container);
	
		$website = DocumentHelper::getDocumentInstance($websiteId, 'modules_website/website');
		if (TagService::getInstance()->hasDocumentByContextualTag('contextual_website_website_modules_privatemessaging_threadlist', $website) || 
			TagService::getInstance()->hasDocumentByContextualTag('contextual_website_website_modules_privatemessaging_thread', $website) || 
			TagService::getInstance()->hasDocumentByContextualTag('contextual_website_website_modules_privatemessaging_newthread', $website) || 
			TagService::getInstance()->hasDocumentByContextualTag('contextual_website_website_modules_privatemessaging_newpost', $website))
		{
			throw new BaseException('Some pages of the global structure are already initialized', 'modules.privatemessaging.bo.general.Some-pages-already-initialized');
		}
		
		// Set atrtibutes.
		$attributes['byDocumentId'] = $container->getId();
		$attributes['type'] = $container->getPersistentModel()->getName();
		return $attributes;
	}
}