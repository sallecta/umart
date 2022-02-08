<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\Html;

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('textarea');

class JFormFieldTagEditor extends JFormFieldTextarea
{
	protected $type = 'TagEditor';

	protected function getInput()
	{
		if (is_array($this->value))
		{
			foreach ($this->value as &$value)
			{
				$value = $value['name'];
			}

			$this->value = implode(',', $this->value);
		}

		$dataSource = [];
		$context    = $this->getAttribute('context', 'com_easyshop.product');
		$db         = easyshop('db');
		$query      = $db->getQuery(true)
			->select('DISTINCT a.name')
			->from($db->quoteName('#__easyshop_tags', 'a'))
			->where('a.context = ' . $db->quote($context));
		$db->setQuery($query);

		if ($tags = $db->loadColumn())
		{
			$dataSource = $tags;
		}

		easyshop(Html::class)
			->jui(['widget', 'position', 'menu', 'autocomplete'])
			->addCss('jquery.tag-editor.css')
			->addJs('jquery.caret.min.js')
			->addJs('jquery.tag-editor.min.js');
		easyshop('doc')->addScriptDeclaration('
			_es.$(document).ready(function($){
				$("#' . $this->id . '").tagEditor({
					autocomplete: {
						source: ' . json_encode($dataSource) . ',
						minLength: 2
					}
				});
			});
		');

		return parent::getInput();
	}
}
