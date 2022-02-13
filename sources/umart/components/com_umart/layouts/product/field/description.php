<?php
/**
 
 
 
 
 
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
