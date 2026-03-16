<template>
    <div class="mx-auto max-w-2xl">
        <div class="flex flex-col gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="truncate text-xl font-semibold text-slate-900">Recharge SMS Balance</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                        <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : 'Pay Now' }}</button>
                    </div>
                </div>
            </div>

            <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
            <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-12 items-center gap-3">
                        <div class="col-span-5 text-sm font-semibold text-slate-700">Invoice Date :</div>
                        <div class="col-span-7">
                            <input v-model="invoiceDate" readonly type="text" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm outline-none" />
                        </div>
                    </div>

                    <div class="grid grid-cols-12 items-center gap-3">
                        <div class="col-span-5 text-sm font-semibold text-slate-700">Recharge Amount :</div>
                        <div class="col-span-7">
                            <input v-model.number="form.amount" type="number" min="0" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Recharge Amount" />
                        </div>
                    </div>

                    <div class="pt-4 text-center">
                        <button type="button" class="h-9 rounded-lg bg-emerald-600 px-8 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? 'processing..' : 'PAY NOW' }}</button>
                    </div>

                    <div class="text-center text-xs text-slate-500">Note: Payment gateway integration is not enabled in this build yet. This will create a pending invoice.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SmsTransactionManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            invoiceDate: new Date().toISOString().slice(0, 10),
            form: {
                amount: 0,
            },
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
            this.go('/admin/smsTransaction')
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                if (!this.form.amount || Number(this.form.amount) < 100) {
                    this.error = 'Minimum amount is 100'
                    this.toast(this.error, 'error')
                    return
                }

                const res = await window.axios.post('/admin/smsTransaction', { amount: Number(this.form.amount) })
                const msg = res?.data?.message || 'Invoice create successfully'
                this.success = msg
                this.toast(msg, 'success')
                this.goIndex()
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Failed to create invoice.'
                this.toast(this.error, 'error')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
