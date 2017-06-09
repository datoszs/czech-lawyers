import React from 'react';
import {Jumbotron, PageHeader, Row, Col} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import autocomplete from '../autocomplete';
import {getTop, getBottom} from './selectors';
import LeaderBoard from './LeaderBoard';

const Container = () => (
    <section>
        <Jumbotron>
            <h1><Msg msg="app.title" /></h1>
            <p><Msg msg="home.above" /></p>
            <autocomplete.Container />
        </Jumbotron>
        <Row>
            <Col sm={0} lg={2} />
            <Col sm={6} lg={4}><LeaderBoard msg="leaderboard.top" type="positive" selector={getTop} /></Col>
            <Col sm={6} lg={4}><LeaderBoard msg="leaderboard.bottom" type="negative" selector={getBottom} /></Col>
        </Row>
        <RichText msg="home.below" />
    </section>
);

export default Container;
