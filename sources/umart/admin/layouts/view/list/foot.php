<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
$view = $displayData;
?>
</tbody>
<tfoot>
<tr>
    <td colspan="<?php echo $view->columns; ?>" style="border:none"></td>
</tr>
</tfoot>
</table>
</div>
</div>
<?php echo $view->get('pagination')->getListFooter(); ?>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<?php echo JHtml::_('form.token'); ?>
</form>

<?php if (easyshop('config', 'display_footer', 1)): ?>
    <p style="text-align: center">
        Powered by <a href="https://www.joomtech.net/" target="_blank">JoomTech</a>
        | <i class="fab fa-joomla"></i>
        <a href="https://extensions.joomla.org/extension/easy-shop/" target="_blank">Rate on JED</a>
    </p>
<?php endif; ?>

</div>
