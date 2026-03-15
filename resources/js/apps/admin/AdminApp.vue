<template>
    <AdminAppFixed />
</template>

<script>
import AdminAppFixed from './AdminAppFixed.vue'

export default {
    name: 'AdminApp',
    components: {
        AdminAppFixed,
    },
}
</script>

<style>
@media print {
    html,
    body {
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    body * {
        visibility: hidden;
    }

    #printArea,
    #printArea * {
        visibility: visible;
    }

    header,
    aside {
        display: none !important;
    }

    .pointer-events-none.fixed {
        display: none !important;
    }

    .h-screen,
    .min-h-screen {
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
    }

    .min-h-0 {
        min-height: auto !important;
    }

    .overflow-hidden,
    .overflow-y-auto {
        overflow: visible !important;
    }

    main {
        overflow: visible !important;
        padding: 0 !important;
    }
}
</style>
