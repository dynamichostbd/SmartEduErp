<template>
<div class="mt-3 rounded-sm border border-slate-300 bg-white p-4 sm:p-6">
    <div class="mx-auto w-full max-w-xl">
        <div class="text-center text-xl font-extrabold text-slate-900 sm:text-2xl">STUDENT LOGIN</div>
        <div class="mx-auto mt-3 h-[2px] w-full max-w-md bg-red-500"></div>

        <div v-if="studentAuthError" class="mt-6 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            {{ studentAuthError }}
        </div>

        <form class="mt-8 flex flex-col gap-6" @submit.prevent="submitStudentLogin">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="min-w-0 mb-4">
                    <div class="text-sm font-semibold text-slate-900">Account Login</div>
                    <div class="mt-1 text-xs text-slate-500">Please enter your credentials to login</div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-semibold text-slate-600">Email or Mobile <span class="text-red-600">*</span></div>
                        <input
                            v-model="studentLoginForm.login"
                            type="text"
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                            required
                        />
                    </div>

                    <div>
                        <div class="text-xs font-semibold text-slate-600">Password <span class="text-red-600">*</span></div>
                        <input
                            v-model="studentLoginForm.password"
                            type="password"
                            class="mt-1 h-9 w-full rounded-sm border border-slate-300 bg-[#eaf2ff] px-3 text-sm shadow-sm outline-none focus:border-[#0d6b75] focus:ring-2 focus:ring-[#0d6b75]/10"
                            required
                        />
                    </div>

                    <div class="flex items-center justify-end">
                        <button
                            type="button"
                            class="rounded-sm border border-red-500 px-3 py-1 text-xs font-bold text-red-600 hover:bg-red-50"
                            @click="app.go('/forgot-password')"
                        >
                            Forgot password?
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center gap-3">
                <button
                    type="submit"
                    class="rounded-sm bg-[#0b1d4d] px-10 py-2.5 text-sm font-extrabold text-white hover:bg-[#09163c]"
                    :disabled="studentAuthLoading"
                >
                    {{ studentAuthLoading ? 'Logging in...' : 'LOGIN' }}
                </button>
                <button type="button" class="text-sm font-bold text-[#0d6b75] hover:underline" @click="app.go('/registration')">Not registered? Apply Now</button>
            </div>
        </form>
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
