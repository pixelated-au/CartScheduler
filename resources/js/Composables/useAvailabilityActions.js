import {computed} from "vue";

export default function (form, ranges) {

  const buildRange = (start, end) => {
    const range = []
    for (let i = start; i <= end; i++) {
      range.push(i)
    }
    return range
  }

  function toggleRosterDay(day) {
    return computed({
      get: () => form[`num_${day}s`] > 0,
      set: value => form[`num_${day}s`] = (value ? 1 : 0),
    })
  }


  function computedRange(day) {
    return computed({
      get: () => {
        return [form[`day_${day}`][0], form[`day_${day}`][form[`day_${day}`].length - 1]];
      },
      set: (value) => {
        form[`day_${day}`] = form[`num_${day}s`] > 0
          ? buildRange(value[0], value[1])
          : buildRange([ranges.value.start, ranges.value.end]);
      },
    })
  }

  function tooltipFormat(value) {
    value = Math.round(value) // seems to be a rounding issue with the slider. This fixes it.
    if (value < 12) {
      return `${value}am`
    }
    if (value === 12) {
      return `${value}pm`
    }
    return `${value - 12}pm`
  }

  const numberOfWeeks = [
    {value: 1, label: '1 week/month'},
    {value: 2, label: '2 weeks/month'},
    {value: 3, label: '3 weeks/month'},
    {value: 4, label: 'Every week'},
  ]

  return {
    toggleRosterDay,
    computedRange,
    tooltipFormat,
    numberOfWeeks,
  }
}
