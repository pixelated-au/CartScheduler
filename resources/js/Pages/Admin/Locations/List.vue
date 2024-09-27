<script setup>
import DragDrop from "@/Components/Icons/DragDrop.vue";
import useToast from "@/Composables/useToast.js";
import JetButton from '@/Jetstream/Button.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import {router} from '@inertiajs/vue3';
import {useSortable} from "@vueuse/integrations/useSortable";
import {nextTick, onMounted, onUnmounted, ref, watch} from "vue";

const props = defineProps({
    locations: Object.data,
});

const toast = useToast();

const onNewLocation = () => {
    if (isSortingMode.value) {
        toast.warning('You cannot create a new location while sorting is enabled');
        return;
    }
    router.visit(route('admin.locations.create'));
};

const locationClicked = (location) => {
    if (isSortingMode.value) {
        return;
    }
    router.visit(route('admin.locations.edit', {id: location.id}));
};

const locationsRef = ref(null);
if (!locationsRef) {
    throw new Error('An unexpected error occurred. The locationsRef is not defined. Please report this to the developers.');
}

const storeSortOrder = async (locations) => {
    try {
        await axios.put(route('admin.locations.sort-order'), {
            locations: locations.map(location => location.id),
        });
    } catch (e) {
        return false;
    }

    return true;
};

const moveArrayElement = async (evt) => {
    const from = evt.oldIndex;
    const to = evt.newIndex;

    const locationsArray = props.locations.data;

    if (to >= 0 && to < locationsArray.length) {
        const element = locationsArray.splice(from, 1)[0];
        await nextTick(async () => {
            locationsArray.splice(to, 0, element);
            const success = await storeSortOrder(locationsArray);
            if (!success) {
                const element = locationsArray.splice(to, 1)[0];
                locationsArray.splice(from, 0, element);
                toast.error('There was an error while saving the sort order. The order has been reverted. Please try again.');
            }
        });
    }
};

const {start, stop, option} = useSortable(locationsRef, props.locations.data, {
    animation: 250,
    disabled: true,
    onUpdate: moveArrayElement,
});

const pause = () => {
    option('disabled', true);
};
const resume = () => {
    console.log('resume', option('disabled'), option('animation'));
    option('disabled', false);
};

onMounted(() => start());
onUnmounted(() => stop());

const isSortingMode = ref(false);
watch(isSortingMode, (value) => {
    if (value) {
        resume();
    } else {
        pause();
    }
});

const transitionDelayStyle = (index) => `animation-delay: -${index * 0.37}s`;

</script>

<template>
    <AppLayout title="Locations">
        <template #header>
            <div class="flex justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Locations</h2>
                <div class="flex">
                    <JetButton outline :style-type="isSortingMode ? 'danger' : 'secondary'" class="mx-3"
                               @click="isSortingMode = !isSortingMode">
                        <drag-drop color="currentColor" box="16"/>
                        <span class="ml-3">{{ isSortingMode ? 'Stop sorting' : 'Sort locations' }}</span>
                    </JetButton>
                    <JetButton class="mx-3" style-type="primary" @click="onNewLocation">
                        New Location
                    </JetButton>
                </div>
            </div>
        </template>


        <div
            ref="locationsRef"
            class="max-w-7xl mx-auto pt-10 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div v-for="(location, idx) in locations.data"
                 :key="location.id"
                 class="bg-white dark:bg-slate-700 shadow-xl sm:rounded-lg p-6 cursor-pointer hover:bg-violet-100 hover:transition-colors"
                 :class="{'is-sorting-mode': isSortingMode}"
                 :style="transitionDelayStyle(idx)"
                 @click="locationClicked(location)">
                <div class="flex flex-col justify-between h-full dark:text-gray-100">
                    <div>
                        <h4 class="font-semibold">{{ location.name }}</h4>
                        <div class="line-clamp-3">{{ location.clean_description }}</div>
                    </div>
                    <div>
                        <div class="border-t border-gray-100 dark:border-gray-700 mt-3 pt-3"></div>
                        <h5 class="font-semibold text-center">Shifts</h5>
                        <div class="text-center text-4xl">{{ location.shifts.length }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

.sortable-ghost {
    @apply opacity-40 border-dashed border-2 border-gray-700 dark:border-slate-300;
}

.is-sorting-mode {
    @apply select-none shadow-2xl hover:bg-white dark:hover:bg-slate-700 cursor-grab;
    animation: shake-left-right .9s infinite linear;
}

@keyframes shake-left-right {
    0%, 20%, 40%, 60%, 80%, 100% {
        rotate: -1deg;
        //transform: rotate3d(0, 0, 1, -1deg) translate3d(0, 0, 0);
        //transform: rotate(-1deg) translate3d(0, 0, 0);
    }
    10%, 30%, 50%, 70%, 90% {
        rotate: 1deg;
        //transform: rotate3d(0, 0, 1, 1deg) translate3d(0, 0, 0);
        //transform: rotate(1deg) translate3d(0, 0, 0);
    }
}
</style>
