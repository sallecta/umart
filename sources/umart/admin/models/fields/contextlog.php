<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
FormHelper::loadFieldClass('list');

class JFormFieldContextLog extends JFormFieldList
{
	protected $type = 'ContextLog';

	protected function getOptions()
	{
		$options = parent::getOptions();
		$db      = easyshop('db');
		$query   = $db->getQuery(true)
			->select('DISTINCT a.context')
			->from($db->quoteName('#__easyshop_logs', 'a'));
		$db->setQuery($query);

		if ($columns = $db->loadColumn())
		{
			foreach ($columns as $column)
			{
				$option        = new stdClass;
				$option->value = $column;
				$option->text  = Text::_(strtoupper(str_replace('.', '_', $column)) . '_LOG_TITLE');
				$options[]     = $option;
			}
		}

		return $options;
	}
}
