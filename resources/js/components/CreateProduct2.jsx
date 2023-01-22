import axios from "axios"
import React from 'react'
import ReactDOM from "react-dom"
import { Button, Card, Col, Row } from 'react-bootstrap'
import { useDropzone } from "react-dropzone"
import { TagsInput } from "react-tag-input-component"

export default function CreateProduct(props) {
    const [variants, setVariants] = React.useState([]);
    const [product,setProduct] = React.useState({
        title: ``,
        sku: ``,
        description: ``,
        product_image: [],
        product_variant: [],
        product_variant_prices: []
    })

    const onDrop = React.useCallback(acceptedFiles => {
        const images = acceptedFiles.map(file =>
            Object.assign(file, {
                preview: URL.createObjectURL(file)
            })
        )
        setProduct({
            ...product,
            product_image : [product.product_image, ...images]
        })
    }, []);

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        accept: { "image/*" : [] },
        onDrop
    });

    const handleGetStartUpData = async () => {
        await axios.get("/api/variants")
        .then(response => {
            let infos = response.data 
            let variants = []

            Object.values(infos).map(info=>{
                variants.push({
                    option : info.id,
                    tags: []
                })
            })
            
            setProduct({
                ...product,
                product_variant : variants
            })
            setVariants(infos)
        })
    }

    React.useEffect(()=>{
        handleGetStartUpData()
    },[props])

    console.log(product)

    return (
        <section>
            <Row>
                <Col md={6}>
                    <Card className='shadow mb-4'>
                        <Card.Body>
                            <div className="form-group">
                                <label htmlFor="">Product Name</label>
                                <input type="text"
                                    placeholder="Product Name"
                                    className="form-control"
                                    onChange={e => setProduct({
                                        ...product,
                                        title : e.target.value
                                    })}
                                />
                            </div>

                            <div className="form-group">
                                <label htmlFor="">Product SKU</label>
                                <input type="text"
                                    placeholder="Product SKU"
                                    className="form-control"
                                    onChange={e => setProduct({
                                        ...product,
                                        sku : e.target.value
                                    })}
                                />
                            </div>

                            <div className="form-group">
                                <label htmlFor="">Description</label>
                                <textarea cols="30" rows="4"
                                    className="form-control"
                                    onChange={e => setProduct({
                                        ...product,
                                        description : e.target.value
                                    })}
                                ></textarea>
                            </div>
                        </Card.Body>
                    </Card>

                    <Card className='shadow mb-4'>
                        <Card.Header className='font-weight-bold text-primary'>Media</Card.Header>
                        <Card.Body>
                            <div className="card-body border"
                                {...getRootProps({ className: "dropzone" })}
                            >
                                <input className="p-5" {...getInputProps()} />
                                <p className="p-5 text-center m-3 border">
                                    Drop files here to upload
                                </p>
                            </div>
                        </Card.Body>
                    </Card>
                </Col>
                <Col md={6}>
                    <Card className='shadow mb-4'>
                        <Card.Header className='font-weight-bold text-primary'>Variants</Card.Header>
                        <Card.Body>
                            {product.product_variant.map((item, index) => (
                                <Row key={index}>
                                    <Col md={4}>
                                        <div className="form-group">
                                            <label htmlFor="">Option</label>
                                            <select
                                                className="form-control"
                                                onChange={e =>
                                                    handleVariantChange(
                                                        index,
                                                        e.target.value
                                                    )
                                                }
                                            >
                                                {variants.map((variant,vi) => (
                                                    <option key={vi} value={variant.id}>
                                                        {variant.title}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                    </Col>
                                    <Col md={8}>
                                        <div className="form-group">
                                            <label
                                                className="float-right text-primary"
                                                style={{
                                                    cursor: "pointer"
                                                }}
                                                onClick={() =>
                                                    handleVariantRemove(index)
                                                }
                                            >
                                                Remove
                                            </label>
                                            <label>.</label>
                                            <TagsInput
                                                value={item.tags}
                                                onChange={_tags =>
                                                    handleVariantTagAdd(
                                                        index,
                                                        _tags
                                                    )
                                                }
                                                className="form-control"
                                            />
                                        </div>
                                    </Col>
                                </Row>
                            ))}
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
            <Button>Save</Button>
            <Button variant='secondary' className='ml-2'>Cancel</Button>
        </section>
    )
}
if (document.getElementById('createProduct')) {  
    const element = document.getElementById('createProduct')
    ReactDOM.render(<CreateProduct />, element);
}