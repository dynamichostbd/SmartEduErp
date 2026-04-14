<template>
    <form class="flex flex-col gap-4" @submit.prevent="submit">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Academic Session' : 'Create Academic Session' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update academic session information' : 'Create a new academic session' }}</div>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900">Session Details</div>
                        <div class="mt-1 text-xs text-slate-500">Name, description and settings</div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Session Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Current Financial Year</div>
                            <select v-model="form.current" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Online Admission</div>
                            <select v-model="form.online_admission" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Registration</div>
                            <select v-model="form.registration" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Application Fee</div>
                            <select v-model="form.application_fee" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold text-slate-600">Description</div>
                            <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>
                    <div class="mt-4 flex flex-col gap-2">
                        <button type="button" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Back</button>
                        <button type="submit" class="h-9 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving">{{ saving ? '...' : isEdit ? 'Update' : 'Create' }}</button>
                        <button type="button" class="h-9 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
export default {
    name: 'AcademicSessionManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        sessionId: {
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
                description: '',
                current: 0,
                online_admission: 0,
                registration: 0,
                application_fee: 0,
                status: 'active',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.sessionId
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
            this.go('/admin/academicSession')
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/academicSession/${this.sessionId}`)
                const row = res?.data?.academic_session || res?.data?.data || res?.data || {}

                this.form.name = row?.name || ''
                this.form.description = row?.description || ''
                this.form.current = Number(row?.current || 0)
                this.form.online_admission = Number(row?.online_admission || 0)
                this.form.registration = Number(row?.registration || 0)
                this.form.application_fee = Number(row?.application_fee || 0)
                this.form.status = row?.status || 'active'
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load academic session.'
            }
        },
        async submit() {
            this.saving = true
            this.error = ''
            this.success = ''

            try {
                const payload = {
                    name: this.form.name,
                    description: this.form.description,
                    current: this.form.current ? 1 : 0,
                    online_admission: this.form.online_admission ? 1 : 0,
                    registration: this.form.registration ? 1 : 0,
                    application_fee: this.form.application_fee ? 1 : 0,
                    status: this.form.status,
                }

                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/academicSession/${this.sessionId}`, payload)
                } else {
                    res = await window.axios.post('/admin/academicSession', payload)
                }

                const msg = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = msg
                this.toast(msg, 'success')

                if (!this.isEdit) {
                    const id = res?.data?.id
                    if (id) {
                        this.go(`/admin/academicSession/${id}/edit`)
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
