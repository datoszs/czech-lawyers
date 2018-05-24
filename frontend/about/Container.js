import React from 'react';
import {PageHeader, Panel} from 'react-bootstrap';
import {StickyLayout} from '../components';
import {Msg, RichText, PageTitle} from '../containers';

const Container = () => (
    <section>
        <PageTitle msg="about.title" />
        <StickyLayout>
            <PageHeader><Msg msg="about.title" /></PageHeader>
        </StickyLayout>
        <StickyLayout sidebar={<Panel>O projektu</Panel>}>
            <RichText msg="about.text" />
        </StickyLayout>
    </section>
);

export default Container;
