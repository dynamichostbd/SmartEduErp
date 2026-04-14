<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Migration Roll Verify</div>
                    <div class="mt-1 text-sm text-slate-600">View roll list</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        class="rounded-sm border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        @click="goBack"
                    >
                        Back
                    </button>
                </div>
            </div>
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Academic Session</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.academic_session?.name || '--' }}
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Migrate Department</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.department?.name || '--' }}
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="keyword" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Search..." />
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-slate-300">
                <div class="max-h-[280px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-emerald-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission Roll</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="r in filteredRolls" :key="'roll-' + r.id">
                                <td class="px-3 py-2 text-slate-800">{{ r.admission_roll }}</td>
                            </tr>

                            <tr v-if="!filteredRolls.length">
                                <td class="px-3 py-10 text-center text-sm text-slate-500">No rolls found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StudentMigrationRollVerifyView',
    props: {
        groupId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            error: '',
            keyword: '',
            item: null,
            rollLists: [],
        }
    },
    computed: {
        filteredRolls() {
            const q = String(this.keyword || '').trim().toLowerCase()
            if (!q) return this.rollLists
            return (this.rollLists || []).filter((r) => String(r?.admission_roll || '').toLowerCase().includes(q))
        },
    },
    mounted() {
        this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/studentMigrationRollVerify'
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/studentMigrationRollVerify/${this.groupId}/details`)
                this.item = res?.data?.item || null
                this.rollLists = Array.isArray(res?.data?.roll_lists) ? res.data.roll_lists : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
    },
}
</script>
