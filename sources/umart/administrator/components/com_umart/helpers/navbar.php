<?php
/**
 
 
 
 
 
 */

namespace Umart\Helper;
use Umart\Classes\Renderer;

defined('_JEXEC') or die;

class Navbar
{
	public static function render()
	{
		$navbar = plg_sytem_umart_main('state')->get('system.navbar', null);

		if ($navbar === null)
		{
			$items = [
				'system'  => [
					'url'      => '#',
					'icon'     => 'es-icon-cog',
					'title'    => 'COM_UMART_SYSTEM',
					'children' =>
						[
							[
								'icon'  => 'es-icon-pie-chart',
								'url'   => 'index.php?option=com_umart&view=dashboard',
								'title' => 'COM_UMART_DASHBOARD'
							],
							[
								'icon'  => 'es-icon-map',
								'url'   => 'index.php?option=com_umart&view=zones',
								'title' => 'COM_UMART_ZONES'
							],
							[
								'icon'  => 'es-icon-cash-register',
								'url'   => 'index.php?option=com_umart&view=taxes',
								'title' => 'COM_UMART_TAXES'
							],
							[
								'icon'  => 'es-icon-banknote',
								'url'   => 'index.php?option=com_umart&view=methods&filter_type=payment',
								'title' => 'COM_UMART_PAYMENT_METHODS'
							],
							[
								'icon'  => 'es-icon-truck',
								'url'   => 'index.php?option=com_umart&view=methods&filter_type=shipping',
								'title' => 'COM_UMART_SHIPPING_METHODS'
							],
							[
								'icon'  => 'es-icon-email',
								'url'   => 'index.php?option=com_umart&view=emails',
								'title' => 'COM_UMART_SYSTEM_EMAILS',
							],
							[
								'icon'  => 'es-icon-currency',
								'url'   => 'index.php?option=com_umart&view=currencies',
								'title' => 'COM_UMART_CURRENCIES'
							],
							[
								'icon'  => 'es-icon-gift',
								'url'   => 'index.php?option=com_umart&view=discounts',
								'title' => 'COM_UMART_DISCOUNTS',
							],
							[
								'icon'  => 'es-icon-foursquare',
								'url'   => 'index.php?option=com_umart&view=customfields&reflector=com_umart.checkout',
								'title' => 'COM_UMART_CHECKOUT_FIELDS',
							],
							[
								'icon'  => 'es-icon-language',
								'url'   => 'index.php?option=com_umart&view=languages',
								'title' => 'COM_UMART_LANGUAGES',
							],
							[
								'icon'  => 'es-icon-hour-glass',
								'url'   => 'index.php?option=com_umart&view=logs',
								'title' => 'COM_UMART_SYSTEM_LOGS',
							],
							[
								'icon'  => 'es-icon-info',
								'url'   => 'index.php?option=com_umart&view=system',
								'title' => 'COM_UMART_SYSTEM_INFO',
							],
						],
				],
				'order'   => [
					'icon'  => 'es-icon-receipt',
					'url'   => 'index.php?option=com_umart&view=orders',
					'title' => 'COM_UMART_ORDERS',
				],
				'product' => [
					'url'      => '#',
					'title'    => 'COM_UMART_PRODUCTS',
					'icon'     => 'es-icon-cart',
					'children' => [
						[
							'icon'  => 'plus-circle',
							'url'   => 'index.php?option=com_umart&view=product&layout=edit&id=0',
							'title' => 'COM_UMART_PRODUCT_ADDNEW',
						],
						[
							'icon'  => 'es-icon-product',
							'url'   => 'index.php?option=com_umart&view=products',
							'title' => 'COM_UMART_PRODUCTS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_umart.product',
							'title' => 'COM_UMART_CATEGORIES',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_umart.brand',
							'title' => 'COM_UMART_PRODUCT_BRANDS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_umart.product.customfield',
							'title' => 'COM_UMART_CUSTOMFIELD_GROUPS',
						],
						[
							'icon'  => 'es-icon-field',
							'url'   => 'index.php?option=com_umart&view=customfields&reflector=com_umart.product.customfield',
							'title' => 'COM_UMART_CUSTOMFIELDS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_umart.product.option',
							'title' => 'COM_UMART_OPTION_GROUPS',
						],
						[
							'icon'  => 'es-icon-field',
							'url'   => 'index.php?option=com_umart&view=customfields&reflector=com_umart.product.option',
							'title' => 'COM_UMART_OPTIONS',
						],
						[
							'icon'  => 'es-icon-tag',
							'url'   => 'index.php?option=com_umart&view=tags&context=com_umart.product',
							'title' => 'COM_UMART_TAGS',
						],
					]
				],
				'user'    => [
					'url'      => '#',
					'title'    => 'COM_UMART_USERS',
					'icon'     => 'es-icon-users-o',
					'children' => [
						[
							'icon'  => 'es-icon-user-o',
							'url'   => 'index.php?option=com_umart&view=users',
							'title' => 'COM_UMART_CUSTOMERS',
						],
						[
							'icon'  => 'es-icon-profile',
							'url'   => 'index.php?option=com_umart&view=customfields&reflector=com_umart.user',
							'title' => 'COM_UMART_PROFILE',
						],
					]
				],
				'media'   => [
					'url'      => '#',
					'title'    => 'COM_UMART_MEDIA',
					'icon'     => 'es-icon-pictures',
					'children' => [
						[
							'icon'  => 'es-icon-picture-1',
							'url'   => 'index.php?option=com_umart&view=media',
							'title' => 'COM_UMART_IMAGES',
						],
					],
				],
			];

			plg_sytem_umart_main('app')->triggerEvent('onUmartNavbarPrepare', [&$items]);

			if (empty($items))
			{
				return null;
			}

			/** @var Renderer $layoutHelper */
			$navbar = plg_sytem_umart_main('renderer')->render('system.navbar', ['items' => $items]);
		}

		return $navbar;
	}
}
