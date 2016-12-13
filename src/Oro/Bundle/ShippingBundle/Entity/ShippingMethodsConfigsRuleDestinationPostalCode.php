<?php

namespace Oro\Bundle\ShippingBundle\Entity;

/**
 * @ORM\Entity
 * @ORM\Table("oro_shipping_methods_configs_rule_destination_postal_code")
 */
class ShippingMethodsConfigsRuleDestinationPostalCode
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var ShippingMethodsConfigsRuleDestination
     *
     * @ORM\ManyToOne(targetEntity="ShippingMethodsConfigsRuleDestination")
     * @ORM\JoinColumn(name="destination_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $destination;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ShippingMethodsConfigsRuleDestinationPostalCode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ShippingMethodsConfigsRuleDestination
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param ShippingMethodsConfigsRuleDestination $destination
     * @return ShippingMethodsConfigsRuleDestinationPostalCode
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }
}
