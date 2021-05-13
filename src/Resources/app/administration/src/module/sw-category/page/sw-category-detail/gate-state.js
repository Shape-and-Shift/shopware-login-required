const { Criteria } = Shopware.Data;

export default {
    namespaced: true,

    state() {
        return {
            gate: null
        };
    },

    mutations: {
        setGate(state, gate) {
            state.gate = gate;
        }
    },

    actions: {
        setGate({ commit }, payload) {
            commit('setGate', payload);
        },

        loadGate({ commit }, { gateRepository, categoryId, apiContext }) {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('categoryId', categoryId));
            criteria.addAssociation('customerGroups');

            return gateRepository.search(criteria, apiContext).then((gates) => {
                if (gates.total > 0) {
                    const gate = gates[0];
                    commit('setGate', gate);
                } else {
                    const gate = gateRepository.create(Shopware.Context.api);
                    gate.categoryId = categoryId;
                    gate.isEnabled = false;
                    gateRepository.save(gate, apiContext).then(() => {
                        gate._isNew = false;
                        commit('setGate', gate);
                    });
                }
            });
        }
    }
};
