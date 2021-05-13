<?php declare(strict_types=1);

namespace SasLoginRequired\Extension\Content\SasGate;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class SasGateEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $categoryId;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @var CategoryEntity
     */
    protected $category;

    /**
     * @var CustomerGroupCollection
     */
    protected $customerGroups;

    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    public function setCategoryId(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getCustomerGroups(): ?CustomerGroupCollection
    {
        return $this->customerGroups;
    }
}
