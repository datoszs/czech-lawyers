import React from 'react';
import {Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import {CaptchaForm, TextAreaField, EmailField, TextField} from '../containers/form';
import {sendEmail} from './actions';

export default () => (
    <section>
        <RichText msg="contact.us.text" />
        <CaptchaForm form="contact" action={sendEmail}>
            <TextField name="full_name" label="form.name" />
            <EmailField name="from" label="form.email" />
            <TextAreaField name="content" label="contact.form.message" />
            <Button type="submit" bsStyle="primary"><Msg msg="contact.form.submit" /></Button>
        </CaptchaForm>
    </section>
);
