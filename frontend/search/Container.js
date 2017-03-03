import React from 'react';
import {
    Panel,
    Row,
    Col,
    FormGroup,
    FormControl,
    Form,
    Button,
} from 'react-bootstrap';
import AdvocateDetail from './AdvocateDetail';

const Container = () => (
    <div>
        <Panel>
            <Form inline>
                <FormGroup><FormControl type="text" value="Novák" /></FormGroup>
                <Button bsStyle="primary">Hledej</Button>
                <Button style={{float: 'right'}}>Table</Button>
            </Form>
        </Panel>
        <Row>
            <Col md={6}>
                <AdvocateDetail
                    name="Judr. Jiří Novák"
                    status="active"
                    city="Praha 2"
                    IC="66203147"
                    positive={51}
                    negative={12}
                    neutral={3}
                />
            </Col>
            <Col md={6}>
                <AdvocateDetail
                    name="Mgr. Šárka Nováková"
                    status="suspended"
                    city="Olomouc"
                    IC="01175530"
                    positive={5}
                    negative={6}
                    neutral={0}
                />
            </Col>
        </Row>
    </div>
);

export default Container;
