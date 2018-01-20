import React from 'react';
import {Row, Col} from 'react-bootstrap';
import formstatus from '../formstatus';
import {FORM} from './constants';
import Header from './Header';
import Title from './Title';
import Detail from './Detail';
import Documents from './DocumentContainer';
import DisputeButton from './DisputeButton';

const Container = () => (
    <section>
        <Title />
        <Header />
        <Row>
            <Col sm={6}>
                <Detail />
                <formstatus.SuccessContainer formName={FORM} msg="case.dispute.success" />
                <DisputeButton />
            </Col>
            <Col sm={6}>
                <Documents />
            </Col>
        </Row>
    </section>
);

export default Container;
