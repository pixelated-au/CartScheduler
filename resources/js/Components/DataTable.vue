<script setup>
    import EasyDataTable from 'vue3-easy-data-table'

    defineProps({
        headers: Array,
        items: Array,
        searchField: String,
        searchValue: String,
    })

    defineEmits(['click-row'])

</script>

<template>
    <EasyDataTable buttons-pagination
                   :headers="headers"
                   :items="items"
                   :search-field="searchField"
                   :search-value="searchValue"
                   theme-color="rgb(55 65 81)"
                   table-class-name="data-table"
                   body-row-class-name="data-table-row"
                   @click-row="$emit('click-row', $event)">
        <template v-for="(slot, index) of Object.keys($slots)" :key="index" v-slot:[slot]="data">
            <slot :name="slot" v-bind="data"></slot>
        </template>

    </EasyDataTable>
</template>

<style lang="scss">
@import 'vue3-easy-data-table/dist/style.css';

.data-table {
    --tw-th-bg: rgb(221 214 254);
    --tw-bg-200: rgb(237 233 254);
    --tw-bg-900: rgb(17 24 39);

    --easy-table-border: none;
    --easy-table-header-font-size: 1rem;
    --easy-table-header-item-padding: 1rem 15px;
    --easy-table-header-background-color: var(--tw-th-bg);

    --easy-table-body-row-font-size: 1rem;
    --easy-table-row-border: none;
    --easy-table-body-item-padding: .8rem 15px;

    --easy-table-body-row-hover-background-color: var(--tw-bg-200);
    --easy-table-body-row-hover-font-color: var(--tw-bg-900);

    --easy-table-footer-font-size: 1rem;

    table {
        cursor: pointer;
        border-collapse: separate;

        thead tr {
            th:first-child {
                @apply rounded-l-lg;
            }

            th:last-child {
                @apply rounded-r-lg;
            }
        }

        tbody tr:hover {
            td:first-child {
                @apply rounded-l-lg;
            }

            td:last-child {
                @apply rounded-r-lg;
            }
        }
    }

    .vue3-easy-data-table__footer {
        .easy-data-table__rows-selector {
            .rows-input__wrapper {
                border-bottom-width: 0;
            }
        }

        ul.select-items.show {
            @apply rounded-lg;

            li {
                text-align: center;

                &:first-child {
                    @apply rounded-t-lg;
                }

                &:last-child {
                    @apply rounded-b-lg;
                }
            }
        }
    }
}

</style>
