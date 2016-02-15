<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use Oro\Component\Testing\Unit\EntityTrait;

use OroB2B\Bundle\PricingBundle\Entity\ProductPriceChangeTrigger;
use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\ProductBundle\Entity\Product;

class ChangedProductPriceTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;
    use EntityTrait;

    public function testConstructor()
    {
        $priceList = new PriceList();
        $product = new Product();
        $changedProductPrice = new ProductPriceChangeTrigger($priceList, $product);

        $this->assertSame($priceList, $changedProductPrice->getPriceList());
        $this->assertSame($product, $changedProductPrice->getProduct());
    }

    public function testGetObjectIdentifier()
    {
        $changedProductPrice = $this->getChangedProductPrice(1, 1);

        $this->assertSame(
            'OroB2B\Bundle\PricingBundle\Entity\ProductPriceChangeTrigger_1_1',
            $changedProductPrice->getObjectIdentifier()
        );
    }

    /**
     * @expectedExceptionMessage Product id and priceList id, required for identifier generation
     * @expectedException \InvalidArgumentException
     */
    public function testGetObjectIdentifierError()
    {
        $changedProductPrice = $this->getChangedProductPrice();
        $changedProductPrice->getObjectIdentifier();
    }

    /**
     * @param null|int $productId
     * @param null|int $priceListId
     * @return ProductPriceChangeTrigger
     */
    protected function getChangedProductPrice($productId = null, $priceListId = null)
    {
        /** @var Product $product */
        $product = $this->getEntity('OroB2B\Bundle\ProductBundle\Entity\Product', ['id' => $productId]);
        /** @var PriceList $priceList */
        $priceList = $this->getEntity('OroB2B\Bundle\PricingBundle\Entity\PriceList', ['id' => $priceListId]);

        return new ProductPriceChangeTrigger($priceList, $product);
    }
}
