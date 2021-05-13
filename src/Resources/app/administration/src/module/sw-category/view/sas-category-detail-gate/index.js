import template from './sas-category-detail-gate.html.twig';

const { Component } = Shopware;

Component.register('sas-category-detail-gate', {
    template,

    inject: ['repositoryFactory'],

    data() {
        return {
            hasLoadedCustomerGroup: false
        }
    },

    computed: {
        categoryDetailGate() {
            if (!Shopware.State.get('sasCategoryDetailGate')) {
                return {};
            }

            return Shopware.State.get('sasCategoryDetailGate').gate;
        },
    },

    watch: {
        'categoryDetailGate.id': {
            immediate: true,
            handler() {
                this.loadedGateCustomerGroups();
            }
        }
    },


    methods: {
        loadedGateCustomerGroups() {
            if (!this.hasLoadedCustomerGroup && this.categoryDetailGate !== null) {
                this.hasLoadedCustomerGroup = true;
            }
        }
    }
});

