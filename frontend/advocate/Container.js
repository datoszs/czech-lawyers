import React from 'react';
import {Row, Col} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {PageSubheader} from '../components';
import {TimelineScroll} from '../components/timeline';
import {courts} from '../model';
import Header from './Header';
import Detail from './Detail';
import CakLink from './CakLink';
import StatisticsContainer from './Statistics';
import CourtFilter from './CourtFilter';
import TimelineContainer from './Timeline';
import CaseContainer from './CaseContainer';
import CourtStatistics from './CourtStatistics';

export default () => (
    <section>
        <Header />
        <Row>
            <Col sm={6}>
                <Detail />
                <CakLink />
            </Col>
            <Col sm={6}>
                <StatisticsContainer />
                <Row>
                    <Col sm={4}><CourtStatistics court={courts.NS} /></Col>
                    <Col sm={4}><CourtStatistics court={courts.NSS} /></Col>
                    <Col sm={4}><CourtStatistics court={courts.US} /></Col>
                </Row>
            </Col>
        </Row>
        <PageSubheader><Msg msg="advocate.cases" /></PageSubheader>
        <CourtFilter />
        <TimelineScroll>
            <TimelineContainer />
        </TimelineScroll>
        <CaseContainer />
        <RichText msg="advocate.cases.disclaimer" />
    </section>
);
