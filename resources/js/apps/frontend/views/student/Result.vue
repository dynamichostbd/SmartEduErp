<template>
    <div class="rounded-sm border border-slate-300 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50/50 p-4">
            <h2 class="text-lg font-bold text-slate-800">Exam Results</h2>
        </div>
        <div class="p-6">
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Academic Year</label>
                    <select v-model="filter.session_id" class="w-full rounded-sm border border-slate-300 p-2 text-sm outline-none">
                        <option value="">Select Year</option>
                        <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Examination</label>
                    <select v-model="filter.exam_id" class="w-full rounded-sm border border-slate-300 p-2 text-sm outline-none">
                        <option value="">Select Exam</option>
                        <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button 
                        class="w-full rounded-sm bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600 transition-colors"
                        @click="search"
                    >
                        Search Result
                    </button>
                </div>
            </div>

            <div v-if="searching" class="text-center py-10">Fetching results...</div>
            <div v-else-if="!resultFound" class="text-center py-12 border-2 border-dashed border-slate-100 rounded-sm">
                <div class="text-slate-300 mb-2">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"></path></svg>
                </div>
                <div class="text-sm font-medium text-slate-500">Please select criteria to view your results.</div>
            </div>

            <div v-else class="space-y-6">
                <!-- Result Display Table -->
                <div class="rounded-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-slate-600 border-b border-slate-200">
                                <th class="px-4 py-3 font-semibold">Subject</th>
                                <th class="px-4 py-3 font-semibold text-center">Mark</th>
                                <th class="px-4 py-3 font-semibold text-center">Grade</th>
                                <th class="px-4 py-3 font-semibold text-center">Point</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 italic">
                            <tr v-for="row in results" :key="row.id">
                                <td class="px-4 py-3 text-slate-900 font-medium">{{ row.subject_name }}</td>
                                <td class="px-4 py-3 text-center text-slate-600">{{ row.mark }}</td>
                                <td class="px-4 py-3 text-center font-bold" :class="row.grade === 'F' ? 'text-red-500' : 'text-emerald-600'">{{ row.grade }}</td>
                                <td class="px-4 py-3 text-center text-slate-600 font-mono">{{ row.point }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right text-slate-700">GPA :</td>
                                <td colspan="2" class="px-4 py-3 text-center text-emerald-700 text-lg font-mono italic">5.00</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject, computed } from 'vue'

const studentProfileSystems = inject('studentProfileSystems', {})
const sessions = computed(() => studentProfileSystems.value?.academic_sessions || [])
const exams = ref([
    { id: 1, name: 'First Term' },
    { id: 2, name: 'Final Exam' }
])

const filter = ref({
    session_id: '',
    exam_id: ''
})

const searching = ref(false)
const resultFound = ref(false)
const results = ref([])

const search = () => {
    searching.value = true
    window.setTimeout(() => {
        searching.value = false
        resultFound.value = true
        results.value = [
            { id: 1, subject_name: 'Bangla', mark: 85, grade: 'A+', point: '5.00' },
            { id: 2, subject_name: 'English', mark: 78, grade: 'A', point: '4.00' }
        ]
    }, 800)
}
</script>
