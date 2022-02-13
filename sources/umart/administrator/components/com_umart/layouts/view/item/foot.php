<?php
/**
 
 * @version     1.0.5
 * @Author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2019 github.com/sallecta/umart All Rights Reserved.
 
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;
?>
<input type="hidden" name="task" value=""/>
<?php echo HTMLHelper::_('form.token'); ?>
</fieldset>

<?php if (plg_sytem_umart_main('config', 'display_footer', 1)): ?>
    <p style="text-align: center">
        Powered by <a href="https://github.com/sallecta/umart/" target="_blank">JoomTech</a>
        | <i class="fab fa-joomla"></i>
        <a href="https://github.com/sallecta/umart" target="_blank">Rate on JED</a>
    </p>
<?php endif; ?>

</form>
