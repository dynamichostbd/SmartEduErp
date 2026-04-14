<template>
    <div class="rounded-xl border border-slate-300 bg-white p-4">
        <div class="flex items-start justify-between gap-4 border-b border-slate-300 pb-3">
            <div class="flex items-center gap-3">
                <img v-if="logo" :src="logo" class="h-[60px] w-auto" />
                <div>
                    <div class="text-lg font-semibold text-slate-900">{{ title }}</div>
                </div>
            </div>

            <div class="text-right text-xs text-slate-600">
                <div v-if="data.payment_date"><span class="font-semibold">PAYMENT:</span> {{ data.payment_date }}</div>
                <div><span class="font-semibold">CREATED:</span> {{ data.invoice_date }}</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">{{ data.invoice_number }}</div>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-12">
            <div class="sm:col-span-8 text-sm text-slate-800">
                <div v-if="data.student">
                    <div><span class="font-semibold">Name :</span> {{ data.student.name }}</div>
                    <div><span class="font-semibold">Mobile :</span> {{ data.student.mobile }}</div>
                </div>
                <div v-else-if="data.online_admission">
                    <div><span class="font-semibold">Name :</span> {{ data.online_admission.name }}</div>
                    <div><span class="font-semibold">Mobile :</span> {{ data.online_admission.mobile }}</div>
                </div>

                <div class="mt-2">
                    <span class="font-semibold">Level :</span> {{ data.qualification ? data.qualification.name : '' }}
                    <span v-if="data.academic_session">({{ data.academic_session.name }})</span>
                </div>
                <div>
                    <span class="font-semibold">Dept. :</span> {{ data.department ? data.department.name : '' }}
                    <span v-if="data.academic_class">({{ data.academic_class.name }})</span>
                </div>

                <div v-if="data.student" class="mt-2">
                    <div><span class="font-semibold">Admission Roll :</span> {{ data.admission_id }}</div>
                    <div><span class="font-semibold">College Roll :</span> {{ data.college_roll }}</div>
                    <div><span class="font-semibold">Reg. No :</span> {{ data.reg_no }}</div>
                </div>
                <div v-else-if="data.online_admission" class="mt-2">
                    <div><span class="font-semibold">Admission Roll :</span> {{ data.online_admission.admission_roll }}</div>
                    <div><span class="font-semibold">Reg. No :</span> {{ data.online_admission.registration_no }}</div>
                </div>
            </div>

            <div class="sm:col-span-4 flex items-center justify-center">
                <div v-if="String(data.status) === 'success'" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-center">
                    <div class="text-lg font-extrabold text-emerald-700">PAID</div>
                </div>
                <div v-else class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-center">
                    <div class="text-lg font-extrabold text-amber-700">{{ String(data.status || 'PENDING').toUpperCase() }}</div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center text-base font-semibold text-slate-900">EPAYMENT INVOICE</div>

        <div class="mt-3 overflow-hidden rounded-xl border border-slate-300">
            <table class="w-full border-collapse text-sm">
                <thead class="bg-emerald-100">
                    <tr>
                        <th class="border border-slate-300 px-3 py-2 text-center font-semibold" style="width: 90px">Sl No.</th>
                        <th v-if="hostel" class="border border-slate-300 px-3 py-2 text-left font-semibold">Year</th>
                        <th v-if="hostel" class="border border-slate-300 px-3 py-2 text-left font-semibold">Month</th>
                        <th class="border border-slate-300 px-3 py-2 text-left font-semibold">Description</th>
                        <th class="border border-slate-300 px-3 py-2 text-right font-semibold" style="width: 130px">Amount</th>
                    </tr>
                </thead>

                <slot name="account-heads">
                    <tbody class="bg-white">
                        <tr>
                            <td class="border border-slate-300 px-3 py-2 text-center">01</td>
                            <td v-if="hostel" class="border border-slate-300 px-3 py-2">{{ formatYear(data.invoice_date) }}</td>
                            <td v-if="hostel" class="border border-slate-300 px-3 py-2">{{ formatMonth(data.invoice_date) }}</td>
                            <td class="border border-slate-300 px-3 py-2">{{ data.head ? data.head.name : '' }}</td>
                            <td class="border border-slate-300 px-3 py-2 text-right font-semibold">{{ money(data.amount) }}</td>
                        </tr>
                    </tbody>
                </slot>
            </table>
        </div>

        <div class="mt-3 text-xs text-slate-700"><span class="font-semibold">Inword :</span> {{ inWordsText }}</div>

        <div class="mt-6 text-center text-xs text-slate-500">Powered By Dynamic Host BD</div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceTemplate',
    props: {
        data: {
            type: Object,
            default: () => ({}),
        },
        systems: {
            type: Object,
            default: () => ({}),
        },
        hostel: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        title() {
            return this.systems?.site?.title || ''
        },
        logo() {
            return this.systems?.site?.logo || ''
        },
        inWordsText() {
            const a = Number(this.data?.amount || 0)
            if (!Number.isFinite(a)) return ''
            return `${a.toFixed(2)} taka only`
        },
    },
    methods: {
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
