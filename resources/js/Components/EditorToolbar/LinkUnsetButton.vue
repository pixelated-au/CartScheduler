<script setup>import BaseButton from '@/Components/EditorToolbar/BaseButton.vue';</script>

<template>
    <BaseButton action="unsetLink" tooltip="Remove link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" class="dark:fill-gray-100">
            <path fill="none" d="M0 0h24v24H0z"/>
            <path
                d="M17 17h5v2h-3v3h-2v-5zM7 7H2V5h3V2h2v5zm11.364 8.536L16.95 14.12l1.414-1.414a5 5 0 1 0-7.071-7.071L9.879 7.05 8.464 5.636 9.88 4.222a7 7 0 0 1 9.9 9.9l-1.415 1.414zm-2.828 2.828l-1.415 1.414a7 7 0 0 1-9.9-9.9l1.415-1.414L7.05 9.88l-1.414 1.414a5 5 0 1 0 7.071 7.071l1.414-1.414 1.415 1.414zm-.708-10.607l1.415 1.415-7.071 7.07-1.415-1.414 7.071-7.07z"/>
        </svg>
    </BaseButton>
</template>
