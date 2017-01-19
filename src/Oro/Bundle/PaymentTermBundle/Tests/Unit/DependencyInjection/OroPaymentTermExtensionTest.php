<?php

namespace Oro\Bundle\PaymentTermBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\PaymentTermBundle\DependencyInjection\OroPaymentTermExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroPaymentTermExtensionTest extends ExtensionTestCase
{
    public function testLoad()
    {
        $this->loadExtension(new OroPaymentTermExtension());

        $expectedParameters = [
            'oro_payment_term.entity.payment_term.class',
            'oro_payment_term.type'
        ];
        $this->assertParametersLoaded($expectedParameters);

        $expectedDefinitions = [
            'oro_payment_term.payment_term.manager.api',
            'oro_payment_term.form.type.payment_term',
            'oro_payment_term.integration.channel',
            'oro_payment_term.integration.transport',
            'oro_payment_term.config.by_settings_factory_parameter_bag',
            'oro_payment_term.repository.payment_term_settings',
            'oro_payment_term.config.provider',
            'oro_payment_term.config.provider_basic',
            'oro_payment_term.config.provider_basic_memory_cached',
            'oro_payment_term.config.integration_method_identifier_generator'
        ];
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
