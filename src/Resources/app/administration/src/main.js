import './module/sw-category/component/sw-category-view';
import './module/sw-category/view/sas-category-detail-gate';
import './module/sw-category/page/sw-category-detail';

Shopware.Module.register('sas-category-detail-gate-tab', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.category.detail') {
            currentRoute.children.push({
                name: 'sw.category.detail.gate',
                path: '/sw/category/index/:id/gate',
                component: 'sas-category-detail-gate',
                meta: {
                    parentPath: 'sw.category.index',
                    privilege: 'category.viewer'
                }
            });
        }
        next(currentRoute);
    }
});
