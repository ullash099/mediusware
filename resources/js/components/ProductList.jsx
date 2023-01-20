import axios from "axios"
import React from 'react'
import { Button, Card, Col, Pagination, Row, Spinner, Table } from 'react-bootstrap';
import ReactDOM from "react-dom"

export default function ProductList(props) {
    const [isrefreshingList,setRefreshingList] = React.useState(false)

    const [page,setPage] = React.useState({})
    const [datatable,setDatatable] = React.useState({
        infos : {},
        prev_page_url : null,
        last_page_url : null,
        current_page : 0,
        per_page : 0,
        path : ``,
        from : 0,
        to : 0,
        total : 0
    })

    const handleGetStartUpData = async () => {
        setRefreshingList(true)

        await axios.get(`get-products`)
        .then(function (response) {
            let info = response.data
            setPage(info.page)
            setDatatable({
                ...datatable,
                infos : info.datatable.data,
                prev_page_url : info.datatable.prev_page_url,
                last_page_url : info.datatable.last_page_url,
                current_page : info.datatable.current_page,
                per_page : info.datatable.per_page,
                path : info.datatable.path,
                from : info.datatable.from,
                to : info.datatable.to,
                total : info.datatable.total
            })
            setRefreshingList(false)
        })
        .catch(function (error) {
            if(error.request && error.request.status == 401){
              location.reload()
            }
        })
    }

    const handlePaginations = async (page) => {
        let url = datatable.path
        url = `${url}?page=${page}`
        
        await axios.get(url)
        .then(function (response) {
            let info = response.data
            setPage(info.page)

            setDatatable({
                ...datatable,
                infos : info.datatable.data,
                prev_page_url : info.datatable.prev_page_url,
                last_page_url : info.datatable.last_page_url,
                current_page : info.datatable.current_page,
                per_page : info.datatable.per_page,
                path : info.datatable.path,
                from : info.datatable.from,
                to : info.datatable.to,
                total : info.datatable.total
            })
            setRefreshingList(false)
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
        <Row>
            <Col>
                <Card>
                    <Card.Header></Card.Header>
                    <Card.Body>
                        <Table striped responsive bordered size="sm" className='border-success mb-0'>
                            <thead>
                                <tr>
                                    {page.theads && Object.keys(page.theads).length > 0 ? (
                                            Object.values(page.theads).map((thead,th)=>(
                                                <th key={th} style={thead.style} className={thead.class ? thead.class : ''}
                                                >
                                                    {thead.txt}
                                                </th>
                                            ))
                                        ) :(null)
                                    }
                                </tr>
                            </thead>
                            <tbody>
                                {isrefreshingList ? 
                                    (<tr>
                                    <td colSpan={page.theads && Object.keys(page.theads).length} className='text-center'>
                                        <div className='text-center'><Spinner animation="border" /></div>
                                    </td>
                                    </tr>):
                                    (Object.keys(datatable.infos).length > 0 ? 
                                        Object.values(datatable.infos).map((info,index)=>(
                                            <tr key={index}>
                                                <td>
                                                    {/* {index+1} */}
                                                    {datatable.current_page == 1 ? (index+1) :
                                                        ((index+1)+(datatable.per_page*(datatable.current_page-1)))
                                                    }
                                                </td>
                                                <td>
                                                    <p className="m-0">{info.title}</p>
                                                    Created At : {(info.created_at).slice(0, 10)}
                                                </td>
                                                <td>{(info.description).slice(0, 80)}...</td>
                                                <td>
                                                    {Object.keys(info.variants).length > 0 ?
                                                        Object.values(info.variants).map((variant,i)=>(
                                                            <Row key={i}>
                                                                <Col>
                                                                    {variant.variant_one ? `${variant.variant_one.variant} /` : ``}
                                                                    {variant.variant_two ? `${variant.variant_two.variant} /` : ``}
                                                                    {variant.variant_three ? `${variant.variant_three.variant}` : ``}
                                                                </Col>
                                                                <Col>Price : {parseFloat(variant.price).toFixed(2)}</Col>
                                                                <Col>InStock : {parseFloat(variant.stock).toFixed(2)}</Col>
                                                            </Row>
                                                        )
                                                    ):(``)}
                                                </td>
                                                <td className="text-center">
                                                    <Button variant="success">Edit</Button>
                                                </td>
                                            </tr>
                                        ))
                                        :(<tr>
                                            <td colSpan={page.theads && Object.keys(page.theads).length} className="text-center py-3">
                                                <h3>No Data Found</h3>
                                            </td>
                                        </tr>)
                                    )
                                }
                            </tbody>
                        </Table>
                        Showing {datatable.from} to {datatable.to} of {datatable.total}
                    </Card.Body>
                    {datatable.total > 0 ? 
                        <Card.Footer>
                            <Row>
                                <Col>
                                    <Pagination>
                                    {(() => {
                                        const links = [];
                                        for (let i = 1; i <= Math.ceil(datatable.total/datatable.per_page); i++) {
                                            links.push(
                                                <Pagination.Item key={i}
                                                    activeLabel=""
                                                    active={i === datatable.current_page}
                                                    onClick={()=> i === datatable.current_page ? `` : handlePaginations(i)}
                                                >{i}</Pagination.Item>
                                            );
                                        }
                                        return links;
                                    })()}
                                    </Pagination>
                                </Col>
                            </Row>
                        </Card.Footer>
                    :(``)}
                </Card>
            </Col>
        </Row>
    )
}
if (document.getElementById('products')) {  
    const element = document.getElementById('products')
    ReactDOM.render(<ProductList />, element);
}