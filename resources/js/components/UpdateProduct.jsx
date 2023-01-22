import axios from "axios"
import React from 'react'
import ReactDOM from "react-dom"

export default function UpdateProduct(props) {

    const handleGetStartUpData = async () => {
        let paths = (window.location.pathname).substring(1).split("/")
        let id = paths[1]
        await axios.get(`/api/product/${id}`)
        .then(function (response) {
            let info = response.data
            console.log(info);
        })
        .catch(function (error) {
            if(error.request && error.request.status == 401){
                location.reload()
            }
        })

    }

    React.useEffect(()=>{
        handleGetStartUpData()
    },[props])

    return (
        <div>UpdateProduct</div>
    )
}
if (document.getElementById('updateProduct')) {  
    const element = document.getElementById('updateProduct')
    ReactDOM.render(<UpdateProduct />, element);
}