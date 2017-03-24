import React from 'react';
import {Row, Col} from 'react-bootstrap';
import Header from './Header';
import Detail from './Detail';
import Documents from './DocumentContainer';

const Container = () => (
    <section>
        <Header />
        <Row>
            <Col sm={6}>
                <Detail />
            </Col>
            <Col sm={6}>
                <Documents />
            </Col>
        </Row>
    </section>
);

export default Container;
