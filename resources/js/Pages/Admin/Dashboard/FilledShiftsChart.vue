<script setup>
    import { BarChart } from 'echarts/charts'
    import { GridComponent, LegendComponent, TitleComponent, TooltipComponent } from 'echarts/components'
    import { use } from 'echarts/core'
    import { CanvasRenderer } from 'echarts/renderers'
    import { computed, inject, provide } from 'vue'
    import VChart, { THEME_KEY } from 'vue-echarts'

    const props = defineProps({
        shiftData: Array,
    })

    use([CanvasRenderer, TitleComponent, TooltipComponent, LegendComponent, BarChart, GridComponent])

    const isDarkMode = inject('darkMode', false)

    const themeKey = computed(() => {
        return isDarkMode.value ? 'dark' : 'light'
    })

    provide(THEME_KEY, themeKey)
    // provide(THEME_KEY, {
    //     color: ['#3398DB'],
    //     backgroundColor: '#fff',
    // })

    const shiftsData = computed(() => ({
        // title: {
        //     text: 'Filled Shifts',
        //     left: 'center',
        // },
        responsive: true,
        maintainAspectRatio: false,
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b}: {c}',
        },
        xAxis: {
            data: props.shiftData.map((item) => item.date),
            axisLabel: {
                interval: 0,
                rotate: 45,
            },
        },
        yAxis: {},
        series: [
            {
                data: props.shiftData.map((item) => item.shifts_filled),
                type: 'bar',
                stack: 'x',
                name: 'Filled Shifts',
            },
            {
                data: props.shiftData.map((item) => item.shifts_available - item.shifts_filled),
                type: 'bar',
                stack: 'x',
                name: 'Shifts Available',
            },
        ],
    }))
</script>

<template>
    <VChart class="shifts-chart" :option="shiftsData"/>
</template>
