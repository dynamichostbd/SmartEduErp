<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5 print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admission</div>
                    <div class="mt-1 text-sm text-slate-600">View</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="!admission" @click="goEdit">Edit</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="printing || !admission" @click="print">{{ printing ? '...' : 'PRINT' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800 print:hidden">{{ error }}</div>

        <div class=" border border-slate-300 bg-white p-5">
            <div id="printArea" class="mx-auto flex max-w-3xl flex-col gap-4">
                <InvoiceTemplate v-if="invoiceLike" :systems="systems" :data="invoiceLike" />
                <InvoiceTemplate v-if="invoiceLike" :systems="systems" :data="invoiceLike" />
                <div v-if="!invoiceLike" class="rounded-xl border border-slate-300 bg-slate-50 p-8 text-center text-sm text-slate-600">No data Found</div>
            </div>
        </div>
    </div>
</template>

<script>
import InvoiceTemplate from './InvoiceTemplate.vue'

export default {
    name: 'AdmissionView',
    components: { InvoiceTemplate },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        admissionId: {
            type: [String, Number],
            default: null,
        },
    },
    data() {
        return {
            printing: false,
            loading: false,
            error: '',
            admission: null,
        }
    },
    computed: {
        invoiceLike() {
            const a = this.admission
            if (!a) return null

            return {
                ...a,
                student: {
                    name: a?.name || '',
                    mobile: a?.mobile || '',
                },
                admission_id: a?.admission_roll || a?.admission_id || '',
                college_roll: a?.college_roll || '',
                reg_no: a?.reg_no || '',
                head: a?.head || null,
                department: a?.department || null,
                qualification: a?.qualification || null,
                academic_class: a?.academic_class || null,
                academic_session: a?.academic_session || null,
            }
        },
    },
    created() {
        this.load()
    },
    watch: {
        admissionId() {
            this.load()
        },
    },
    methods: {
        goBack() {
            window.history.back()
        },
        goEdit() {
            if (!this.admissionId) return
            window.history.pushState({}, '', `/admin/admission/${this.admissionId}/edit`)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        async load() {
            this.error = ''
            this.admission = null
            const id = this.admissionId
            if (!id) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/admission/${id}`)
                this.admission = res?.data || null
            } catch (e) {
                this.admission = null
                this.error = e?.response?.data?.message || 'Failed to load admission.'
            } finally {
                this.loading = false
            }
        },
        print() {
            this.printing = true
            const styleEl = document.createElement('style')
            styleEl.setAttribute('data-print-page-style', 'admission')
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
