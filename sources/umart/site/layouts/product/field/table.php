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
    <table class="uk-table uk-table-small uk-table-striped uk-table-responsive">
        <tbody>
		<?php foreach ($fields as $field): ?>
			<?php if (!empty($field->display)):
				$params = new Registry($field->params);
				?>
                <tr>
                    <th class="uk-table-small">
						<?php echo !$params->get('hiddenLabel') ? $field->name : ''; ?>
                    </th>
                    <td class="uk-table-expand">
						<?php echo $field->display; ?>
                    </td>
                </tr>
			<?php endif; ?>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
