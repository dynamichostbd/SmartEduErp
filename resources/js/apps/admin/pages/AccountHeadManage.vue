<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Account Head' : 'Create Account Head' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-9">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                            <div class="mt-2 flex flex-wrap gap-2 rounded-xl border border-slate-200 p-3">
                                <label v-for="q in qualifications" :key="'q-' + q.id" class="flex items-center gap-2 text-sm text-slate-700">
                                    <input v-model="form.academic_qualification_ids" type="checkbox" class="h-4 w-4" :value="Number(q.id)" />
                                    <span>{{ q.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Department/Group</div>
                            <div class="mt-2 flex flex-wrap gap-2 rounded-xl border border-slate-200 p-3">
                                <label v-for="d in departmentsFiltered" :key="'d-' + d.id" class="flex items-center gap-2 text-sm text-slate-700">
                                    <input v-model="form.department_ids" type="checkbox" class="h-4 w-4" :value="Number(d.id)" />
                                    <span>{{ d.name }}</span>
                                </label>
                                <div v-if="departmentsFiltered.length === 0" class="text-sm text-slate-500">Select academic level first.</div>
                            </div>
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Class</div>
                            <div class="mt-2 flex flex-wrap gap-2 rounded-xl border border-slate-200 p-3">
                                <label v-for="c in classesFiltered" :key="'c-' + c.id" class="flex items-center gap-2 text-sm text-slate-700">
                                    <input v-model="form.academic_class_ids" type="checkbox" class="h-4 w-4" :value="Number(c.id)" />
                                    <span>{{ c.name }}</span>
                                </label>
                                <div v-if="classesFiltered.length === 0" class="text-sm text-slate-500">Select academic level first.</div>
                            </div>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name (en)</div>
                            <input v-model="form.name_en" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Head Type</div>
                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-700">
                                <label class="flex items-center gap-2"><input v-model="form.type" type="radio" class="h-4 w-4" value="college" /> <span>College</span></label>
                                <label class="flex items-center gap-2"><input v-model="form.type" type="radio" class="h-4 w-4" value="admission" /> <span>Application Fee</span></label>
                                <label class="flex items-center gap-2"><input v-model="form.type" type="radio" class="h-4 w-4" value="hostel" /> <span>Hostel</span></label>
                                <label class="flex items-center gap-2"><input v-model="form.type" type="radio" class="h-4 w-4" value="certificate" /> <span>Certificate</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-10 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AccountHeadManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        accountHeadId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            form: {
                name: '',
                name_en: '',
                type: 'college',
                status: 'active',
                academic_qualification_ids: [],
                department_ids: [],
                academic_class_ids: [],
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.accountHeadId
        },
        qualifications() {
            const list = this.systems?.global?.academic_qualifications
            return Array.isArray(list) ? list : []
        },
        departments() {
            const list = this.systems?.global?.departments
            return Array.isArray(list) ? list : []
        },
        departmentQualidactions() {
            const list = this.systems?.global?.department_qualidactions
            return Array.isArray(list) ? list : []
        },
        classes() {
            const list = this.systems?.global?.academic_classes
            return Array.isArray(list) ? list : []
        },
        departmentsFiltered() {
            const ids = new Set((this.form.academic_qualification_ids || []).map((x) => String(x)))
            if (ids.size === 0) return []
            const allowed = new Set(
                this.departmentQualidactions
                    .filter((m) => ids.has(String(m.academic_qualification_id)))
                    .map((m) => String(m.department_id))
            )
            return this.departments.filter((d) => allowed.has(String(d.id)))
        },
        classesFiltered() {
            const ids = new Set((this.form.academic_qualification_ids || []).map((x) => String(x)))
            if (ids.size === 0) return []
            return this.classes.filter((c) => ids.has(String(c.academic_qualification_id)))
        },
    },
    watch: {
        'form.academic_qualification_ids'() {
            // reset dependent selections if they become invalid
            const allowedDepts = new Set(this.departmentsFiltered.map((d) => Number(d.id)))
            this.form.department_ids = (this.form.department_ids || []).filter((id) => allowedDepts.has(Number(id)))

            const allowedClasses = new Set(this.classesFiltered.map((c) => Number(c.id)))
            this.form.academic_class_ids = (this.form.academic_class_ids || []).filter((id) => allowedClasses.has(Number(id)))
        },
    },
    async created() {
        if (this.isEdit) {
            await this.load()
        }
    },
    methods: {
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        parseIds(v) {
            if (Array.isArray(v)) return v.map((x) => Number(x)).filter((x) => Number.isFinite(x))
            if (!v) return []
            if (typeof v === 'string') {
                try {
                    const d = JSON.parse(v)
                    if (Array.isArray(d)) return d.map((x) => Number(x)).filter((x) => Number.isFinite(x))
                } catch {
                    return []
                }
            }
            return []
        },
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/accountHead')
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/accountHead/${this.accountHeadId}`)
                const row = res?.data?.account_head || res?.data || {}
                this.form.name = row?.name || ''
                this.form.name_en = row?.name_en || ''
                this.form.type = row?.type || 'college'
                this.form.status = row?.status || 'active'
                this.form.academic_qualification_ids = this.parseIds(row?.academic_qualification_ids)
                this.form.department_ids = this.parseIds(row?.department_ids)
                this.form.academic_class_ids = this.parseIds(row?.academic_class_ids)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load account head.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    name: this.form.name,
                    name_en: this.form.name_en,
                    type: this.form.type,
                    status: this.form.status,
                    academic_qualification_ids: this.form.academic_qualification_ids,
                    department_ids: this.form.department_ids,
                    academic_class_ids: this.form.academic_class_ids,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/accountHead/${this.accountHeadId}`, payload)
                } else {
                    res = await window.axios.post('/admin/accountHead', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/accountHead/${id}/edit`)
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
