<template>
    <div class="flex flex-col gap-4">
        <div class="border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Hostel Payment</div>
                    <div class="mt-1 text-sm text-slate-600">View</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !data" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class="border border-slate-300 bg-white p-5">
            <div id="printArea" class="mx-auto flex max-w-3xl flex-col gap-4">
                <InvoiceTemplate v-if="data" :systems="systems" :data="data" :hostel="true">
                    <template #account-heads>
                        <tbody v-if="Array.isArray(data.details) && data.details.length" class="bg-white">
                            <tr v-for="(fees, key) in data.details" :key="fees.id">
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ key + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ formatYear(fees.invoice_date) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ formatMonth(fees.invoice_date) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ fees.head?.name || '' }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(fees.amount) }}</td>
                            </tr>

                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Sub-Total =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(totalAmount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Discount =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(data.discount_amount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Total =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(data.amount) }}</td>
                            </tr>
                        </tbody>
                    </template>
                </InvoiceTemplate>

                <InvoiceTemplate v-if="data" :systems="systems" :data="data" :hostel="true">
                    <template #account-heads>
                        <tbody v-if="Array.isArray(data.details) && data.details.length" class="bg-white">
                            <tr v-for="(fees, key) in data.details" :key="'2-' + fees.id">
                                <td class="border border-slate-300 px-3 py-2 text-center">{{ key + 1 }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ formatYear(fees.invoice_date) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ formatMonth(fees.invoice_date) }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ fees.head?.name || '' }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(fees.amount) }}</td>
                            </tr>

                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Sub-Total =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(totalAmount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Discount =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(data.discount_amount) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="border border-slate-300 px-3 py-2 text-right font-semibold">Total =</td>
                                <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(data.amount) }}</td>
                            </tr>
                        </tbody>
                    </template>
                </InvoiceTemplate>

                <div v-if="!data" class="rounded-xl border border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">No data Found</div>
            </div>
        </div>
    </div>
</template>

<script>
import InvoiceTemplate from './InvoiceTemplate.vue'

export default {
    name: 'HostelPaymentView',
    components: { InvoiceTemplate },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        hostelPaymentId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            printing: false,
            loading: false,
            error: '',
            data: null,
        }
    },
    computed: {
        totalAmount() {
            if (!this.data) return 0
            return Number(this.data.amount || 0) + Number(this.data.discount_amount || 0)
        },
    },
    created() {
        this.load()
    },
    watch: {
        hostelPaymentId() {
            this.load()
        },
    },
    methods: {
        goBack() {
            window.history.back()
        },
        async load() {
            this.error = ''
            this.data = null
            const id = this.hostelPaymentId
            if (!id) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/hostelPayment/${id}`)
                this.data = res?.data || null
            } catch (e) {
                this.data = null
                this.error = e?.response?.data?.message || 'Failed to load invoice.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'hostel-invoice')
            styleEl.textContent = '@media print{@page{size:A4;margin:10mm;}}'
            document.head.appendChild(styleEl)
            this.$nextTick(() => {
                window.print()
                setTimeout(() => {
                    styleEl.remove()
                    this.printing = false
                }, 500)
            })
        },
        money(v) {
            const n = Number(v || 0)
            const val = Number.isFinite(n) ? n : 0
            return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        },
        formatMonth(date) {
            if (!date) return ''
            return new Date(date).toLocaleDateString('en-US', { month: 'long' })
        },
        formatYear(date) {
            if (!date) return ''
            return new Date(date).getFullYear()
        },
    },
}
</script>

<style scoped>
@media print {
    table,
    th,
    td {
        font-size: 12px !important;
    }
}
</style>
