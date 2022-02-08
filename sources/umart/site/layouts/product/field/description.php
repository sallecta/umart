<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
/**
 * @var $displayData array
 * @var $fields      array
 */
extract($displayData);

use Joomla\Registry\Registry;

?>
<div class="product-custom-fields uk-margin">
    <dl class="uk-description-list">
		<?php foreach ($fields as $field)
		{
			if (!empty($field->display))
			{
				$params = new Registry($field->params);

				if (!$params->get('hiddenLabel'))
				{
					echo '<dt>' . $field->name . '</dt>';
				}

				echo '<dd>' . $field->display . '</dd>';
			}
		}
		?>
    </dl>
</div>
