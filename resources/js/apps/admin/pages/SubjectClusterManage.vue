<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Subject Cluster' : 'Create Subject Cluster' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update subject cluster information' : 'Create a new subject cluster' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Cluster Details</div>
                        <div class="mt-1 text-xs text-slate-500">Level, department/group, subjects and rules</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                            <select v-model="form.academic_qualification_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="q in qualifications" :key="'q-' + q.id" :value="String(q.id)">{{ q.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                            <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Select</option>
                                <option v-for="d in departmentsFiltered" :key="'d-' + d.id" :value="String(d.id)">{{ d.name }}</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Min. Select Subject</div>
                            <input v-model.number="form.minimum_select" type="number" min="0" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Name (en)</div>
                            <input v-model="form.name_en" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Name (bn)</div>
                            <input v-model="form.name_bn" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Fixed Subject Select</div>
                            <select v-model="form.fixed_subject" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="0">No</option>
                                <option :value="1">Yes</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Subjects</div>
                            <select v-model="form.subjects_json" multiple class="mt-1 h-40 w-full rounded-sm border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option v-for="s in subjects" :key="'s-' + s.id" :value="String(s.id)">{{ s.name_bn || s.name_en || s.name }}</option>
                            </select>
                            <div class="mt-1 text-xs text-slate-500">Hold Ctrl/Shift to select multiple.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-9 w-full rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-sm border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectClusterManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        subjectClusterId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            subjects: [],
            form: {
                academic_qualification_id: '',
                department_id: '',
                name_en: '',
                name_bn: '',
                subjects_json: [],
                minimum_select: 0,
                fixed_subject: 0,
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.subjectClusterId
        },
        qualifications() {
            return Array.isArray(this.systems?.global?.academic_qualifications) ? this.systems.global.academic_qualifications : []
        },
        departments() {
            return Array.isArray(this.systems?.global?.departments) ? this.systems.global.departments : []
        },
        departmentQualidactions() {
            return Array.isArray(this.systems?.global?.department_qualidactions) ? this.systems.global.department_qualidactions : []
        },
        departmentsFiltered() {
            const qid = String(this.form.academic_qualification_id || '')
            if (!qid) return this.departments
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => String(m.academic_qualification_id || '') === qid)
                    .map((m) => String(m.department_id || ''))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
    },
    async created() {
        await this.loadSubjects()
        if (this.isEdit) {
            await this.load()
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
            this.go('/admin/subjectCluster')
        },
        parseSubjectsJson(v) {
            if (!v) return []
            if (Array.isArray(v)) return v
            if (typeof v === 'string') {
                try {
                    const out = JSON.parse(v)
                    return Array.isArray(out) ? out : []
                } catch {
                    return []
                }
            }
            return []
        },
        async loadSubjects() {
            try {
                const res = await window.axios.get('/admin/subject', { params: { allData: true } })
                const list = Array.isArray(res?.data) ? res.data : []
                this.subjects = list
            } catch {
                this.subjects = []
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/subjectCluster/${this.subjectClusterId}`)
                const row = res?.data?.subject_cluster || res?.data || {}
                this.form.academic_qualification_id = row?.academic_qualification_id != null ? String(row.academic_qualification_id) : ''
                this.form.department_id = row?.department_id != null ? String(row.department_id) : ''
                this.form.name_en = row?.name_en || ''
                this.form.name_bn = row?.name_bn || ''
                this.form.subjects_json = this.parseSubjectsJson(row?.subjects_json).map((x) => String(x))
                this.form.minimum_select = row?.minimum_select ?? 0
                this.form.fixed_subject = Number(row?.fixed_subject || 0)
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject cluster.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    academic_qualification_id: this.form.academic_qualification_id ? Number(this.form.academic_qualification_id) : null,
                    department_id: this.form.department_id ? Number(this.form.department_id) : null,
                    name_en: this.form.name_en,
                    name_bn: this.form.name_bn,
                    subjects_json: (this.form.subjects_json || []).map((x) => Number(x)),
                    minimum_select: Number(this.form.minimum_select || 0),
                    fixed_subject: this.form.fixed_subject ? 1 : 0,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/subjectCluster/${this.subjectClusterId}`, payload)
                } else {
                    res = await window.axios.post('/admin/subjectCluster', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/subjectCluster/${id}/edit`)
                    } else {
                        this.goIndex()
                    }
                }
            } catch (e) {
                this.error = e?.response?.data?.message || e?.response?.data?.exception || 'Save failed.'
                this.toast(this.error, 'error')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
