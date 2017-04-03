import React from 'react';
import {PageHeader} from 'react-bootstrap';
import {Msg, RichText} from '../containers';
import {PageSubheader} from '../components';

const Container = () => (
    <section>
        <PageHeader><Msg msg="contact.title" /></PageHeader>
        <p><Msg msg="contact.subtitle" /></p>
        <dl>
            <dd>DATOS &mdash; data o spravedlnosti, z. S.</dd>
            <dd>IČ 05003997</dd>
            <dd>Fleischnerova 20</dd>
            <dd>635 00 Brno</dd>
            <dd><a href="mailto:info@cestiadvokati.cz">info@cestiadvokati.cz</a></dd>
        </dl>
        <RichText msg="contact.authors" />
        <PageSubheader><Msg msg="contact.appeal" /></PageSubheader>
        <RichText msg="contact.us.text" />
    </section>
);

export default Container;
