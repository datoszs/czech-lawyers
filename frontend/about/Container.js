import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText} from '../containers';

const Container = () => (
    <section>
        <PageHeader><Msg msg="about.title" /></PageHeader>
        <RichText msg="about.text" />
    </section>
);

export default Container;
