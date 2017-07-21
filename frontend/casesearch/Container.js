import React from 'react';
import {Panel} from 'react-bootstrap';
import {Msg} from '../containers';
import {SearchStatus} from '../containers/search';
import {search} from './modules';
import SearchContainer from './SearchContainer';
import ResultsContainer from './ResultsContainer';
import CurrentSearch from './CurrentSearchContainer';

export default () => (
    <section>
        <header><h1><Msg msg="case.search.title" /></h1></header>
        <Panel><SearchContainer /></Panel>
        <CurrentSearch />
        <ResultsContainer />
        <SearchStatus module={search} />
    </section>
);
