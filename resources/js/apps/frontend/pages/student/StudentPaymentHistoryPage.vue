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
                            PAYMENT HISTORY</div>
                        <div class="p-4">
                            <div v-if="loading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else-if="error"
                                class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{ error }}
                            </div>
                            <div v-else>
                                <div v-if="invoices.length" class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr
                                                class="border-b bg-slate-100 text-left text-xs font-bold text-slate-700">
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Invoice Date</th>
                                                <th class="px-3 py-2">Payment Date</th>
                                                <th class="px-3 py-2">Invoice No.</th>
                                                <th class="px-3 py-2">Purpose</th>
                                                <th class="px-3 py-2 text-right">Amount</th>
                                                <th class="px-3 py-2">Status</th>
                                                <th class="px-3 py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(inv, idx) in invoices" :key="'sph-' + inv.id"
                                                class="border-b last:border-b-0">
                                                <td class="px-3 py-2">{{ idx + 1 }}</td>
                                                <td class="px-3 py-2">{{ inv.invoice_date || '—' }}</td>
                                                <td class="px-3 py-2">{{ inv.payment_date || '—' }}</td>
                                                <td class="px-3 py-2 font-semibold">{{ inv.invoice_number }}</td>
                                                <td class="px-3 py-2">{{ inv.head?.name || '' }}</td>
                                                <td class="px-3 py-2 text-right">{{ Number(inv.amount || 0).toFixed(2)
                                                    }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="rounded-sm px-2 py-1 text-xs font-bold"
                                                        :class="String(inv.status).toLowerCase() === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                                                        {{ String(inv.status || '').toUpperCase() }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2">
                                                    <button type="button"
                                                        class="rounded-sm bg-[#2b6cb0] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#245a94]"
                                                        @click="viewInvoice(inv)">
                                                        VIEW INVOICE
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else
                                    class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">
                                    No payment history found.</div>
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

const loading = computed(() => app?.studentPaymentHistoryLoading)
const error = computed(() => app?.studentPaymentHistoryError)
const invoices = computed(() => app?.studentPaymentHistoryInvoices || [])

function viewInvoice(inv) {
    return app?.studentDashboardViewInvoice?.(inv)
}
</script>
