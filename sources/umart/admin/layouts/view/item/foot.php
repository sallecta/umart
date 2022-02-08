<?php
/**
 * @package     com_easyshop
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
?>
<input type="hidden" name="task" value=""/>
<?php echo HTMLHelper::_('form.token'); ?>
</fieldset>

<?php if (easyshop('config', 'display_footer', 1)): ?>
    <p style="text-align: center">
        Powered by <a href="https://www.joomtech.net/" target="_blank">JoomTech</a>
        | <i class="fab fa-joomla"></i>
        <a href="https://extensions.joomla.org/extension/easy-shop/" target="_blank">Rate on JED</a>
    </p>
<?php endif; ?>

</form>
