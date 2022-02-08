<?php

/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use ES\Classes\User;
use ES\Helper\Navbar;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
echo Navbar::render();
$admin = easyshop(User::class)->core('admin');
?>

<div id="es-body" class="uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s">
    <div id="uk-main-container">
        <div class="uk-overflow-auto">
            <table class="uk-table uk-table-hover uk-table-divider uk-table-small" id="esLanguageFiles">
                <thead>
                <tr>
                    <th class="uk-table-shrink uk-text-center">#</th>
                    <th><?php echo Text::_('COM_EASYSHOP_FILE'); ?></th>
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
								echo '<a href="' . Route::_('index.php?option=com_easyshop&view=language&layout=edit&file=' . urlencode($file)) . '">' . $file . '</a>';
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
