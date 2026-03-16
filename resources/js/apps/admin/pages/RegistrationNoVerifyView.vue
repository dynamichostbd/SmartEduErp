<template>
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="truncate text-xl font-semibold text-slate-900">Registration No Verify</div>
                    <div class="mt-1 text-sm text-slate-600">View and add registration numbers</div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="goBack">Back</button>
                    <button type="button" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700" @click="openAdd">Add Roll</button>
                </div>
            </div>
        </div>

        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
        <div v-if="message" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">{{ message }}</div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Session</div>
                    <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">{{ data?.academic_session?.name || '--' }}</div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Level</div>
                    <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">{{ data?.qualification?.name || '--' }}</div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Department</div>
                    <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">{{ data?.department?.name || '--' }}</div>
                </div>

                <div class="lg:col-span-3">
                    <div class="text-xs font-semibold text-slate-600">Academic Class</div>
                    <div class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">{{ data?.academic_class?.name || '--' }}</div>
                </div>

                <div class="lg:col-span-12">
                    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm font-semibold text-slate-900">Roll Lists</div>
                        <div class="w-full sm:w-72">
                            <input v-model="keyword" type="text" class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" placeholder="Search by roll..." />
                        </div>
                    </div>

                    <div class="mt-3 rounded-xl border border-slate-200">
                        <div class="max-h-[280px] overflow-y-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700">Registration No</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    <tr v-for="(roll, i) in filteredLists" :key="'r-' + i">
                                        <td class="px-3 py-2 text-slate-800">{{ roll }}</td>
                                    </tr>
                                    <tr v-if="!filteredLists.length">
                                        <td class="px-3 py-10 text-center text-sm text-slate-500">No rolls found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="addOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4" @click.self="closeAdd">
            <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="text-base font-semibold text-slate-900">Added Roll Number</div>
                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeAdd">Close</button>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-9">
                            <input
                                v-model="inputData.registration_no"
                                type="text"
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                placeholder="Registration No"
                            />
                        </div>
                        <div class="col-span-3">
                            <button
                                type="button"
                                class="h-9 w-full rounded-lg bg-sky-600 text-sm font-semibold text-white hover:bg-sky-700"
                                :disabled="!inputData.registration_no"
                                @click="addRegistrationNo"
                            >
                                Add
                            </button>
                        </div>

                        <div v-if="formData.registration_no_lists.length" class="col-span-12">
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span
                                    v-for="(roll, idx) in formData.registration_no_lists"
                                    :key="'tag-' + idx"
                                    class="inline-flex items-center gap-2 rounded-full bg-slate-700 px-3 py-1 text-xs font-semibold text-white"
                                >
                                    {{ roll }}
                                    <button type="button" class="text-white/90 hover:text-white" @click="removeIdx(idx)">x</button>
                                </span>
                            </div>
                        </div>

                        <div v-if="formData.registration_no_lists.length" class="col-span-12 mt-4 flex justify-center">
                            <button
                                type="button"
                                class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
                                :disabled="submitting"
                                @click="submit"
                            >
                                {{ submitting ? 'Processing...' : 'Submit' }}
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
    name: 'RegistrationNoVerifyView',
    props: {
        verifyId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            submitting: false,
            error: '',
            message: '',
            keyword: '',
            data: null,

            addOpen: false,
            inputData: { registration_no: '' },
            formData: { registration_no_lists: [] },
        }
    },
    computed: {
        filteredLists() {
            const list = Array.isArray(this.data?.registration_no_lists) ? this.data.registration_no_lists : []
            const q = String(this.keyword || '').trim().toLowerCase()
            if (!q) return list
            return list.filter((item) => String(item).toLowerCase().includes(q))
        },
    },
    mounted() {
        this.load()
    },
    methods: {
        goBack() {
            window.location.href = '/admin/registrationNoVerify'
        },
        openAdd() {
            this.addOpen = true
            this.message = ''
            this.error = ''
        },
        closeAdd() {
            this.addOpen = false
            this.inputData.registration_no = ''
            this.formData = { registration_no_lists: [] }
        },
        removeIdx(idx) {
            this.formData.registration_no_lists.splice(idx, 1)
        },
        addRegistrationNo() {
            const val = String(this.inputData.registration_no || '').trim()
            if (!val) return
            const exist = this.formData.registration_no_lists.includes(val)
            if (exist) {
                this.inputData.registration_no = ''
                return
            }
            this.formData.registration_no_lists.push(Number(val))
            this.inputData.registration_no = ''
        },
        async load() {
            this.loading = true
            this.error = ''
            try {
                const res = await window.axios.get(`/admin/registrationNoVerify/${this.verifyId}/details`)
                this.data = res?.data || null
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to load data.'
            } finally {
                this.loading = false
            }
        },
        async submit() {
            this.submitting = true
            this.error = ''
            this.message = ''
            try {
                const res = await window.axios.put(`/admin/registrationNoVerify/${this.verifyId}`, this.formData)
                this.message = res?.data?.message || 'Added Successfully!'
                this.formData = { registration_no_lists: [] }
                await this.load()
                this.closeAdd()
            } catch (e) {
                this.error = e?.response?.data?.message || 'Failed to add.'
            } finally {
                this.submitting = false
            }
        },
    },
}
</script>
