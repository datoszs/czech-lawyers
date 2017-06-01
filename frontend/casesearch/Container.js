import React from 'react';
import {Panel} from 'react-bootstrap';
import {Msg} from '../containers';
import SearchContainer from './SearchContainer';
import ResultsContainer from './ResultsContainer';

export default () => (
    <section>
        <header><h1><Msg msg="case.search.title" /></h1></header>
        <Panel><SearchContainer /></Panel>
        <ResultsContainer />
    </section>
);
