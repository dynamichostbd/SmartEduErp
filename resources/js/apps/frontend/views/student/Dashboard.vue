<template>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="group relative overflow-hidden rounded-sm border border-slate-300 bg-white p-5 hover:border-emerald-500">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Invoices</div>
                <div class="mt-2 text-3xl font-bold text-slate-900">{{ invoices.length }}</div>
                <div class="absolute -bottom-2 -right-2 h-12 w-12 text-slate-100 transition-colors group-hover:text-emerald-50"></div>
            </div>
            <div class="group relative overflow-hidden rounded-sm border border-slate-300 bg-white p-5 hover:border-emerald-500">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Paid</div>
                <div class="mt-2 text-3xl font-bold text-emerald-600">{{ totalPaid.toFixed(2) }} BDT</div>
            </div>
            <div class="group relative overflow-hidden rounded-sm border border-slate-300 bg-white p-5 hover:border-emerald-500">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pending Fees</div>
                <div class="mt-2 text-3xl font-bold text-amber-600">--</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-sm border border-slate-300 bg-white">
                <div class="border-b border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Recent Notices</h3>
                </div>
                <div class="p-4">
                    <div v-if="noticeLoading" class="text-sm text-slate-500">Loading notices...</div>
                    <div v-else-if="!recentNotices.length" class="py-10 text-center text-sm text-slate-400">No notices found</div>
                    <div v-else class="space-y-3">
                        <div v-for="n in recentNotices" :key="n.id" class="flex cursor-pointer items-start gap-3 rounded-sm p-2 hover:bg-slate-50" @click="injectGo('/notices/' + n.id)">
                            <div class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-emerald-500"></div>
                            <div>
                                <div class="text-sm font-medium text-slate-900 line-clamp-1">{{ n.title }}</div>
                                <div class="text-xs text-slate-500">{{ n.date }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="recentNotices.length" class="mt-4 border-t border-slate-100 pt-4">
                        <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700" @click="injectGo('/notices')">View All Notices</button>
                    </div>
                </div>
            </div>

            <div class="rounded-sm border border-slate-300 bg-white">
                <div class="border-b border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-slate-900">Recent Paid Invoices</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600">
                                <th class="px-4 py-3 font-semibold">Date</th>
                                <th class="px-4 py-3 font-semibold">Head</th>
                                <th class="px-4 py-3 font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-600">{{ inv.payment_date || inv.invoice_date }}</td>
                                <td class="px-4 py-3 text-slate-900 font-medium">{{ inv.head?.name || '—' }}</td>
                                <td class="px-4 py-3 text-emerald-600 font-bold">{{ inv.amount }}</td>
                            </tr>
                            <tr v-if="!invoices.length">
                                <td colspan="3" class="px-4 py-10 text-center text-slate-400">No records found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, computed } from 'vue'

const invoices = inject('studentDashboardInvoices', [])
const recentNotices = inject('studentDashboardRecentNotices', [])
const noticeLoading = inject('studentDashboardNoticeLoading', false)
const injectGo = inject('go')

const totalPaid = computed(() => {
    return (invoices.value || []).reduce((acc, curr) => acc + Number(curr.amount || 0), 0)
})
</script>
