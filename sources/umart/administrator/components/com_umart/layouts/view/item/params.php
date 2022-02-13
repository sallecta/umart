<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Umart\Form\Form;

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

	HTMLHelper::_('umartui.addTab', Text::_($fieldSet->label, true));

	?>

    <div class="uk-card uk-card-small uk-card-default uk-card-body es-border" data-zone-group>
		<?php echo $form->renderFieldset($fieldSet->name) ?>
    </div>

	<?php echo HTMLHelper::_('umartui.endTab'); ?>
<?php endforeach; ?>
