<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Subject' : 'Create Subject' }}</div>
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
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name (en)</div>
                            <input v-model="form.name_en" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name (bn)</div>
                            <input v-model="form.name_bn" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Is Child?</div>
                            <select v-model="form.is_child" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @change="onIsChildChange">
                                <option :value="0">No</option>
                                <option :value="1">Yes</option>
                            </select>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Parent Subject</div>
                            <select v-model="form.parent_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="Number(form.is_child) !== 1">
                                <option value="">Select Parent Subject</option>
                                <option v-for="s in parentSubjects" :key="'p-' + s.id" :value="String(s.id)">{{ s.name }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Sorting</div>
                            <input v-model.number="form.sorting" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                        <option value="draft">Draft</option>
                    </select>

                    <button type="button" class="mt-4 h-9 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                    <button type="button" class="mt-2 h-9 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SubjectManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        subjectId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            parentSubjects: [],
            form: {
                name_en: '',
                name_bn: '',
                sorting: 1,
                is_child: 0,
                parent_id: '',
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.subjectId
        },
    },
    async created() {
        await this.loadParentSubjects()
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
            this.go('/admin/subject')
        },
        onIsChildChange() {
            if (Number(this.form.is_child) !== 1) {
                this.form.parent_id = ''
            }
        },
        async loadParentSubjects() {
            try {
                const res = await window.axios.get('/admin/all-subjects')
                const list = Array.isArray(res?.data) ? res.data : []
                this.parentSubjects = list
            } catch {
                this.parentSubjects = []
            }
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/subject/${this.subjectId}`)
                const row = res?.data?.subject || res?.data || {}
                this.form.name_en = row?.name_en || row?.name || ''
                this.form.name_bn = row?.name_bn || ''
                this.form.sorting = row?.sorting ?? 1
                this.form.is_child = Number(row?.is_child || 0)
                this.form.parent_id = row?.parent_id != null ? String(row.parent_id) : ''
                this.form.status = row?.status || 'active'

                this.parentSubjects = (this.parentSubjects || []).filter((s) => String(s?.id) !== String(this.subjectId))
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load subject.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    name_en: this.form.name_en,
                    name_bn: this.form.name_bn,
                    sorting: this.form.sorting,
                    is_child: this.form.is_child ? 1 : 0,
                    parent_id: Number(this.form.is_child) === 1 && this.form.parent_id ? Number(this.form.parent_id) : null,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/subject/${this.subjectId}`, payload)
                } else {
                    res = await window.axios.post('/admin/subject', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/subject/${id}/edit`)
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
