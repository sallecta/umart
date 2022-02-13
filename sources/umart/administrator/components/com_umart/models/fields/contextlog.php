<?php
/**
 
 
 
 
 
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
		$db      = plg_sytem_umart_main('db');
		$query   = $db->getQuery(true)
			->select('DISTINCT a.context')
			->from($db->quoteName('#__umart_logs', 'a'));
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
