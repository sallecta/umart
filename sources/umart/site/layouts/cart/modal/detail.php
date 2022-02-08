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
extract($displayData);

?>
<div data-cart-modal tabindex="-1" uk-modal="center: true">
    <div class="uk-modal-dialog uk-width-2xlarge uk-width-xxlarge uk-modal-body es-cart-modal-detail">
        <a class="uk-modal-close-default" data-uk-close></a>
        <div data-cart-output>
			<?php echo $displayData['cartOutputHTML']; ?>
        </div>
    </div>
</div>
