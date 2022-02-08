<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

class JFormFieldCard extends JFormFieldList
{
	protected $type = 'card';

	protected function getOptions()
	{
		$options = parent::getOptions();
		$cards   = [
			'amex'                      => JText::_('COM_EASYSHOP_CARD_AMEX'),
			'dankort'                   => JText::_('COM_EASYSHOP_CARD_DANKORT'),
			'diners_club_carte_blanche' => JText::_('COM_EASYSHOP_CARD_DINERS_CLUB_CARTE_BLANCHE'),
			'diners_club_international' => JText::_('COM_EASYSHOP_CARD_DINERS_CLUB_INTERNATIONAL'),
			'discover'                  => JText::_('COM_EASYSHOP_CARD_DISCOVER'),
			'jcb'                       => JText::_('COM_EASYSHOP_CARD_JCB'),
			'laser'                     => JText::_('COM_EASYSHOP_CARD_LASER'),
			'maestro'                   => JText::_('COM_EASYSHOP_CARD_MAESTRO'),
			'mastercard'                => JText::_('COM_EASYSHOP_CARD_MASTERCARD'),
			'uatp'                      => JText::_('COM_EASYSHOP_CARD_UATP'),
			'visa'                      => JText::_('COM_EASYSHOP_CARD_VISA'),
			'visa_electron'             => JText::_('COM_EASYSHOP_CARD_VISA_ELECTRON'),
		];

		foreach ($cards as $value => $text)
		{
			$option        = new stdClass;
			$option->value = $value;
			$option->text  = $text;
			$options[]     = $option;
		}

		return $options;
	}
}
