<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
$data           = $displayData['view']->get('data');
$secret         = $displayData['view']->get('secret');
$token          = JSession::getFormToken();
$eTable         = JTable::getInstance('Extension', 'JTable');
$wrapTag        = empty($displayData['wrapTag']) ? 'div' : $displayData['wrapTag'];
?>

<?php foreach ($data['packages'] as $extend):
	$version = $extend['version'];
	$installed  = false;
	$enabled    = false;
	$oldVersion = null;
	$keys       = [
		'type'    => $extend['type'],
		'element' => $extend['element'],
	];

	if ($extend['type'] == 'plugin')
	{
		$keys['folder'] = $extend['folder'];
	}

	if ($eTable->load($keys))
	{
		$installed = true;
		$enabled   = (bool) $eTable->enabled;
		$manifest  = json_decode($eTable->manifest_cache);

		if (version_compare($version, @$manifest->version, 'gt'))
		{
			$oldVersion = $manifest->version;
		}
	}

	$download = $extend['download'] . (!empty($secret) ? ':' . $secret : '');

	?>
    <<?php echo $wrapTag; ?>>
    <div class="uk-card uk-card-small uk-card-default">
        <div class="uk-card-media-top uk-text-center">
            <img src="<?php echo $extend['image'] ?>" alt="<?php echo $extend['name']; ?>">
        </div>
        <div class="uk-card-body">
			<?php echo $extend['free'] ? JText::_('COM_EASYSHOP_FREE_PRODUCT') : $extend['price']; ?>
            <br/>
            <a class="uk-h6 uk-text-uppercase uk-margin-remove uk-link-reset"
               href="<?php echo $extend['link'] ?>"
               target="_blank">
				<?php echo $extend['name']; ?>
                <br/>
                <small>
                    <span uk-icon="icon: git-fork; ratio: .8"></span>
					<?php echo 'version ' . ($oldVersion ? $oldVersion : $version); ?>
                </small>
            </a>
            <p>
				<?php echo $extend['description']; ?>
            </p>
            <p class="uk-text-center">
				<?php if (!$extend['free'] && !$installed): ?>
                    <a href="<?php echo $extend['link']; ?>"
                       class="uk-width-1-1 uk-button uk-button-primary"
                       target="_blank" uk-icon="icon: credit-card">
						<?php echo JText::_('PLG_EASYSHOP_MARKET_BUY_NOW'); ?>
                    </a>
				<?php else: ?>
					<?php if ($installed): ?>
						<?php if ($enabled): ?>
							<?php if ($oldVersion): ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_easyshop&task=market.install&download=' . base64_encode($download) . '&' . $token . '=1', false); ?>"
                                   class="uk-width-1-1 uk-button uk-button-secondary"
                                   uk-icon="cloud-upload">
									<?php echo JText::sprintf('PLG_EASYSHOP_MARKET_UPDATE_TO_VERSION_FORMAT', $version); ?>
                                </a>
							<?php else: ?>
                                <span class="uk-text-success uk-text-uppercase" uk-icon="check">
								    <?php echo JText::_('PLG_EASYSHOP_MARKET_INSTALLED'); ?>
                                </span>
							<?php endif; ?>
						<?php else: ?>
                            <span class="uk-text-danger uk-text-uppercase" uk-icon="icon: close">
							    <?php echo JText::_('PLG_EASYSHOP_MARKET_DISABLED'); ?>
                            </span>
						<?php endif; ?>
					<?php else: ?>
                        <a href="<?php echo JRoute::_('index.php?option=com_easyshop&task=market.install&download=' . base64_encode($extend['download']) . '&' . $token . '=1', false); ?>"
                           class="uk-width-1-1 uk-button uk-button-primary"
                           uk-icon="icon: bolt">
							<?php echo JText::_('PLG_EASYSHOP_MARKET_INSTALL'); ?>
                        </a>
					<?php endif; ?>
				<?php endif; ?>
            </p>
        </div>
    </div>
    </<?php echo $wrapTag; ?>>
<?php endforeach; ?>
