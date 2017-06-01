import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg} from '../../containers';
import ResultContainer from './ResultContainer';
import CaseButtonContainer from './CaseButtonContainer';

export default () => (
    <section>
        <PageHeader><Msg msg="case.dispute.verify.title" /></PageHeader>
        <ResultContainer />
        <CaseButtonContainer />
    </section>
);
