<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\User;
use ES\View\BaseView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class EasyshopViewLanguage extends BaseView
{
	protected $languages;
	protected $files;
	protected $file;
	protected $form;
	protected $langTag;

	public function display($tpl = null)
	{
		$app             = easyshop('app');
		$this->languages = EasyshopHelper::getLanguageList();
		$edit            = $this->getLayout() == 'edit';
		$display         = true;

		if ($edit)
		{
			$this->file    = $app->input->get('file', '', 'string');
			$parts         = explode('.', basename($this->file));
			$this->langTag = $parts[0];

			if (is_file(JPATH_ROOT . $this->file))
			{
				$this->form = new Form('com_easyshop.language', ['control' => 'jform']);
				$this->form->loadFile(ES_COMPONENT_ADMINISTRATOR . '/models/forms/language.xml');
				$this->form->setValue('file_contents', null, file_get_contents(JPATH_ROOT . $this->file));
			}
			else
			{
				$display = false;
			}
		}
		else
		{
			$this->langTag = $app->input->get('tag', '', 'string');
			$this->files   = EasyshopHelper::getAllLanguagesFiles($this->langTag);
		}

		if (!in_array($this->langTag, array_keys($this->languages)))
		{
			$display = false;
		}

		if ($display)
		{
			parent::display($tpl);
			$this->addToolbar();
		}
		else
		{
			$app->enqueueMessage('Language not found.', 'warning');
			$app->redirect(Route::_('index.php?option=com_easyshop&view=languages', false));
		}
	}

	protected function addToolbar()
	{
		if ($this->getLayout() == 'edit')
		{
			ToolbarHelper::title(Text::sprintf('COM_EASYSHOP_LANGUAGE_EDIT_FILE_FORMAT', $this->file));
			ToolbarHelper::apply('language.editFile');
			ToolbarHelper::link(Route::_('index.php?option=com_easyshop&view=language&tag=' . $this->langTag, false), 'JTOOLBAR_BACK');
		}
		else
		{
			ToolbarHelper::title(Text::sprintf('COM_EASYSHOP_LANGUAGE_FILES', $this->langTag));
			ToolbarHelper::link(Route::_('index.php?option=com_easyshop&view=languages', false), 'JTOOLBAR_BACK');
		}

		if (easyshop(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_easyshop');
		}
	}
}
