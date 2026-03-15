<template>
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <div class="pointer-events-none fixed right-4 top-4 z-[9999] flex w-[320px] max-w-[90vw] flex-col gap-2">
            <div
                v-for="t in toasts"
                :key="t.id"
                class="pointer-events-auto rounded-xl border p-3 shadow-xl"
                :class="t.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : t.type === 'error' ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-slate-200 bg-white text-slate-900'"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1 text-sm font-semibold">{{ t.message }}</div>
                    <button type="button" class="-mr-1 -mt-1 rounded-md px-2 py-1 text-xs font-semibold hover:bg-black/5" @click="removeToast(t.id)">✕</button>
                </div>
            </div>
        </div>

        <div ref="adminHeader" class="sticky top-0 z-30 w-full border-b border-emerald-200 bg-white relative">
            <div class="mx-auto flex h-[70px] w-full items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex items-center gap-3 min-w-0">
                    <button class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 lg:hidden" @click="sidebarOpen = true">Menu</button>
                    <img
                        v-if="collegeLogo"
                        :src="collegeLogo"
                        class="h-9 w-9 rounded border border-slate-200 bg-white object-contain"
                        alt="logo"
                    />
                    <div class="min-w-0">
                        <a href="#" class="truncate text-sm font-semibold text-slate-900 hover:underline" @click.prevent.stop="goDashboard">{{ collegeName }}</a>
                        <div class="text-[11px] text-slate-500">{{ pageTitle }}</div>
                    </div>
                </div>

                <div class="relative" @keydown.esc.stop.prevent="userMenuOpen = false">
                    <button type="button" class="flex items-center gap-3 rounded-lg px-2 py-1 hover:bg-slate-100" @click="userMenuOpen = !userMenuOpen">
                        <img
                            v-if="profileImage"
                            :src="profileImage"
                            class="h-9 w-9 rounded-full border border-slate-200 object-cover"
                            alt="user avatar"
                        />
                        <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">{{ profileInitials }}</div>
                        <div class="hidden sm:block text-left">
                            <div class="text-xs font-semibold text-slate-800">{{ profileName }}</div>
                            <div class="text-[11px] text-slate-500">{{ profileRole }}</div>
                        </div>
                    </button>

                    <div v-if="userMenuOpen" class="absolute right-0 mt-2 w-56 rounded-xl border border-slate-200 bg-white py-2 shadow-xl" @click.stop>
                        <button
                            v-if="hasPermission('admin.show')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('profile')"
                        >
                            <span class="text-slate-500">👤</span>
                            <span>Profile</span>
                        </button>
                        <button
                            v-if="hasPermission('admin.index')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('admin')"
                        >
                            <span class="text-slate-500">🧑</span>
                            <span>Admin</span>
                        </button>
                        <button
                            v-if="hasPermission('role.index')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('role')"
                        >
                            <span class="text-slate-500">🔑</span>
                            <span>Role</span>
                        </button>
                        <button
                            v-if="hasPermission('siteSetting.index')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('siteSettings')"
                        >
                            <span class="text-slate-500">⚙️</span>
                            <span>Site Settings</span>
                        </button>
                        <button
                            v-if="hasPermission('menu.index')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('menuList')"
                        >
                            <span class="text-slate-500">📋</span>
                            <span>Menu List</span>
                        </button>
                        <button
                            v-if="hasPermission('activityLog.index')"
                            type="button"
                            class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                            @click="goUserMenu('activityLog')"
                        >
                            <span class="text-slate-500">📊</span>
                            <span>Activity Log</span>
                        </button>

                        <div class="my-2 border-t border-slate-100"></div>

                        <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" :disabled="loggingOut" @click="logout">
                            <span class="text-slate-500">↩</span>
                            <span>{{ loggingOut ? 'Logging out...' : 'Logout' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="w-full border-t border-emerald-200 bg-emerald-50" @click.stop>
                <div class="mx-auto flex h-[60px] w-full items-center justify-between gap-1 overflow-x-auto px-2 sm:px-4">
                    <button
                        v-for="item in menuTree"
                        :key="'top-' + item.id"
                        type="button"
                        class="flex h-9 shrink-0 items-center gap-2 rounded-lg px-2 text-sm font-medium text-slate-700 hover:bg-emerald-100"
                        :class="activeTopMenuId === item.id && megaMenuOpen ? 'bg-emerald-100 text-emerald-900' : ''"
                        @mouseenter="openMegaMenu(item, $event)"
                        @mouseleave="scheduleMegaClose"
                        @click.stop.prevent="toggleMegaMenu(item, $event)"
                    >
                        <span class="text-emerald-700" v-html="menuIcon(item, 'parent')"></span>
                        <span class="truncate">{{ item.menu_name || item.name || item.title || 'Menu' }}</span>
                    </button>
                </div>

                <div v-if="megaMenuOpen && activeTopMenu" class="absolute top-full z-40" :style="megaPanelStyle">
                    <div ref="megaPanel" class="px-2" @click.stop @mouseenter="cancelMegaClose" @mouseleave="scheduleMegaClose">
                        <div class="rounded-xl border border-emerald-600 bg-white/95 p-3 shadow-xl backdrop-blur">
                            <div v-if="megaMenuSimple" class="space-y-1">
                                <a
                                    v-for="leaf in getChildren(activeTopMenu)"
                                    :key="'mega-simple-' + leaf.id"
                                    href="#"
                                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-emerald-100"
                                    @click.prevent="handleMenuClick(leaf)"
                                >
                                    <span class="text-emerald-700" v-html="menuIcon(leaf, 'child')"></span>
                                    <span class="min-w-0 whitespace-normal">{{ leaf.menu_name || leaf.name || leaf.title || 'Item' }}</span>
                                </a>
                            </div>

                            <div v-else class="grid gap-6" :style="megaGridStyle">
                                <div v-for="child in getChildren(activeTopMenu)" :key="'mega-' + child.id" class="min-w-0">
                                    <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ child.menu_name || child.name || child.title || 'Section' }}</div>
                                    <div class="space-y-1">
                                        <a
                                            v-for="grand in (getChildren(child).length ? getChildren(child) : [child])"
                                            :key="'mega-item-' + grand.id"
                                            href="#"
                                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-emerald-900 hover:bg-emerald-100"
                                            @click.prevent="handleMenuClick(grand)"
                                        >
                                            <span class="text-emerald-700" v-html="menuIcon(grand, 'child')"></span>
                                            <span class="min-w-0 whitespace-normal">{{ grand.menu_name || grand.name || grand.title || 'Item' }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-slate-900/40" @click="sidebarOpen = false"></div>
            <aside class="absolute inset-y-0 left-0 w-80 max-w-[85vw] bg-slate-900 text-slate-100 shadow-2xl">
                <div class="flex h-14 items-center justify-between border-b border-white/10 px-4">
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold">{{ collegeName }}</div>
                        <div class="text-[11px] text-slate-300">Admin Panel</div>
                    </div>
                    <button class="rounded-md bg-white/10 px-2 py-1 text-xs hover:bg-white/15" @click="sidebarOpen = false">Close</button>
                </div>
                <nav class="h-[calc(100vh-56px)] overflow-y-auto px-3 py-3">
                    <div v-if="loading" class="px-2 py-2 text-sm text-slate-300">Loading menus...</div>
                    <div v-else>
                        <div v-for="item in menuTree" :key="'side-' + item.id" class="mb-1">
                            <button class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-white/10" @click="toggleSection(item.id)">
                                <span class="min-w-0 truncate font-medium">{{ item.menu_name || item.name || item.title || 'Menu' }}</span>
                                <span v-if="getChildren(item).length" class="text-xs text-slate-300">{{ openSections[item.id] ? '−' : '+' }}</span>
                            </button>
                            <div v-if="openSections[item.id]" class="ml-3 mt-1 space-y-1">
                                <a
                                    v-for="child in flattenLeafChildren(item)"
                                    :key="'side-leaf-' + child.id"
                                    href="#"
                                    class="block rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-white/10"
                                    @click.prevent="handleMenuClick(child)"
                                >
                                    {{ child.menu_name || child.name || child.title || 'Item' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>
            </aside>
        </div>

        <main class="mx-auto w-full p-4 sm:p-6">
            <div v-if="error" class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ error }}</div>
            <component :is="resolvedComponent" v-bind="resolvedProps" />
        </main>
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue'

const pages = import.meta.glob('./pages/*.vue')

export default {
    name: 'AdminAppFixed',
    data() {
        return {
            loading: true,
            error: '',
            systems: {},
            toasts: [],
            toastSeq: 0,
            sidebarOpen: false,
            openSections: {},
            megaMenuOpen: false,
            activeTopMenuId: null,
            megaCloseTimer: null,
            megaPanelLeftPx: 12,
            megaPanelWidthPx: 320,
            megaMenuSimple: false,
            megaGridColumns: 1,
            _lastMegaAnchorEl: null,
            currentPath: `${window.location.pathname || ''}${window.location.search || ''}`,
            userMenuOpen: false,
            loggingOut: false,
        }
    },
    computed: {
        menuTree() {
            return Array.isArray(this.systems?.menus) ? this.systems.menus : []
        },
        activeTopMenu() {
            const id = this.activeTopMenuId
            return (this.menuTree || []).find((x) => String(x?.id) === String(id)) || null
        },
        megaPanelStyle() {
            const left = Number(this.megaPanelLeftPx || 0)
            const width = Number(this.megaPanelWidthPx || 0)
            const out = {}
            if (Number.isFinite(left)) out.left = `${Math.max(0, left)}px`
            if (Number.isFinite(width) && width > 0) out.width = `${width}px`
            return out
        },
        megaGridStyle() {
            const cols = Number(this.megaGridColumns || 1)
            const safeCols = Number.isFinite(cols) && cols > 0 ? Math.min(4, Math.max(1, cols)) : 1
            return {
                gridTemplateColumns: `repeat(${safeCols}, minmax(0, 1fr))`,
            }
        },
        profileName() {
            return this.systems?.global?.auth_user?.name || 'Admin'
        },
        profileInitials() {
            const name = (this.profileName || '').trim()
            if (!name) return 'A'
            const parts = name.split(/\s+/).filter(Boolean)
            const first = parts[0]?.[0] || 'A'
            const last = (parts.length > 1 ? parts[parts.length - 1]?.[0] : '') || ''
            return `${first}${last}`.toUpperCase()
        },
        profileRole() {
            return this.systems?.global?.auth_user?.type || ''
        },
        profileImage() {
            const raw = this.systems?.profile || this.systems?.global?.auth_user?.profile || ''
            const v = String(raw || '').trim()
            if (!v) return ''
            if (v.startsWith('http://') || v.startsWith('https://') || v.startsWith('data:')) return v
            if (v.startsWith('/')) return v
            return `/${v}`
        },
        collegeLogo() {
            const raw = this.systems?.site?.college_logo || this.systems?.site?.logo || this.systems?.site?.logo_small || ''
            return this.resolveAssetUrl(raw)
        },
        collegeName() {
            return this.systems?.site?.college_name || this.systems?.site?.title || (window.laravel?.app_name || 'ERP')
        },
        pageTitle() {
            return this.currentPath || ''
        },
        resolved() {
            const raw = String(this.currentPath || '')
            const p = raw.split('?')[0].replace(/\/+$/, '') || '/admin/dashboard'

            // Dashboard
            if (p === '/admin' || p === '/admin/dashboard') {
                return { file: './pages/Dashboard.vue', props: { systems: this.systems } }
            }

            // Academic Session
            if (p === '/admin/academicSession') return { file: './pages/AcademicSessionIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/academicSession/create') return { file: './pages/AcademicSessionManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/academicSession\/[0-9]+\/edit$/)) return { file: './pages/AcademicSessionManage.vue', props: { systems: this.systems, sessionId: this.numericId('/admin/academicSession/') } }
            if (p.match(/^\/admin\/academicSession\/[0-9]+$/)) return { file: './pages/AcademicSessionView.vue', props: { sessionId: this.numericId('/admin/academicSession/') } }

            // Academic Level
            if (p === '/admin/academicQualification') return { file: './pages/AcademicQualificationIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/academicQualification/create') return { file: './pages/AcademicQualificationManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/academicQualification\/[0-9]+\/edit$/)) return { file: './pages/AcademicQualificationManage.vue', props: { systems: this.systems, qualificationId: this.numericId('/admin/academicQualification/') } }
            if (p.match(/^\/admin\/academicQualification\/[0-9]+$/)) return { file: './pages/AcademicQualificationView.vue', props: { qualificationId: this.numericId('/admin/academicQualification/') } }

            // Academic Class
            if (p === '/admin/academicClass') return { file: './pages/AcademicClassIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/academicClass/create') return { file: './pages/AcademicClassManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/academicClass\/[0-9]+\/edit$/)) return { file: './pages/AcademicClassManage.vue', props: { systems: this.systems, classId: this.numericId('/admin/academicClass/') } }
            if (p.match(/^\/admin\/academicClass\/[0-9]+$/)) return { file: './pages/AcademicClassView.vue', props: { classId: this.numericId('/admin/academicClass/') } }

            // Department
            if (p === '/admin/department') return { file: './pages/DepartmentIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/department/create') return { file: './pages/DepartmentManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/department\/[0-9]+\/edit$/)) return { file: './pages/DepartmentManage.vue', props: { systems: this.systems, departmentId: this.numericId('/admin/department/') } }
            if (p.match(/^\/admin\/department\/[0-9]+$/)) return { file: './pages/DepartmentView.vue', props: { departmentId: this.numericId('/admin/department/') } }

            // Admin
            if (p === '/admin/admin') return { file: './pages/AdminIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/admin/create') return { file: './pages/AdminManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/admin\/[0-9]+\/edit$/)) return { file: './pages/AdminManage.vue', props: { systems: this.systems, adminId: this.numericId('/admin/admin/') } }
            if (p.match(/^\/admin\/admin\/[0-9]+$/)) return { file: './pages/AdminShow.vue', props: { systems: this.systems, adminId: this.numericId('/admin/admin/') } }

            // Role
            if (p === '/admin/role') return { file: './pages/RoleIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/role/create') return { file: './pages/RoleManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/role\/[0-9]+\/edit$/)) return { file: './pages/RoleManage.vue', props: { systems: this.systems, roleId: this.numericId('/admin/role/') } }
            if (p.match(/^\/admin\/role\/[0-9]+$/)) return { file: './pages/RoleShow.vue', props: { systems: this.systems, roleId: this.numericId('/admin/role/') } }

            // Site Setting
            if (p === '/admin/siteSetting') return { file: './pages/SiteSettingEdit.vue', props: { systems: this.systems, settingId: 1 } }
            if (p.match(/^\/admin\/siteSetting\/[0-9]+\/edit$/)) return { file: './pages/SiteSettingEdit.vue', props: { systems: this.systems, settingId: this.numericId('/admin/siteSetting/') } }

            // Menu
            if (p === '/admin/menu') return { file: './pages/MenuIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/menu/create') return { file: './pages/MenuManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/menu\/[0-9]+\/edit$/)) return { file: './pages/MenuManage.vue', props: { systems: this.systems, menuId: this.numericId('/admin/menu/') } }
            if (p.match(/^\/admin\/menu\/[0-9]+$/)) return { file: './pages/MenuShow.vue', props: { systems: this.systems, menuId: this.numericId('/admin/menu/') } }

            // Activity Log
            if (p === '/admin/activityLog') return { file: './pages/ActivityLogIndex.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/activityLog\/[0-9]+$/)) return { file: './pages/ActivityLogShow.vue', props: { systems: this.systems, logId: this.numericId('/admin/activityLog/') } }

            // Student
            if (p === '/admin/student') return { file: './pages/StudentsIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/student/create') return { file: './pages/StudentCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/student\/[0-9]+\/edit$/)) return { file: './pages/StudentEdit.vue', props: { systems: this.systems, studentId: this.numericId('/admin/student/') } }
            if (p.match(/^\/admin\/student\/[0-9]+$/)) return { file: './pages/StudentShow.vue', props: { systems: this.systems, studentId: this.numericId('/admin/student/') } }
            if (p === '/admin/student-import') return { file: './pages/StudentImport.vue', props: { systems: this.systems } }

            if (p === '/admin/studentPromotion/create') return { file: './pages/StudentPromotionCreate.vue', props: { systems: this.systems } }
            if (p === '/admin/idcard') return { file: './pages/StudentIDCard.vue', props: { systems: this.systems } }
            if (p === '/admin/studentMigrationRollVerify') return { file: './pages/StudentMigrationRollVerifyIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/studentMigrationRollVerify/create') return { file: './pages/StudentMigrationRollVerifyCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/studentMigrationRollVerify\/[0-9]+$/)) return { file: './pages/StudentMigrationRollVerifyView.vue', props: { systems: this.systems, groupId: this.numericId('/admin/studentMigrationRollVerify/') } }
            if (p === '/admin/studentMigration') return { file: './pages/StudentMigrationIndex.vue', props: { systems: this.systems } }

            if (p === '/admin/registrationNoVerify') return { file: './pages/RegistrationNoVerifyIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/registrationNoVerify/create') return { file: './pages/RegistrationNoVerifyCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/registrationNoVerify\/[0-9]+$/)) return { file: './pages/RegistrationNoVerifyView.vue', props: { systems: this.systems, verifyId: this.numericId('/admin/registrationNoVerify/') } }

            // Admit Card
            if (p === '/admin/admitCard') return { file: './pages/AdmitCardIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/admitCard/create') return { file: './pages/AdmitCardCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/admitCard\/[0-9]+$/)) return { file: './pages/AdmitCardView.vue', props: { systems: this.systems, admitCardId: this.numericId('/admin/admitCard/') } }
            if (p === '/admin/admin-admit-card-four-in-one') return { file: './pages/AdminAdmitCardFourInOne.vue', props: { systems: this.systems } }
            if (p === '/admin/admin-admit-card-two-in-one') return { file: './pages/AdminAdmitCardTwoInOne.vue', props: { systems: this.systems } }
            if (p === '/admin/exam-seat-card') return { file: './pages/SeatCard.vue', props: { systems: this.systems } }
            if (p === '/admin/exam-attendance-sheet') return { file: './pages/ExamAttendanceSheet.vue', props: { systems: this.systems } }

            // Attendance
            if (p === '/admin/attendance') return { file: './pages/AttendanceIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/attendance/create') return { file: './pages/AttendanceCreate.vue', props: { systems: this.systems } }
            if (p === '/admin/attendance-report') return { file: './pages/AttendanceReport.vue', props: { systems: this.systems } }
            if (p === '/admin/attendance-sheet') return { file: './pages/AttendanceSheet.vue', props: { systems: this.systems } }
            if (p === '/admin/attendanceSummary') return { file: './pages/AttendanceSummaryIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/attendanceSummary/create') return { file: './pages/AttendanceSummaryCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/attendanceSummary\/[0-9]+$/)) return { file: './pages/AttendanceSummaryView.vue', props: { systems: this.systems, summaryId: this.numericId('/admin/attendanceSummary/') } }

            // Result
            if (p === '/admin/result') return { file: './pages/ResultIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/result/create') return { file: './pages/ResultCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/result\/[0-9]+\/edit$/)) return { file: './pages/ResultEdit.vue', props: { systems: this.systems, resultId: this.numericId('/admin/result/') } }
            if (p.match(/^\/admin\/result\/[0-9]+$/)) return { file: './pages/ResultView.vue', props: { systems: this.systems, resultId: this.numericId('/admin/result/') } }
            if (p === '/admin/result-report') return { file: './pages/ResultReport.vue', props: { systems: this.systems } }
            if (p === '/admin/subjectwise-result') return { file: './pages/ResultSubjectwiseResult.vue', props: { systems: this.systems } }
            if (p === '/admin/tabulation-sheet') return { file: './pages/ResultTabulationSheet.vue', props: { systems: this.systems } }
            if (p === '/admin/tabulation-sheet-ct') return { file: './pages/ResultTabulationSheetCT.vue', props: { systems: this.systems } }
            if (p === '/admin/tabulation-sheet-v2') return { file: './pages/ResultTabulationSheetV2.vue', props: { systems: this.systems } }
            if (p === '/admin/result-grade-summary') return { file: './pages/ResultGradeSummary.vue', props: { systems: this.systems } }

            // Class Test Result
            if (p === '/admin/classTestResult') return { file: './pages/ClassTestResultIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/classTestResult/create') return { file: './pages/ClassTestResultCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/classTestResult\/[0-9]+\/edit$/)) return { file: './pages/ClassTestResultEdit.vue', props: { systems: this.systems, resultId: this.numericId('/admin/classTestResult/') } }
            if (p.match(/^\/admin\/classTestResult\/[0-9]+$/)) return { file: './pages/ClassTestResultView.vue', props: { systems: this.systems, resultId: this.numericId('/admin/classTestResult/') } }

            // Invoice
            if (p === '/admin/invoice') return { file: './pages/InvoiceIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/invoice/create') return { file: './pages/InvoiceCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/invoice\/[0-9]+$/)) return { file: './pages/InvoiceView.vue', props: { systems: this.systems, invoiceId: this.numericId('/admin/invoice/') } }
            if (p === '/admin/invoice-print') return { file: './pages/InvoicePrint.vue', props: { systems: this.systems } }
            if (p === '/admin/account-wise-payment') return { file: './pages/InvoiceAccountWisePayment.vue', props: { systems: this.systems } }
            if (p === '/admin/account-head-wise-payment') return { file: './pages/InvoiceAccountHeadWisePayment.vue', props: { systems: this.systems } }
            if (p === '/admin/account-summary') return { file: './pages/InvoiceAccountSummary.vue', props: { systems: this.systems } }
            if (p === '/admin/refund-amount') return { file: './pages/InvoiceRefundAmount.vue', props: { systems: this.systems } }

            // Bank Settlement
            if (p === '/admin/bank-settlement') return { file: './pages/BankSettlement.vue', props: { systems: this.systems } }

            // Admission
            if (p === '/admin/admission') return { file: './pages/AdmissionIndex.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/admission\/[0-9]+\/edit$/)) return { file: './pages/AdmissionEdit.vue', props: { systems: this.systems, admissionId: this.numericId('/admin/admission/') } }
            if (p.match(/^\/admin\/admission\/[0-9]+$/)) return { file: './pages/AdmissionView.vue', props: { systems: this.systems, admissionId: this.numericId('/admin/admission/') } }
            if (p === '/admin/admission-account-wise') return { file: './pages/AdmissionAccountWise.vue', props: { systems: this.systems } }
            if (p === '/admin/admission-refund-amount') return { file: './pages/AdmissionRefundAmount.vue', props: { systems: this.systems } }

            // Admission Fee Setup
            if (p === '/admin/admissionFeeSetup') return { file: './pages/AdmissionFeeSetupIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/admissionFeeSetup/create') return { file: './pages/AdmissionFeeSetupCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/admissionFeeSetup\/[0-9]+\/edit$/)) return { file: './pages/AdmissionFeeSetupCreate.vue', props: { systems: this.systems, feeSetupId: this.numericId('/admin/admissionFeeSetup/') } }
            if (p.match(/^\/admin\/admissionFeeSetup\/[0-9]+$/)) return { file: './pages/AdmissionFeeSetupView.vue', props: { systems: this.systems, feeSetupId: this.numericId('/admin/admissionFeeSetup/') } }

            // Certificate Application
            if (p === '/admin/certificateApplication') return { file: './pages/CertificateApplicationIndex.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/certificateApplication\/[0-9]+\/edit$/)) return { file: './pages/CertificateApplicationEdit.vue', props: { systems: this.systems, applicationId: this.numericId('/admin/certificateApplication/') } }
            if (p.match(/^\/admin\/certificateApplication\/[0-9]+$/)) return { file: './pages/CertificateApplicationView.vue', props: { systems: this.systems, applicationId: this.numericId('/admin/certificateApplication/') } }

            // Certificate Template
            if (p === '/admin/certificateTemplate') return { file: './pages/CertificateTemplateIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/certificateTemplate/create') return { file: './pages/CertificateTemplateCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/certificateTemplate\/[0-9]+\/edit$/)) return { file: './pages/CertificateTemplateEdit.vue', props: { systems: this.systems, templateId: this.numericId('/admin/certificateTemplate/') } }
            if (p.match(/^\/admin\/certificateTemplate\/[0-9]+$/)) return { file: './pages/CertificateTemplateView.vue', props: { systems: this.systems, templateId: this.numericId('/admin/certificateTemplate/') } }

            // Online Admission
            if (p === '/admin/onlineAdmission') return { file: './pages/OnlineAdmissionIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/onlineAdmission-rejected') return { file: './pages/OnlineAdmissionRejectedIndex.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/onlineAdmission\/[0-9]+\/edit$/)) return { file: './pages/OnlineAdmissionEdit.vue', props: { systems: this.systems, applicantId: this.numericId('/admin/onlineAdmission/') } }

            if (p === '/admin/onlineAdmissionRollVerify') return { file: './pages/OnlineAdmissionRollVerifyIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/onlineAdmissionRollVerify/create') return { file: './pages/OnlineAdmissionRollVerifyCreate.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/onlineAdmissionRollVerify\/[0-9]+$/)) return { file: './pages/OnlineAdmissionRollVerifyView.vue', props: { systems: this.systems, groupId: this.numericId('/admin/onlineAdmissionRollVerify/') } }

            // Content (CMS)
            const mContentCreate = p.match(/^\/admin\/content\/([^/]+)\/create$/)
            if (mContentCreate) return { file: './pages/ContentCreate.vue', props: { systems: this.systems, slug: decodeURIComponent(mContentCreate[1]) } }
            const mContent = p.match(/^\/admin\/content\/([^/]+)$/)
            if (mContent) return { file: './pages/ContentView.vue', props: { systems: this.systems, slug: decodeURIComponent(mContent[1]) } }

            // Website modules
            const mWebCreate = p.match(/^\/admin\/(slider|videoSlider|bus|calender|class|examR)\/create$/)
            if (mWebCreate) return { file: './pages/WebsiteFileModuleManage.vue', props: { systems: this.systems, module: mWebCreate[1] } }
            const mWebEdit = p.match(/^\/admin\/(slider|videoSlider|bus|calender|class|examR)\/([0-9]+)\/edit$/)
            if (mWebEdit) return { file: './pages/WebsiteFileModuleManage.vue', props: { systems: this.systems, module: mWebEdit[1], id: Number(mWebEdit[2]) } }
            const mWebView = p.match(/^\/admin\/(slider|videoSlider|bus|calender|class|examR)\/([0-9]+)$/)
            if (mWebView) return { file: './pages/WebsiteFileModuleView.vue', props: { systems: this.systems, module: mWebView[1], id: Number(mWebView[2]) } }
            const mWebIndex = p.match(/^\/admin\/(slider|videoSlider|bus|calender|class|examR)$/)
            if (mWebIndex) return { file: './pages/WebsiteFileModuleIndex.vue', props: { systems: this.systems, module: mWebIndex[1] } }

            // Popup/Notice
            if (p === '/admin/popup') return { file: './pages/PopupIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/popup/create') return { file: './pages/PopupManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/popup\/[0-9]+\/edit$/)) return { file: './pages/PopupManage.vue', props: { systems: this.systems, popupId: this.numericId('/admin/popup/') } }
            if (p.match(/^\/admin\/popup\/[0-9]+$/)) return { file: './pages/PopupView.vue', props: { systems: this.systems, popupId: this.numericId('/admin/popup/') } }

            if (p === '/admin/notice') return { file: './pages/NoticeIndex.vue', props: { systems: this.systems } }
            if (p === '/admin/notice/create') return { file: './pages/NoticeManage.vue', props: { systems: this.systems } }
            if (p.match(/^\/admin\/notice\/[0-9]+\/edit$/)) return { file: './pages/NoticeManage.vue', props: { systems: this.systems, noticeId: this.numericId('/admin/notice/') } }
            if (p.match(/^\/admin\/notice\/[0-9]+$/)) return { file: './pages/NoticeView.vue', props: { systems: this.systems, noticeId: this.numericId('/admin/notice/') } }

            return { file: './pages/ComingSoon.vue', props: { title: p } }
        },
        resolvedComponent() {
            const file = this.resolved?.file
            if (file && pages[file]) {
                return defineAsyncComponent(pages[file])
            }
            return defineAsyncComponent(() => import('./pages/ComingSoon.vue'))
        },
        resolvedProps() {
            return this.resolved?.props || {}
        },
    },
    async mounted() {
        window.addEventListener('popstate', this.onPopState)
        window.addEventListener('app-toast', this.onAppToast)
        window.addEventListener('click', this.onGlobalClick)

        try {
            await this.initializeSystems()
        } catch (e) {
            this.error = e?.response?.data?.message || 'Failed to initialize admin panel.'
        } finally {
            this.loading = false
        }
    },
    beforeUnmount() {
        window.removeEventListener('popstate', this.onPopState)
        window.removeEventListener('app-toast', this.onAppToast)
        window.removeEventListener('click', this.onGlobalClick)
    },
    methods: {
        resolveAssetUrl(v) {
            const raw = String(v || '').trim()
            if (!raw) return ''
            if (raw.match(/^https?:\/\//i) || raw.startsWith('data:')) return raw
            if (raw.startsWith('/')) return raw
            const path = raw
                .replace(/^storage\//i, '')
                .replace(/^upload\//i, '')
                .replace(/^public\//i, '')
                .replace(/^\//, '')
            return `/storage/${path}`
        },
        numericId(prefix) {
            const p = String(this.currentPath || '').split('?')[0]
            if (!p.startsWith(prefix)) return null
            const rest = p.slice(prefix.length)
            const idStr = rest.split('/').filter(Boolean)[0] || ''
            const n = Number(idStr)
            return Number.isFinite(n) && n > 0 ? n : null
        },
        onPopState() {
            this.currentPath = `${window.location.pathname || ''}${window.location.search || ''}`
        },
        onAppToast(e) {
            const d = e?.detail || {}
            const message = String(d?.message || '').trim()
            if (!message) return
            const type = String(d?.type || 'success')

            this.toastSeq += 1
            const id = `${Date.now()}-${this.toastSeq}`
            this.toasts = [{ id, type, message }, ...(this.toasts || [])].slice(0, 5)

            const timeout = Number.isFinite(Number(d?.timeout)) ? Number(d?.timeout) : 1500
            window.setTimeout(() => this.removeToast(id), Math.max(300, timeout))
        },
        menuIcon(item, variant = 'parent') {
            const raw = `${item?.icon || ''} ${item?.route_name || ''} ${item?.menu_name || ''} ${item?.name || ''}`.toLowerCase()

            const size = variant === 'child' ? 16 : 18
            const svg = (path) =>
                `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="${path}" /></svg>`

            if (raw.includes('dashboard') || raw.includes('home')) {
                return svg('M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4m8-11v10a1 1 0 01-1 1h-4')
            }
            if (raw.includes('setting') || raw.includes('config')) {
                return svg('M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM19.4 15a7.8 7.8 0 000-6l-2.1.7a6.2 6.2 0 00-1.4-1.4l.7-2.1a7.8 7.8 0 00-6 0l.7 2.1a6.2 6.2 0 00-1.4 1.4L7 9a7.8 7.8 0 000 6l2.1-.7c.4.5.9 1 1.4 1.4L9.8 17.8a7.8 7.8 0 006 0l-.7-2.1c.5-.4 1-.9 1.4-1.4l2.1.7z')
            }
            if (raw.includes('user') || raw.includes('admin') || raw.includes('role') || raw.includes('permission')) {
                return svg('M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8M20 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75')
            }
            if (raw.includes('student') || raw.includes('admission') || raw.includes('academic')) {
                return svg('M22 10l-10-5-10 5 10 5 10-5zM6 12v5c3 3 9 3 12 0v-5')
            }
            if (raw.includes('teacher') || raw.includes('staff')) {
                return svg('M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8')
            }
            if (raw.includes('result') || raw.includes('exam') || raw.includes('grade')) {
                return svg('M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11')
            }
            if (raw.includes('account') || raw.includes('payment') || raw.includes('invoice') || raw.includes('fee')) {
                return svg('M12 1v22M17 5H9.5a3.5 3.5 0 000 7H14a3.5 3.5 0 010 7H6')
            }
            if (raw.includes('report')) {
                return svg('M3 3v18h18M7 14v4M11 10v8M15 6v12')
            }
            return svg('M3 7h18M3 12h18M3 17h18')
        },
        looksLikeQuery(params) {
            const p = String(params || '').trim()
            if (!p) return false
            if (p.startsWith('?') || p.startsWith('&')) return true
            return p.includes('=') || p.includes('?')
        },
        queryFromItem(item) {
            const raw = String(item?.params || '').trim()
            if (!raw) return ''

            if (raw.includes('?')) {
                const q = raw.split('?').slice(1).join('?')
                return q ? `?${q}` : ''
            }

            if (raw.startsWith('?') || raw.startsWith('&')) return raw
            if (raw.includes('=')) return `?${raw}`
            return ''
        },
        removeToast(id) {
            this.toasts = (this.toasts || []).filter((t) => String(t.id) !== String(id))
        },
        onGlobalClick(evt) {
            const header = this.$refs?.adminHeader
            const panel = this.$refs?.megaPanel
            const target = evt?.target
            if (header && target && header.contains(target)) {
                return
            }
            if (panel && target && panel.contains(target)) {
                return
            }
            this.userMenuOpen = false
            if (this.megaMenuOpen) this.closeMegaMenu()
        },
        getChildren(item) {
            const children = item?.childMenus || item?.child_menus || item?.childs
            return Array.isArray(children) ? children : []
        },
        flattenLeafChildren(item) {
            const out = []
            const walk = (n) => {
                const kids = this.getChildren(n)
                if (!kids.length) {
                    out.push(n)
                    return
                }
                for (const k of kids) walk(k)
            }
            for (const child of this.getChildren(item)) walk(child)
            return out
        },
        toggleSection(id) {
            this.openSections = { ...this.openSections, [id]: !this.openSections?.[id] }
        },
        updateMegaPanelPosition(anchorEl) {
            this.$nextTick(() => {
                const headerEl = this.$refs?.adminHeader
                if (!headerEl) return

                const headerRect = headerEl.getBoundingClientRect()
                const headerWidth = headerRect.width || window.innerWidth

                const count = this.activeTopMenu ? this.getChildren(this.activeTopMenu).length : 0

                const margin = 12
                const usableWidth = Math.max(0, headerWidth - margin * 2)

                if ((window.innerWidth || 0) < 1024) {
                    this.megaPanelLeftPx = margin
                    this.megaPanelWidthPx = Math.round(usableWidth)
                    return
                }

                const columns = this.megaMenuSimple ? 1 : Math.min(4, Math.max(1, count || 1))
                this.megaGridColumns = columns
                const colWidth = this.megaMenuSimple ? 320 : 260
                const extra = 48
                const idealWidth = columns * colWidth + extra
                const panelWidth = Math.min(usableWidth, Math.max(320, Math.round(idealWidth)))

                const anchorRect = anchorEl?.getBoundingClientRect?.()
                const anchorLeftWithinHeader = anchorRect ? anchorRect.left - headerRect.left : margin

                let left = Math.round(anchorLeftWithinHeader)
                if (left + panelWidth > headerWidth - margin) {
                    left = Math.round(headerWidth - panelWidth - margin)
                }
                if (left < margin) left = margin

                this.megaPanelLeftPx = left
                this.megaPanelWidthPx = panelWidth
            })
        },
        computeMegaSimple(item) {
            const kids = this.getChildren(item)
            if (!kids.length) return false
            return kids.every((k) => !this.getChildren(k).length)
        },
        openMegaMenu(item, evt) {
            if (!item) return
            if (!this.getChildren(item).length) return
            this.cancelMegaClose()
            this.activeTopMenuId = item.id
            this.megaMenuSimple = this.computeMegaSimple(item)
            this.megaMenuOpen = true

            const anchorEl = evt?.currentTarget
            if (anchorEl) {
                this._lastMegaAnchorEl = anchorEl
                this.updateMegaPanelPosition(anchorEl)
            }
        },
        toggleMegaMenu(item, evt) {
            if (!item) return
            if (!this.getChildren(item).length) {
                this.handleMenuClick(item)
                this.closeMegaMenu()
                return
            }
            this.cancelMegaClose()
            if (String(this.activeTopMenuId) === String(item.id) && this.megaMenuOpen) {
                this.closeMegaMenu()
                return
            }
            this.activeTopMenuId = item.id
            this.megaMenuSimple = this.computeMegaSimple(item)
            this.megaMenuOpen = true

            const anchorEl = evt?.currentTarget
            if (anchorEl) {
                this._lastMegaAnchorEl = anchorEl
                this.updateMegaPanelPosition(anchorEl)
            }
        },
        closeMegaMenu() {
            this.cancelMegaClose()
            this.megaMenuOpen = false
            this.megaMenuSimple = false
        },
        scheduleMegaClose() {
            this.cancelMegaClose()
            this.megaCloseTimer = window.setTimeout(() => {
                this.closeMegaMenu()
            }, 180)
        },
        cancelMegaClose() {
            if (this.megaCloseTimer) {
                window.clearTimeout(this.megaCloseTimer)
                this.megaCloseTimer = null
            }
        },
        routeToPath(routeName, item = null) {
            const name = String(routeName || '')
            if (name === 'admin.index') return '/admin/admin'
            if (name === 'admin.create') return '/admin/admin/create'
            if (name === 'admin.dashboard') return '/admin/dashboard'
            if (name === 'role.index') return '/admin/role'
            if (name === 'role.create') return '/admin/role/create'
            if (name === 'menu.index') return '/admin/menu'
            if (name === 'menu.create') return '/admin/menu/create'
            if (name === 'siteSetting.index') return '/admin/siteSetting'
            if (name === 'activityLog.index') return '/admin/activityLog'
            if (name === 'academicSession.index') return '/admin/academicSession'
            if (name === 'academicSession.create') return '/admin/academicSession/create'
            if (name === 'academicQualification.index') return '/admin/academicQualification'
            if (name === 'academicQualification.create') return '/admin/academicQualification/create'
            if (name === 'academicClass.index') return '/admin/academicClass'
            if (name === 'academicClass.create') return '/admin/academicClass/create'
            if (name === 'department.index') return '/admin/department'
            if (name === 'department.create') return '/admin/department/create'
            if (name === 'videoSlider.index') return '/admin/videoSlider'
            if (name === 'videoSlider.create') return '/admin/videoSlider/create'
            if (name === 'bus.index') return '/admin/bus'
            if (name === 'bus.create') return '/admin/bus/create'
            if (name === 'calender.index') return '/admin/calender'
            if (name === 'calender.create') return '/admin/calender/create'
            if (name === 'class.index') return '/admin/class'
            if (name === 'class.create') return '/admin/class/create'
            if (name === 'examR.index') return '/admin/examR'
            if (name === 'examR.create') return '/admin/examR/create'
            if (name === 'popup.index') return '/admin/popup'
            if (name === 'popup.create') return '/admin/popup/create'
            if (name === 'notice.index') return '/admin/notice'
            if (name === 'notice.create') return '/admin/notice/create'
            if (name === 'notice.officeOrder') return '/admin/notice'
            if (name === 'student.index') return '/admin/student'
            if (name === 'student.create') return '/admin/student/create'
            if (name === 'student.import') return '/admin/student-import'
            if (name === 'studentPromotion.create') return '/admin/studentPromotion/create'
            if (name === 'idcard.index') return '/admin/idcard'
            if (name === 'studentMigrationRollVerify.index') return '/admin/studentMigrationRollVerify'
            if (name === 'studentMigration.index') return '/admin/studentMigration'
            if (name === 'registrationNoVerify.index') return '/admin/registrationNoVerify'
            if (name === 'certificateApplication.index') return '/admin/certificateApplication'
            if (name === 'certificateTemplate.index') return '/admin/certificateTemplate'
            if (name === 'onlineAdmission.index') return '/admin/onlineAdmission'
            if (name === 'onlineAdmission.rejectedList') return '/admin/onlineAdmission-rejected'
            if (name === 'onlineAdmissionRollVerify.index') return '/admin/onlineAdmissionRollVerify'
            if (name === 'onlineAdmissionRollVerify.create') return '/admin/onlineAdmissionRollVerify/create'
            if (name === 'attendance.index') return '/admin/attendance'
            if (name === 'attendance.create') return '/admin/attendance/create'
            if (name === 'attendance.attendanceReport') return '/admin/attendance-report'
            if (name === 'attendance.attendanceSheet') return '/admin/attendance-sheet'
            if (name === 'attendanceSummary.index') return '/admin/attendanceSummary'
            if (name === 'attendanceSummary.create') return '/admin/attendanceSummary/create'
            if (name === 'admitCard.index') return '/admin/admitCard'
            if (name === 'admitCard.create') return '/admin/admitCard/create'
            if (name === 'admin.admitCard.index') return '/admin/admin-admit-card-four-in-one'
            if (name === 'admin.admitCardTwoInOne.index') return '/admin/admin-admit-card-two-in-one'
            if (name === 'seat.card.index') return '/admin/exam-seat-card'
            if (name === 'exam.attendance.sheet') return '/admin/exam-attendance-sheet'
            if (name === 'classTestResult.index') return '/admin/classTestResult'
            if (name === 'classTestResult.create') return '/admin/classTestResult/create'
            if (name === 'result.index') return '/admin/result'
            if (name === 'result.create') return '/admin/result/create'
            if (name === 'result.result') return '/admin/result-report'
            if (name === 'result.subjectwiseResult') return '/admin/subjectwise-result'
            if (name === 'result.tabulationSheet') return '/admin/tabulation-sheet'
            if (name === 'result.tabulationSheetCt') return '/admin/tabulation-sheet-ct'
            if (name === 'result.tabulationSheetV2') return '/admin/tabulation-sheet-v2'
            if (name === 'result.gradeSummary') return '/admin/result-grade-summary'
            if (name === 'invoice.index') return '/admin/invoice'
            if (name === 'invoice.create') return '/admin/invoice/create'
            if (name === 'invoice.print') return '/admin/invoice-print'
            if (name === 'invoice.accountWise') return '/admin/account-wise-payment'
            if (name === 'invoice.accountHeadWise') return '/admin/account-head-wise-payment'
            if (name === 'invoice.accountSummary') return '/admin/account-summary'
            if (name === 'invoice.refundAmount') return '/admin/refund-amount'
            if (name === 'bankSettlement.index') return '/admin/bank-settlement'
            if (name === 'admission.index') return '/admin/admission'
            if (name === 'admission.accountWise') return '/admin/admission-account-wise'
            if (name === 'admission.refundAmount') return '/admin/admission-refund-amount'
            if (name === 'admissionFeeSetup.index') return '/admin/admissionFeeSetup'
            if (name === 'admissionFeeSetup.create') return '/admin/admissionFeeSetup/create'
            if (name === 'content.create') {
                const slug = this.contentSlugFromItem(item) || String(this.currentContentSlug || '')
                return slug ? `/admin/content/${encodeURIComponent(slug)}/create` : ''
            }
            if (name === 'content.show') {
                const slug = this.contentSlugFromItem(item) || String(this.currentContentSlug || '')
                return slug ? `/admin/content/${encodeURIComponent(slug)}` : ''
            }
            return ''
        },
        handleMenuClick(item) {
            let path = this.routeToPath(item?.route_name, item)
            const q = this.queryFromItem(item)
            if (q) {
                if (path.includes('?')) {
                    const extra = q.replace(/^\?/, '').replace(/^&/, '')
                    if (extra) path = `${path}&${extra}`
                } else {
                    const extra = q.startsWith('&') ? `?${q.slice(1)}` : q
                    path = `${path}${extra}`
                }
            }
            if (!path) return

            if ((this.currentPath || '') !== path) {
                window.history.pushState({}, '', path)
                this.currentPath = path
            }

            this.sidebarOpen = false
            this.closeMegaMenu()
        },
        goDashboard() {
            const path = '/admin/dashboard'
            this.userMenuOpen = false
            this.closeMegaMenu()
            if ((this.currentPath || '') !== path) {
                window.history.pushState({}, '', path)
                this.currentPath = path
            }
        },
        hasPermission(routeName) {
            const name = String(routeName || '').trim()
            if (!name) return true
            const perms = this.systems?.permissions
            if (!perms) return true
            if (Array.isArray(perms)) return perms.includes(name)
            if (typeof perms === 'object') {
                if (Object.prototype.hasOwnProperty.call(perms, name)) return !!perms[name]
                return Object.values(perms).includes(name)
            }
            return true
        },
        goUserMenu(key) {
            const k = String(key || '')
            const id = this.systems?.global?.auth_user?.id
            let path = ''
            if (k === 'profile') path = id ? `/admin/admin/${id}` : '/admin/admin'
            if (k === 'admin') path = '/admin/admin'
            if (k === 'role') path = '/admin/role'
            if (k === 'siteSettings') path = '/admin/siteSetting'
            if (k === 'menuList') path = '/admin/menu'
            if (k === 'activityLog') path = '/admin/activityLog'
            if (!path) return

            this.userMenuOpen = false
            if ((this.currentPath || '') !== path) {
                window.history.pushState({}, '', path)
                this.currentPath = path
            }
        },
        async initializeSystems() {
            if (!window.axios) throw new Error('Axios is not available.')

            const liteRes = await window.axios.get('/admin/initialize-systems', { params: { lite: 1 } })
            this.systems = liteRes?.data || {}

            const ids = {}
            const collectIds = (list) => {
                if (!Array.isArray(list)) return
                for (const i of list) {
                    if (i?.id !== undefined && i?.id !== null) ids[i.id] = false
                    collectIds(this.getChildren(i))
                }
            }
            collectIds(this.menuTree)
            this.openSections = ids

            if (!this.activeTopMenuId && Array.isArray(this.menuTree) && this.menuTree.length) {
                this.activeTopMenuId = this.menuTree[0].id
            }

            // load full
            window.setTimeout(async () => {
                try {
                    const res = await window.axios.get('/admin/initialize-systems')
                    const full = res?.data || {}
                    this.systems = {
                        ...this.systems,
                        ...full,
                        global: full.global || this.systems.global,
                        permissions: full.permissions || this.systems.permissions,
                        site: full.site || this.systems.site,
                        menus: full.menus || this.systems.menus,
                    }
                } catch {
                }
            }, 0)
        },
        async logout() {
            this.loggingOut = true
            try {
                await window.axios.post('/admin/logout')
                window.location = `${window.laravel?.baseurl || ''}/admin/loginme`
            } catch {
                this.error = 'Logout failed.'
            } finally {
                this.loggingOut = false
            }
        },
        contentSlugFromItem(item) {
            const raw = item?.slug || item?.content_slug || item?.menu_slug || item?.route_slug || (this.looksLikeQuery(item?.params) ? '' : item?.params) || ''
            const v = String(raw || '').trim()
            if (!v) return ''
            const base = v.split('?')[0].replace(/^\/+/, '').replace(/\/+$/, '')
            const first = base.split('/').filter(Boolean)[0] || ''
            return first
        },
    },
}
</script>
