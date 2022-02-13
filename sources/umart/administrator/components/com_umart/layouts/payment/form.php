<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
$data = $displayData['response']->getRedirectData();
?>
<h3 class="uk-h3">
	<?php echo JText::_('COM_UMART_REDIRECTING_HEADER') . '...'; ?>
</h3>
<p>
	<?php echo JText::_('COM_UMART_REDIRECTING_BODY'); ?>
</p>
<form action="<?php echo $displayData['response']->getRedirectUrl(); ?>"
      method="<?php echo $displayData['response']->getRedirectMethod(); ?>" id="UmartPaymentRedirectForm">
	<?php if (!empty($data)): ?>
		<?php foreach ($data as $name => $value): ?>
			<?php if (isset($value)): ?>
                <input type="hidden" name="<?php echo $name; ?>"
                       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>"/>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
    <button type="submit" class="uk-button uk-button-primary">
		<?php echo JText::_('COM_UMART_REDIRECTING_NOW'); ?>
        <span uk-icon="icon: forward"></span>
    </button>
    <script>
        _umart.$(document).ready(function ($) {
            $('#UmartPaymentRedirectForm').submit();
        });
    </script>
</form>
