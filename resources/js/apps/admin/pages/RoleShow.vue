<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Role View</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goIndex">Back</button>
                    <button type="button" class="rounded-sm bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" @click="goEdit">Edit</button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>

        <div v-if="role" class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Role Type</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ role.type || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Role Name</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ role.name || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Status</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ role.status || '' }}</div>
                </div>
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Created at</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">{{ role.created_at || '' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'RoleShow',
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
            error: '',
            role: null,
        }
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
            this.go('/admin/role')
        },
        goEdit() {
            this.go(`/admin/role/${this.roleId}/edit`)
        },
        async load() {
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/role/${this.roleId}`)
                this.role = res?.data || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load role.'
            }
        },
    },
}
</script>
