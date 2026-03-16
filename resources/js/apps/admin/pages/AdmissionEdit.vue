<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Admission</div>
                    <div class="mt-1 text-sm text-slate-600">Edit</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting || !admissionId" @click="submit">
                        {{ submitting ? '...' : 'Paid & Update Info' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div v-if="loading" class="py-10 text-center text-sm text-slate-600">Loading...</div>

            <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-12 border-b border-slate-200 pb-3 text-sm text-slate-700">
                    <span class="font-semibold">Invoice No:</span> <span class="font-semibold text-slate-900">{{ form.invoice_number }}</span>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Student Name</div>
                    <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Admission Roll</div>
                    <input v-model="form.admission_roll" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Mobile</div>
                    <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Amount</div>
                    <input v-model="form.amount" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Purpose</div>
                    <select v-model="form.account_head_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select Any--</option>
                        <option v-for="p in purposes" :key="'p-' + p.id" :value="String(p.account_head_id)">
                            {{ p?.head?.name || '' }}
                        </option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Session</div>
                    <select v-model="form.academic_session_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <select v-model="form.academic_class_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">--Select--</option>
                        <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Payment Date</div>
                    <input v-model="form.payment_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Refund Amount</div>
                    <div class="mt-2 flex items-center gap-2">
                        <input id="refunded" v-model="refunded" type="checkbox" class="h-4 w-4" />
                        <label for="refunded" class="text-sm text-slate-700">Are you sure?</label>
                    </div>
                </div>

                <div v-if="refunded" class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Refund Amount</div>
                    <input v-model="form.refund_amount" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @input="onRefundChange" />
                </div>

                <div v-if="refunded" class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Refund Date</div>
                    <input v-model="form.refund_date" type="date" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                </div>

                <div v-if="refunded" class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Refund Note</div>
                    <textarea v-model="form.refund_note" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdmissionEdit',
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
            loading: false,
            submitting: false,
            error: '',
            purposes: [],
            refunded: false,
            totalAmountBeforeRefund: 0,
            form: {
                invoice_number: '',
                name: '',
                admission_roll: '',
                mobile: '',
                amount: '',
                status: 'success',
                account_head_id: '',
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                payment_date: this.today(),
                refund_amount: '',
                refund_date: this.today(),
                refund_note: '',
            },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = [...this.sessions]
            const sortKey = (s) => {
                const name = String(s?.name || '').trim()
                const m = name.match(/(19|20)\d{2}/)
                return m ? Number(m[0]) : 0
            }
            return list.sort((a, b) => sortKey(b) - sortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qId = this.form.academic_qualification_id
            if (!qId) return this.departments

            const dqs = this.systems?.global?.department_qualidactions
            const allowed = Array.isArray(dqs) ? dqs.filter((x) => String(x?.academic_qualification_id) === String(qId)).map((x) => String(x?.department_id)) : []
            if (!allowed.length) return this.departments

            return this.departments.filter((d) => allowed.includes(String(d?.id)))
        },
        filteredClasses() {
            const qId = this.form.academic_qualification_id
            if (!qId) return this.classes
            return this.classes.filter((c) => String(c?.academic_qualification_id) === String(qId))
        },
    },
    created() {
        this.load()
        this.loadPurposes()
    },
    watch: {
        admissionId() {
            this.load()
        },
    },
    methods: {
        today() {
            return new Date().toISOString().slice(0, 10)
        },
        goBack() {
            window.history.back()
        },
        onRefundChange() {
            const refund = Number(this.form.refund_amount || 0) || 0
            const total = Number(this.totalAmountBeforeRefund || 0) || 0
            const next = total - refund
            if (Number.isFinite(next)) {
                this.form.amount = String(next)
            }
        },
        async loadPurposes() {
            try {
                const res = await window.axios.get('/admin/admission-purposes')
                this.purposes = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.purposes = []
            }
        },
        async load() {
            this.error = ''
            if (!this.admissionId) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/admission/${this.admissionId}`)
                const a = res?.data || null
                if (!a) return

                const today = this.today()
                const currentRefund = Number(a?.refund_amount || 0) || 0
                const amount = Number(a?.amount || 0) || 0

                this.totalAmountBeforeRefund = amount + currentRefund
                this.refunded = currentRefund > 0

                this.form.invoice_number = a?.invoice_number || ''
                this.form.name = a?.name || ''
                this.form.admission_roll = a?.admission_roll || ''
                this.form.mobile = a?.mobile || ''
                this.form.amount = String(amount)
                this.form.status = 'success'
                this.form.account_head_id = a?.head?.id != null ? String(a.head.id) : ''
                this.form.academic_session_id = a?.academic_session?.id != null ? String(a.academic_session.id) : ''
                this.form.academic_qualification_id = a?.qualification?.id != null ? String(a.qualification.id) : ''
                this.form.department_id = a?.department?.id != null ? String(a.department.id) : ''
                this.form.academic_class_id = a?.academic_class?.id != null ? String(a.academic_class.id) : ''
                this.form.payment_date = a?.payment_date || today
                this.form.refund_amount = currentRefund ? String(currentRefund) : ''
                this.form.refund_date = a?.refund_date || today
                this.form.refund_note = a?.refund_note || ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load admission.'
            } finally {
                this.loading = false
            }
        },
        validate() {
            if (!this.form.name) return 'Student Name is required'
            if (!this.form.mobile) return 'Mobile is required'
            if (!this.form.amount) return 'Amount is required'
            if (!this.form.account_head_id) return 'Purpose is required'
            if (!this.form.academic_session_id) return 'Session is required'
            if (!this.form.academic_qualification_id) return 'Academic Level is required'
            if (!this.form.department_id) return 'Department is required'
            if (!this.form.academic_class_id) return 'Class is required'
            return ''
        },
        async submit() {
            this.error = ''
            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            if (!confirm('Are you sure want to update?')) return

            this.submitting = true
            try {
                const payload = {
                    name: this.form.name,
                    mobile: this.form.mobile,
                    admission_roll: this.form.admission_roll,
                    amount: Number(this.form.amount),
                    status: 'success',
                    account_head_id: Number(this.form.account_head_id),
                    academic_session_id: Number(this.form.academic_session_id),
                    academic_qualification_id: Number(this.form.academic_qualification_id),
                    department_id: Number(this.form.department_id),
                    academic_class_id: Number(this.form.academic_class_id),
                    payment_date: this.form.payment_date,
                    refund_amount: this.refunded ? Number(this.form.refund_amount || 0) : 0,
                    refund_date: this.refunded ? this.form.refund_date : null,
                    refund_note: this.refunded ? this.form.refund_note : null,
                }

                await window.axios.put(`/admin/admission/${this.admissionId}`, payload)

                window.history.pushState({}, '', `/admin/admission/${this.admissionId}`)
                window.dispatchEvent(new PopStateEvent('popstate'))
            } catch (e) {
                this.error = e?.response?.data?.message || 'Update failed.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
