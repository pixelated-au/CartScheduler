<script setup>
    import { computed, nextTick, ref, watch } from 'vue'

    const props = defineProps({
        items: Array,
        label: String,
        uid: String,
    })

    const parentNodes = computed(() => {
        return props.items.map(item => ({
            key: item[props.uid],
            label: item[props.label],
            data: item,
        }))
    })

    const compId = Math.random().toString(36).substring(2, 9)

    const compItems = ref()

    const computeItems = (tabs) => {
        const i = []
        let index = 0
        for (const item of tabs) {
            const key = item.dataset.key
            const id = `accordion-label-${key}-${compId}`
            i.push({
                id: id,
                triggerEl: item.querySelector(`#${id}`),
                targetEl: item.querySelector(`#accordion-body-${key}-${compId}`),
                active: false,
            })
            index++
        }
        // return i
        compItems.value = i
    }

    const options = {
        alwaysOpen: false,
        activeClasses: 'bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-white rounded',
        inactiveClasses: 'text-gray-300 dark:text-gray-400',
    }

    const initAccordion = items => {
        nextTick(() => {
            new Accordion(items, options)
        })
    }

    const skipUnwrap = { myTabs: ref([]) } // hack as per https://github.com/vuejs/core/issues/5525

    watch(skipUnwrap.myTabs, (val) => {
        nextTick(() => {
            if (val && val.length) {
                computeItems(val)
                initAccordion(compItems.value)
            }
        })
    }, { deep: true })
</script>

<template>
    <div class="px-3 py-2 border-md rounded-lg border border-gray-300">
        <div data-accordion="collapse">
            <!-- data-active-classes="bg-purple-400 dark:bg-gray-900 text-gray-900 dark:text-white"
             data-inactive-classes="text-gray-500 dark:text-gray-400" -->
            <div v-for="item in parentNodes" :key="item.key" :ref="skipUnwrap.myTabs" :data-key="item.key">
                <h2 :id="`accordion-label-${item.key}-${compId}`" class="text-xl">
                    <button type="button"
                            class="flex items-center justify-between w-full py-2 px-3 font-bold text-base text-left text-gray-600 border-b border-gray-200 last:border-b-0 dark:border-gray-700 dark:text-gray-400"
                            data-accordion-target="#accordion-flush-body-1"
                            aria-expanded="true"
                            aria-controls="accordion-flush-body-1">
                        <span>{{ item.label }}</span>
                        <svg data-accordion-icon
                             class="w-6 h-6 rotate-180 shrink-0"
                             fill="currentColor"
                             viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </h2>
                <div :id="`accordion-body-${item.key}-${compId}`"
                     class="hidden"
                     aria-labelledby="accordion-flush-heading-1">
                    <div class="py-5 font-light border-b border-gray-200 dark:border-gray-700 flex">
                        <slot :location="item.data"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
