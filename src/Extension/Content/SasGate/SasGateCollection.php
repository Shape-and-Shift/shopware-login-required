<?php declare(strict_types=1);

namespace SasLoginRequired\Extension\Content\SasGate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void               add(SasGateEntity $entity)
 * @method void               set(string $key, SasGateEntity $entity)
 * @method SasGateEntity[]    getIterator()
 * @method SasGateEntity[]    getElements()
 * @method SasGateEntity|null get(string $key)
 * @method SasGateEntity|null first()
 * @method SasGateEntity|null last()
 */
class SasGateCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SasGateEntity::class;
    }
}
