<template>
    <div class="min-h-screen bg-slate-50">
        <div class="mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">Hostel Fee Setup</h1>
                <p class="text-slate-600">Manage hostel fee configurations</p>
            </div>

            <!-- Search Filters -->
            <div class="border border-slate-300 bg-white p-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search Field</label>
                        <select v-model="filters.field_name" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" :disabled="loading">
                            <option value="">Select Field</option>
                            <option value="store_id">Store ID</option>
                            <option value="hostel_name">Hostel Name</option>
                            <option value="purpose">Purpose</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Search</label>
                        <input v-model="filters.value" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @keydown.enter.prevent="loadFeeSetups" />
                    </div>
                    <div class="flex items-end">
                        <button @click="loadFeeSetups" :disabled="loading" class="h-9 w-full rounded-sm bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ loading ? 'Loading...' : 'Search' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fee Setup Table -->
            <div class="mt-6 border border-slate-300 bg-white p-5">
                <div class="mb-4 flex justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Fee Setups</h2>
                    <div class="flex gap-2">
                        <button @click="createFeeSetup" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-plus mr-2"></i>
                            Create Fee Setup
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-slate-300">
                        <thead class="bg-emerald-100">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-700">
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Hostel Name</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Purpose</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Amount</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Store ID</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-slate-700">Store Password</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Status</th>
                                <th class="border border-slate-300 bg-emerald-100 px-1 py-2 text-center text-slate-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(setup, index) in feeSetups" :key="setup.id" :class="index % 2 === 0 ? 'bg-white' : 'bg-slate-50'">
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ setup.hostel?.name }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ setup.head?.name }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-center text-slate-800 font-medium">{{ formatMoney(setup.amount) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">{{ setup.store_id }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-sm text-slate-800">••••••••</td>
                                <td class="border border-slate-300 px-1 py-1 text-center">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusClass(setup.status)">
                                        {{ setup.status }}
                                    </span>
                                </td>
                                <td class="border border-slate-300 px-1 py-1 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button @click="editFeeSetup(setup)" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-slate-300 bg-white text-slate-700 hover:bg-slate-50" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteFeeSetup(setup.id)" class="inline-flex h-8 w-8 items-center justify-center rounded-sm border border-red-200 bg-white text-red-700 hover:bg-red-50" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-600">Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} entries</div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page <= 1" @click="loadFeeSetups(meta.current_page - 1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="text-sm font-semibold text-slate-700">{{ meta.current_page }} / {{ meta.last_page }}</div>
                        <button type="button" class="rounded-sm border border-slate-300 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="loading || meta.current_page >= meta.last_page" @click="loadFeeSetups(meta.current_page + 1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @click="closeModal">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-sm bg-white p-6" @click.stop>
                <div class="mb-4 flex justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">{{ editingSetup ? 'Edit' : 'Create' }} Fee Setup</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hostel *</label>
                            <select v-model="form.hostel_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" required>
                                <option value="">Select Hostel</option>
                                <option v-for="hostel in hostels" :key="hostel.id" :value="hostel.id">{{ hostel.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Purpose *</label>
                            <select v-model="form.account_head_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" required>
                                <option value="">Select Purpose</option>
                                <option v-for="(name, id) in accountHeads" :key="id" :value="id">{{ name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Amount *</label>
                            <input v-model="form.amount" type="number" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Store ID *</label>
                            <input v-model="form.store_id" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Store Password *</label>
                            <input v-model="form.store_password" type="password" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Status</label>
                            <select v-model="form.status" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" :disabled="loading" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                            {{ loading ? 'Processing...' : (editingSetup ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'HostelFeeSetup',
    props: ['systems'],
    data() {
        return {
            loading: false,
            feeSetups: [],
            meta: { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
            showModal: false,
            editingSetup: null,
            searchTimer: null,
            filters: {
                pagination: 10,
                field_name: 'hostel_name',
                value: '',
                status: 'active'
            },
            form: {
                status: 'active',
                amount: null,
                hostel_id: null,
                account_head_id: null,
                store_id: '',
                store_password: ''
            }
        }
    },
    computed: {
        hostels() {
            return this.systems?.global?.hostels || []
        },
        accountHeads() {
            const raw = this.systems?.global?.hostel_heads
            if (!raw) return {}
            if (Array.isArray(raw)) {
                return raw.reduce((acc, r) => {
                    if (r && r.id !== undefined) acc[r.id] = r.name
                    return acc
                }, {})
            }
            if (typeof raw === 'object') return raw
            return {}
        }
    },
    watch: {
        'filters.field_name'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.value'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.status'(next, prev) {
            if (String(next || '') !== String(prev || '')) this.scheduleLoad(true)
        },
        'filters.pagination'(next, prev) {
            if (Number(next || 0) !== Number(prev || 0)) this.scheduleLoad(true)
        },
    },
    methods: {
        scheduleLoad(resetPage) {
            if (this.searchTimer) clearTimeout(this.searchTimer)
            this.searchTimer = setTimeout(() => {
                const page = resetPage ? 1 : Number(this.meta?.current_page || 1)
                this.loadFeeSetups(page)
            }, 250)
        },
        async loadFeeSetups(page = 1) {
            this.loading = true
            try {
                const response = await window.axios.get('/admin/hostelFeeSetup', { params: { ...this.filters, page } })
                const payload = response?.data || {}
                this.feeSetups = payload.data || []
                this.meta = {
                    current_page: payload.current_page || 1,
                    last_page: payload.last_page || 1,
                    from: payload.from || 0,
                    to: payload.to || 0,
                    total: payload.total || 0,
                }
            } catch (error) {
                this.$toast.error('Failed to load fee setups')
            } finally {
                this.loading = false
            }
        },
        createFeeSetup() {
            this.editingSetup = null
            this.form = {
                status: 'active',
                amount: null,
                hostel_id: null,
                account_head_id: null,
                store_id: '',
                store_password: ''
            }
            this.showModal = true
        },
        editFeeSetup(setup) {
            this.editingSetup = setup
            this.form = { ...setup }
            this.showModal = true
        },
        async submitForm() {
            this.loading = true
            try {
                if (this.editingSetup) {
                    await window.axios.put(`/admin/hostelFeeSetup/${this.editingSetup.id}`, this.form)
                    this.$toast.success('Fee setup updated successfully')
                } else {
                    await window.axios.post('/admin/hostelFeeSetup', this.form)
                    this.$toast.success('Fee setup created successfully')
                }
                this.closeModal()
                this.loadFeeSetups()
            } catch (error) {
                this.$toast.error('Failed to save fee setup')
            } finally {
                this.loading = false
            }
        },
        async deleteFeeSetup(id) {
            if (confirm('Are you sure you want to delete this fee setup?')) {
                try {
                    await window.axios.delete(`/admin/hostelFeeSetup/${id}`)
                    this.$toast.success('Fee setup deleted successfully')
                    this.loadFeeSetups()
                } catch (error) {
                    this.$toast.error('Failed to delete fee setup')
                }
            }
        },
        closeModal() {
            this.showModal = false
            this.editingSetup = null
        },
        formatMoney(amount) {
            return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
        },
        getStatusClass(status) {
            const classes = {
                active: 'bg-emerald-50 text-emerald-700',
                inactive: 'bg-red-50 text-red-700'
            }
            return classes[status] || 'bg-slate-50 text-slate-700'
        }
    },
    watch: {
        'form.hostel_id': function(id) {
            if (id && this.hostels) {
                let hostel = this.hostels.find(e => e.id == id)
                this.form.amount = hostel?.fees || null
            }
        }
    },
    mounted() {
        this.loadFeeSetups(1)
    }
}
</script>
