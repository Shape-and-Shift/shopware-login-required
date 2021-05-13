<?php declare(strict_types=1);

namespace SasLoginRequired\Storefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Cache\Annotation\HttpCache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AccountPermissionController extends StorefrontController
{
    /**
     * @HttpCache()
     * @Route("/account-permission", name="frontend.sas-account-permission.page", methods={"GET"})
     */
    public function renderAccountPermissionPage(): ?Response
    {
        $response = $this->renderStorefront(
            '@Storefront/storefront/page/error/account-permission.html.twig'
        );

        $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

        return $response;
    }
}
