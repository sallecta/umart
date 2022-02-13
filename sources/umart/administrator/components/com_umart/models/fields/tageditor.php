<?php
/**
 
 
 
 
 
 */

use Umart\Classes\Html;

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
		$context    = $this->getAttribute('context', 'com_umart.product');
		$db         = plg_sytem_umart_main('db');
		$query      = $db->getQuery(true)
			->select('DISTINCT a.name')
			->from($db->quoteName('#__umart_tags', 'a'))
			->where('a.context = ' . $db->quote($context));
		$db->setQuery($query);

		if ($tags = $db->loadColumn())
		{
			$dataSource = $tags;
		}

		plg_sytem_umart_main(Html::class)
			->jui(['widget', 'position', 'menu', 'autocomplete'])
			->addCss('jquery.tag-editor.css')
			->addJs('jquery.caret.min.js')
			->addJs('jquery.tag-editor.min.js');
		plg_sytem_umart_main('doc')->addScriptDeclaration('
			_umart.$(document).ready(function($){
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
