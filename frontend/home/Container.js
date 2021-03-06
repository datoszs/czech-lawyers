import React from 'react';
import {Jumbotron, Row, Alert} from 'react-bootstrap';
import {Msg, RichText, DefaultPageTitle} from '../containers';
import {ApplicationTitle} from '../components';
import autocomplete from '../autocomplete';
import {courts} from '../model';
import LeaderBoardColumn from './LeaderBoardColumn';
import ProblemStatements from './ProblemStatements';

const Container = () => (
    <section>
        <DefaultPageTitle />
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
            <LeaderBoardColumn court={courts.NS} />
            <LeaderBoardColumn court={courts.NSS} />
            <LeaderBoardColumn court={courts.US} />
        </Row>
        <Alert bsStyle="warning"><Msg msg="home.disclaimer" /></Alert>
    </section>
);

export default Container;
