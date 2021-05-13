<?php declare(strict_types=1);

namespace SasLoginRequired\Extension\Content\SasGate;

use SasLoginRequired\Extension\Content\SasGateCustomerGroup\SasGateCustomerGroupDefinition;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class SasGateDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'sas_gate';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SasGateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SasGateCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new Required(), new PrimaryKey()),
            (new FkField('category_id', 'categoryId', CategoryDefinition::class))->addFlags(new Required()),
            new BoolField('is_enabled', 'isEnabled'),
            (new OneToOneAssociationField('category', 'category_id', 'id', CategoryDefinition::class, false)),
            new ManyToManyAssociationField('customerGroups', CustomerGroupDefinition::class, SasGateCustomerGroupDefinition::class, 'gate_id', 'customer_group_id'),
        ]);
    }
}
