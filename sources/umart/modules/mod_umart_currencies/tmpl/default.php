<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

/** @var $currency \Umart\Currency */

$activeId    = $currency->getActiveId();
$displayMode = $params->get('display_mode', 'dropdown');
$displayType = $params->get('display_type', 'symbol2code');
$displayName = function ($item, $displayType)
{
	$code = strtoupper($item->code);

	switch ($displayType)
	{
		case 'symbol2code':
			return $item->symbol . ' ' . $code;

		case 'symbol':
			return $item->symbol;

		case 'name':
			return $item->name;
	}

	return $code;
};
?>
<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post">
    <div id="mod-umart-currencies-<?php echo $module->id; ?>"
         class="uk-scope mod-umart-currencies <?php echo $moduleClassSfx; ?>">
		<?php if ($displayMode == 'dropdown'): ?>
            <select name="umart_currency_id" class="uk-select uk-width-small not-chosen"
                    onchange="this.form.submit();">
				<?php foreach ($currency->getList() as $item): ?>
                    <option value="<?php echo $item->id; ?>"<?php echo $activeId == $item->id ? ' selected' : ''; ?>>
						<?php echo $displayName($item, $displayType); ?>
                    </option>
				<?php endforeach; ?>
            </select>
		<?php elseif ($displayMode == 'subnav'): ?>
            <ul class="uk-subnav uk-subnav-divider uk-margin-remove">
				<?php foreach ($currency->getList() as $item): ?>
                    <li<?php echo $activeId == $item->id ? ' class="uk-active"' : ''; ?>>
                        <button type="button"
                                onclick="this.form.umart_currency_id.value=<?php echo (int) $item->id; ?>; this.form.submit();"
                                class="uk-button uk-button-link">
							<?php echo $displayName($item, $displayType); ?>
                        </button>
                    </li>
				<?php endforeach; ?>
            </ul>
            <input type="hidden" name="umart_currency_id" value=""/>
		<?php else: ?>
            <div class="uk-button-group">
				<?php foreach ($currency->getList() as $item): ?>
                    <button type="button"
                            onclick="this.form.umart_currency_id.value=<?php echo (int) $item->id; ?>; this.form.submit();"
                            class="uk-button uk-button-small uk-button-<?php echo $activeId == $item->id ? 'primary' : 'default'; ?>">
						<?php echo $displayName($item, $displayType); ?>
                    </button>
				<?php endforeach; ?>
            </div>
            <input type="hidden" name="umart_currency_id" value=""/>
		<?php endif; ?>
    </div>
</form>
