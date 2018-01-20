import React from 'react';
import {RichText, Anchor, Msg} from '../containers';
import {StatementHeading} from '../components/statement';
import {STATEMENTS_ADVOCATES, STATEMENTS_PROCEEDINGS, STATEMENTS_CASES} from '../routes';

const params = {
    ending: '.',
};

export default () => (
    <section>
        <Anchor anchor={STATEMENTS_CASES} />
        <StatementHeading><RichText msg="statement.cases" {...params} /></StatementHeading>
        <RichText msg="statement.cases.long" />
        <Anchor anchor={STATEMENTS_PROCEEDINGS} />
        <StatementHeading><Msg msg="statement.proceedings" {...params} /></StatementHeading>
        <RichText msg="statement.proceedings.long" />
        <Anchor anchor={STATEMENTS_ADVOCATES} />
        <StatementHeading><Msg msg="statement.advocates" {...params} /></StatementHeading>
        <RichText msg="statement.advocates.long" />
    </section>
);
