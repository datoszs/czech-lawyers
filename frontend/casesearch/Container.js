import React from 'react';
import {Panel} from 'react-bootstrap';
import SearchContainer from './SearchContainer';
import ResultsContainer from './ResultsContainer';

export default () => (
    <section>
        <Panel><SearchContainer /></Panel>
        <ResultsContainer />
    </section>
);
