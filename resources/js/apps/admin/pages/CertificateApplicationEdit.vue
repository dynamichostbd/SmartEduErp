<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Certificate Application</div>
                    <div class="mt-1 text-sm text-slate-600">Edit application (legacy fields)</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="submitting" @click="goBack">Back</button>
                    <button type="submit" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="submitting">
                        {{ submitting ? 'Processing...' : 'Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="message" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ message }}</div>
        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-6  border border-slate-300 bg-white p-5">
                <div class="text-sm font-semibold text-slate-900">English</div>
                <div class="mt-4 grid grid-cols-1 gap-3">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Certificate Type</div>
                        <div class="mt-2 flex items-center gap-4 text-sm">
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_type" type="radio" value="en" /> English</label>
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_type" type="radio" value="bn" /> Bangla</label>
                            <label class="inline-flex items-center gap-2"><input v-model="data.certificate_type" type="radio" value="both" /> Both</label>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Mobile</div>
                        <input v-model="data.mobile" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Student name (EN)</div>
                        <input v-model="data.student_name_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Father's name (EN)</div>
                        <input v-model="data.fathers_name_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Mother's name (EN)</div>
                        <input v-model="data.mothers_name_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Academic year (EN)</div>
                        <input v-model="data.academic_year_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Registration no (EN)</div>
                        <input v-model="data.registration_no_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Exam roll (EN)</div>
                        <input v-model="data.exam_roll_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Exam year (EN)</div>
                        <input v-model="data.exam_year_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">GPA (EN)</div>
                        <input v-model="data.gpa_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">College Roll (EN)</div>
                        <input v-model="data.college_roll_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Address (EN)</div>
                        <textarea v-model="data.address_en" :disabled="enDisable" rows="3" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Division (EN)</div>
                        <input v-model="data.division_en" :disabled="enDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6  border border-slate-300 bg-white p-5">
                <div class="text-sm font-semibold text-slate-900">Bangla</div>
                <div class="mt-4 grid grid-cols-1 gap-3">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Session</div>
                        <select v-model="data.academic_session_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">Select</option>
                            <option v-for="s in sessionsSorted" :key="'ses-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                        <select v-model="data.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option value="">Select</option>
                            <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Certificate</div>
                        <select v-model="data.certificate_template_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm">
                            <option :value="''">--Select Certificate--</option>
                            <option v-for="t in certificateTemplates" :key="'t-' + t.id" :value="String(t.id)">{{ t.title }}</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Student name (BN)</div>
                        <input v-model="data.student_name_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Father's name (BN)</div>
                        <input v-model="data.fathers_name_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Mother's name (BN)</div>
                        <input v-model="data.mothers_name_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Academic year (BN)</div>
                        <input v-model="data.academic_year_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Registration no (BN)</div>
                        <input v-model="data.registration_no_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Exam roll (BN)</div>
                        <input v-model="data.exam_roll_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Exam year (BN)</div>
                        <input v-model="data.exam_year_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">GPA (BN)</div>
                        <input v-model="data.gpa_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">College Roll (BN)</div>
                        <input v-model="data.college_roll_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Address (BN)</div>
                        <textarea v-model="data.address_bn" :disabled="bnDisable" rows="3" class="mt-1 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Division (BN)</div>
                        <input v-model="data.division_bn" :disabled="bnDisable" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" />
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'CertificateApplicationEdit',
    props: {
        applicationId: {
            type: [String, Number],
            required: true,
        },
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            submitting: false,
            error: '',
            message: '',
            templates: [],
            enDisable: false,
            bnDisable: true,
            data: { certificate_type: '' },
        }
    },
    computed: {
        sessions() {
            const list = this.systems?.global?.academic_sessions
            return Array.isArray(list) ? list : []
        },
        sessionsSorted() {
            const list = Array.isArray(this.sessions) ? [...this.sessions] : []
            return list.sort((a, b) => this.sessionSortKey(b) - this.sessionSortKey(a))
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        certificateTemplates() {
            const qid = this.data?.academic_qualification_id
            if (!qid) return []
            return (this.templates || []).filter((t) => String(t.academic_qualification_id) === String(qid))
        },
    },
    watch: {
        'data.certificate_type'(type) {
            this.enDisable = type === 'en' ? false : true
            this.bnDisable = type === 'bn' ? false : true
            if (type === 'both') {
                this.enDisable = false
                this.bnDisable = false
            }
        },
    },
    mounted() {
        this.loadTemplates()
        this.load()
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
            window.location.href = '/admin/certificateApplication'
        },
        async loadTemplates() {
            try {
                const res = await window.axios.get('/admin/certificateTemplate/list', { params: { allData: true } })
                this.templates = Array.isArray(res?.data) ? res.data : []
            } catch (e) {
                this.error = 'Failed to load templates.'
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/certificateApplication/${this.applicationId}/details`)
                this.data = res?.data || this.data
                if (!this.data.certificate_type) {
                    this.data.certificate_type = 'en'
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load application.'
            }
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.put(`/admin/certificateApplication/${this.applicationId}`, this.data)
                this.message = res?.data?.message || 'Update Successfully!'
                setTimeout(() => {
                    window.location.href = '/admin/certificateApplication'
                }, 300)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
