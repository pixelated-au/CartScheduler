export default function () {
    const baseColors = {
        'primary': 'violet',
        'secondary': 'slate',
        'success': 'green',
        'info': 'blue',
        'danger': 'amber',
        'help': 'purple',
        'warning': 'red',
    }

    /**
     * ************************************************************************
     *  Note: Tailwind styles will need to be added to the safelist in the
     *  tailwind.config.js file
     * ************************************************************************
     * @param {string} prefix - Eg 'bg-' for background-color
     * @param {number} weight - The tailwind weight of the color. Eg 500, 600, etc
     * @param {string[]} colours - primary, secondary, success, info, danger, help, warning
     * @returns {string}
     */
    const mapColours = (prefix = '', weight, ...colours) => {
        if (colours.length === 1) {
            return `${prefix}${baseColors[colours]}-${weight}`
        }
        const c = {}
        colours.forEach(colour => {
            c[colour] = `${prefix}${baseColors[colour]}-${weight}`
        })
        return c
    }
    const getColours = (...colours) => {
        return 'border-transparent text-white ' + mapColours('', 500, colours)
    }

    const getBackgroundColours = colours => {
        return 'border-transparent text-white ' + mapColours('bg-', 500, colours)
    }

    const getHoverColours = colours => {
        return 'border-transparent text-white ' + mapColours('hover:', 600, colours)
    }

    const getBackgroundHoverColours = colours => {
        return 'hover:border-transparent hover:text-white ' + mapColours('hover:bg-', 600, colours)
    }

    const getActiveColours = colours => {
        return 'border-transparent text-white ' + mapColours('active:', 900, colours)
    }

    const getBackgroundActiveColours = colours => {
        return 'active:border-transparent active:text-white ' + mapColours('active:', 900, colours)
    }

    const getOutlineColours = colours => {
        return 'bg-transparent border-2 '
            + mapColours('border-', 600, colours)
            + ' ' + mapColours('dark:border-', 300, colours)
            + ' ' + mapColours('text-', 600, colours)
            + ' ' + mapColours('dark:text-', 300, colours)
    }

    const getOutlineHoverColours = colours => {
        return 'hover:border-transparent'
            + ' ' + mapColours('hover:bg-', 300, colours)
            + ' ' + mapColours('hover:text-', 900, colours)
            + ' ' + mapColours('dark:hover:bg-', 900, colours)
            + ' ' + mapColours('dark:hover:text-', 300, colours)
    }

    return {
        getActiveColours,
        getBackgroundActiveColours,
        getBackgroundColours,
        getBackgroundHoverColours,
        getColours,
        getHoverColours,
        getOutlineHoverColours,
        getOutlineColours,
    }
}
