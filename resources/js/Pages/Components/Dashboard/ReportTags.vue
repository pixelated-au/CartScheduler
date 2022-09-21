<script setup>

    import ReportTagButton from '@/Pages/Components/Dashboard/ReportTagButton.vue'
    import { inject, ref } from 'vue'

    const emit = defineEmits(['toggled'])

    const tags = inject('report-tags', [])

    const enabledTags = ref([])

    const handleToggle = (name, value) => {
        if (value) {
            enabledTags.value.push(name)
        } else {
            enabledTags.value = enabledTags.value.filter(tag => tag !== name)
        }

        emit('toggled', enabledTags.value)
    }
</script>

<template>
    <div class="inline-flex flex-wrap">
        <ReportTagButton v-for="tag in tags" :key="tag.id" :name="tag.name" @toggled="handleToggle(tag.name, $event)"/>
    </div>
</template>
