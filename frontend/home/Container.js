import React from 'react';
import {Jumbotron, PageHeader, Form, FormControl, FormGroup, Button} from 'react-bootstrap';

const Container = () => (
    <Jumbotron
        style={{
            padding: 20,
            margin: 100,
        }}
    >
        <PageHeader>Čeští advokáti.cz</PageHeader>
        <Form inline>
            <FormGroup>
                <FormControl type="text" />
            </FormGroup>
            <Button bsStyle="primary">Hledej</Button>
        </Form>
    </Jumbotron>
);

export default Container;
