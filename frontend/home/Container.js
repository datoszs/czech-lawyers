import React from 'react';
import {Jumbotron, PageHeader, Row, Col} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import autocomplete from '../autocomplete';
import {getTop, getBottom} from './selectors';
import LeaderBoard from './LeaderBoard';

const Container = () => (
    <section>
        <Jumbotron>
            <PageHeader><Msg msg="app.title" /></PageHeader>
            <p><Msg msg="home.above" /></p>
            <autocomplete.Container />
        </Jumbotron>
        <Row>
            <Col sm={6}><LeaderBoard msg="leaderboard.top" bsStyle="success" selector={getTop} /></Col>
            <Col sm={6}><LeaderBoard msg="leaderboard.bottom" bsStyle="danger" selector={getBottom} /></Col>
        </Row>
        <RichText msg="home.below" />
    </section>
);

export default Container;
