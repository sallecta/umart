<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
use Umart\Table\AbstractTable;

class UmartTableMedia extends AbstractTable
{
	protected function getTableDBName()
	{
		return '#__umart_medias';
	}

	public function check()
	{
		if ((int) $this->product_id < 1)
		{
			$this->setError(JText::_('COM_UMART_WARNING_PROVIDE_VALID_PRODUCT'));

			return false;
		}

		if (empty($this->file_path) || !is_file(UMART_MEDIA . '/' . $this->file_path))
		{
			$this->setError(JText::_('COM_UMART_WARNING_PROVIDE_VALID_MEDIA'));

			return false;
		}

		return true;
	}
}
