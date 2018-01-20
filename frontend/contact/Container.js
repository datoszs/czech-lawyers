import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText, Anchor, PageTitle} from '../containers';
import {PageSubheader} from '../components';
import {FORM_ANCHOR} from './constants';
import SocietyContainer from './SocietyContainer';
import ContactForm from './ContactForm';

const Container = () => (
    <section>
        <PageTitle msg="contact.title" />
        <PageHeader><Msg msg="contact.title" /></PageHeader>
        <p><Msg msg="contact.subtitle" /></p>
        <SocietyContainer />
        <RichText msg="contact.authors" />
        <Anchor anchor={FORM_ANCHOR} />
        <PageSubheader><Msg msg="contact.appeal" /></PageSubheader>
        <ContactForm />
    </section>
);

export default Container;
