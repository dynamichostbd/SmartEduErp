<template>
    <div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
        <div class="mx-auto w-full max-w-6xl">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <div class="overflow-hidden rounded-sm border border-slate-300 bg-[#3f4b7a] text-white">
                        <div class="border-b border-white/15 px-4 py-3">
                            <div class="text-sm font-bold">{{ (studentMe?.name || '').toUpperCase() }}</div>
                            <div class="mt-1 text-xs font-semibold opacity-90">
                                <span v-if="studentMe?.student_id">{{ studentMe.student_id }}</span>
                                <span v-else>—</span>
                            </div>
                        </div>
                        <div class="py-2">
                            <a href="/dashboard"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'dashboard' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'dashboard'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 11l9-8 9 8" />
                                    <path d="M5 10v10h5v-6h4v6h5V10" />
                                </svg>
                                <span>Dashboard</span>
                            </a>

                            <a href="/student/profile"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'profile' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'profile'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21a8 8 0 10-16 0" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                <span>My Profile</span>
                            </a>

                            <a href="/student/pay-now"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'pay' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'pay'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <path d="M2 10h20" />
                                </svg>
                                <span>Pay Now</span>
                            </a>

                            <div class="my-2 border-t border-white/10"></div>

                            <a href="/student/payment-history"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'history' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'history'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 12a9 9 0 101-4" />
                                    <path d="M3 4v4h4" />
                                    <path d="M12 7v5l3 3" />
                                </svg>
                                <span>Payment History</span>
                            </a>

                            <a href="/student/fees"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'fees' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'fees'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 6h13" />
                                    <path d="M8 12h13" />
                                    <path d="M8 18h13" />
                                    <path d="M3 6h.01" />
                                    <path d="M3 12h.01" />
                                    <path d="M3 18h.01" />
                                </svg>
                                <span>Fees List</span>
                            </a>

                            <a href="/student/admit-card"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'admit' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'admit'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9z" />
                                    <path d="M14 3v6h6" />
                                    <path d="M8 13h8" />
                                    <path d="M8 17h6" />
                                </svg>
                                <span>Admit Card</span>
                            </a>

                            <a href="/student/result"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'result' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'result'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 3v18h18" />
                                    <path d="M7 14l4-4 3 3 5-6" />
                                </svg>
                                <span>Result</span>
                            </a>

                            <a href="/student/subjects"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'subject' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'subject'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                </svg>
                                <span>Subject Choice</span>
                            </a>

                            <a href="/student/change-password"
                                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                                :class="studentSidebarActive === 'password' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'">
                                <span v-if="studentSidebarActive === 'password'"
                                    class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" />
                                    <path d="M7 11V7a5 5 0 0110 0v4" />
                                </svg>
                                <span>Change Password</span>
                            </a>
                        </div>
                        <div class="border-t border-white/15 px-4 py-3">
                            <button type="button"
                                class="w-full rounded-sm bg-white/10 px-4 py-2.5 text-sm font-bold transition hover:bg-white/20"
                                @click="doStudentLogout" :disabled="studentAuthLoading">Logout</button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-9">
                    <div class="rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800">
                            INSTITUTION PAID INVOICE</div>
                        <div class="p-4">
                            <div v-if="studentDashboardLoading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else-if="studentDashboardError"
                                class="rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">{{
                                studentDashboardError }}</div>
                            <div v-else>
                                <div v-if="studentDashboardInvoices.length" class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr
                                                class="border-b bg-slate-100 text-left text-xs font-bold text-slate-700">
                                                <th class="px-3 py-2">#</th>
                                                <th class="px-3 py-2">Invoice Date</th>
                                                <th class="px-3 py-2">Invoice No.</th>
                                                <th class="px-3 py-2">Purpose</th>
                                                <th class="px-3 py-2 text-right">Amount</th>
                                                <th class="px-3 py-2">Status</th>
                                                <th class="px-3 py-2">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(inv, idx) in studentDashboardInvoices" :key="'sdinv-' + inv.id"
                                                class="border-b last:border-b-0">
                                                <td class="px-3 py-2">{{ idx + 1 }}</td>
                                                <td class="px-3 py-2">{{ inv.invoice_date || '—' }}</td>
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
                                                        @click="studentDashboardViewInvoice(inv)">
                                                        VIEW INVOICE
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else
                                    class="rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">
                                    No paid invoices found.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-sm border border-slate-300 bg-white">
                        <div class="border-b border-slate-300 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-800">
                            RECENT NOTICE</div>
                        <div class="p-4">
                            <div v-if="studentDashboardNoticeLoading" class="text-sm text-slate-600">Loading...</div>
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b bg-slate-100 text-left text-xs font-bold text-slate-700">
                                            <th class="px-3 py-2">#</th>
                                            <th class="px-3 py-2">Date</th>
                                            <th class="px-3 py-2">Title</th>
                                            <th class="px-3 py-2">Image / File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(n, idx) in studentDashboardRecentNotices" :key="'sdn-' + n.id"
                                            class="border-b last:border-b-0">
                                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                                            <td class="px-3 py-2">{{ n.date || '—' }}</td>
                                            <td class="px-3 py-2">
                                                <button type="button"
                                                    class="text-left font-semibold text-[#0d6b75] hover:underline"
                                                    @click="go('/notices/' + n.id)">{{ n.title }}</button>
                                            </td>
                                            <td class="px-3 py-2">
                                                <button type="button"
                                                    class="rounded-sm border border-slate-300 bg-white px-2 py-1 text-xs font-bold text-slate-700 hover:bg-slate-50"
                                                    @click="go('/notices/' + n.id)">View</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="!studentDashboardRecentNotices.length"
                                    class="mt-3 rounded-sm border border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-700">
                                    No notices found.</div>
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

const app = inject('app')

const studentMe = computed(() => app?.studentMe)
const studentSidebarActive = computed(() => app?.studentSidebarActive)
const studentAuthLoading = computed(() => app?.studentAuthLoading)

const studentDashboardLoading = computed(() => app?.studentDashboardLoading)
const studentDashboardError = computed(() => app?.studentDashboardError)
const studentDashboardInvoices = computed(() => app?.studentDashboardInvoices || [])
const studentDashboardNoticeLoading = computed(() => app?.studentDashboardNoticeLoading)
const studentDashboardRecentNotices = computed(() => app?.studentDashboardRecentNotices || [])

function go(href) {
    return app?.go?.(href)
}

function studentSidebarGo(key, href) {
    return app?.studentSidebarGo?.(key, href)
}

function doStudentLogout() {
    return app?.doStudentLogout?.()
}

function studentDashboardViewInvoice(inv) {
    return app?.studentDashboardViewInvoice?.(inv)
}
</script>
