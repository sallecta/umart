<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Model\AdminModel;
use Joomla\CMS\Language\Text;

class UmartModelTag extends AdminModel
{
	protected $translationRefTable = 'umart_tags';

	public function getItem($pk = null)
	{
		$item    = parent::getItem($pk);
		$context = $this->getState('filter.context');

		if (empty($item->context))
		{
			$item->context = $context;
		}

		if (empty($item->id) && $context !== $item->context)
		{
			throw new RuntimeException(Text::_('COM_UMART_ERROR_INVALID_CONTEXT'));
		}

		return $item;
	}

	protected function populateState()
	{
		parent::populateState();
		$this->setState('filter.context', plg_sytem_umart_main('app')->input->getCmd('context', 'com_umart.product'));
	}
}
