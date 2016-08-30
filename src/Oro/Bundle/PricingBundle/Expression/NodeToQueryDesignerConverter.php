<?php

namespace Oro\Bundle\PricingBundle\Expression;

use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\PricingBundle\Entity\ProductPrice;
use Oro\Bundle\PricingBundle\Model\PriceListQueryDesigner;
use Oro\Bundle\PricingBundle\Provider\PriceRuleFieldsProvider;
use Oro\Bundle\ProductBundle\Entity\Product;

class NodeToQueryDesignerConverter
{
    /**
     * @var PriceRuleFieldsProvider
     */
    protected $fieldsProvider;

    /**
     * @param PriceRuleFieldsProvider $fieldsProvider
     */
    public function __construct(PriceRuleFieldsProvider $fieldsProvider)
    {
        $this->fieldsProvider = $fieldsProvider;
    }

    /**
     * @param NodeInterface $node
     * @return AbstractQueryDesigner
     */
    public function convert(NodeInterface $node)
    {
        $source = new PriceListQueryDesigner();
        $source->setEntity(Product::class);

        $definition = [
            'columns' => [],
        ];
        $addedColumns = [];
        foreach ($node->getNodes() as $subNode) {
            if ($subNode instanceof NameNode) {
                $this->convertNames($subNode, $addedColumns, $definition);
            } elseif ($subNode instanceof RelationNode) {
                $this->convertRelations($subNode, $addedColumns, $definition);
            }
        }

        $source->setDefinition(json_encode($definition));

        return $source;
    }

    /**
     * @param NameNode $subNode
     * @param array $addedColumns
     * @param array $definition
     */
    protected function convertNames(NameNode $subNode, array &$addedColumns, array &$definition)
    {
        if ($subNode->getContainer() === Product::class) {
            if (empty($addedColumns[$subNode->getField()])) {
                $definition['columns'][] = [
                    'name' => $subNode->getField(),
                    'table_identifier' => $subNode->getContainer(),
                ];
                $addedColumns[$subNode->getField()] = true;
            }
        } elseif ($subNode->getContainer() === ProductPrice::class) {
            $path = sprintf('%1$s::product+%1$s::%2$s', ProductPrice::class, $subNode->getField());
            if (empty($addedColumns[$path])) {
                $definition['columns'][] = [
                    'name' => $path,
                    'table_identifier' => $subNode->getContainer(),
                ];
                $addedColumns[$path] = true;
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('Unsupported field %s::%s', $subNode->getContainer(), $subNode->getField())
            );
        }
    }

    /**
     * @param RelationNode $subNode
     * @param array $addedColumns
     * @param array $definition
     */
    protected function convertRelations(RelationNode $subNode, array &$addedColumns, array &$definition)
    {
        $tableIdentifier = $subNode->getRelationAlias();

        $resolvedContainer = $this->fieldsProvider->getRealClassName($tableIdentifier);
        $path = sprintf(
            '%s+%s::%s',
            $subNode->getField(),
            $resolvedContainer,
            $subNode->getRelationField()
        );
        if (empty($addedColumns[$path])) {
            $definition['columns'][] = [
                'name' => $path,
                'table_identifier' => $tableIdentifier,
            ];
            $addedColumns[$path] = true;
        }
    }
}