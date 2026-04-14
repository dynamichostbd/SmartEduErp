<template>
    <div class="min-h-screen bg-white text-slate-900">
        <div class="min-h-screen lg:flex">
            <div class="hidden lg:relative lg:flex lg:w-1/2 lg:items-center lg:justify-center lg:bg-white">
                <div class="absolute left-10 top-10 text-xl font-semibold leading-snug text-slate-900">
                    {{ appName }}
                </div>
                <img
                    :src="loginbgImage"
                    alt="Login"
                    class="h-auto w-[78%] max-w-[460px]"
                />
            </div>

            <div class="lg:hidden bg-white px-6 pt-10">
                <div class="text-center text-lg font-semibold text-slate-900">{{ appName }}</div>
                <div class="mt-6 flex justify-center">
                    <img
                        :src="loginbgImage"
                        alt="Login"
                        class="h-auto w-full max-w-[420px]"
                    />
                </div>
            </div>

            <div class="flex w-full items-center justify-center bg-[#075985] px-6 py-12 lg:w-1/2">
                <div class="w-full max-w-sm">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/10">
                            <!-- <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 8V7a3 3 0 1 1 6 0v3H9Z" fill="currentColor"/>
                            </svg> -->
                            <img
                                :src="loginImage"
                                alt="Login"
                                class="h-16 w-16"
                            />
                        </div>
                        <div class="mt-5 text-xl font-semibold text-white">Admin Login</div>
                        <div class="mt-1 text-sm text-white/80">Welcome Back!!</div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <div>
                            <!-- <label class="text-sm text-center font-medium text-white/90">Email</label> -->
                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="username"
                                class="mt-2 text-center w-full rounded-sm border border-white/15 bg-white/95 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-white/40 focus:bg-white"
                                :disabled="step !== 'credentials' || loading"
                            />
                        </div>

                        <div>
                            <!-- <label class="text-sm text-center font-medium text-white/90">Password</label> -->
                            <input
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                class="mt-2 text-center w-full rounded-sm border border-white/15 bg-white/95 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-white/40 focus:bg-white"
                                :disabled="step !== 'credentials' || loading"
                            />
                        </div>

                        <div v-if="step === 'otp'" class="rounded-sm border border-white/15 bg-white/10 p-4">
                            <div class="text-sm font-semibold text-white">OTP Verification</div>
                            <div class="mt-1 text-sm text-white/80">
                                Enter the 6-digit OTP sent to your registered mobile number.
                            </div>

                            <div class="mt-3">
                                <label class="text-sm font-medium text-white/90">OTP</label>
                                <input
                                    v-model="form.otp"
                                    inputmode="numeric"
                                    maxlength="6"
                                    class="mt-2 w-full rounded-sm border border-white/15 bg-white/95 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-white/40 focus:bg-white"
                                    placeholder="e.g. 123456"
                                    :disabled="loading"
                                />
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <button
                                    class="rounded-sm bg-white/10 px-3 py-2 text-sm font-medium text-white hover:bg-white/15"
                                    :disabled="loading"
                                    @click="backToCredentials"
                                >
                                    Back
                                </button>
                                <button
                                    class="flex-1 rounded-sm bg-white px-3 py-2 text-sm font-semibold text-[#075985] hover:bg-white/90"
                                    :disabled="loading || !form.otp"
                                    @click="verifyOtpAndLogin"
                                >
                                    {{ loading ? 'Please wait...' : 'Verify & Login' }}
                                </button>
                            </div>
                            
                        </div>

                        <button
                            v-if="step === 'credentials'"
                            class="w-full rounded-sm bg-white px-3 py-2 text-sm font-semibold text-[#075985] hover:bg-white/90"
                            :disabled="loading || !form.email || !form.password"
                            @click="startLogin"
                        >
                            {{ loading ? 'Please wait...' : 'Login' }}
                        </button>

                        <!-- <div class="pt-2 text-center text-xs text-white/75">
                            If OTP is enabled for your account, you will be asked to verify.
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'LoginApp',
    mounted() {
        // Initialize simple toast system for login page
        this.initToastSystem()
    },
    data() {
        const baseurl = (window.laravel?.baseurl || '').replace(/\/$/, '')
        return {
            appName: window.laravel?.app_name ?? 'ERP',
            loginImage: `${baseurl}/images/login.png`,
            loginbgImage: `${baseurl}/images/loginbg.svg`,
            loading: false,
            error: '',
            message: '',
            step: 'credentials',
            lastAction: '',
            form: {
                email: '',
                password: '',
                otp: '',
            },
        }
    },
    methods: {
        initToastSystem() {
            // Create simple toast container if not exists
            if (!document.getElementById('toast-container')) {
                const container = document.createElement('div')
                container.id = 'toast-container'
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                `
                document.body.appendChild(container)
            }
            
            // Create global toast function
            window.$toast = {
                success: (message) => this.showToast(message, 'success'),
                error: (message) => this.showToast(message, 'error'),
                clear: () => this.clearToasts()
            }
        },
        showToast(message, type = 'success') {
            const container = document.getElementById('toast-container')
            const toast = document.createElement('div')
            const bgColor = type === 'success' ? '#10b981' : '#ef4444'
            const textColor = '#ffffff'
            
            toast.style.cssText = `
                background-color: ${bgColor};
                color: ${textColor};
                padding: 12px 16px;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                font-size: 14px;
                font-weight: 500;
                max-width: 300px;
                animation: slideIn 0.3s ease-out;
                margin-bottom: 10px;
            `
            
            toast.textContent = message
            container.appendChild(toast)
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out'
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast)
                    }
                }, 300)
            }, 3000)
        },
        clearToasts() {
            const container = document.getElementById('toast-container')
            if (container) {
                container.innerHTML = ''
            }
        },
        backToCredentials() {
            this.step = 'credentials'
            this.form.otp = ''
            // Clear any existing toasts
            if (window.$toast) {
                window.$toast.clear()
            }
            // Clear error and message states
            this.error = ''
            this.message = ''
        },
        async startLogin() {
            // Clear error and message states
            this.error = ''
            this.message = ''
            this.loading = true

            try {
                this.lastAction = 'send-otp'
                const otpRes = await window.axios.post('/admin/send-otp', {
                    email: this.form.email,
                    password: this.form.password,
                })

                if (otpRes?.data?.data?.verified_otp) {
                    this.lastAction = 'system-admin'
                    await this.doLogin()
                    return
                }

                this.step = 'otp'
                // Use toast system if available
                if (window.$toast) {
                    window.$toast.success(otpRes?.data?.message || 'OTP sent. Please check your mobile.')
                } else {
                    this.message = otpRes?.data?.message || 'OTP sent. Please check your mobile.'
                }
            } catch (e) {
                const msg = e?.response?.data?.message || 'Login failed'
                // Use toast system if available
                if (window.$toast) {
                    window.$toast.error(this.lastAction ? `[${this.lastAction}] ${msg}` : msg)
                } else {
                    this.error = this.lastAction ? `[${this.lastAction}] ${msg}` : msg
                }
            } finally {
                this.loading = false
            }
        },
        async verifyOtpAndLogin() {
            // Clear error and message states
            this.error = ''
            this.message = ''
            this.loading = true

            try {
                this.lastAction = 'verify-otp'
                await window.axios.post('/admin/verify-otp', {
                    email: this.form.email,
                    otp: this.form.otp,
                })

                this.lastAction = 'system-admin'
                await this.doLogin()
            } catch (e) {
                const msg = e?.response?.data?.message || 'OTP verification failed'
                // Use toast system if available
                if (window.$toast) {
                    window.$toast.error(this.lastAction ? `[${this.lastAction}] ${msg}` : msg)
                } else {
                    this.error = this.lastAction ? `[${this.lastAction}] ${msg}` : msg
                }
            } finally {
                this.loading = false
            }
        },
        async doLogin() {
            // Clear error and message states
            this.error = ''
            this.message = ''
            
            const loginRes = await window.axios.post('/admin/system-admin', {
                email: this.form.email,
                password: this.form.password,
            })

            // Use toast system if available
            if (window.$toast) {
                window.$toast.success(loginRes?.data?.message || 'Login OK')
            } else {
                this.message = loginRes?.data?.message || 'Login OK'
            }
            
            window.location = `${window.laravel?.baseurl || ''}/admin/dashboard`
        },
    },
}
</script>

<style scoped>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}
</style>
