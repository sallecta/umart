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
 * @var array  $displayData
 * @var string $label
 * @var string $input
 */

extract($displayData);

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory as CMSFactory;

if (!empty($options['showonEnabled']))
{
    if (ES_DETECT_JVERSION === 4)
    {
	    CMSFactory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
    }
    else
    {
	    HTMLHelper::_('jquery.framework');
	    HTMLHelper::_('script', 'jui/cms.js', ['relative' => true, 'version' => 'auto']);
    }
}

$class = empty($options['class']) ? '' : ' ' . $options['class'];
$rel   = empty($options['rel']) ? '' : ' ' . $options['rel'];

if (!empty($options['helpText']))
{
	$input .= $options['helpText'];
}

?>

<div class="uk-clearfix uk-margin-small-bottom <?php echo $class; ?>"<?php echo $rel; ?>>
	<?php if (empty($options['hiddenLabel'])) : ?>
		<?php echo $label; ?>
        <div class="uk-form-controls">
			<?php echo $input; ?>
        </div>
	<?php else: ?>
		<?php echo $input; ?>
	<?php endif; ?>
</div>