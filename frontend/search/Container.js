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
                    name="JUDr. Jiří Novák"
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
        <Row>
            <Col md={6}>
                <AdvocateDetail
                    name="JUDr. Dana Nováková"
                    IC="10118675"
                    city="Žďár nad Sázavou "
                    status="active"
                    positive="6"
                    negative="8"
                    neutral="1"
                />
            </Col>
            <Col md={6}>
                <AdvocateDetail
                    name="JUDr. Ivo Novák, Ing."
                    IC="66226465"
                    city=""
                    status="removed"
                    positive="0"
                    negative="0"
                    neutral="0"
                />
            </Col>
        </Row>
    </div>
);

export default Container;
