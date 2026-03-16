<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Leave Type' : 'Create Leave Type' }}</div>
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
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Short Name</div>
                            <input v-model="form.short_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Allocated Leave Days</div>
                            <input v-model.number="form.allocated_leave_days" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Back Date Select</div>
                            <select v-model="form.back_date_select" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Documents Upload</div>
                            <select v-model="form.documents_upload" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Reason</div>
                            <select v-model="form.reason" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
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
    name: 'LeaveTypeManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        leaveTypeId: {
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
                short_name: '',
                allocated_leave_days: null,
                back_date_select: 0,
                documents_upload: 0,
                reason: 0,
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.leaveTypeId
        },
    },
    created() {
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
            this.go('/admin/leaveType')
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/leaveType/${this.leaveTypeId}`)
                const row = res?.data?.leave_type || res?.data?.data || res?.data || {}
                this.form.name = row?.name || ''
                this.form.short_name = row?.short_name || ''
                this.form.allocated_leave_days = row?.allocated_leave_days ?? null
                this.form.back_date_select = Number(row?.back_date_select || 0)
                this.form.documents_upload = Number(row?.documents_upload || 0)
                this.form.reason = Number(row?.reason || 0)
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load leave type.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''
            try {
                const payload = {
                    name: this.form.name,
                    short_name: this.form.short_name,
                    allocated_leave_days: this.form.allocated_leave_days,
                    back_date_select: this.form.back_date_select ? 1 : 0,
                    documents_upload: this.form.documents_upload ? 1 : 0,
                    reason: this.form.reason ? 1 : 0,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/leaveType/${this.leaveTypeId}`, payload)
                } else {
                    res = await window.axios.post('/admin/leaveType', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/leaveType/${id}/edit`)
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
