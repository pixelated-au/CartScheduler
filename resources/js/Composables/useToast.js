import 'floating-vue/dist/style.css';
import {POSITION, TYPE, useToast as ut} from 'vue-toastification';
//https://vue-toastification.maronato.dev/

const options = {
    position: POSITION.TOP_CENTER,
    timeout: 2000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: true,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false,
};

export default function useToast() {
    const toast = ut();

    const error = (message, itemOptions = null) => {
        toast.error(message, {...options, ...itemOptions});
    };

    const success = (message, itemOptions = null) => {
        toast.success(message, {...options, ...itemOptions});
    };

    const warning = (message, itemOptions = null) => {
        toast.warning(message, {...options, ...itemOptions});
    };

    /**
     * @param {TYPE} type
     * @param {string} message
     * @param {object} itemOptions
     */
    const message = (type, message, itemOptions = null) => {
        toast(message, {...options, ...itemOptions, ...{type: type}});
    };

    return {
        error,
        success,
        warning,
        message,
    };
}
