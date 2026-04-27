<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-600">
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Invoice No.</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Payment For</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-right">Amount</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-slate-900 font-medium">{{ inv.invoice_number }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ inv.head?.name || '—' }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ inv.payment_date || inv.invoice_date }}</td>
                        <td class="px-6 py-4 text-emerald-600 font-bold text-right">{{ Number(inv.amount || 0).toFixed(2) }} BDT</td>
                        <td class="px-6 py-4 text-center">
                            <button 
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-600 transition-colors"
                                title="View/Download Invoice"
                                @click="injectGo('/student/invoices/' + inv.id)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!invoices.length && !loading">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="h-10 w-10 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>No payment records found.</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="loading">
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">Loading records...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue'

const invoices = inject('studentPaymentHistoryInvoices', [])
const loading = inject('studentPaymentHistoryLoading', false)
const injectGo = inject('go')
</script>
