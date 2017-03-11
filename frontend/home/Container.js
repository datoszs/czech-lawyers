import React from 'react';
import {Jumbotron, PageHeader} from 'react-bootstrap';
import {Msg} from '../containers';
import autocomplete from '../autocomplete';

const Container = () => (
    <Jumbotron>
        <PageHeader><Msg msg="app.title" /></PageHeader>
        <autocomplete.Container />
    </Jumbotron>
);

export default Container;
