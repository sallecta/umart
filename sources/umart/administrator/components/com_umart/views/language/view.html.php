<?php
/**
 
 * @version     1.0.5
 
 
 
 */

defined('_JEXEC') or die;

use Umart\Classes\User;
use Umart\View\BaseView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class UmartViewLanguage extends BaseView
{
	protected $languages;
	protected $files;
	protected $file;
	protected $form;
	protected $langTag;

	public function display($tpl = null)
	{
		$app             = plg_sytem_umart_main('app');
		$this->languages = UmartHelper::getLanguageList();
		$edit            = $this->getLayout() == 'edit';
		$display         = true;

		if ($edit)
		{
			$this->file    = $app->input->get('file', '', 'string');
			$parts         = explode('.', basename($this->file));
			$this->langTag = $parts[0];

			if (is_file(JPATH_ROOT . $this->file))
			{
				$this->form = new Form('com_umart.language', ['control' => 'jform']);
				$this->form->loadFile(UMART_COMPONENT_ADMINISTRATOR . '/models/forms/language.xml');
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
			$this->files   = UmartHelper::getAllLanguagesFiles($this->langTag);
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
			$app->redirect(Route::_('index.php?option=com_umart&view=languages', false));
		}
	}

	protected function addToolbar()
	{
		if ($this->getLayout() == 'edit')
		{
			ToolbarHelper::title(Text::sprintf('COM_UMART_LANGUAGE_EDIT_FILE_FORMAT', $this->file));
			ToolbarHelper::apply('language.editFile');
			ToolbarHelper::link(Route::_('index.php?option=com_umart&view=language&tag=' . $this->langTag, false), 'JTOOLBAR_BACK');
		}
		else
		{
			ToolbarHelper::title(Text::sprintf('COM_UMART_LANGUAGE_FILES', $this->langTag));
			ToolbarHelper::link(Route::_('index.php?option=com_umart&view=languages', false), 'JTOOLBAR_BACK');
		}

		if (plg_sytem_umart_main(User::class)->core('admin'))
		{
			ToolbarHelper::preferences('com_umart');
		}
	}
}
