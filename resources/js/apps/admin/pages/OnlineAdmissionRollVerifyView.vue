<template>
    <div class="flex flex-col gap-4">
        <div class=" border border-slate-300 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Online Admission Roll Verify</div>
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

        <div v-if="message" class=" border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            {{ message }}
        </div>

        <div v-if="error" class=" border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ error }}
        </div>

        <div class=" border border-slate-300 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Session</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.academic_session?.name || '--' }}
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.qualification?.name || '--' }}
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.department?.name || '--' }}
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Class</div>
                    <div class="mt-1 rounded-sm border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                        {{ item?.academic_class?.name || '--' }}
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Search</div>
                    <input v-model="keyword" type="text" class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="Search roll/name..." />
                </div>

                <div class="lg:col-span-6">
                    <div class="text-xs font-semibold text-slate-600">Add New</div>
                    <div class="mt-1 flex gap-2">
                        <input v-model="newRoll" type="text" class="h-9 w-40 rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="Roll" />
                        <input v-model="newName" type="text" class="h-9 flex-1 rounded-sm border border-slate-300 bg-white px-3 text-sm" placeholder="Name" />
                        <button
                            type="button"
                            class="h-9 rounded-sm bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800"
                            :disabled="saving"
                            @click="addRoll"
                        >
                            {{ saving ? 'Saving...' : 'Add' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-slate-300">
                <div class="max-h-[360px] overflow-y-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-emerald-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Admission Roll</th>
                                <th class="px-3 py-2 text-left font-semibold text-slate-700">Name</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="(r, idx) in filteredLists" :key="'roll-' + idx">
                                <td class="px-3 py-2 text-slate-800">{{ r.roll }}</td>
                                <td class="px-3 py-2 text-slate-800">{{ r.name || '' }}</td>
                            </tr>

                            <tr v-if="!filteredLists.length">
                                <td colspan="2" class="px-3 py-10 text-center text-sm text-slate-500">No rolls found</td>
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
    name: 'OnlineAdmissionRollVerifyView',
    props: {
        groupId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            saving: false,
            error: '',
            message: '',
            keyword: '',
            item: null,
            rollLists: [],
            nameLists: [],
            lists: [],
            newRoll: '',
            newName: '',
        }
    },
    computed: {
        filteredLists() {
            const q = String(this.keyword || '').trim().toLowerCase()
            if (!q) return this.lists
            return (this.lists || []).filter((r) => {
                const roll = String(r?.roll || '').toLowerCase()
                const name = String(r?.name || '').toLowerCase()
                return roll.includes(q) || name.includes(q)
            })
        },
    },
    mounted() {
        this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/onlineAdmissionRollVerify'
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/onlineAdmissionRollVerify/${this.groupId}/details`)
                this.item = res?.data || null
                this.rollLists = Array.isArray(res?.data?.roll_lists) ? res.data.roll_lists : []
                this.nameLists = Array.isArray(res?.data?.name_lists) ? res.data.name_lists : []
                this.lists = Array.isArray(res?.data?.lists) ? res.data.lists : []
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
        async addRoll() {
            const roll = String(this.newRoll || '').trim()
            if (!roll) return

            const rolls = [...(this.rollLists || [])]
            const names = [...(this.nameLists || [])]

            rolls.push(roll)
            names.push(String(this.newName || '').trim())

            this.saving = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.put(`/admin/onlineAdmissionRollVerify/${this.groupId}`, {
                    roll_lists: rolls,
                    name_lists: names,
                })
                this.message = res?.data?.message || 'Updated Successfully'
                this.newRoll = ''
                this.newName = ''
                await this.load()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to update.'
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
