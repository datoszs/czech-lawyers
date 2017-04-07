import React from 'react';
import {Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {CaptchaForm, TextAreaField, EmailField} from '../containers/form';
import {sendEmail} from './actions';

export default () => (
    <section>
        <RichText msg="contact.us.text" />
        <CaptchaForm form="contact" action={sendEmail}>
            <EmailField name="email" label="form.email" />
            <TextAreaField name="text" label="contact.form.message" />
            <Button type="submit" bsStyle="primary"><Msg msg="contact.form.submit" /></Button>
        </CaptchaForm>
    </section>
);
