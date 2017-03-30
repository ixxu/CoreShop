<?php
/**
 * CoreShop.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Bundle\CoreShopLegacyBundle\Controller\Model;

use CoreShop\Bundle\CoreShopLegacyBundle\Controller\Admin;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProductSpecificPriceController
 *
 * @Route("/product-specific-price")
 */
class ProductSpecificPriceController extends Admin\AdminController
{
    public function init()
    {
        parent::init();
    }

    /**
     * @Route("/get-config")
     */
    public function getConfigAction()
    {
        return $this->json([
            'success' => true,
            'conditions' => \CoreShop\Bundle\CoreShopLegacyBundle\Model\Product\SpecificPrice::getConditionDispatcher()->getTypeKeys(),
            'actions' => \CoreShop\Bundle\CoreShopLegacyBundle\Model\Product\SpecificPrice::getActionDispatcher()->getTypeKeys(),
        ]);
    }
}
