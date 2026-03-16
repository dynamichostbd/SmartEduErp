<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Invoice Edit' : 'Invoice Create' }}</div>
                    <div class="mt-1 text-sm text-slate-600">Student Payment</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting" @click="submit">{{ submitting ? '...' : 'Save' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Academic Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <select v-model="form.academic_session_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select Session</option>
                            <option v-for="s in sessionsSorted" :key="'s-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                        </select>
                        <select v-model="form.academic_qualification_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select Academic Level</option>
                            <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                        </select>
                        <select v-model="form.department_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select Department/Group</option>
                            <option v-for="d in filteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                        </select>
                        <select v-model="form.academic_class_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Select Class</option>
                            <option v-for="c in filteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                        </select>

                        <button type="button" class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="loadingStudents" @click="loadStudentsAndFees">
                            {{ loadingStudents ? '...' : 'Search Students' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Payment Info</div>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <select v-model="form.student_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="!students.length">
                            <option value="">Select Student</option>
                            <option v-for="s in students" :key="'st-' + s.id" :value="String(s.id)">{{ studentLabel(s) }}</option>
                        </select>

                        <select v-model="form.account_head_id" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="!fees.length" @change="onFeeSelect">
                            <option value="">Select Payment Type</option>
                            <option v-for="f in fees" :key="'f-' + f.id" :value="String(f.id)">{{ f.name }}</option>
                        </select>

                        <input v-model="form.amount" type="number" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Amount" />
                    </div>
                </div>
            </div>

            <div class="xl:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <div class="mt-3 text-sm text-slate-600">Pending</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'InvoiceCreate',
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
            error: '',
            submitting: false,
            loadingStudents: false,
            loadingInvoice: false,
            suppressAcademicReset: false,
            students: [],
            fees: [],
            form: {
                academic_session_id: '',
                academic_qualification_id: '',
                department_id: '',
                academic_class_id: '',
                account_head_id: '',
                student_id: '',
                payment_gateway_id: '',
                amount: '',
            },
        }
    },
    computed: {
        isEdit() {
            return this.invoiceId != null && String(this.invoiceId) !== ''
        },
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
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualifications() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        filteredDepartments() {
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualifications
                    .filter((r) => String(r.academic_qualification_id) === String(qid))
                    .map((r) => String(r.department_id)),
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        filteredClasses() {
            const qid = this.form?.academic_qualification_id
            if (!qid) return this.classes
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
    },
    watch: {
        invoiceId: function () {
            this.loadInvoice()
        },
        'form.academic_qualification_id': function () {
            if (this.loadingInvoice || this.suppressAcademicReset) return
            this.form.department_id = ''
            this.form.academic_class_id = ''
            this.form.account_head_id = ''
            this.form.student_id = ''
            this.form.payment_gateway_id = ''
            this.fees = []
            this.students = []
        },
    },
    created() {
        this.loadInvoice()
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        goBack() {
            window.history.back()
        },
        studentLabel(s) {
            const cr = s.college_roll ? `(${s.college_roll}) - ` : ''
            return `${cr}${s.name} - (${s.mobile})`
        },
        validateAcademic() {
            if (!this.form.academic_session_id) return 'Session is required'
            if (!this.form.academic_qualification_id) return 'Academic Level is required'
            if (!this.form.department_id) return 'Department is required'
            if (!this.form.academic_class_id) return 'Class is required'
            return ''
        },
        async loadStudentsAndFees() {
            this.error = ''
            const msg = this.validateAcademic()
            if (msg) {
                this.error = msg
                this.toast(msg, 'error')
                return
            }

            this.loadingStudents = true
            try {
                const params = {
                    academic_session_id: this.form.academic_session_id,
                    academic_class_id: this.form.academic_class_id,
                    department_id: this.form.department_id,
                    academic_qualification_id: this.form.academic_qualification_id,
                    allData: true,
                }

                const [studentsRes, feesRes] = await Promise.all([
                    window.axios.get('/admin/student', { params }),
                    window.axios.get('/admin/fees-lists', { params }),
                ])

                this.students = Array.isArray(studentsRes?.data) ? studentsRes.data : []
                this.fees = Array.isArray(feesRes?.data) ? feesRes.data : []
            } catch (e) {
                this.students = []
                this.fees = []
                this.error = e?.response?.data?.message || 'Failed to load students/fees.'
            } finally {
                this.loadingStudents = false
            }
        },
        onFeeSelect() {
            const id = this.form.account_head_id
            const found = (this.fees || []).find((x) => String(x.id) === String(id))
            if (!found) return
            this.form.payment_gateway_id = found.payment_gateway_id != null ? String(found.payment_gateway_id) : ''
            if (found.amount != null && (this.form.amount === '' || this.form.amount == null)) {
                this.form.amount = String(found.amount)
            }
        },
        validateSubmit() {
            const msg = this.validateAcademic()
            if (msg) return msg
            if (!this.form.student_id) return 'Student is required'
            if (!this.form.account_head_id) return 'Payment Type is required'
            if (!this.form.payment_gateway_id) return 'Payment gateway is required'
            if (this.form.amount === '' || this.form.amount == null) return 'Amount is required'
            return ''
        },
        async loadInvoice() {
            if (!this.isEdit) return

            this.loadingInvoice = true
            this.suppressAcademicReset = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/invoice/${this.invoiceId}`)
                const inv = res?.data || null
                if (!inv) return

                this.form.academic_session_id = inv?.academic_session?.id != null ? String(inv.academic_session.id) : ''
                this.form.academic_qualification_id = inv?.qualification?.id != null ? String(inv.qualification.id) : ''
                this.form.department_id = inv?.department?.id != null ? String(inv.department.id) : ''
                this.form.academic_class_id = inv?.academic_class?.id != null ? String(inv.academic_class.id) : ''
                this.form.account_head_id = inv?.head?.id != null ? String(inv.head.id) : ''
                this.form.student_id = inv?.student?.id != null ? String(inv.student.id) : ''
                this.form.payment_gateway_id = inv?.payment_gateway_id != null ? String(inv.payment_gateway_id) : ''
                this.form.amount = inv?.amount != null ? String(inv.amount) : ''

                await this.loadStudentsAndFees()
                if (!this.form.payment_gateway_id) {
                    this.onFeeSelect()
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load invoice.'
            } finally {
                this.suppressAcademicReset = false
                this.loadingInvoice = false
            }
        },
        async submit() {
            this.error = ''
            const msg = this.validateSubmit()
            if (msg) {
                this.error = msg
                this.toast(msg, 'error')
                return
            }

            this.submitting = true
            try {
                if (this.isEdit) {
                    await window.axios.put(`/admin/invoice/${this.invoiceId}`, { ...this.form, update_type: 'info_only' })
                    this.toast('Updated Successfully!', 'success')
                } else {
                    await window.axios.post('/admin/invoice', this.form)
                    this.toast('Create Successfully!', 'success')
                }
                window.history.pushState({}, '', '/admin/invoice')
                window.dispatchEvent(new PopStateEvent('popstate'))
            } catch (e) {
                this.error = e?.response?.data?.message || (this.isEdit ? 'Update failed.' : 'Create failed.')
                this.toast(this.error, 'error')
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
