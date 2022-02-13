<?php

/**
 
 
 
 
 
 */

use Umart\Classes\User;
use Umart\Helper\Navbar;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
echo Navbar::render();
$admin = plg_sytem_umart_main(User::class)->core('admin');
?>

<div id="umart_body" class="umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s">
    <div id="uk-main-container">
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-hover uk-table-divider uk-table-small" id="esLanguageFiles">
                <thead>
                <tr>
                    <th class="uk-table-shrink uk-text-center">#</th>
                    <th><?php echo Text::_('COM_UMART_FILE'); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($this->files as $i => $file): ?>
                    <tr>
                        <td class="uk-text-center">
							<?php echo sprintf('%02d', $i + 1); ?>
                        </td>
                        <td>
							<?php
							$file = str_replace(Path::clean(JPATH_ROOT, '/'), '', Path::clean($file, '/'));

							if ($admin)
							{
								echo '<a href="' . Route::_('index.php?option=com_umart&view=language&layout=edit&file=' . urlencode($file)) . '">' . $file . '</a>';
							}
							else
							{
								echo $file;
							}
							?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
