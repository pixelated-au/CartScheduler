<script setup>
    import { defineProps, getCurrentInstance, onMounted, ref } from 'vue'

    defineProps({
        tabList: {
            type: Array,
            required: true,
        },

        variant: {
            type: String,
            required: false,
            default: () => 'horizontal',
            validator: (value) => ['horizontal', 'vertical'].includes(value),
        },
    })

    let compId
    onMounted(() => {
        compId = getCurrentInstance().id || Math.random().toString(36).substring(2, 9)
    })

    const activeTab = ref(1)
    // Individual tabs are defined like <template v-slot:tab-1>...</template>
    // Tab names need to be defined also:     const tabNames = ['Location', 'Shifts']
</script>

<template>
    <div :class="{
      flex: variant === 'vertical',
    }">
        <ul class="list-none bg-blue-900 bg-opacity-30 p-1.5 rounded-lg text-center overflow-auto whitespace-nowrap"
            :class="{
        flex: variant === 'horizontal',
      }">
            <li v-for="(tab, index) in tabList" :key="index" class="w-full px-4 py-1.5 rounded-lg list-none" :class="{
          'text-blue-600 bg-white shadow-xl': index + 1 === activeTab,
          'text-white': index + 1 !== activeTab,
        }">
                <label :for="`${compId}${index}`" v-text="tab" class="cursor-pointer block"/>
                <input :id="`${compId}${index}`"
                       type="radio"
                       :name="`${compId}-tab`"
                       :value="index + 1"
                       v-model="activeTab"
                       class="hidden"/>
            </li>
        </ul>

        <template v-for="(tab, index) in tabList">
            <div :key="index" v-if="index + 1 === activeTab" class="flex-grow bg-white rounded-lg shadow-xl p-4">
                <slot :name="`tab-${index + 1}`"></slot>
            </div>
        </template>
    </div>
</template>
