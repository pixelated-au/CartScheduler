<script setup>
    import JetButton from '@/Jetstream/Button.vue'
    import ConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
    import JetInput from '@/Jetstream/Input.vue'
    import JetLabel from '@/Jetstream/Label.vue'
    import axios from 'axios'
    import { onMounted, ref, watch } from 'vue'
    import { HandleDirective as vHandle, SlickItem, SlickList } from 'vue-slicksort'
    import { useToast } from 'vue-toastification'

    const allTags = ref([])
    const getAllTags = async () => {
        const response = await axios.get('/admin/report-tags')
        allTags.value = response.data.data
    }

    onMounted(() => getAllTags())

    const currentTag = ref({})

    const toast = useToast()
    const addTag = async () => {
        if (!currentTag.value.name?.trim()) {
            toast.error('Tag name cannot be empty')
            return
        }
        await axios.post('/admin/report-tags', currentTag.value)

        toast.success('Tag added successfully')
        currentTag.value = {}
        getAllTags()
    }

    const updateTag = async () => {
        if (!currentTag.value.name?.trim()) {
            toast.error('Tag name cannot be empty')
            return
        }
        await axios.put(`/admin/report-tags/${currentTag.value.id}`, currentTag.value)

        toast.success('Tag updated successfully')
        currentTag.value = {}
        getAllTags()
    }

    const selectTag = tag => {
        currentTag.value = tag
    }

    const showDeleteModal = ref(false)

    const deleteTag = () => {
        axios.delete(`/admin/report-tags/${currentTag.value.id}`)
        toast.success('Tag deleted successfully')
        currentTag.value = {}
        getAllTags()
        showDeleteModal.value = false
    }

    watch(allTags, async (val, oldVal) => {
        if (!oldVal || !val.length) {
            return
        }
        await axios.put('/admin/report-tag-sort-order', { ids: allTags.value.map(tag => tag.id) })
    })
</script>

<template>
    <div class="col-span-full bg-gray-100 dark:bg-slate-700 p-6 rounded-lg shadow-lg grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">
                <span class="text-gray-600 dark:text-gray-200">{{ allTags.length }}</span>
                <span class="text-gray-500 dark:text-gray-300 ml-1">Tags</span>
            </h3>
            <p class="text-gray-700 dark:text-gray-300">Total number of tags in the system.</p>
            <p class="text-gray-700 dark:text-gray-300"><em>Used for quickly filling out reports.</em></p>
        </div>
        <div class="px-3">
            <SlickList axis="xy" v-model:list="allTags">
                <SlickItem v-for="(tag, i) in allTags"
                           :key="tag.id"
                           :index="i"
                           class="inline-flex items-center mr-2 mb-2 border border-1 border-gray-400 dark:border-gray-500 rounded">
                    <div v-handle class="px-1 cursor-grab">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             width="16"
                             height="16"
                             class="dark:fill-gray-100">
                            <path fill="none" d="M0 0h24v24H0z"/>
                            <path d="M12 3c-.825 0-1.5.675-1.5 1.5S11.175 6 12 6s1.5-.675 1.5-1.5S12.825 3 12 3zm0 15c-.825 0-1.5.675-1.5 1.5S11.175 21 12 21s1.5-.675 1.5-1.5S12.825 18 12 18zm0-7.5c-.825 0-1.5.675-1.5 1.5s.675 1.5 1.5 1.5 1.5-.675 1.5-1.5-.675-1.5-1.5-1.5z"/>
                        </svg>
                    </div>
                    <button class="px-2 py-1 focus:outline-none dark:text-gray-100 focus:border-gray-900 dark:focus:border-slate-200 focus:ring focus:ring-gray-300 dark:focus:ring-gray-700 leading-none hover:bg-gray-600 hover:text-gray-100 active:bg-gray-900 dark:hover:bg-slate-400 dark:hover:text-gray-800 dark:active:bg-gray-200"
                            @click="selectTag(tag)">
                        {{ tag.name }}
                    </button>
                </SlickItem>
            </SlickList>
        </div>
        <div class="px-6">
            <h4 class="dark:text-gray-100">Add new Tag</h4>
            <JetLabel for="name" value="Name"/>
            <form class="flex flex-wrap" @submit.prevent="addTag">
                <JetInput id="name" v-model="currentTag.name" type="text" class="w-full mb-3" autocomplete="name"/>
                <div class="flex justify-between w-full">
                    <JetButton v-if="currentTag?.id"
                               class=""
                               outline
                               type="button"
                               style-type="warning"
                               @click.prevent="showDeleteModal = true">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             width="16"
                             height="16"
                             class="fill-red-600 dark:fill-red-400">
                            <path fill="none" d="M0 0h24v24H0z"/>
                            <path d="M17 6h5v2h-2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8H2V6h5V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v3zm1 2H6v12h12V8zm-4.586 6l1.768 1.768-1.414 1.414L12 15.414l-1.768 1.768-1.414-1.414L10.586 14l-1.768-1.768 1.414-1.414L12 12.586l1.768-1.768 1.414 1.414L13.414 14zM9 4v2h6V4H9z"/>
                        </svg>
                    </JetButton>
                    <div class="flex">
                        <JetButton v-if="currentTag?.id"
                                   class="mr-3"
                                   type="button"
                                   style-type="primary"
                                   @click.prevent="updateTag">
                            Save
                        </JetButton>
                        <JetButton v-else class="" type="button" style-type="primary" @click.prevent="addTag">
                            Add
                        </JetButton>
                        <JetButton v-if="currentTag?.id"
                                   outline
                                   type="button"
                                   style-type="secondary"
                                   @click.prevent="currentTag = {}">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 width="16"
                                 height="16"
                                 class="dark:fill-gray-100">
                                <path fill="none" d="M0 0h24v24H0z"/>
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm0-9.414l2.828-2.829 1.415 1.415L13.414 12l2.829 2.828-1.415 1.415L12 13.414l-2.828 2.829-1.415-1.415L10.586 12 7.757 9.172l1.415-1.415L12 10.586z"/>
                            </svg>
                        </JetButton>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <ConfirmationModal :show="showDeleteModal">
        <template #title>
            Delete Tag
        </template>
        <template #content>
            <p>Are you sure you want to delete this tag?</p>
        </template>
        <template #footer>
            <JetButton class="mr-3" type="button" style-type="primary" @click.prevent="deleteTag">
                Delete
            </JetButton>
            <JetButton outline type="button" style-type="secondary" @click.prevent="showDeleteModal = false">
                Cancel
            </JetButton>
        </template>
    </ConfirmationModal>
</template>
