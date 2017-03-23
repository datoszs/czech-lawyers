import React from 'react';
import {Row, Col, ButtonToolbar} from 'react-bootstrap';
import {BackButton} from '../containers';
import {TimelineScroll} from '../components/timeline';
import Header from './Header';
import Detail from './Detail';
import CakLink from './CakLink';
import StatisticsContainer from './Statistics';
import CourtFilter from './CourtFilter';
import TimelineContainer from './Timeline';
import CaseContainer from './CaseContainer';

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
                <StatisticsContainer />
            </Col>
        </Row>
        <CourtFilter />
        <TimelineScroll>
            <TimelineContainer />
        </TimelineScroll>
        <CaseContainer />
    </section>
);
