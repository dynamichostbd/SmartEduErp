<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Invoice Print</div>
                    <div class="mt-1 text-sm text-slate-600">Multiple</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !rows.length" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div id="printArea" class="mx-auto flex max-w-3xl flex-col gap-6">
                <template v-for="(item, idx) in rows" :key="'p-' + idx">
                    <InvoiceTemplate :systems="systems" :data="item" />
                    <div class="h-4"></div>
                </template>

                <div v-if="!rows.length" class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-600">No data Found</div>
            </div>
        </div>
    </div>
</template>

<script>
import InvoiceTemplate from './InvoiceTemplate.vue'

export default {
    name: 'InvoicePrint',
    components: { InvoiceTemplate },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            printing: false,
            loading: false,
            error: '',
            rows: [],
        }
    },
    created() {
        this.load()
    },
    methods: {
        goBack() {
            window.history.back()
        },
        parseQuery() {
            const params = new URLSearchParams(window.location.search || '')
            const out = {}
            for (const [k, v] of params.entries()) {
                out[k] = v
            }
            return out
        },
        async load() {
            this.loading = true
            this.error = ''
            this.rows = []
            try {
                const q = this.parseQuery()
                const res = await window.axios.post('/admin/get-print-invoice', q)
                this.rows = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.rows = []
                this.error = e?.response?.data?.message || 'Failed to load invoices.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'invoice-print')
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
