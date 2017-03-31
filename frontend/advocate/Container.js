import React from 'react';
import {Row, Col, ButtonToolbar} from 'react-bootstrap';
import {BackButton, Msg} from '../containers';
import {PageSubheader} from '../components';
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
        <PageSubheader><Msg msg="advocate.cases"/></PageSubheader>
        <CourtFilter />
        <TimelineScroll>
            <TimelineContainer />
        </TimelineScroll>
        <CaseContainer />
    </section>
);
