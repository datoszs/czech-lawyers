import React from 'react';
import {Row, Col, ButtonToolbar} from 'react-bootstrap';
import {BackButton} from '../containers';
import Header from './Header';
import Detail from './Detail';
import CakLink from './CakLink';
import Statistics from './Statistics';

export default () => (
    <section>
        <Header />
        <Row>
            <Col sm={6}>
                <ButtonToolbar>
                    <BackButton />
                    <CakLink />
                </ButtonToolbar>
                <Detail />
            </Col>
            <Col sm={6}>
                <Statistics />
            </Col>
        </Row>
    </section>
);
