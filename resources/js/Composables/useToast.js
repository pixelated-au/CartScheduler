// import "floating-vue/dist/style.css";
import { useToast as ut } from "primevue/usetoast";

// https://vue-toastification.maronato.dev/

const options = {
    group: "default",
    closable: true,
    life: 5000,
    styleClass: undefined,
    contentStyleClass: undefined,
};

export default function useToast() {
    const toast = ut();

    /**
     * @param {string} message
     * @param {?string} title
     * @param {?object|undefined} itemOptions
     */
    const error = (message, title = undefined, itemOptions = undefined) => {
        makeToast("error", message, title, itemOptions);
    };

    /**
     * @param {string} message
     * @param {?string} title
     * @param {?object|undefined} itemOptions
     */
    const success = (message, title = undefined, itemOptions = undefined) => {
        makeToast("success", message, title, itemOptions);
    };

    /**
     * @param {string} message
     * @param {?string} title
     * @param {?object|undefined} itemOptions
     */
    const warning = (message, title = undefined, itemOptions = undefined) => {
        makeToast("warn", message, title, itemOptions);
    };

    /**
     * @param {"success"|"secondary"|"info"|"warn"|"error"|"contrast"} severity
     * @param {string} message
     * @param {?string|undefined} title
     * @param {?object|undefined} itemOptions
     */
    const makeToast = (severity, message, title = undefined, itemOptions = undefined) => {
        const summary = title ? { summary: title } : { summary: "Notice" };
        const overrides = itemOptions ? itemOptions : {};
        if (severity === "danger") {
            severity = "warn";
        }
        toast.add({ ...summary, ...options, ...overrides, ...{ severity, detail: message } });
    };

    return {
        error,
        success,
        warning,
        message: makeToast,
    };
}
