import React from 'react';
import {Msg} from '../containers';
import {StatementContainer} from '../components/statement';
import {STATEMENTS_ADVOCATES, STATEMENTS_CASES, STATEMENTS_PROCEEDINGS} from '../routes';
import ProblemStatement from './ProblemStatement';

const params = {
    ending: ' ...',
};

export default () => (
    <StatementContainer>
        <ProblemStatement anchor={STATEMENTS_CASES}><Msg msg="statement.cases" {...params} /></ProblemStatement>
        <ProblemStatement anchor={STATEMENTS_PROCEEDINGS}><Msg msg="statement.proceedings" {...params} /></ProblemStatement>
        <ProblemStatement anchor={STATEMENTS_ADVOCATES}><Msg msg="statement.advocates" {...params} /></ProblemStatement>
    </StatementContainer>
);
