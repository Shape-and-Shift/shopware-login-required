import gateState from "./gate-state";

const { Component } = Shopware;

Component.override('sw-category-detail', {
    inject: ['repositoryFactory'],

    props: {
        categoryId: {
            type: String,
            required: false,
            default: null
        }
    },

    beforeCreate() {
        Shopware.State.registerModule('sasCategoryDetailGate', gateState);
    },

    beforeDestroy() {
        Shopware.State.unregisterModule('sasCategoryDetailGate');
    },


    computed: {
        gateRepository() {
            return this.repositoryFactory.create('sas_gate');
        },

        category() {
            return Shopware.State.get('swCategoryDetail').category;
        },

        categoryDetailGate() {
            return Shopware.State.get('sasCategoryDetailGate').gate;
        },
    },

    watch: {
        categoryId() {
            this.loadGate(this.categoryId);
        }
    },

    methods: {
        createdComponent() {
            this.$super('createdComponent');

            this.loadGate(this.categoryId);
        },

        async onSave() {
            if (this.categoryDetailGate) {
                // Save gate information
                this.gateRepository.save(this.categoryDetailGate, Shopware.Context.api);
            }

            this.$super('onSave');
        },

        loadGate(categoryId) {
            Shopware.State.dispatch('sasCategoryDetailGate/loadGate', {
                gateRepository: this.gateRepository,
                apiContext: Shopware.Context.api,
                categoryId: categoryId
            });
        }
    }
});
