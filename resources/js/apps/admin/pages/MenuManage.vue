<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">{{ isEdit ? 'Edit Menu' : 'Create Menu' }}</div>
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
                            <div class="text-xs font-semibold text-slate-600">Parent Menu</div>
                            <select v-model="form.parent_id" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="null">--Select Any--</option>
                                <option v-for="(label, id) in allMenus" :key="'pm-' + id" :value="Number(id)">{{ label }}</option>
                            </select>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Menu Name</div>
                            <input v-model="form.menu_name" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Menu Icon</div>
                            <input v-model="form.icon" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Sorting</div>
                            <input v-model="form.sorting" type="number" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Route Name</div>
                            <input
                                v-model="form.route_name"
                                list="routeOptionsList"
                                type="text"
                                class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                placeholder="Type or select route name"
                            />
                            <datalist id="routeOptionsList">
                                <option v-for="(p, idx) in routeOptions" :key="'rn-' + idx" :value="p">{{ p }}</option>
                            </datalist>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Menu Look Type</div>
                            <select v-model="form.menu_look_type" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="normal">Normal</option>
                                <option value="mega">Mega</option>
                            </select>
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Params</div>
                            <input v-model="form.params" type="text" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                        </div>

                        <div class="lg:col-span-6">
                            <div class="text-xs font-semibold text-slate-600">Show Dashboard</div>
                            <select v-model="form.show_dasboard" class="mt-1 h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option :value="1">Yes</option>
                                <option :value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="text-sm font-semibold text-slate-900">Actions</div>
                    <div class="mt-3 flex flex-col gap-2">
                        <button type="button" class="h-9 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800" :disabled="saving" @click="submit">
                            {{ saving ? '...' : (isEdit ? 'Update' : 'Create') }}
                        </button>
                        <button type="button" class="h-9 rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50" :disabled="saving" @click="goIndex">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MenuManage',
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
        menuId: {
            type: [Number, String],
            default: null,
        },
    },
    data() {
        return {
            saving: false,
            error: '',
            success: '',
            allMenus: {},
            allPermissionRoutes: [],
            form: {
                parent_id: null,
                menu_name: '',
                icon: "<i class='bx bx-right-arrow-alt'></i>",
                sorting: 0,
                route_name: null,
                menu_look_type: 'normal',
                params: null,
                show_dasboard: 0,
            },
        }
    },
    computed: {
        isEdit() {
            return !!this.menuId
        },
        routeOptions() {
            const a = Array.isArray(this.allPermissionRoutes) ? this.allPermissionRoutes : []
            if (a.length) return a
            const perms = this.systems?.permissions
            if (!Array.isArray(perms)) return []
            return perms.filter((x) => !!x).map((x) => String(x))
        },
    },
    async created() {
        await this.loadMenus()
        await this.loadPermissionRoutes()
        if (this.isEdit) await this.loadMenu()
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
            this.go('/admin/menu')
        },
        async loadMenus() {
            try {
                const res = await window.axios.get('/admin/get-menus/menu')
                const data = res?.data
                this.allMenus = data && typeof data === 'object' && !Array.isArray(data) ? data : {}
            } catch {
                this.allMenus = {}
            }
        },
        async loadMenu() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/menu/${this.menuId}`)
                const m = res?.data || {}
                this.form.parent_id = m?.parent_id ?? null
                this.form.menu_name = m?.menu_name || ''
                this.form.icon = m?.icon || "<i class='bx bx-right-arrow-alt'></i>"
                this.form.sorting = m?.sorting ?? 0
                this.form.route_name = m?.route_name ?? ''
                this.form.menu_look_type = m?.menu_look_type || 'normal'
                this.form.params = m?.params ?? null
                this.form.show_dasboard = Number(m?.show_dasboard || 0)
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load menu.'
            }
        },
        async loadPermissionRoutes() {
            try {
                const res = await window.axios.get('/admin/get-permissions')
                const tree = Array.isArray(res?.data) ? res.data : []
                const out = []

                for (const p of tree) {
                    const children = Array.isArray(p?.children) ? p.children : []
                    for (const c of children) {
                        const r = String(c?.route || '').trim()
                        if (r) out.push(r)
                    }
                }

                this.allPermissionRoutes = Array.from(new Set(out)).sort()
            } catch {
                this.allPermissionRoutes = []
            }
        },
        validate() {
            if (!this.form.menu_name) return 'Menu Name is required'
            if (this.form.sorting === null || this.form.sorting === undefined || this.form.sorting === '') return 'Sorting is required'
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

            if (this.form.route_name === '') {
                this.form.route_name = null
            }

            this.saving = true
            try {
                let res
                if (this.isEdit) {
                    res = await window.axios.put(`/admin/menu/${this.menuId}`, this.form)
                } else {
                    res = await window.axios.post('/admin/menu', this.form)
                }

                const m = res?.data?.message || (this.isEdit ? 'Update Successfully!' : 'Create Successfully!')
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
