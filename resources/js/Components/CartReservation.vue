<script setup>
    //https://vue3datepicker.com/
    import Datepicker from '@vuepic/vue-datepicker'
    import { ref } from 'vue'

    const date = ref()
</script>

<template>
    <div>
        <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
            <Datepicker inline auto-apply :enable-time-picker="false" v-model="date">
                <template #day="{day, date}">
                    <pre class="text-sm">{{ day }}</pre>
                </template>
            </Datepicker>
            <pre>{{ date }}</pre>
        </div>
    </div>
</template>
