import React from 'react';
import {Jumbotron, Row, Alert} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import autocomplete from '../autocomplete';
import {courts} from '../model';
import LeaderBoardColumn from './LeaderBoardColumn';
import ProblemStatements from './ProblemStatements';

const Container = () => (
    <section>
        <Jumbotron>
            <h1><Msg msg="app.title" /></h1>
            <RichText msg="home.above" />
            <autocomplete.Container />
            <br />
            <RichText msg="home.cak.search" />
        </Jumbotron>
        <ProblemStatements />
        <RichText msg="leaderboard.legend" />
        <Row>
            <LeaderBoardColumn court={courts.NS} />
            <LeaderBoardColumn court={courts.NSS} />
            <LeaderBoardColumn court={courts.US} />
        </Row>
        <Alert bsStyle="warning"><Msg msg="home.disclaimer" /></Alert>
    </section>
);

export default Container;
