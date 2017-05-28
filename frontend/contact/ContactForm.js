import React from 'react';
import {Button} from 'react-bootstrap';
import {RichText, Msg} from '../containers';
import formstatus from '../formstatus';
import {CaptchaForm, TextAreaField, EmailField, TextField} from '../containers/form';
import {sendEmail} from './actions';
import {CONTACT_FORM} from './constants';

export default () => (
    <section>
        <RichText msg="contact.us.text" />
        <formstatus.Container formName={CONTACT_FORM} />
        <CaptchaForm form={CONTACT_FORM} action={sendEmail}>
            <TextField name="full_name" label="form.name" required />
            <EmailField name="from" label="form.email" required />
            <TextAreaField name="content" label="contact.form.message" required />
            <Button type="submit" bsStyle="primary"><Msg msg="contact.form.submit" /></Button>
        </CaptchaForm>
    </section>
);
