<?php

namespace Oro\Bundle\WebCatalogBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\ScopeBundle\Form\Type\ScopeCollectionType;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\ContentVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentNodeType extends AbstractType
{
    const NAME = 'oro_web_catalog_content_node';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'parentNode',
                EntityIdentifierType::NAME,
                ['class' => ContentNode::class, 'multiple' => false]
            )
            ->add(
                'titles',
                LocalizedFallbackValueCollectionType::NAME,
                [
                    'label'    => 'oro.webcatalog.contentnode.titles.label',
                ]
            )
            ->add(
                'parentScopeUsed',
                CheckboxType::class,
                [
                    'label' => 'oro.webcatalog.contentnode.parent_scope_used.label',
                    'required' => false
                ]
            )
            ->add(
                'scopes',
                ScopeCollectionType::NAME,
                [
                    'entry_options' => [
                        'scope_type' => 'web_content'
                    ]
                ]
            )
            ->add(
                'contentVariants',
                ContentVariantCollectionType::NAME,
                [
                    'label' => 'oro.webcatalog.contentvariant.entity_plural_label'
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof ContentNode && $data->getParentNode() instanceof ContentNode) {
            $event->getForm()->add(
                'slugPrototypes',
                LocalizedFallbackValueCollectionType::NAME,
                [
                    'label'    => 'oro.webcatalog.contentnode.slug_prototypes.label',
                    'required' => true,
                    'options'  => ['constraints' => [new NotBlank()]],
                ]
            );
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var ContentNode $contentNode */
        $data = $event->getData();
        if ($data instanceof ContentNode) {
            if ($data->isParentScopeUsed()) {
                $data->resetScopes();
            }
            if (!$data->getContentVariants()->isEmpty()) {
                $data->getContentVariants()->map(
                    function (ContentVariant $contentVariant) use ($data) {
                        if (!$contentVariant->getNode()) {
                            $contentVariant->setNode($data);
                        }
                    }
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ContentNode::class,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}