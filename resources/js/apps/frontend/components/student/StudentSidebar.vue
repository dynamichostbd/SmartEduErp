<template>
    <div class="flex min-h-[75vh] flex-col overflow-hidden rounded-md border border-slate-200 bg-[#3f4b7a] text-white">
        <template v-if="variant === 'profile'">
            <div class="border-b border-white/15 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 overflow-hidden rounded-md bg-white/10">
                        <img v-if="studentMe?.profile" :src="studentMe.profile" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-xs font-extrabold">{{ (studentMe?.student_id || 'NA').slice(0, 2) }}</div>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-extrabold">{{ (studentMe?.name || '').toUpperCase() }}</div>
                        <div class="mt-1 text-xs font-semibold opacity-90">
                            <span v-if="studentMe?.student_id">{{ studentMe.student_id }}</span>
                            <span v-else>—</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="border-b border-white/15 px-4 py-3">
                <div class="text-sm font-extrabold">{{ (studentMe?.name || '').toUpperCase() }}</div>
                <div class="mt-1 text-xs font-semibold opacity-90">
                    <span v-if="studentMe?.student_id">{{ studentMe.student_id }}</span>
                    <span v-else>—</span>
                </div>
            </div>
        </template>

        <div class="flex-1 py-2">
            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'dashboard' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('dashboard', '/dashboard')"
            >
                <span v-if="active === 'dashboard'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l9-8 9 8" />
                    <path d="M5 10v10h5v-6h4v6h5V10" />
                </svg>
                <span>Dashboard</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'profile' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('profile', '/student/profile')"
            >
                <span v-if="active === 'profile'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21a8 8 0 10-16 0" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
                <span>My Profile</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'pay' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('pay', '/student/pay-now')"
            >
                <span v-if="active === 'pay'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="5" width="20" height="14" rx="2" />
                    <path d="M2 10h20" />
                </svg>
                <span>Pay Now</span>
            </button>

            <div class="my-2 border-t border-white/10"></div>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'history' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('history', '/student/payment-history')"
            >
                <span v-if="active === 'history'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 101-4" />
                    <path d="M3 4v4h4" />
                    <path d="M12 7v5l3 3" />
                </svg>
                <span>Payment History</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'fees' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('fees', '/student/fees')"
            >
                <span v-if="active === 'fees'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 6h13" />
                    <path d="M8 12h13" />
                    <path d="M8 18h13" />
                    <path d="M3 6h.01" />
                    <path d="M3 12h.01" />
                    <path d="M3 18h.01" />
                </svg>
                <span>Fees List</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'admit' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('admit', '/student/admit-card')"
            >
                <span v-if="active === 'admit'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 3H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V9z" />
                    <path d="M14 3v6h6" />
                    <path d="M8 13h8" />
                    <path d="M8 17h6" />
                </svg>
                <span>Admit Card</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'result' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('result', '/student/result')"
            >
                <span v-if="active === 'result'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="M7 14l4-4 3 3 5-6" />
                </svg>
                <span>Result</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'subject' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('subject', '/student/subjects')"
            >
                <span v-if="active === 'subject'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                </svg>
                <span>Subject Choice</span>
            </button>

            <button
                type="button"
                class="group relative flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-semibold transition"
                :class="active === 'password' ? 'bg-white/10 pl-3.5' : 'hover:bg-white/10'"
                @click="navigate('password', '/student/change-password')"
            >
                <span v-if="active === 'password'" class="absolute left-0 top-0 h-full w-1 bg-emerald-300"></span>
                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" />
                    <path d="M7 11V7a5 5 0 0110 0v4" />
                </svg>
                <span>Change Password</span>
            </button>
        </div>

        <div class="border-t border-white/15 px-4 py-3">
            <button
                type="button"
                class="w-full rounded-md bg-white/10 px-4 py-2.5 text-sm font-extrabold transition hover:bg-white/20"
                @click="onLogout"
                :disabled="logoutDisabled"
            >
                Logout
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    variant: { type: String, default: 'dashboard' },
    studentMe: { type: Object, default: null },
    active: { type: String, default: 'dashboard' },
    onNavigate: { type: Function, required: true },
    onLogout: { type: Function, required: true },
    logoutDisabled: { type: Boolean, default: false },
})

function navigate(key, href) {
    props.onNavigate(key, href)
}
</script>
