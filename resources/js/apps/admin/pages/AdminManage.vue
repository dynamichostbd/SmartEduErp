<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Admin' : 'Create Admin' }}</div>
                    <div class="mt-1 text-sm text-slate-600">{{ isEdit ? 'Update admin user profile and access' : 'Create a new admin user profile' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        :disabled="saving"
                        @click="goIndex"
                    >
                        Back
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                        :disabled="saving"
                        @click="submit"
                    >
                        {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="space-y-4 lg:col-span-8 xl:col-span-9">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Profile</div>
                    <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <img :src="previewProfile || form.profile || fallback" class="h-16 w-16 rounded-full border border-slate-300 object-cover" alt="profile" />
                        <input type="file" accept="image/*" class="block w-full text-sm" @change="onFile" />
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Admin Details</div>
                    <div class="mt-1 text-xs text-slate-500">Basic information and access configuration</div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Mobile</div>
                            <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div>
                            <div class="text-xs font-semibold text-slate-600">Emergency Contacts</div>
                            <input v-model="form.emergency_contacts" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-2">
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.email" type="email" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Password</div>
                            <input v-model="form.password" type="password" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :placeholder="isEdit ? '(keep blank to keep same)' : ''" />
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Select Role</div>
                            <select v-model="form.role_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">--Select Any--</option>
                                <option v-for="r in roles" :key="'r-' + r.id" :value="r.id">{{ r.name }}</option>
                            </select>
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Department</div>
                            <select v-model="form.department_id" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">--Select Any--</option>
                                <option v-for="d in departments" :key="'d-' + d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Is Two Factor Authentication?</div>
                            <select v-model="form.is_two_factor_auth" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Is Block?</div>
                            <select v-model="form.block" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>

                        <div v-if="isSuper">
                            <div class="text-xs font-semibold text-slate-600">Status</div>
                            <select v-model="form.status" class="mt-1 h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm shadow-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="active">Active</option>
                                <option value="deactive">Deactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 lg:col-span-4 xl:col-span-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>
                    <div class="mt-3 flex flex-col gap-2">
                        <button type="button" class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">
                            {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                        </button>
                        <button type="button" class="h-9 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        adminId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            loading: false,
            saving: false,
            error: '',
            success: '',
            file: null,
            previewProfile: '',
            fallback: window.laravel?.userimage || '',
            form: {
                name: '',
                email: '',
                mobile: '',
                emergency_contacts: '',
                password: '',
                role_id: '',
                department_id: '',
                status: 'active',
                is_two_factor_auth: 0,
                block: 0,
                profile: '',
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.adminId
        },
        authRoleId() {
            return Number(this.systems?.global?.auth_user?.role_id || 0)
        },
        isSuper() {
            return this.authRoleId === 1
        },
        roles() {
            const rs = this.systems?.global?.roles
            return Array.isArray(rs) ? rs : []
        },
        departments() {
            const ds = this.systems?.global?.departments
            return Array.isArray(ds) ? ds : []
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
            this.go('/admin/admin')
        },
        onFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
            this.previewProfile = f ? URL.createObjectURL(f) : ''
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/admin/${this.adminId}`)
                const a = res?.data || {}
                this.form.name = a?.name || ''
                this.form.email = a?.email || ''
                this.form.mobile = a?.mobile || ''
                this.form.emergency_contacts = a?.emergency_contacts || ''
                this.form.role_id = a?.role_id || ''
                this.form.department_id = a?.department_id || ''
                this.form.status = a?.status || 'active'
                this.form.is_two_factor_auth = Number(a?.is_two_factor_auth || 0)
                this.form.block = Number(a?.block || 0)
                this.form.profile = a?.profile || ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load admin.'
            } finally {
                this.loading = false
            }
        },
        validate() {
            if (!this.form.name) return 'Name is required'
            if (!this.form.email) return 'Email is required'
            if (!this.form.role_id && this.isSuper) return 'Role is required'
            if (!this.isEdit && !this.form.password) return 'Password is required'
            if (!this.isEdit && String(this.form.password || '').length < 6) return 'Password must be at least 6 characters'
            if (this.isEdit && this.form.password && String(this.form.password).length < 6) return 'Password must be at least 6 characters'
            return ''
        },
        async submit() {
            this.error = ''
            this.success = ''

            const msg = this.validate()
            if (msg) {
                this.error = msg
                return
            }

            this.saving = true
            try {
                const fd = new FormData()
                Object.keys(this.form).forEach((k) => {
                    if (k === 'profile') return
                    const v = this.form[k]
                    if (v === null || v === undefined) return
                    fd.append(k, String(v))
                })
                if (this.file) fd.append('profile', this.file)

                let res
                if (this.isEdit) {
                    res = await window.axios.post(`/admin/admin/${this.adminId}?_method=PUT`, fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                } else {
                    res = await window.axios.post('/admin/admin', fd, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    })
                }

                const m = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
                this.success = m
                this.toast(m, 'success')

                if (!this.isEdit) {
                    this.goIndex()
                } else {
                    await this.load()
                }
            } catch (e) {
                this.error = e?.response?.data?.message || 'Submit failed.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
