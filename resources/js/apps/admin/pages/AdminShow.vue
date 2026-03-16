<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Profile</div>
                    <div class="mt-1 text-sm text-slate-600">{{ admin?.role?.name || '' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Admin List</button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="saveProfile">{{ saving ? '...' : 'Update Information' }}</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ success }}</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-col items-center text-center">
                        <img :src="previewProfile || admin?.profile || fallback" alt="Admin" class="h-28 w-28 rounded-full border border-slate-200 object-cover" />
                        <div class="mt-3">
                            <div class="text-lg font-semibold text-slate-900">{{ admin?.name || '' }}</div>
                            <div class="text-sm text-slate-600">{{ admin?.role?.name || '' }}</div>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-4">
                        <div class="text-xs font-semibold text-slate-600">Profile</div>
                        <input type="file" accept="image/*" class="mt-2 block w-full text-sm" @change="onFile" />
                    </div>

                    <div class="mt-4">
                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <div class="flex items-center justify-between px-3 py-2 text-sm">
                                <div class="font-semibold text-slate-700">Email</div>
                                <div class="text-slate-600">{{ admin?.email || '' }}</div>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 px-3 py-2 text-sm">
                                <div class="font-semibold text-slate-700">Mobile</div>
                                <div class="text-slate-600">{{ admin?.mobile || '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
                        <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="tab === 'edit' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'" @click="tab = 'edit'">Profile Edit</button>
                        <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="tab === 'password' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'" @click="tab = 'password'">Change Password</button>
                    </div>

                    <div v-if="tab === 'edit'" class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Email</div>
                            <input v-model="form.email" type="email" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="!canEditEmail" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Mobile</div>
                            <input v-model="form.mobile" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Emergency Contact</div>
                            <input v-model="form.emergency_contacts" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-12">
                            <div class="text-xs font-semibold text-slate-600">Address</div>
                            <textarea v-model="form.address" rows="3" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                        </div>
                    </div>

                    <div v-else class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Old Password</div>
                            <input v-model="pass.old_password" type="password" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" @blur="checkOld" />
                            <div v-if="pass.old_checked" class="mt-1 text-xs" :class="pass.old_valid ? 'text-emerald-700' : 'text-rose-700'">
                                {{ pass.old_valid ? 'Old password matched.' : 'Old password do not match our records!!' }}
                            </div>
                        </div>
                        <div class="lg:col-span-6"></div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">New Password</div>
                            <input v-model="pass.new_password" type="password" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Confirm Password</div>
                            <input v-model="pass.confirm_password" type="password" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" :disabled="savingPassword" @click="changePassword">
                                {{ savingPassword ? '...' : 'Change Password' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminShow',
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
            savingPassword: false,
            error: '',
            success: '',
            tab: 'edit',
            admin: null,
            file: null,
            previewProfile: '',
            fallback: window.laravel?.userimage || '',
            form: {
                name: '',
                email: '',
                mobile: '',
                emergency_contacts: '',
                address: '',
                department_id: '',
            },
            pass: {
                old_password: '',
                new_password: '',
                confirm_password: '',
                old_checked: false,
                old_valid: false,
            },
        }
    },
    computed: {
        authRoleId() {
            return Number(this.systems?.global?.auth_user?.role_id || 0)
        },
        isSuper() {
            return this.authRoleId === 1
        },
        canEditEmail() {
            return this.isSuper
        },
    },
    created() {
        this.load()
    },
    methods: {
        go(path) {
            window.history.pushState({}, '', path)
            window.dispatchEvent(new PopStateEvent('popstate'))
        },
        goIndex() {
            this.go('/admin/admin')
        },
        toast(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('app-toast', { detail: { message, type } }))
        },
        onFile(e) {
            const f = e?.target?.files?.[0] || null
            this.file = f
            this.previewProfile = f ? URL.createObjectURL(f) : ''
        },
        async load() {
            this.error = ''
            this.success = ''
            if (!this.adminId) return

            this.loading = true
            try {
                const res = await window.axios.get(`/admin/admin/${this.adminId}`)
                this.admin = res?.data || null

                this.form.name = this.admin?.name || ''
                this.form.email = this.admin?.email || ''
                this.form.mobile = this.admin?.mobile || ''
                this.form.emergency_contacts = this.admin?.emergency_contacts || ''
                this.form.address = this.admin?.address || ''
                this.form.department_id = this.admin?.department_id || ''
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load profile.'
            } finally {
                this.loading = false
            }
        },
        validateProfile() {
            if (!this.form.name) return 'Name is required'
            if (!this.form.email) return 'Email is required'
            return ''
        },
        async saveProfile() {
            this.error = ''
            this.success = ''
            if (!this.adminId) return

            const msg = this.validateProfile()
            if (msg) {
                this.error = msg
                return
            }

            this.saving = true
            try {
                const fd = new FormData()
                Object.keys(this.form).forEach((k) => fd.append(k, this.form[k] ?? ''))
                if (this.file) fd.append('profile', this.file)

                const res = await window.axios.post(`/admin/admin/${this.adminId}?_method=PUT`, fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                })

                this.success = res?.data?.message || 'Information Update Successfully!'
                this.toast(this.success, 'success')
                await this.load()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Update failed.'
            } finally {
                this.saving = false
            }
        },
        async checkOld() {
            this.pass.old_checked = false
            this.pass.old_valid = false
            const old = String(this.pass.old_password || '')
            if (!old) return

            try {
                const res = await window.axios.post('/admin/check-old-password', { old_password: old, id: this.adminId })
                this.pass.old_checked = true
                this.pass.old_valid = !!res?.data
            } catch {
                this.pass.old_checked = true
                this.pass.old_valid = false
            }
        },
        validatePassword() {
            if (!this.pass.new_password) return 'New password is required'
            if (String(this.pass.new_password).length < 6) return 'New password must be at least 6 characters'
            if (!this.pass.confirm_password) return 'Password confirmation is required'
            if (this.pass.confirm_password !== this.pass.new_password) return 'Confirm password does not match'

            if (!this.isSuper) {
                if (!this.pass.old_password) return 'Old password is required'
                if (this.pass.old_checked && !this.pass.old_valid) return 'Old password do not match our records!!'
            }

            return ''
        },
        async changePassword() {
            this.error = ''
            this.success = ''
            const msg = this.validatePassword()
            if (msg) {
                this.error = msg
                return
            }

            this.savingPassword = true
            try {
                const res = await window.axios.post('/admin/change-password', {
                    id: this.adminId,
                    old_password: this.pass.old_password,
                    new_password: this.pass.new_password,
                    confirm_password: this.pass.confirm_password,
                })

                const m = res?.data?.message || 'Password changed successfully!'
                this.toast(m, 'success')
                this.success = m

                this.pass.old_password = ''
                this.pass.new_password = ''
                this.pass.confirm_password = ''
                this.pass.old_checked = false
                this.pass.old_valid = false
            } catch (e) {
                this.error = e?.response?.data?.message || 'Password change failed.'
            } finally {
                this.savingPassword = false
            }
        },
    },
}
</script>
