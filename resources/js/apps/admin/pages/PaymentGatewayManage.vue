<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Payment Gateway' : 'Create Payment Gateway' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Title</div>
                            <input v-model="form.title" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Department</div>
                            <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Gateway Provider</div>
                            <select v-model="form.provider" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="(label, key) in gatewayProviders" :key="'g-' + key" :value="key">{{ label }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-4">
                            <div class="text-xs font-semibold text-slate-600">Account No</div>
                            <input v-model="form.account_no" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Store ID</div>
                            <input v-model="form.store_id" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Store Password</div>
                            <input v-model="form.store_password" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Description</div>
                            <textarea v-model="form.description" rows="5" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-9 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PaymentGatewayManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        paymentGatewayId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            form: {
                title: '',
                department_id: '',
                provider: '',
                store_id: '',
                store_password: '',
                account_no: '',
                description: '',
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.paymentGatewayId
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        gatewayProviders() {
            return this.systems?.global?.gateway || {}
        },
    },
    async created() {
        if (this.isEdit) {
            await this.load()
        }
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/paymentGateway')
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/paymentGateway/${this.paymentGatewayId}`)
                const row = res?.data?.payment_gateway || res?.data || {}
                this.form.title = row?.title || ''
                this.form.department_id = row?.department_id != null ? String(row.department_id) : ''
                this.form.provider = row?.provider || ''
                this.form.store_id = row?.store_id || ''
                this.form.store_password = row?.store_password || ''
                this.form.account_no = row?.account_no || ''
                this.form.description = row?.description || ''
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load payment gateway.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    title: this.form.title,
                    department_id: this.form.department_id ? Number(this.form.department_id) : null,
                    provider: this.form.provider,
                    store_id: this.form.store_id,
                    store_password: this.form.store_password,
                    account_no: this.form.account_no,
                    description: this.form.description,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/paymentGateway/${this.paymentGatewayId}`, payload)
                } else {
                    res = await window.axios.post('/admin/paymentGateway', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/paymentGateway/${id}/edit`)
                    } else {
                        this.goIndex()
                    }
                }
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Save failed.'
                this.toast(this.error, 'error')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
