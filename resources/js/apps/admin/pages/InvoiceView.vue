<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Invoice</div>
                    <div class="mt-1 text-sm text-slate-600">View</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button
                        v-if="invoice && String(invoice.status) === 'success'"
                        type="button"
                        class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="printing"
                        @click="print"
                    >
                        {{ printing ? '...' : 'PRINT' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class=" border border-slate-300 bg-slate-100 p-5">
            <div class="mx-auto flex max-w-3xl flex-col gap-4">
                <div id="printArea" class="flex flex-col gap-4">
                    <template v-if="invoice">
                        <InvoiceTemplate v-for="i in 2" :key="'inv-' + i" :systems="systems" :data="invoice" height="510px" />
                    </template>
                    <div v-else class="rounded-xl border border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">No data Found</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import InvoiceTemplate from './InvoiceTemplate.vue'

export default {
    name: 'InvoiceView',
    components: { InvoiceTemplate },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        invoiceId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            printing: false,
            loading: false,
            error: '',
            invoice: null,
        }
    },
    created() {
        this.load()
    },
    watch: {
        invoiceId() {
            this.load()
        },
    },
    methods: {
        goBack() {
            window.history.back()
        },
        async load() {
            this.error = ''
            this.invoice = null
            const id = this.invoiceId
            if (!id) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/invoice/${id}`)
                this.invoice = res?.data || null
            } catch (e) {
                this.invoice = null
                this.error = e?.response?.data?.message || 'Failed to load invoice.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'invoice')
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
