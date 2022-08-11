import 'floating-vue/dist/style.css'
import { useToast as ut } from 'vue-toastification'

const options = {
    position: 'top-center',
    timeout: 5000,
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

    const error = message => {
        toast.error(message, options)
    }

    const success = message => {
        toast.success(message, options)
    }

    const warning = message => {
        toast.warning(message, options)
    }

    return {
        error,
        success,
        warning,
    }
}
