import React from 'react';
import {Jumbotron, PageHeader} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import autocomplete from '../autocomplete';

const Container = () => (
    <section>
        <Jumbotron>
            <PageHeader><Msg msg="app.title" /></PageHeader>
            <p><Msg msg="home.above" /></p>
            <autocomplete.Container />
        </Jumbotron>
        <RichText msg="home.below" />
    </section>
);

export default Container;
