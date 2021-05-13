<?php declare(strict_types=1);

namespace SasLoginRequired\Subscriber;

use SasLoginRequired\Extension\Content\SasGate\SasGateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Event\BeforeSendResponseEvent;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Framework\Cache\CacheResponseSubscriber;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Framework\Routing\StorefrontResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class ResponseEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * @var EntityRepositoryInterface
     */
    private $gateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $gateRepository,
        EntityRepositoryInterface $salesChannelRepository
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->gateRepository = $gateRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeSendResponseEvent::class => 'onBeforeSendResponseEvent',
        ];
    }

    public function onBeforeSendResponseEvent(BeforeSendResponseEvent $event): void
    {
        $request = $event->getRequest();
        /** @var StorefrontResponse $response */
        $response = $event->getResponse();

        $salesChannelId = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID);
        if (empty($salesChannelId)) {
            return;
        }

        if (!empty($request->attributes->get('resolved-uri'))) {
            if (str_contains($request->attributes->get('resolved-uri'), '/account')) {
                return;
            }
        }

        // If the request is homepage
        $this->checkLoginHomePage($request, $response, $salesChannelId);

        // If the request is navigation
        $this->checkLoginNavigationPage($request, $response);
    }

    private function checkLoginNavigationPage(Request $request, StorefrontResponse $response): void
    {
        if (!str_contains($request->getPathInfo(), '/navigation/')) {
            return;
        }

        $explode = explode('/navigation/', $request->getPathInfo(), 2);
        $navigationId = $explode[1];

        $criteria = new Criteria();
        $criteria->addAssociation('customerGroups');
        $criteria->addFilter(new EqualsFilter('categoryId', $navigationId));
        $criteria->addFilter(new EqualsFilter('isEnabled', true));
        $criteria->setLimit(1);
        /** @var SasGateEntity $gate */
        $gate = $this->gateRepository->search($criteria, Context::createDefaultContext())->first();
        if (!$gate) {
            // Stop if this navigation doesn't enable gate
            return;
        }

        $this->checkByCustomerGroup($gate, $request, $response);
    }

    private function checkLoginHomePage(Request $request, StorefrontResponse $response, string $salesChannelId): void
    {
        if ($request->getRequestUri() === '/') {
            $meta = $this->getMetaTags($response->getContent());
            if (!\array_key_exists('sas-group-id', $meta)) {
                return;
            }

            // If the homepage category is enabled
            $criteria = new Criteria([$salesChannelId]);
            $criteria->addAssociation('navigationCategory.sasCategoryGate.customerGroups');
            /** @var SalesChannelEntity $salesChannel */
            $salesChannel = $this->salesChannelRepository->search($criteria, Context::createDefaultContext())->first();
            $category = $salesChannel->getNavigationCategory();
            if (empty($category->getExtension('sasCategoryGate'))) {
                // Empty means did not setting the login required plugin for this category
                return;
            }

            /** @var SasGateEntity $gate */
            $gate = $category->getExtension('sasCategoryGate');
            if (!$gate->getIsEnabled()) {
                // Return if it disabled
                return;
            }

            $this->checkByCustomerGroup($gate, $request, $response);
        }
    }

    private function checkByCustomerGroup(SasGateEntity $gate, Request $request, StorefrontResponse $response): void
    {
        $groupId = null;
        if ($_SERVER['SHOPWARE_HTTP_CACHE_ENABLED']) {
            $cacheState = $request->cookies->get('sw-states');
            if ($cacheState === CacheResponseSubscriber::STATE_LOGGED_IN) {
                $meta = $this->getMetaTags($response->getContent());
                if (empty($meta['sas-group-id'])) {
                    return;
                }

                $groupId = $meta['sas-group-id'];
            } else {
                $this->directToLoginPage($request);
            }
        } else {
            if (empty($response->getContext())) {
                $this->directToLoginPage($request);
            }

            if (!$response->getContext()->getCustomer()) {
                $this->directToLoginPage($request);
            } else {
                $groupId = $response->getContext()->getCustomer()->getGroupId();
            }
        }

        if ($groupId) {
            if (\count($gate->getCustomerGroups()) === 0) {
                // Direct to login page because extended settings of allow customer group is empty
                return;
            }

            if (empty($gate->getCustomerGroups()->get($groupId))) {
                // direct to permission page if does not exists groupId
                $this->directToPermissionPage($request);
            }
        }
    }

    private function directToLoginPage(Request $request): void
    {
        $storefrontUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);
        $loginUrl = $storefrontUrl . '/account/login';

        header('Location: ' . $loginUrl);
    }

    private function directToPermissionPage(Request $request): void
    {
        $storefrontUrl = $request->attributes->get(RequestTransformer::STOREFRONT_URL);
        $loginUrl = $storefrontUrl . '/account-permission';

        header('Location: ' . $loginUrl);
    }

    private function getMetaTags($str): array
    {
        $pattern = '
          ~<\s*meta\s

          # using lookahead to capture type to $1
            (?=[^>]*?
            \b(?:name|property|http-equiv)\s*=\s*
            (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
            ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
          )

          # capture content to $2
          [^>]*?\bcontent\s*=\s*
            (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
            ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
          [^>]*>

          ~ix';

        if (preg_match_all($pattern, $str, $out)) {
            return array_combine($out[1], $out[2]);
        }

        return [];
    }
}
