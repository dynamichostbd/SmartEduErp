<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Available Fees</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-600">
                        <th class="px-6 py-4 font-bold uppercase tracking-wider">Account Head</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-right">Amount</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="f in fees" :key="f.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-slate-900 font-medium">{{ f.account_head?.name }}</div>
                            <div class="text-xs text-slate-400">Regular Fee</div>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-700">{{ Number(f.amount || 0).toFixed(2) }} BDT</td>
                        <td class="px-6 py-4 text-center">
                            <span v-if="f.paid" class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-600">Paid</span>
                            <span v-else class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-600">Unpaid</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button 
                                v-if="!f.paid"
                                class="rounded-sm bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition-colors"
                                @click="injectGo('/student/pay-now?head=' + f.account_head_id)"
                            >
                                Pay Now
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!fees.length && !loading">
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">No fees available.</td>
                    </tr>
                    <tr v-if="loading">
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">Loading fees...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue'

const fees = inject('studentFees', [])
const loading = inject('studentFeesLoading', false)
const injectGo = inject('go')
</script>
