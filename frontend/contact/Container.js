import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {PageSubheader} from '../components';
import SocietyContainer from './SocietyContainer';
import ContactForm from './ContactForm';

const Container = () => (
    <section>
        <PageHeader><Msg msg="contact.title" /></PageHeader>
        <p><Msg msg="contact.subtitle" /></p>
        <SocietyContainer />
        <RichText msg="contact.authors" />
        <PageSubheader><Msg msg="contact.appeal" /></PageSubheader>
        <ContactForm />
    </section>
);

export default Container;
