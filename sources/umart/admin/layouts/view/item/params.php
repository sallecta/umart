<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use ES\Form\Form;

/**
 * @var object $displayData
 * @var Form   $form
 */

$form = $displayData->get('form');

foreach ($form->getFieldsets('params') as $fieldSet):

	if ($fieldSet->name == 'extra_params')
	{
		continue;
	}

	HTMLHelper::_('ukui.addTab', Text::_($fieldSet->label, true));

	?>

    <div class="uk-card uk-card-small uk-card-default uk-card-body es-border" data-zone-group>
		<?php echo $form->renderFieldset($fieldSet->name) ?>
    </div>

	<?php echo HTMLHelper::_('ukui.endTab'); ?>
<?php endforeach; ?>
