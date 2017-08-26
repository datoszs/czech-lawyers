import React from 'react';
import {Jumbotron, Row, Col, Alert} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import autocomplete from '../autocomplete';
import {getTop, getBottom} from './selectors';
import LeaderBoard from './LeaderBoard';
import ProblemStatements from './ProblemStatements';

const Container = () => (
    <section>
        <Jumbotron>
            <h1><Msg msg="app.title" /></h1>
            <p><Msg msg="home.above" /></p>
            <autocomplete.Container />
            <RichText msg="home.cak.search" />
        </Jumbotron>
        <ProblemStatements />
        <RichText msg="home.below" />
        <hr />
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
