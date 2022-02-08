<?php
/**
 *  @package     com_easyshop
 *  @version     1.0.5
 *  @Author      JoomTech Team
* @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 *  @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
namespace ES\Classes;

defined('_JEXEC') or die;

class StringHelper
{
	protected $plural = [
		'/(s)tatus$/i'                                                       => '\1tatuses',
		'/(quiz)$/i'                                                         => '\1zes',
		'/^(ox)$/i'                                                          => '\1\2en',
		'/([m|l])ouse$/i'                                                    => '\1ice',
		'/(matr|vert|ind)(ix|ex)$/i'                                         => '\1ices',
		'/(x|ch|ss|sh)$/i'                                                   => '\1es',
		'/([^aeiouy]|qu)y$/i'                                                => '\1ies',
		'/(hive)$/i'                                                         => '\1s',
		'/(chef)$/i'                                                         => '\1s',
		'/(?:([^f])fe|([lre])f)$/i'                                          => '\1\2ves',
		'/sis$/i'                                                            => 'ses',
		'/([ti])um$/i'                                                       => '\1a',
		'/(p)erson$/i'                                                       => '\1eople',
		'/(?<!u)(m)an$/i'                                                    => '\1en',
		'/(c)hild$/i'                                                        => '\1hildren',
		'/(buffal|tomat)o$/i'                                                => '\1\2oes',
		'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin)us$/i' => '\1i',
		'/us$/i'                                                             => 'uses',
		'/(alias)$/i'                                                        => '\1es',
		'/(ax|cris|test)is$/i'                                               => '\1es',
		'/s$/'                                                               => 's',
		'/^$/'                                                               => '',
		'/$/'                                                                => 's',
	];
	protected $singular = [
		'/(s)tatuses$/i'                                                          => '\1\2tatus',
		'/^(.*)(menu)s$/i'                                                        => '\1\2',
		'/(quiz)zes$/i'                                                           => '\\1',
		'/(matr)ices$/i'                                                          => '\1ix',
		'/(vert|ind)ices$/i'                                                      => '\1ex',
		'/^(ox)en/i'                                                              => '\1',
		'/(alias)(es)*$/i'                                                        => '\1',
		'/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
		'/([ftw]ax)es/i'                                                          => '\1',
		'/(cris|ax|test)es$/i'                                                    => '\1is',
		'/(shoe)s$/i'                                                             => '\1',
		'/(o)es$/i'                                                               => '\1',
		'/ouses$/'                                                                => 'ouse',
		'/([^a])uses$/'                                                           => '\1us',
		'/([m|l])ice$/i'                                                          => '\1ouse',
		'/(x|ch|ss|sh)es$/i'                                                      => '\1',
		'/(m)ovies$/i'                                                            => '\1\2ovie',
		'/(s)eries$/i'                                                            => '\1\2eries',
		'/([^aeiouy]|qu)ies$/i'                                                   => '\1y',
		'/(tive)s$/i'                                                             => '\1',
		'/(hive)s$/i'                                                             => '\1',
		'/(drive)s$/i'                                                            => '\1',
		'/([le])ves$/i'                                                           => '\1f',
		'/([^rfoa])ves$/i'                                                        => '\1fe',
		'/(^analy)ses$/i'                                                         => '\1sis',
		'/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'             => '\1\2sis',
		'/([ti])a$/i'                                                             => '\1um',
		'/(p)eople$/i'                                                            => '\1\2erson',
		'/(m)en$/i'                                                               => '\1an',
		'/(c)hildren$/i'                                                          => '\1\2hild',
		'/(n)ews$/i'                                                              => '\1\2ews',
		'/eaus$/'                                                                 => 'eau',
		'/^(.*us)$/'                                                              => '\\1',
		'/s$/i'                                                                   => ''
	];

	public function toPlural($word)
	{
		foreach ($this->plural as $pattern0 => $pattern1)
		{
			if (preg_match($pattern0, $word))
			{
				return preg_replace($pattern0, $pattern1, $word);
			}
		}

		return $word;
	}

	public function toSingular($word)
	{
		foreach ($this->singular as $pattern0 => $pattern1)
		{
			if (preg_match($pattern0, $word))
			{
				return preg_replace($pattern0, $pattern1, $word);
			}
		}

		return $word;
	}
}
