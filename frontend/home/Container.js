import React from 'react';
import {Jumbotron, Row, Col} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {SmallText} from '../components';
import autocomplete from '../autocomplete';
import {getTop, getBottom} from './selectors';
import LeaderBoard from './LeaderBoard';

const Container = () => (
    <section>
        <Jumbotron>
            <p><Msg msg="home.above" /></p>
            <autocomplete.Container />
            <SmallText><RichText msg="home.below" /></SmallText>
        </Jumbotron>
        <Row>
            <Col sm={6} lg={4}><LeaderBoard msg="leaderboard.top" type="positive" selector={getTop} /></Col>
            <Col sm={6} lg={4}><RichText msg="home.leaderboard" /></Col>
            <Col sm={6} lg={4}><LeaderBoard msg="leaderboard.bottom" type="negative" selector={getBottom} /></Col>
        </Row>
    </section>
);

export default Container;
