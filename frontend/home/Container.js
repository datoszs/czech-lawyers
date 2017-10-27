import React from 'react';
import {Jumbotron, Row, Col, Alert} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {ApplicationTitle} from '../components';
import autocomplete from '../autocomplete';
import {getTop, getBottom} from './selectors';
import LeaderBoard from './LeaderBoard';
import ProblemStatements from './ProblemStatements';

const Container = () => (
    <section>
        <Jumbotron>
            <RichText msg="home.intro" />
            <ApplicationTitle><Msg msg="app.title" /></ApplicationTitle>
            <RichText msg="home.above" />
            <autocomplete.Container />
            <br />
            <RichText msg="home.cak.search" />
        </Jumbotron>
        <ProblemStatements />
        <RichText msg="leaderboard.legend" />
        <Row>
            <Col sm={0} lg={2} />
            <Col sm={6} lg={4}><LeaderBoard type="positive" selector={getTop} /></Col>
            <Col sm={6} lg={4}><LeaderBoard type="negative" selector={getBottom} /></Col>
        </Row>
        <Alert bsStyle="warning"><Msg msg="home.disclaimer" /></Alert>
    </section>
);

export default Container;
