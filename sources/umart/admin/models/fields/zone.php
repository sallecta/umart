<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

use ES\Classes\Utility;
use ES\Classes\Zone;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

FormHelper::loadFieldClass('list');


class JFormFieldZone extends JFormFieldList
{
	protected $type = 'zone';

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if ($setUp = parent::setup($element, $value, $group))
		{
			$this->class .= ' es-zone es-zone-' . strtolower($this->getAttribute('zone_type', 'country'));
		}

		return $setUp;
	}

	protected function getOptions()
	{
		$zoneType = strtolower($this->getAttribute('zone_type', 'country'));
		$parentId = $this->getAttribute('parent_id', '');
		$key      = $zoneType . ':' . $parentId;
		static $options = [];

		if (!isset($options[$key]))
		{
			$config     = easyshop('config');
			$display    = (int) $config->get('zone_display', 1);
			$name       = $display === 1 ? 'CONCAT(a.name, " (", a.name_english, ")")' : ($display === 2 ? 'a.name' : 'a.name_english');
			$zonesModel = easyshop('model', 'zones', ES_COMPONENT_ADMINISTRATOR);
			$zonesModel->setState('list.select', 'a.id AS value, ' . $name . ' AS text, a.code_2');
			$zonesModel->setState('list.ordering', 'a.name');
			$zonesModel->setState('list.direction', 'asc');
			$zonesModel->setState('list.start', 0);
			$zonesModel->setState('list.limit', 0);
			$zonesModel->setState('filter.type', $zoneType);
			$zonesModel->setState('filter.published', 1);

			if (in_array($zoneType, ['state', 'subzone']))
			{
				if (!empty($this->value))
				{
					$value = (array) $this->value;
					$value = ArrayHelper::arrayUnique(ArrayHelper::toInteger($value));
					$db    = easyshop('db');
					$query = $db->getQuery(true)
						->select('a.parent_id')
						->from($db->quoteName('#__easyshop_zones', 'a'))
						->where('a.id = ' . (int) $value[0]);

					if ($zoneType === 'subzone')
					{
						$query->select('a2.id AS country_id')
							->innerJoin($db->quoteName('#__easyshop_zones', 'a2') . ' ON a2.id = a.parent_id AND a2.type = ' . $db->quote('country'));
					}

					$db->setQuery($query);

					if ($zone = $db->loadObject())
					{
						if ($zoneType === 'state')
						{
							$zonesModel->setState('filter.country_id', $zone->parent_id);
						}
						else
						{
							$zonesModel->setState('filter.country_id', $zone->country_id);
							$zonesModel->setState('filter.state_id', $zone->parent_id);
						}
					}
				}
				elseif (is_numeric($parentId))
				{
					if ($zoneType === 'state')
					{
						$zonesModel->setState('filter.country_id', $parentId);
					}
					else
					{
						$zoneTable = easyshop(Zone::class)->load($parentId);
						$zonesModel->setState('filter.country_id', $zoneTable->parent_id);
						$zonesModel->setState('filter.state_id', $parentId);
					}

				}
			}

			if (empty($this->value)
				&& empty($this->default)
				&& empty($this->multiple))
			{
				$zone = easyshop('app')->getUserState('com_easyshop.zone.' . $zoneType, false);

				if (is_object($zone) && !empty($zone->id))
				{
					$this->value = (int) $zone->id;
				}
			}

			$options[$key] = parent::getOptions();

			if ($items = $zonesModel->getItems())
			{
				if ($zoneType == 'country' && $config->get('country_flag_emoji', 1))
				{
					$utility = easyshop(Utility::class);

					foreach ($items as $item)
					{
						$item->text = trim($utility->getCountryFlagEmoji($item->code_2) . ' ' . $item->text);
					}
				}

				$options[$key] = array_merge($options[$key], $items);
			}
		}

		$this->prepareDocument();

		return $options[$key];
	}

	protected function prepareDocument()
	{
		static $done = false;

		if (!$done)
		{
			$done = true;
			easyshop('doc')->addScriptDeclaration('
				_es.$(document).ready(function($){		
					var zoneCountry = $("select.es-zone-country");
					$(document).on("change", "select.es-zone-country, select.es-zone-state", function(){
						var 
							el = $(this),
							group = el.parents("[data-zone-group]:eq(0)"),
							fill = el.hasClass("es-zone-country") ? group.find(".es-zone-state") : group.find(".es-zone-subzone"),
							selected = fill.find("option:selected"),
							parents = [];
						el.find("option:selected").each(function() {
							parents.push($(this).val());
						});
						
						if(fill.length && parents.length) {							
							$.ajax({
								url: "' . Uri::root(true) . '/index.php?option=com_easyshop&task=zone.loadByParents",
								type: "post",								
								data: {
									parents: parents
								},
								success: function(data){
									fill.html(data);
									selected.each(function() {
										fill
											.find("option[value=\""+$(this).val()+"\"]")
											.prop("selected", true);
									});									
									
									if(!fill.attr("onchange")){
										fill.trigger("change");
									}
									
									_es.initChosen(group.get(0));		
								}
							});
						}
					});
					
					if(zoneCountry.length && zoneCountry.val() !== "" && !zoneCountry.get(0).hasAttribute("onchange")){
						zoneCountry.trigger("change");
					}
				});
			');
		}
	}
}
