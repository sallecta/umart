<?php
/**
 
 
 
 
 
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

<?php if (plg_sytem_umart_main('config', 'display_footer', 1)): ?>
    <p style="text-align: center">
        Powered by <a href="https://github.com/sallecta/umart/" target="_blank">JoomTech</a>
        | <i class="fab fa-joomla"></i>
        <a href="https://github.com/sallecta/umart" target="_blank">Rate on JED</a>
    </p>
<?php endif; ?>

</div>
