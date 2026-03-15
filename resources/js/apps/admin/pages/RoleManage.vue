<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Role' : 'Create Role' }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">
                        Back
                    </button>
                    <button type="button" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700" :disabled="saving" @click="submit">
                        {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                    </button>
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
                            <div class="text-xs font-semibold text-slate-600">Role Type</div>
                            <select v-model="form.type" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="!hasTypeColumn">
                                <option :value="null">--Select Any--</option>
                                <option value="Admin">Admin</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Name</div>
                            <input v-model="form.name" type="text" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-12">
                            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200">
                                <div class="flex items-center justify-between bg-slate-50 px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900">Permission Setting</div>
                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                        <input v-model="checkAll" type="checkbox" class="h-4 w-4" />
                                        All
                                    </label>
                                </div>

                                <div class="p-4">
                                    <div v-if="loadingPerms" class="text-sm text-slate-600">Loading permissions...</div>
                                    <div v-else-if="permissions.length === 0" class="text-sm text-slate-600">No permissions found.</div>
                                    <div v-else class="flex flex-col gap-3">
                                        <div v-for="perm in permissions" :key="'p-' + perm.id" class="rounded-xl border border-slate-200">
                                            <div class="bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900">
                                                {{ prettyController(perm.name) }}
                                            </div>
                                            <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2 xl:grid-cols-4">
                                                <label v-for="child in perm.children" :key="'c-' + child.id" class="inline-flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox" class="h-4 w-4" :value="child.id" v-model="form.permissions" />
                                                    <span class="capitalize">{{ child.name }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Status</div>
                    <select v-model="form.status" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" :disabled="!hasStatusColumn">
                        <option value="active">Active</option>
                        <option value="deactive">Deactive</option>
                    </select>

                    <button type="button" class="mt-4 h-10 w-full rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">
                        {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                    </button>

                    <button type="button" class="mt-2 h-10 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RoleManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        roleId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            permissions: [],
            loadingPerms: false,
            checkAll: false,
            form: {
                name: '',
                type: null,
                status: 'active',
                permissions: [],
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.roleId
        },
        hasStatusColumn() {
            return true
        },
        hasTypeColumn() {
            return true
        },
    },
    watch: {
        checkAll(val) {
            if (!val) {
                this.form.permissions = []
                return
            }

            const all = []
            for (const p of this.permissions) {
                for (const c of p.children || []) {
                    all.push(Number(c.id))
                }
            }
            this.form.permissions = all
        },
    },
    async created() {
        await this.loadPermissions()
        if (this.isEdit) await this.loadRole()
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
            this.go('/admin/role')
        },
        prettyController(name) {
            return String(name || '').replace(/Controller$/i, '').replace(/([a-z])([A-Z])/g, '$1 $2')
        },
        async loadPermissions() {
            this.loadingPerms = true
            try {
                const res = await window.axios.get('/admin/get-permissions')
                this.permissions = Array.isArray(res?.data) ? res.data : []
            } catch {
                this.permissions = []
            } finally {
                this.loadingPerms = false
            }
        },
        async loadRole() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/role/${this.roleId}`)
                const r = res?.data || {}
                this.form.name = r?.name || ''
                this.form.type = r?.type ?? null
                this.form.status = r?.status || 'active'
                this.form.permissions = Array.isArray(r?.permissions) ? r.permissions.map((x) => Number(x)) : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load role.'
            }
        },
        validate() {
            if (!this.form.name) return 'Name is required'
            if (this.form.type === null) return 'Type is required'
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
                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/role/${this.roleId}`, this.form)
                } else {
                    res = await window.axios.post('/admin/role', this.form)
                }

                const m = res?.data?.message || (this.isEdit ? 'You have successfully updated' : 'You have successfully created')
                this.success = m
                this.toast(m, 'success')

                if (!this.isEdit) {
                    this.goIndex()
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
