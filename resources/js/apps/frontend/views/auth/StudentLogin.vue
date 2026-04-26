<template>
    <div class="relative min-h-[90vh] bg-[#f8fafc] overflow-hidden flex items-center justify-center p-4">
        <!-- Decorative Background Blobs -->
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-blue-50/50 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-teal-50/50 blur-3xl"></div>
        
        <div class="relative w-full max-w-[650px] py-6">
            <!-- Header Section -->
            <div class="text-center mb-6">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 shadow-sm border border-teal-100">
                    <svg class="h-8 w-8 text-[#0d6b75]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10L12 5L2 10L12 15L22 10Z"></path>
                        <path d="M6 12V17C6 17 6 20 12 20C18 20 18 17 18 17V12"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-slate-800 uppercase">
                    STUDENT <span class="text-[#0d6b75]">LOGIN</span>
                </h1>
                <div class="mx-auto my-4 flex items-center justify-center gap-2">
                    <div class="h-px w-8 bg-slate-200"></div>
                    <div class="h-2 w-2 rounded-full bg-teal-500"></div>
                    <div class="h-px w-8 bg-slate-200"></div>
                </div>
                <!-- <p class="text-[15px] font-medium text-slate-500">Welcome back! Please login to your account</p> -->
            </div>

            <div v-if="studentAuthError"
                class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-semibold text-red-600 animate-shake">
                {{ studentAuthError }}
            </div>

            <!-- Login Card -->
            <div class="rounded-[2.5rem] bg-white p-4 md:p-8 shadow-xl shadow-slate-200/50 border border-slate-50">
                <div class="flex items-center gap-4 mb-8">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal-50 text-[#0d6b75]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Account Login</h2>
                        <p class="text-sm font-medium text-slate-400">Please enter your credentials to login</p>
                    </div>
                </div>

                <form class="space-y-6" @submit.prevent="submitStudentLogin">
                    <!-- Email/Mobile Input -->
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-[12px] font-bold text-slate-700">
                            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Email or Mobile <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-5 text-emerald-500 transition-colors group-focus-within:text-[#0d6b75]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </div>
                            <input v-model="studentLoginForm.login" type="text"
                                class="h-14 w-full rounded-2xl border-2 border-emerald-50 bg-white pl-14 pr-4 text-[15px] font-bold text-slate-700 placeholder:text-slate-300 outline-none focus:border-[#0d6b75] focus:ring-4 focus:ring-[#0d6b75]/5 transition-all"
                                placeholder="Email or Mobile"
                                required />
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-[13px] font-bold text-slate-700">
                            <svg class="h-4 w-4 text-purple-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-5 text-purple-500 transition-colors group-focus-within:text-purple-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <input v-model="studentLoginForm.password" :type="showPassword ? 'text' : 'password'"
                                class="h-14 w-full rounded-2xl border-2 border-purple-50 bg-[#f8fafc] pl-14 pr-12 text-[15px] font-bold text-slate-700 placeholder:text-slate-300 outline-none focus:border-purple-200 focus:ring-4 focus:ring-purple-500/5 transition-all"
                                placeholder="••••••••••••"
                                required />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-5 text-slate-400 hover:text-slate-600">
                                <svg v-if="!showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            </button>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="text-[13px] font-bold text-indigo-600 hover:underline" @click="app.go('/forgot-password')">Forgot password?</button>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button type="submit"
                        class="group relative flex w-full h-14 items-center justify-center gap-3 overflow-hidden rounded-2xl bg-[#0d6b75] text-md font-bold text-white shadow-sm shadow-emerald-500/50 transition-all hover:-translate-y-1 hover:shadow-emerald-500/30 active:scale-[0.98] disabled:opacity-70"
                        :disabled="studentAuthLoading">
                        <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <span>{{ studentAuthLoading ? 'LOGGING IN...' : 'LOGIN' }}</span>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-[15px] font-medium text-slate-500">
                    Not registered? <button @click="app.go('/registration')" class="font-bold text-[#0d6b75] hover:underline">Apply Now</button>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue'

const app = inject('app')

const studentLoginForm = ref({
    login: '',
    password: '',
})

const studentAuthLoading = ref(false)
const studentAuthError = ref('')
const showPassword = ref(false)

const submitStudentLogin = async () => {
    if (studentAuthLoading.value) return
    studentAuthLoading.value = true
    studentAuthError.value = ''
    try {
        const res = await window.axios.post('/api/public/auth/login', studentLoginForm.value)
        app.studentMe = res?.data?.student || res?.data || null
        app.go('/dashboard')
    } catch (e) {
        studentAuthError.value = e?.response?.data?.message || 'Login failed.'
    } finally {
        studentAuthLoading.value = false
    }
}
</script>
