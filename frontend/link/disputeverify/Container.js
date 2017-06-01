import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg} from '../../containers';
import ResultContainer from './ResultContainer';
import CaseButton from './CaseButton';

export default () => (
    <section>
        <PageHeader><Msg msg="case.dispute.verify.title" /></PageHeader>
        <ResultContainer />
        <CaseButton />
    </section>
);
