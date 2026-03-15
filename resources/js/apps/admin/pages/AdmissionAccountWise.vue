<template>
    <AdmissionIndex :systems="systems" mode="accountWise" />
</template>

<script>
import AdmissionIndex from './AdmissionIndex.vue'

export default {
    name: 'AdmissionAccountWise',
    components: { AdmissionIndex },
    props: {
        systems: {
            type: Object,
            default: () => ({}),
        },
    },
}
</script>
