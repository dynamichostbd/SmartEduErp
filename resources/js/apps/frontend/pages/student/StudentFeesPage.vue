<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <StudentSidebar :studentMe="app.studentMe" :active="app.studentSidebarActive"
                        :onNavigate="app.studentSidebarGo" :onLogout="app.doStudentLogout"
                        :logoutDisabled="app.studentAuthLoading" />
                </div>

                <div class="lg:col-span-9">
                    <div class="rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800">
                            FEES LIST</div>
                        <div class="p-4">
                            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else-if="error"
                                class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}
                            </div>
                            <div v-else>
                                <div class="mb-3 flex items-center justify-end gap-2 text-sm font-bold text-red-600">
                                    <span>TOTAL AMOUNT :</span>
                                    <span>{{ formatMoney(totalAmount) }}</span>
                                </div>
                                <div v-if="fees.length" class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr
                                                class="border-b bg-slate-100 text-left text-xs font-bold text-slate-700">
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Purpose</th>
                                                <th class="px-3 py-2 text-right">Amount</th>
                                                <th class="px-3 py-2">Start Date</th>
                                                <th class="px-3 py-2">Expired Date</th>
                                                <th class="px-3 py-2">Additional Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(f, idx) in fees" :key="'sf-' + f.id"
                                                class="border-b last:border-b-0">
                                                <td class="px-3 py-2">{{ idx + 1 }}</td>
                                                <td class="px-3 py-2 font-semibold">{{ f.account_head?.name || '' }}
                                                </td>
                                                <td class="px-3 py-2 text-right">{{ formatMoney(f.amount) }}</td>
                                                <td class="px-3 py-2">{{ formatDate(f.start_date) }}</td>
                                                <td class="px-3 py-2">{{ formatDate(f.expire_date) }}</td>
                                                <td class="px-3 py-2">{{ formatDate(f.additional_date) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t bg-slate-50 text-xs font-bold text-slate-800">
                                                <td class="px-3 py-2 text-right" colspan="5">Total Amount</td>
                                                <td class="px-3 py-2 text-right">{{ formatMoney(totalAmount) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div v-else
                                    class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">
                                    No fees found.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject } from 'vue'
import StudentSidebar from '../../components/student/StudentSidebar.vue'

const app = inject('app')

const loading = computed(() => app?.studentFeesLoading)
const error = computed(() => app?.studentFeesError)
const fees = computed(() => app?.studentFees || [])

const totalAmount = computed(() => {
    return (fees.value || []).reduce((sum, f) => sum + Number.parseFloat(f?.amount || 0), 0)
})

const moneyFormatter = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatMoney = (v) => moneyFormatter.format(Number(v || 0))

const formatDate = (v) => {
    if (!v) return ''
    const d = new Date(v)
    if (Number.isNaN(d.getTime())) return ''
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
