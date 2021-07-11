<?php declare(strict_types=1);

namespace SasLoginRequired\Framework\Adapter\Twig\Extension;

use SasLoginRequired\Extension\Content\SasGate\SasGateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\Exception\InvalidDomainException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SasLoginRequiredExtension extends AbstractExtension
{
    private EntityRepositoryInterface $gateRepository;

    public function __construct(EntityRepositoryInterface $gateRepository)
    {
        $this->gateRepository = $gateRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sasCheckLoginRequired', [$this, 'checkLoginRequired']),
        ];
    }

    public function checkLoginRequired(string $navigationId, SalesChannelContext $context): void
    {
        $gate = $this->getGate($navigationId);
        if (!$gate) {
            // Stop if this navigation doesn't enable gate
            return;
        }

        if (!$context->getCustomer()) {
            $this->directToLoginPage($context);
        } else {
            if ($groupId = $context->getCustomer()->getGroupId()) {
                if (\count($gate->getCustomerGroups()) === 0) {
                    // Direct to login page because extended settings of allow customer group is empty
                    return;
                }

                if (empty($gate->getCustomerGroups()->get($groupId))) {
                    // direct to permission page if does not exists groupId
                    $this->directToPermissionPage($context);
                }
            }
        }
    }

    private function getGate(string $categoryId): ?SasGateEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('customerGroups');
        $criteria->addFilter(new EqualsFilter('categoryId', $categoryId));
        $criteria->addFilter(new EqualsFilter('isEnabled', true));
        $criteria->setLimit(1);
        /** @var SasGateEntity $gate */
        $gate = $this->gateRepository->search($criteria, Context::createDefaultContext())->first();

        return $gate;
    }

    private function getHost(SalesChannelContext $context): string
    {
        $domains = $context->getSalesChannel()->getDomains();
        $languageId = $context->getSalesChannel()->getLanguageId();

        if ($domains instanceof SalesChannelDomainCollection) {
            foreach ($domains as $domain) {
                if ($domain->getLanguageId() === $languageId) {
                    return $domain->getUrl();
                }
            }
        }

        throw new InvalidDomainException('Empty domain');
    }

    private function directToLoginPage(SalesChannelContext $context): void
    {
        $storefrontUrl = $this->getHost($context);
        $loginUrl = $storefrontUrl . '/account/login';

        header('Location: ' . $loginUrl);
    }

    private function directToPermissionPage(SalesChannelContext $context): void
    {
        $storefrontUrl = $this->getHost($context);
        $loginUrl = $storefrontUrl . '/account-permission';

        header('Location: ' . $loginUrl);
    }
}
