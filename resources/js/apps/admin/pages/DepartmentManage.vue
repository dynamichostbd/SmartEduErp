<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Department' : 'Create Department' }}</div>
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
            <div class="lg:col-span-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Department Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Description</div>
                            <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold text-slate-600">Is Registration?</div>
                                <select v-model="form.registration" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </select>
                            </div>

                            <div>
                                <div class="text-xs font-semibold text-slate-600">Application Fee?</div>
                                <select v-model="form.application_fee" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option :value="1">Yes</option>
                                    <option :value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div class="text-sm font-semibold text-slate-900">Academic Level</div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <input v-model="checkAll" type="checkbox" class="h-4 w-4" />
                            Check all
                        </label>
                    </div>

                    <div class="max-h-[420px] overflow-y-auto p-5">
                        <div v-if="!deptQuals.length" class="text-sm text-slate-500">No academic level list found.</div>
                        <div v-else class="space-y-2">
                            <div v-for="q in deptQuals" :key="'dq-' + q.academic_qualification_id" class="grid grid-cols-12 items-center gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                <label class="col-span-7 flex items-center gap-3 text-sm font-semibold text-slate-800">
                                    <input v-model="q.checked" type="checkbox" class="h-4 w-4" />
                                    <span class="min-w-0 truncate">{{ q.name }}</span>
                                </label>

                                <input v-model="q.department_code" type="text" class="col-span-5 h-9 rounded-lg border border-slate-200 bg-white px-3 text-sm text-center outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Department Code" :disabled="!q.checked" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
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
    name: 'DepartmentManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        departmentId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            checkAll: false,
            deptQuals: [],
            form: {
                name: '',
                description: '',
                registration: 0,
                application_fee: 0,
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.departmentId
        },
        qualifications() {
            const q = this.systems?.global?.academic_qualifications
            return Array.isArray(q) ? q : []
        },
    },
    watch: {
        checkAll(val) {
            for (const x of this.deptQuals) {
                x.checked = !!val
            }
        },
    },
    created() {
        this.resetDeptQuals([])
        if (this.isEdit) this.load()
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
            this.go('/admin/department')
        },
        resetDeptQuals(existingMaps = []) {
            const maps = Array.isArray(existingMaps) ? existingMaps : []
            const byQual = {}
            for (const m of maps) {
                const k = String(m?.academic_qualification_id ?? '')
                if (!k) continue
                byQual[k] = m
            }

            this.deptQuals = (this.qualifications || []).map((q) => {
                const key = String(q?.id ?? '')
                const exist = byQual[key]
                return {
                    name: q?.name || key,
                    academic_qualification_id: key,
                    department_code: exist?.department_code || '',
                    checked: !!exist,
                }
            })

            this.checkAll = this.deptQuals.length ? this.deptQuals.every((x) => x.checked) : false
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/department/${this.departmentId}`)
                const row = res?.data?.department || res?.data?.data || res?.data || {}
                const maps = res?.data?.department_qualidactions || []

                this.form.name = row?.name || ''
                this.form.description = row?.description || ''
                this.form.registration = Number(row?.registration || 0)
                this.form.application_fee = Number(row?.application_fee || 0)
                this.form.status = row?.status || 'active'

                this.resetDeptQuals(maps)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load department.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const checked = (this.deptQuals || [])
                    .filter((x) => x.checked)
                    .map((x) => ({
                        academic_qualification_id: Number(x.academic_qualification_id),
                        department_code: x.department_code,
                    }))

                const payload = {
                    name: this.form.name,
                    description: this.form.description,
                    registration: this.form.registration ? 1 : 0,
                    application_fee: this.form.application_fee ? 1 : 0,
                    status: this.form.status,
                    checked_qualifications: checked,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/department/${this.departmentId}`, payload)
                } else {
                    res = await window.axios.post('/admin/department', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/department/${id}/edit`)
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
