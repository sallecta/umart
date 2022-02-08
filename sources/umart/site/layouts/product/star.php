<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
/**
 * @var array $displayData
 */
extract($displayData);

$votes = $product->votes;

?>
<div class="product-vote">
	<?php for ($i = 0; $i < 5; $i++):
		$vote = $i + 1;
		$active = $votes >= $vote ? ' active' : '';
		$star = 'star';

		if ($votes < $vote && $votes > $i)
		{
			$star   = 'star-half-o';
			$active = ' active';
		}

		?>
        <a href="#" class="star<?php echo $active; ?>">
            <i class="fa fa-<?php echo $star; ?>"></i>
        </a>
	<?php endfor; ?>
	<?php if (easyshop('config', 'vote_count', 1)): ?>
        (<?php echo Text::sprintf('COM_EASYSHOP_VOTED_PLURAL', $product->voteCount); ?>)
	<?php endif; ?>
</div>
