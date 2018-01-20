import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText, PageTitle} from '../containers';

const Container = () => (
    <section>
        <PageTitle msg="about.title" />
        <PageHeader><Msg msg="about.title" /></PageHeader>
        <RichText msg="about.text" />
    </section>
);

export default Container;
