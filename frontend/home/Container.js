import React from 'react';
import {Jumbotron, PageHeader, Form, FormControl, FormGroup, Button} from 'react-bootstrap';
import {Msg} from '../containers';

const Container = () => (
    <Jumbotron
        style={{
            padding: 20,
            margin: 100,
        }}
    >
        <PageHeader><Msg msg="app.title" /></PageHeader>
        <Form inline>
            <FormGroup>
                <FormControl type="text" />
            </FormGroup>
            <Button bsStyle="primary"><Msg msg="button.search" /></Button>
        </Form>
    </Jumbotron>
);

export default Container;
