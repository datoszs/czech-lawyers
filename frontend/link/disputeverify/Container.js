import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, PageTitle} from '../../containers';
import ResultContainer from './ResultContainer';
import CaseButton from './CaseButton';

export default () => (
    <section>
        <PageTitle msg="case.dispute.verify.title" />
        <PageHeader><Msg msg="case.dispute.verify.title" /></PageHeader>
        <ResultContainer />
        <CaseButton />
    </section>
);
