<?php

namespace Oro\Bundle\PaymentBundle\Method\View;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractPaymentMethodViewProvider implements PaymentMethodViewProviderInterface
{
    /** @var ArrayCollection|PaymentMethodViewInterface[] */
    protected $views;

    /**
     * @return ArrayCollection|PaymentMethodViewInterface[]
     */
    abstract protected function buildViews();

    public function __construct()
    {
        $this->views = new ArrayCollection();
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function hasPaymentMethodView($identifier)
    {
        return $this->getViews()->containsKey($identifier);
    }

    /**
     * @param string $identifier
     *
     * @return PaymentMethodViewInterface|null
     */
    public function getPaymentMethodView($identifier)
    {
        if (!$this->hasPaymentMethodView($identifier)) {
            return null;
        }

        return $this->getViews()->get($identifier);
    }

    /**
     * @param array $identifiers
     *
     * @return array|PaymentMethodViewInterface[]
     */
    public function getPaymentMethodViews($identifiers)
    {
        $views = [];
        foreach ($identifiers as $identifier) {
            if ($this->hasPaymentMethodView($identifier)) {
                $views[] = $this->getPaymentMethodView($identifier);
            }
        }

        return $views;
    }

    /**
     * @return ArrayCollection|PaymentMethodViewInterface[]
     */
    protected function getViews()
    {
        if ($this->views->isEmpty()) {
            $this->buildViews();
        }

        return $this->views;
    }

    /**
     * @param string $identifier
     * @param PaymentMethodViewInterface $view
     */
    protected function addView($identifier, PaymentMethodViewInterface $view)
    {
        $this->views->set($identifier, $view);
    }
}
