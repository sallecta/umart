<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;

/** @var array $displayData */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$data   = $displayData['response']->getRedirectData() ?: [];
$url    = $displayData['response']->getRedirectUrl();
$method = $displayData['response']->getRedirectMethod();

if ('GET' === strtoupper($method))
{
	$uri  = Uri::getInstance($url);
	$data = array_merge($data, $uri->getQuery(true));
}

?>

<h3 class="uk-h3">
	<?php echo Text::_('COM_EASYSHOP_REDIRECTING_HEADER') . '...'; ?>
</h3>
<p>
	<?php echo Text::_('COM_EASYSHOP_REDIRECTING_BODY'); ?>
</p>

<form action="<?php echo $url; ?>" method="<?php echo $method; ?>" id="es-payment-redirect-form">

	<?php if (!empty($data)): ?>
		<?php foreach ($data as $name => $value): ?>
			<?php if (isset($value)): ?>
                <input type="hidden" name="<?php echo $name; ?>"
                       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8') ?>"/>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

    <button type="submit" class="uk-button uk-button-primary">
		<?php echo Text::_('COM_EASYSHOP_REDIRECTING_NOW'); ?>
        <span uk-icon="icon: forward"></span>
    </button>

    <script>
        window.addEventListener('load', function () {
            document.getElementById('es-payment-redirect-form').submit();
        });
    </script>
</form>
