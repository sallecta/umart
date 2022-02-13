<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\Registry\Registry;

class UmartRuleVat
{
	public static function isValid($value, $required)
	{
		$value = InputFilter::getInstance()->clean($value);

		if (empty($value) && (empty($required) || $required === 'false' || $required === '0'))
		{
			return true;
		}

		if (class_exists('SoapClient') && !empty($value))
		{
			preg_match('/^([a-zA-Z]+)(.+)/', $value, $matches);

			if (!empty($matches[1]) && !empty($matches[2]))
			{
				try
				{
					$client  = new SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
					$checker = $client->checkVat([
						'countryCode' => $matches[1],
						'vatNumber'   => $matches[2],
					]);

					if ($checker->valid)
					{
						return true;
					}
				}
				catch (Exception $e)
				{
					// Ignore Exception
				}
			}
		}

		return false;
	}
}

class JFormRuleVat extends JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		return UmartRuleVat::isValid($value, (string) $element['required']);
	}
}
