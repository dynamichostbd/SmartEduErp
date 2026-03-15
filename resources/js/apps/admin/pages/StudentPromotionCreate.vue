<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Student Promotion</div>
                    <div class="mt-1 text-sm text-slate-600">Search students and promote to new class</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="loading || promoting"
                        @click="goBack"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="loading || promoting"
                        @click="promote"
                    >
                        {{ promoting ? 'Processing...' : 'Promote' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="message" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="text-sm font-semibold text-slate-900">Search Promoted Class</div>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div>
                    <div class="text-xs font-semibold text-slate-600">Academic Session <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Academic Level <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Department/Group <span class="text-red-600">*</span></div>
                    <select v-model="search.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="d in searchFilteredDepartments" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Academic Class <span class="text-red-600">*</span></div>
                    <select v-model="search.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="c in searchFilteredClasses" :key="'c-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>

                <div>
                    <div class="text-xs font-semibold text-slate-600">Student Type <span class="text-red-600">*</span></div>
                    <select v-model="search.student_type" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                        <option value="">Select</option>
                        <option v-for="t in studentTypes" :key="'t-' + t" :value="t">{{ t }}</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        class="h-10 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                        :disabled="loading"
                        @click="searchStudents"
                    >
                        {{ loading ? 'Searching...' : 'Search' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm font-semibold text-slate-900">Student List</div>
                <div v-if="students.length" class="text-xs text-slate-500">Selected: {{ selectedCount }}/{{ students.length }}</div>
            </div>

            <div v-if="students.length" class="mt-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">New Academic Session <span class="text-red-600">*</span></div>
                        <select v-model="promotion.academic_session_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="s in sessionsSorted" :key="'nses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">New Academic Level <span class="text-red-600">*</span></div>
                        <select v-model="promotion.academic_qualification_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="q in qualifications" :key="'nq-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">New Department/Group <span class="text-red-600">*</span></div>
                        <select v-model="promotion.department_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="d in promoFilteredDepartments" :key="'nd-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">New Class <span class="text-red-600">*</span></div>
                        <select v-model="promotion.academic_class_id" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" required>
                            <option value="">Select</option>
                            <option v-for="c in promoFilteredClasses" :key="'nc-' + c.id" :value="String(c.id)">{{ c.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">
                                    <input type="checkbox" v-model="checkAll" />
                                </th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Student</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Software ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">College Roll</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Reg No.</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">New Admission ID</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">New Reg No.</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">New Student Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="s in students" :key="'std-' + s.id" :class="s.check ? '' : 'bg-slate-50'">
                                <td class="px-3 py-2"><input type="checkbox" v-model="s.check" /></td>
                                <td class="px-3 py-2 text-slate-900">{{ s.name }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ s.student_id }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ s.admission_id }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ s.college_roll }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ s.reg_no }}</td>
                                <td class="px-3 py-2">
                                    <input v-model="s.new_admission_id" type="text" class="h-9 w-44 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="New Admission ID" />
                                </td>
                                <td class="px-3 py-2">
                                    <input v-model="s.new_reg_no" type="text" class="h-9 w-44 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="New Reg No." />
                                </td>
                                <td class="px-3 py-2">
                                    <select v-model="s.student_type" class="h-9 w-44 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                        <option value="">Select</option>
                                        <option v-for="t in studentTypes" :key="'st-' + t" :value="t">{{ t }}</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5 text-sm text-slate-600">
                {{ loading ? 'Loading...' : 'No students found' }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentPromotionCreate',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            loading: false,
            promoting: false,
            error: '',
            message: '',
            students: [],
            checkAll: false,
            search: {
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
                student_type: 'Regular',
            },
            promotion: {
                academic_class_id: '',
                academic_session_id: '',
                department_id: '',
                academic_qualification_id: '',
            },
        }
    },
    computed: {
        selectedCount() {
            return (this.students || []).filter((s) => !!s.check).length
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessions() {
            const list = this.systems?.global?.academic_sessions
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departments() {
            const list = this.systems?.global?.departments
            if (!Array.isArray(list)) return []
            return list.filter((r) => r?.status === undefined || r?.status === null || String(r.status) === 'active' || String(r.status) === '1')
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions || this.systems?.global?.department_qualifications
            return Array.isArray(list) ? list : []
        },
        studentTypes() {
            const list = this.systems?.global?.student_types
            return Array.isArray(list) ? list : []
        },
        searchFilteredDepartments() {
            const qid = this.search?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        searchFilteredClasses() {
            const qid = this.search?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
        promoFilteredDepartments() {
            const qid = this.promotion?.academic_qualification_id
            if (!qid) return []
            const allowed = new Set(this.departmentQualidactions.filter((r) => String(r.academic_qualification_id) === String(qid)).map((r) => String(r.department_id)))
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        promoFilteredClasses() {
            const qid = this.promotion?.academic_qualification_id
            if (!qid) return []
            return this.classes.filter((c) => String(c.academic_qualification_id) === String(qid))
        },
    },
    watch: {
        checkAll(val) {
            ;(this.students || []).forEach((s) => {
                s.check = !!val
            })
        },
        'search.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.search.department_id = ''
                this.search.academic_class_id = ''
            }
        },
        'promotion.academic_qualification_id'(next, prev) {
            if (String(next || '') !== String(prev || '')) {
                this.promotion.department_id = ''
                this.promotion.academic_class_id = ''
            }
        },
    },
    methods: {
        sessionSortKey(s) {
            const name = String(s?.name || '')
            const m = name.match(/(\d{4})\s*[-/]\s*(\d{4})/)
            if (m) {
                const start = Number(m[1] || 0)
                const end = Number(m[2] || 0)
                return start * 10000 + end
            }
            const n = Number(name)
            if (!Number.isNaN(n) && Number.isFinite(n)) return n
            const id = Number(s?.id || 0)
            return Number.isFinite(id) ? id : 0
        },
        goBack() {
            window.location.href = '/admin/student'
        },
        async searchStudents() {
            this.loading = true
            this.error = ''
            this.message = ''
            try {
                for (const k of ['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id', 'student_type']) {
                    if (!this.search[k]) {
                        this.error = 'Please select search criteria.'
                        return
                    }
                }

                const res = await window.axios.post('/admin/studentPromotion/search', this.search)
                const rows = Array.isArray(res?.data) ? res.data : []

                this.students = rows.map((s) => ({
                    ...s,
                    check: true,
                    new_reg_no: s?.reg_no ?? '',
                    new_admission_id: s?.admission_id ?? '',
                }))
                this.checkAll = this.students.length > 0

                this.promotion.academic_session_id = this.search.academic_session_id
                this.promotion.academic_qualification_id = ''
                this.promotion.department_id = ''
                this.promotion.academic_class_id = ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to search students.'
                this.students = []
                this.checkAll = false
            } finally {
                this.loading = false
            }
        },
        async promote() {
            this.promoting = true
            this.error = ''
            this.message = ''
            try {
                if (!this.students.length) {
                    this.error = 'Please search students first.'
                    return
                }

                const selected = this.students.filter((s) => !!s.check)
                if (!selected.length) {
                    this.error = 'Please select student.'
                    return
                }

                for (const k of ['academic_session_id', 'academic_qualification_id', 'department_id', 'academic_class_id']) {
                    if (!this.promotion[k]) {
                        this.error = 'Please select new promotion criteria.'
                        return
                    }
                }

                if (String(this.search.academic_qualification_id) === String(this.promotion.academic_qualification_id) && String(this.search.academic_class_id) === String(this.promotion.academic_class_id)) {
                    this.error = 'You cannot promote same qualification and class.'
                    return
                }

                const irregularId = this.students.filter((s) => !s.check).map((s) => s.id)

                const payload = {
                    ...this.promotion,
                    students: selected.map((s) => ({
                        id: s.id,
                        new_reg_no: s.new_reg_no,
                        new_admission_id: s.new_admission_id,
                        student_type: s.student_type,
                    })),
                    irregularId,
                }

                const res = await window.axios.post('/admin/studentPromotion', payload)
                this.message = res?.data?.message || 'Promoted Successfully!'
                this.students = []
                this.checkAll = false
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to promote students.'
            } finally {
                this.promoting = false
            }
        },
    },
}
</script>
