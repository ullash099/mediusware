import { toast } from 'react-toastify'
import 'react-toastify/dist/ReactToastify.css'

export function ShowToast(params) {
    if(params.type == 'error'){
        toast.error(params.msg, {
            position: "top-right",
            autoClose: 10000,
            hideProgressBar: false,
            closeOnClick: false,
            progress: undefined,
            theme: "colored"
        });
    }
    else if(params.type == 'warn'){
        toast.warn(params.msg, {
            position: "top-right",
            autoClose: 5000,
            hideProgressBar: false,
            closeOnClick: false,
            progress: undefined,
            theme: "colored"
        });
    }
    else if(params.type == 'info'){
        toast.warn(params.msg, {
            position: "top-right",
            autoClose: 5000,
            hideProgressBar: false,
            closeOnClick: false,
            progress: undefined,
            theme: "colored"
        });
    }
    else{
        toast.success(params.msg, {
            position: "top-right",
            autoClose: 5000,
            hideProgressBar: false,
            closeOnClick: false,
            progress: undefined,
            theme: "colored"
        });
    }
}