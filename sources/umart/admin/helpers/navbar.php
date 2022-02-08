<?php
/**
 * @package     com_easyshop
 * @version     1.4.1
 * @author      JoomTech Team
 * @copyright   Copyright (C) 2015 - 2021 www.joomtech.net All Rights Reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ES\Helper;
use ES\Classes\Renderer;

defined('_JEXEC') or die;

class Navbar
{
	public static function render()
	{
		$navbar = easyshop('state')->get('system.navbar', null);

		if ($navbar === null)
		{
			$items = [
				'system'  => [
					'url'      => '#',
					'icon'     => 'es-icon-cog',
					'title'    => 'COM_EASYSHOP_SYSTEM',
					'children' =>
						[
							[
								'icon'  => 'es-icon-pie-chart',
								'url'   => 'index.php?option=com_easyshop&view=dashboard',
								'title' => 'COM_EASYSHOP_DASHBOARD'
							],
							[
								'icon'  => 'es-icon-map',
								'url'   => 'index.php?option=com_easyshop&view=zones',
								'title' => 'COM_EASYSHOP_ZONES'
							],
							[
								'icon'  => 'es-icon-cash-register',
								'url'   => 'index.php?option=com_easyshop&view=taxes',
								'title' => 'COM_EASYSHOP_TAXES'
							],
							[
								'icon'  => 'es-icon-banknote',
								'url'   => 'index.php?option=com_easyshop&view=methods&filter_type=payment',
								'title' => 'COM_EASYSHOP_PAYMENT_METHODS'
							],
							[
								'icon'  => 'es-icon-truck',
								'url'   => 'index.php?option=com_easyshop&view=methods&filter_type=shipping',
								'title' => 'COM_EASYSHOP_SHIPPING_METHODS'
							],
							[
								'icon'  => 'es-icon-email',
								'url'   => 'index.php?option=com_easyshop&view=emails',
								'title' => 'COM_EASYSHOP_SYSTEM_EMAILS',
							],
							[
								'icon'  => 'es-icon-currency',
								'url'   => 'index.php?option=com_easyshop&view=currencies',
								'title' => 'COM_EASYSHOP_CURRENCIES'
							],
							[
								'icon'  => 'es-icon-gift',
								'url'   => 'index.php?option=com_easyshop&view=discounts',
								'title' => 'COM_EASYSHOP_DISCOUNTS',
							],
							[
								'icon'  => 'es-icon-foursquare',
								'url'   => 'index.php?option=com_easyshop&view=customfields&reflector=com_easyshop.checkout',
								'title' => 'COM_EASYSHOP_CHECKOUT_FIELDS',
							],
							[
								'icon'  => 'es-icon-language',
								'url'   => 'index.php?option=com_easyshop&view=languages',
								'title' => 'COM_EASYSHOP_LANGUAGES',
							],
							[
								'icon'  => 'es-icon-hour-glass',
								'url'   => 'index.php?option=com_easyshop&view=logs',
								'title' => 'COM_EASYSHOP_SYSTEM_LOGS',
							],
							[
								'icon'  => 'es-icon-info',
								'url'   => 'index.php?option=com_easyshop&view=system',
								'title' => 'COM_EASYSHOP_SYSTEM_INFO',
							],
						],
				],
				'order'   => [
					'icon'  => 'es-icon-receipt',
					'url'   => 'index.php?option=com_easyshop&view=orders',
					'title' => 'COM_EASYSHOP_ORDERS',
				],
				'product' => [
					'url'      => '#',
					'title'    => 'COM_EASYSHOP_PRODUCTS',
					'icon'     => 'es-icon-cart',
					'children' => [
						[
							'icon'  => 'plus-circle',
							'url'   => 'index.php?option=com_easyshop&view=product&layout=edit&id=0',
							'title' => 'COM_EASYSHOP_PRODUCT_ADDNEW',
						],
						[
							'icon'  => 'es-icon-product',
							'url'   => 'index.php?option=com_easyshop&view=products',
							'title' => 'COM_EASYSHOP_PRODUCTS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_easyshop.product',
							'title' => 'COM_EASYSHOP_CATEGORIES',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_easyshop.brand',
							'title' => 'COM_EASYSHOP_PRODUCT_BRANDS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_easyshop.product.customfield',
							'title' => 'COM_EASYSHOP_CUSTOMFIELD_GROUPS',
						],
						[
							'icon'  => 'es-icon-field',
							'url'   => 'index.php?option=com_easyshop&view=customfields&reflector=com_easyshop.product.customfield',
							'title' => 'COM_EASYSHOP_CUSTOMFIELDS',
						],
						[
							'icon'  => 'es-icon-tab',
							'url'   => 'index.php?option=com_categories&extension=com_easyshop.product.option',
							'title' => 'COM_EASYSHOP_OPTION_GROUPS',
						],
						[
							'icon'  => 'es-icon-field',
							'url'   => 'index.php?option=com_easyshop&view=customfields&reflector=com_easyshop.product.option',
							'title' => 'COM_EASYSHOP_OPTIONS',
						],
						[
							'icon'  => 'es-icon-tag',
							'url'   => 'index.php?option=com_easyshop&view=tags&context=com_easyshop.product',
							'title' => 'COM_EASYSHOP_TAGS',
						],
					]
				],
				'user'    => [
					'url'      => '#',
					'title'    => 'COM_EASYSHOP_USERS',
					'icon'     => 'es-icon-users-o',
					'children' => [
						[
							'icon'  => 'es-icon-user-o',
							'url'   => 'index.php?option=com_easyshop&view=users',
							'title' => 'COM_EASYSHOP_CUSTOMERS',
						],
						[
							'icon'  => 'es-icon-profile',
							'url'   => 'index.php?option=com_easyshop&view=customfields&reflector=com_easyshop.user',
							'title' => 'COM_EASYSHOP_PROFILE',
						],
					]
				],
				'media'   => [
					'url'      => '#',
					'title'    => 'COM_EASYSHOP_MEDIA',
					'icon'     => 'es-icon-pictures',
					'children' => [
						[
							'icon'  => 'es-icon-picture-1',
							'url'   => 'index.php?option=com_easyshop&view=media',
							'title' => 'COM_EASYSHOP_IMAGES',
						],
					],
				],
			];

			easyshop('app')->triggerEvent('onEasyshopNavbarPrepare', [&$items]);

			if (empty($items))
			{
				return null;
			}

			/** @var Renderer $layoutHelper */
			$navbar = easyshop('renderer')->render('system.navbar', ['items' => $items]);
		}

		return $navbar;
	}
}
