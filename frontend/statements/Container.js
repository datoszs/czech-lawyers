import React from 'react';
import {RichText, Anchor} from '../containers';
import {StatementHeading} from '../components/statement';
import {STATEMENTS_ADVOCATES, STATEMENTS_PROCEEDINGS, STATEMENTS_CASES} from '../routes';

export default () => (
    <section>
        <Anchor anchor={STATEMENTS_CASES} />
        <StatementHeading><RichText msg="statement.cases" /></StatementHeading>
        <RichText msg="statement.cases.long" />
        <Anchor anchor={STATEMENTS_PROCEEDINGS} />
        <StatementHeading><RichText msg="statement.proceedings" /></StatementHeading>
        <RichText msg="statement.proceedings.long" />
        <Anchor anchor={STATEMENTS_ADVOCATES} />
        <StatementHeading><RichText msg="statement.advocates" /></StatementHeading>
        <RichText msg="statement.advocates.long" />
    </section>
);
