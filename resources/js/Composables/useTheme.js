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
        return mapColours('', 500, colours)
    }

    const getBackgroundColours = colours => {
        return mapColours('bg-', 500, colours)
    }

    const getHoverColours = colours => {
        return mapColours('hover:', 600, colours)
    }

    const getBackgroundHoverColours = colours => {
        return mapColours('hover:bg-', 600, colours)
    }

    const getActiveColours = colours => {
        return mapColours('active:', 900, colours)
    }

    const getBackgroundActiveColours = colours => {
        return mapColours('active:', 900, colours)
    }

    return {
        getColours,
        getBackgroundColours,
        getHoverColours,
        getBackgroundHoverColours,
    }
}
