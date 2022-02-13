<?php
/**
 
 
 
 
 
 */
defined('_JEXEC') or die;
/**
 * @var array   $displayData
 * @var array   $classes
 * @var string  $for
 * @var string  $text
 * @var boolean $required
 */
extract($displayData);

$classes = array_filter((array) $classes);
$id      = $for . '-lbl';
$title   = '';

if ($required)
{
	$classes[] = 'required';
}

$classes[] = 'uk-form-label';
$class     = implode(' ', array_unique($classes));
?>

<label class="<?php echo $class; ?>" id="<?php echo $id; ?>" for="<?php echo $for; ?>">
	<?php echo $text; ?>
	<?php if ($required) : ?>
        <span class="star">&#160;*</span>
	<?php endif; ?>
</label>
