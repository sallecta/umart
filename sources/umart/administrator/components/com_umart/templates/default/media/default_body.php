<?php

/**
 
 
 
 
 
 */
defined('_JEXEC') or die;

use Umart\Classes\User;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;

$baseUrl  = $this->directory->get('path.url');
$type     = $this->isFile ? 'file' : 'image';
$lightbox = !$this->isFile && $this->method !== 'importMedia' ? ' uk-lightbox' : '';
$user     = plg_sytem_umart_main(User::class);

if (plg_sytem_umart_main('site'))
{
	$userPath = 'assets/' . $type . 's/user_customers/' . $user->get()->id . '/';
}
else
{
	$userPath = 'assets/' . $type . 's/';
}

//@since 1.1.6
$renderer = $this->getRenderer();
?>
<div id="es-files" class="list">
    <ul class="files">
		<?php foreach ($this->directory->get('list.path') as $path):
			$removeDirAjax = UmartHelperMedia::getLink($path->get('path'), $type, 'media.removeFolder');
			?>
            <li class="directory">
                <a href="<?php echo $path->get('url'); ?>">
                    <span uk-icon="icon: folder"></span>
					<?php echo $path->get('title'); ?>
                </a>
                <button type="button"
                        data-directory="<?php echo $path->get('path'); ?>"
                        class="uk-button uk-button-default uk-button-small remove">
                    <span uk-icon="icon: trash"></span>
                </button>
            </li>
		<?php endforeach; ?>
		<?php if ($this->isFile): ?>
			<?php foreach ($this->files as $file):
				$name = htmlspecialchars(basename($file->file), ENT_COMPAT, 'UTF-8');
				$fileAlias = htmlspecialchars(str_replace($userPath, '', $file->file));
				?>
                <li class="file"
                    data-file="<?php echo htmlspecialchars($file->file); ?>"
                    data-alias="<?php echo $fileAlias; ?>">
                    <a href="#" class="preview" title="<?php echo $name; ?>" uk-tooltip>
                        <div class="uk-text-center">
							<?php if (preg_match('/(jpe?g|png|gif|svg|webp)$/i', $file->file)): ?>
                                <span uk-icon="icon: image"></span>
							<?php else: ?>
                                <span uk-icon="icon: cloud-download"></span>
							<?php endif; ?>
                        </div>
                        <small class="name">
                            <span><?php echo $name; ?></span>
                        </small>
                    </a>
                    <button type="button"
                            class="uk-button uk-button-link uk-text-danger remove">
                        <i class="fa fa-times"></i>
                    </button>
                </li>
			<?php endforeach; ?>
		<?php else: ?>
			<?php foreach ($this->files as $file):

				if ($file->type === 'image')
				{
					$originBasePath = Path::clean($file->originBasePath, '/');
					$fileName       = basename($file->image);
					$fileAlias      = htmlspecialchars(str_replace($userPath, '', $originBasePath), ENT_COMPAT, 'UTF-8');
					$fileBasePath   = htmlspecialchars($originBasePath, ENT_COMPAT, 'UTF-8');
					$source         = $file->image;
				}
				else
				{
					$filePath     = Path::clean($file->file, '/');
					$fileName     = basename($filePath);
					$fileAlias    = htmlspecialchars(str_replace($userPath, '', $filePath), ENT_COMPAT, 'UTF-8');
					$fileBasePath = htmlspecialchars($filePath, ENT_COMPAT, 'UTF-8');
					$source       = UMART_MEDIA_URL . '/' . $fileBasePath;
				}

				?>
                <li class="file <?php echo $file->type; ?>"<?php echo $lightbox; ?>
                    data-file="<?php echo $fileBasePath; ?>"
                    data-alias="<?php echo $fileAlias; ?>">
                    <a href="<?php echo $source; ?>" class="preview uk-text-truncate"
                       type="<?php echo $file->type; ?>"
                       title="<?php echo htmlspecialchars($fileName, ENT_COMPAT, 'UTF-8'); ?>" uk-tooltip>
						<?php if ($file->type === 'image'): ?>
                            <img data-src="<?php echo $file->tiny; ?>" alt="" uk-img/>
						<?php endif; ?>
                        <small class="name">
							<?php if ($file->type === 'video'): ?>
                                <div>
                                    <span uk-icon="icon: play-circle; ratio: 2.5"></span>
                                </div>
							<?php endif; ?>
                            <span><?php echo $fileName; ?></span>
                        </small>
                    </a>
                    <button type="button"
                            class="uk-button uk-button-default uk-button-small remove">
                        <span uk-icon="icon: trash"></span>
                    </button>
                </li>
			<?php endforeach; ?>
		<?php endif; ?>
    </ul>

	<?php if ($this->isButtonEditor): ?>
        <div id="es-image-attributes" class="uk-child-width-1-3@s uk-grid-small uk-margin" uk-grid>
            <div>
                <label class="uk-form-label">
					<?php echo Text::_('COM_UMART_IMAGE_ALT', true) ?>
                </label>
                <div class="uk-form-controls">
                    <input class="uk-input image-alt" type="text"/>
                </div>
            </div>
            <div>
                <label class="uk-form-label">
					<?php echo Text::_('COM_UMART_IMAGE_WIDTH', true) ?>
                </label>
                <div class="uk-form-controls">
                    <input class="uk-input image-width" type="number" min="0"/>
                </div>
            </div>
            <div>
                <label class="uk-form-label" for="es-image-height">
					<?php echo Text::_('COM_UMART_IMAGE_HEIGHT', true) ?>
                </label>
                <div class="uk-form-controls">
                    <input class="uk-input image-height" type="number" min="0"/>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>
