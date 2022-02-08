<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use ES\Model\AdminModel;
use Joomla\CMS\Language\Text;

class EasyshopModelTag extends AdminModel
{
	protected $translationRefTable = 'easyshop_tags';

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
			throw new RuntimeException(Text::_('COM_EASYSHOP_ERROR_INVALID_CONTEXT'));
		}

		return $item;
	}

	protected function populateState()
	{
		parent::populateState();
		$this->setState('filter.context', easyshop('app')->input->getCmd('context', 'com_easyshop.product'));
	}
}
