import 'floating-vue/dist/style.css'
import { useToast as ut } from 'vue-toastification'
//https://vue-toastification.maronato.dev/

const options = {
    position: 'top-center',
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
}

export default function useToast () {
    const toast = ut()

    const error = (message, itemOptions = null) => {
        toast.error(message, { ...options, ...itemOptions })
    }

    const success = (message, itemOptions = null) => {
        toast.success(message, { ...options, ...itemOptions })
    }

    const warning = (message, itemOptions = null) => {
        toast.warning(message, { ...options, ...itemOptions })
    }

    return {
        error,
        success,
        warning,
    }
}
